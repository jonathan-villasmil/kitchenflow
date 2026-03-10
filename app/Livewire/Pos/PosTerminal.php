<?php

namespace App\Livewire\Pos;

use App\Models\CashRegister;
use App\Models\CashRegisterTransaction;
use App\Models\Dish;
use App\Models\MenuCategory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Table;
use Livewire\Component;

class PosTerminal extends Component
{
    // ─── Table Selection ───────────────────────────────────────────────
    public ?int $selectedTableId = null;
    public string $orderType = 'dine_in'; // dine_in, takeaway, delivery

    // ─── Menu Navigation ───────────────────────────────────────────────
    public ?int $selectedCategoryId = null;
    public string $searchQuery = '';

    // ─── Current Order ─────────────────────────────────────────────────
    public array $cart = []; // [hash => ['dish_id' => int, 'name' => string, 'unit_price' => float, 'quantity' => int, 'notes' => '', 'modifiers' => [['id', 'name', 'price']]]]
    public ?int $currentOrderId = null;
    public string $notes = '';

    // ─── Modifiers Modal ───────────────────────────────────────────────
    public bool $showModifierModal = false;
    public ?int $selectedDishForModifiers = null;
    public array $selectedModifiers = []; // [modifier_group_id => [modifier_ids]]

    // ─── Cash Register ─────────────────────────────────────────────────
    public ?CashRegister $activeRegister = null;
    public bool $showOpenRegisterModal = false;
    public float $openingAmount = 0;

    public bool $showCloseRegisterModal = false;
    public float $closingAmount = 0;
    public float $expectedAmount = 0;
    public float $cashSales = 0;
    public float $cashIn = 0;
    public float $cashOut = 0;

    // ─── Manual Cash In/Out ────────────────────────────────────────────
    public bool $showManualCashModal = false;
    public string $manualCashType = 'cash_out'; // cash_in or cash_out
    public float $manualCashAmount = 0;
    public string $manualCashNotes = '';
    
    // ─── Payment ───────────────────────────────────────────────────────
    public string $view = 'tables'; // tables, pos, payment
    public bool $showPaymentModal = false;
    public string $paymentMethod = 'cash';
    public float $cashReceived = 0;
    public int $splitWays = 1;

    public function mount(): void
    {
        $user = auth()->user();
        $restaurantId = $user->restaurant_id ?? 1;
        
        $this->selectedCategoryId = MenuCategory::where('restaurant_id', $restaurantId)
            ->orderBy('sort_order')
            ->first()?->id;

        $this->checkActiveRegister();
    }

    private function checkActiveRegister(): void
    {
        $user = auth()->user();
        $restaurantId = $user->restaurant_id ?? 1;

        $this->activeRegister = CashRegister::where('restaurant_id', $restaurantId)
            ->where('status', 'open')
            ->first();

        if (!$this->activeRegister) {
            $this->showOpenRegisterModal = true;
        }
    }

    public function openRegister(): void
    {
        $user = auth()->user();
        $restaurantId = $user->restaurant_id ?? 1;

        $this->activeRegister = CashRegister::create([
            'restaurant_id'  => $restaurantId,
            'opened_by'      => $user->id,
            'opening_amount' => $this->openingAmount,
            'opened_at'      => now(),
            'status'         => 'open',
        ]);

        $this->showOpenRegisterModal = false;
        session()->flash('success', 'CAJA ABIERTA: Turno iniciado con €'.number_format($this->openingAmount, 2));
    }

    public function calculateCloseRegister(): void
    {
        if (!$this->activeRegister) return;

        $transactions = $this->activeRegister->transactions;
        $this->cashSales = $transactions->where('type', 'sale')->where('payment_method', 'cash')->sum('amount');
        $this->cashIn = $transactions->where('type', 'cash_in')->sum('amount');
        $this->cashOut = $transactions->where('type', 'cash_out')->sum('amount');
        $refunds = $transactions->where('type', 'refund')->where('payment_method', 'cash')->sum('amount');

        $this->expectedAmount = $this->activeRegister->opening_amount + $this->cashSales + $this->cashIn - $this->cashOut - $refunds;
        $this->closingAmount = $this->expectedAmount; // default to expected
        $this->showCloseRegisterModal = true;
    }

    public function closeRegister(): void
    {
        if (!$this->activeRegister) return;

        $this->calculateCloseRegister(); // Recalculate just in case

        $difference = $this->closingAmount - $this->expectedAmount;
        $notes = $this->activeRegister->notes;
        
        if ($difference != 0) {
            $diffText = $difference > 0 ? "Sobrante de €" . number_format($difference, 2) : "Faltante de €" . number_format(abs($difference), 2);
            $notes = trim($notes . "\nDescuadre al cierre: " . $diffText);
        }

        $closedRegisterId = $this->activeRegister->id;

        $this->activeRegister->update([
            'closed_by'       => auth()->id(),
            'closed_at'       => now(),
            'closing_amount'  => $this->closingAmount,
            'expected_amount' => $this->expectedAmount,
            'status'          => 'closed',
            'notes'           => $notes,
        ]);

        $this->showCloseRegisterModal = false;
        $this->activeRegister = null;
        
        // Dispatch event to client side to open the PDF in a new window/tab
        $this->dispatch('print-z-report', url: route('pos.z-report', $closedRegisterId));
        
        $this->checkActiveRegister(); // This will trigger the Open Register modal again
        
        session()->flash('success', 'CAJA CERRADA correctamente. Descargando Reporte Z...');
    }

    // ─── Manual Cash Movements ─────────────────────────────────────────

    public function openManualCashModal(string $type): void
    {
        if (!$this->activeRegister) return;
        
        $this->manualCashType = $type;
        $this->manualCashAmount = 0;
        $this->manualCashNotes = '';
        $this->showManualCashModal = true;
    }

    public function submitManualCash(): void
    {
        if (!$this->activeRegister || $this->manualCashAmount <= 0) return;

        $this->validate([
            'manualCashAmount' => 'required|numeric|min:0.01',
            'manualCashNotes'  => 'required|string|max:255',
        ]);

        $this->activeRegister->transactions()->create([
            'user_id'        => auth()->id(),
            'type'           => $this->manualCashType,
            'amount'         => $this->manualCashAmount,
            'payment_method' => 'cash',
            'notes'          => $this->manualCashNotes,
        ]);

        $this->showManualCashModal = false;
        
        $action = $this->manualCashType === 'cash_in' ? 'Entrada' : 'Salida';
        session()->flash('success', "{$action} de €" . number_format($this->manualCashAmount, 2) . " registrada correctamente.");
    }

    // ── Select a table and open POS ───────────────────────────────────
    public function selectTable(int $tableId): void
    {
        $this->selectedTableId = $tableId;
        $table = Table::find($tableId);

        // Check if table has an active order
        $activeOrder = $table?->activeOrder;
        if ($activeOrder) {
            $this->currentOrderId = $activeOrder->id;
            $this->loadOrderToCart($activeOrder);
        } else {
            $this->cart = [];
            $this->currentOrderId = null;
        }

        $this->view = 'pos';
    }

    private function loadOrderToCart(Order $order): void
    {
        $this->cart = [];
        foreach ($order->items()->where('status', '!=', 'cancelled')->with('modifiers')->get() as $item) {
            $modifiers = $item->modifiers->map(fn($m) => [
                'id' => $m->modifier_id,
                'name' => $m->modifier_name,
                'price' => (float) $m->price,
            ])->toArray();

            $key = "item_{$item->id}";
            $this->cart[$key] = [
                'order_item_id' => $item->id,
                'dish_id'       => $item->dish_id,
                'name'          => $item->name,
                'unit_price'    => (float) $item->unit_price,
                'quantity'      => $item->quantity,
                'notes'         => $item->notes ?? '',
                'modifiers'     => $modifiers,
                'line_total'    => (float) $item->total,
            ];
        }
    }

    // ── Add dish to cart ──────────────────────────────────────────────
    public function addToCart(int $dishId): void
    {
        $dish = Dish::with('modifierGroups.modifiers')->find($dishId);
        if (!$dish || !$dish->is_available) return;

        if ($dish->modifierGroups->count() > 0) {
            // Open modal
            $this->selectedDishForModifiers = $dish->id;
            $this->selectedModifiers = [];
            $this->showModifierModal = true;
            return;
        }

        $this->insertIntoCart($dish, []);
    }

    public function toggleModifier(int $groupId, int $modifierId, bool $isMultiple): void
    {
        if (!$isMultiple) {
            $this->selectedModifiers[$groupId] = [$modifierId];
        } else {
            if (!isset($this->selectedModifiers[$groupId])) {
                $this->selectedModifiers[$groupId] = [];
            }
            $index = array_search($modifierId, $this->selectedModifiers[$groupId]);
            if ($index !== false) {
                unset($this->selectedModifiers[$groupId][$index]);
                $this->selectedModifiers[$groupId] = array_values($this->selectedModifiers[$groupId]); // reindex
            } else {
                $this->selectedModifiers[$groupId][] = $modifierId;
            }
        }
    }

    public function confirmModifiers(): void
    {
        $dish = Dish::with('modifierGroups.modifiers')->find($this->selectedDishForModifiers);
        if (!$dish) return;

        // Validation: Check required groups
        foreach ($dish->modifierGroups as $group) {
            if ($group->is_required && empty($this->selectedModifiers[$group->id] ?? [])) {
                session()->flash('error', "Debes seleccionar una opción en: {$group->name}");
                return;
            }
        }

        $modifiersToAdd = [];
        $extraPrice = 0;

        foreach ($dish->modifierGroups as $group) {
            if (isset($this->selectedModifiers[$group->id])) {
                foreach ($this->selectedModifiers[$group->id] as $modId) {
                    $mod = $group->modifiers->firstWhere('id', $modId);
                    if ($mod) {
                        $modifiersToAdd[] = [
                            'id' => $mod->id,
                            'name' => $mod->name,
                            'price' => (float) $mod->price,
                        ];
                        $extraPrice += (float) $mod->price;
                    }
                }
            }
        }

        $this->insertIntoCart($dish, $modifiersToAdd, $extraPrice);
        $this->showModifierModal = false;
        $this->selectedDishForModifiers = null;
        $this->selectedModifiers = [];
    }

    private function insertIntoCart(Dish $dish, array $modifiers = [], float $extraPrice = 0): void
    {
        // Generate a unique key based on dish ID and selected modifiers
        $modIds = array_column($modifiers, 'id');
        sort($modIds);
        $hash = md5($dish->id . implode('_', $modIds));

        $key = "dish_{$hash}";

        if (isset($this->cart[$key])) {
            $this->cart[$key]['quantity']++;
            $this->cart[$key]['line_total'] = round($this->cart[$key]['quantity'] * $this->cart[$key]['unit_price'], 2);
        } else {
            $unitPrice = (float) $dish->price + $extraPrice;
            $this->cart[$key] = [
                'order_item_id' => null,
                'dish_id'       => $dish->id,
                'name'          => $dish->name,
                'unit_price'    => $unitPrice,
                'quantity'      => 1,
                'notes'         => '',
                'modifiers'     => $modifiers,
                'line_total'    => $unitPrice,
            ];
        }
    }

    // ── Remove / decrement cart item ──────────────────────────────────
    public function removeFromCart(string $key): void
    {
        if (!isset($this->cart[$key])) return;

        if ($this->cart[$key]['quantity'] > 1) {
            $this->cart[$key]['quantity']--;
            $this->cart[$key]['line_total'] = round(
                $this->cart[$key]['quantity'] * $this->cart[$key]['unit_price'], 2
            );
        } else {
            unset($this->cart[$key]);
        }
    }

    public function clearCart(): void
    {
        $this->cart = [];
    }

    // ── Select category ───────────────────────────────────────────────
    public function selectCategory(?int $categoryId): void
    {
        $this->selectedCategoryId = $categoryId;
        $this->searchQuery = '';
    }

    // ── Computed: subtotal ────────────────────────────────────────────
    public function getSubtotalProperty(): float
    {
        return round(array_sum(array_column($this->cart, 'line_total')), 2);
    }

    public function getTaxProperty(): float
    {
        $user = auth()->user();
        if ($user->restaurant) {
            $rate = (float) $user->restaurant->tax_rate / 100;
        } else {
            $restaurant = \App\Models\Restaurant::find(1);
            $rate = $restaurant ? (float) $restaurant->tax_rate / 100 : 0.10;
        }
        return round($this->subtotal * $rate, 2);
    }

    public function getTotalProperty(): float
    {
        return round($this->subtotal + $this->tax, 2);
    }

    public function getChangeProperty(): float
    {
        return max(0, $this->cashReceived - ($this->total / $this->splitWays));
    }

    public function incrementSplit(): void
    {
        $this->splitWays++;
    }

    public function decrementSplit(): void
    {
        if ($this->splitWays > 1) {
            $this->splitWays--;
        }
    }

    // ── Send order to kitchen ─────────────────────────────────────────
    public function sendToKitchen(): void
    {
        if (empty($this->cart)) return;

        $user = auth()->user();
        $restaurantId = $user->restaurant_id ?? 1;

        if (!$this->currentOrderId) {
            $order = Order::create([
                'restaurant_id' => $restaurantId,
                'table_id'      => $this->selectedTableId,
                'user_id'       => $user->id,
                'type'          => $this->orderType,
                'status'        => 'confirmed',
            ]);
            $this->currentOrderId = $order->id;
        } else {
            $order = Order::find($this->currentOrderId);
        }

        foreach ($this->cart as $key => $item) {
            if ($item['order_item_id']) continue; // already saved

            $orderItem = OrderItem::create([
                'order_id'   => $order->id,
                'dish_id'    => $item['dish_id'],
                'name'       => $item['name'],
                'unit_price' => $item['unit_price'],
                'quantity'   => $item['quantity'],
                'total'      => $item['line_total'],
                'notes'      => $item['notes'],
                'status'     => 'sent',
                'sent_at'    => now(),
            ]);

            if (!empty($item['modifiers'])) {
                foreach ($item['modifiers'] as $mod) {
                    \App\Models\OrderItemModifier::create([
                        'order_item_id' => $orderItem->id,
                        'modifier_id'   => $mod['id'],
                        'modifier_name' => $mod['name'],
                        'price'         => $mod['price'],
                    ]);
                }
            }

            // Mark as saved
            $this->cart[$key]['order_item_id'] = $orderItem->id;
        }

        $order->recalculateTotals();

        // Update table status
        if ($this->selectedTableId) {
            Table::where('id', $this->selectedTableId)->update(['status' => 'occupied']);
        }

        // Broadcast to KDS (Reverb)
        // event(new \App\Events\OrderSentToKitchen($order));

        session()->flash('success', '✅ Pedido enviado a cocina');
    }

    // ── Process payment ───────────────────────────────────────────────
    public function processPayment(): void
    {
        if (!$this->currentOrderId || !$this->activeRegister) return;

        $order = Order::find($this->currentOrderId);
        $order->update(['status' => 'paid', 'closed_at' => now()]);

        if ($this->paymentMethod === 'cash') {
            CashRegisterTransaction::create([
                'cash_register_id' => $this->activeRegister->id,
                'user_id'          => auth()->id(),
                'type'             => 'sale',
                'amount'           => $this->total,
                'payment_method'   => 'cash',
                'reference_type'   => Order::class,
                'reference_id'     => $order->id,
                'notes'            => 'Cobro Mesa ' . ($this->selectedTableId ? Table::find($this->selectedTableId)->number : 'Barra'),
            ]);
        }

        if ($this->selectedTableId) {
            Table::where('id', $this->selectedTableId)->update(['status' => 'available']);
        }

        $this->reset(['cart', 'currentOrderId', 'selectedTableId', 'showPaymentModal', 'splitWays']);
        $this->view = 'tables';
        session()->flash('success', '✅ Pago procesado correctamente');
    }

    public function backToTables(): void
    {
        $this->view = 'tables';
        $this->reset(['cart', 'selectedTableId', 'currentOrderId', 'splitWays', 'showModifierModal', 'selectedDishForModifiers']);
    }

    public function getActiveDishForModifiersProperty()
    {
        if (!$this->selectedDishForModifiers) return null;
        return Dish::with('modifierGroups.modifiers')->find($this->selectedDishForModifiers);
    }

    // ── Dishes for current category/search ────────────────────────────
    public function getDishesProperty()
    {
        $user = auth()->user();
        $restaurantId = $user->restaurant_id ?? 1;
        
        $query = Dish::where('restaurant_id', $restaurantId)
            ->where('is_available', true);

        if ($this->searchQuery) {
            $query->where('name', 'like', "%{$this->searchQuery}%");
        } elseif ($this->selectedCategoryId) {
            $query->where('menu_category_id', $this->selectedCategoryId);
        }

        return $query->orderBy('sort_order')->get();
    }

    public function getCategoriesProperty()
    {
        $user = auth()->user();
        $restaurantId = $user->restaurant_id ?? 1;
        
        return MenuCategory::where('restaurant_id', $restaurantId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    public function getTablesProperty()
    {
        $user = auth()->user();
        $restaurantId = $user->restaurant_id ?? 1;
        
        return Table::where('restaurant_id', $restaurantId)
            ->where('is_active', true)
            ->orderBy('number')
            ->with('activeOrder')
            ->get();
    }

    public function render()
    {
        return view('livewire.pos.pos-terminal')
            ->layout('layouts.pos');
    }
}
