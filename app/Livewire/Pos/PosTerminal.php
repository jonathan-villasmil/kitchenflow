<?php

namespace App\Livewire\Pos;

use App\Models\CashRegister;
use App\Models\CashRegisterTransaction;
use App\Models\Dish;
use App\Models\MenuCategory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Table;
use App\Models\Employee;
use App\Models\Clocking;
use App\Models\Customer;
use App\Models\User;
use App\Events\OrderPaid;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
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
    public ?int $selectedCustomerId = null;
    public string $notes = '';

    // ─── CRM / Customer Selection ──────────────────────────────────────
    public bool $showCustomerModal = false;
    public string $searchCustomerQuery = '';
    public int $pointsToRedeem = 0;
    public float $pointsDiscount = 0;

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
    public $cashReceived = 0;
    public int $splitWays = 1;
    public $tipAmount = 0;
    public ?int $tipPercentage = null;

    // ─── Security / PIN Lock ───────────────────────────────────────────
    public bool $isLocked = false;
    public string $enteredPin = '';
    public string $pinError = '';

    // ─── Cancellation Interceptor ─────────────────────────────────────
    public bool $showCancellationModal = false;
    public ?string $itemKeyToCancel = null;
    public string $cancellationPin = '';
    public string $cancellationError = '';

    private function restaurantId(): int
    {
        return auth()->user()->restaurant_id ?? 1;
    }

    private function findTableForCurrentRestaurant(int $tableId): ?Table
    {
        return Table::where('restaurant_id', $this->restaurantId())->find($tableId);
    }

    private function findDishForCurrentRestaurant(?int $dishId): ?Dish
    {
        if (!$dishId) return null;

        return Dish::with('modifierGroups.modifiers')
            ->where('restaurant_id', $this->restaurantId())
            ->find($dishId);
    }

    private function findOrderForCurrentRestaurant(?int $orderId): ?Order
    {
        if (!$orderId) return null;

        return Order::where('restaurant_id', $this->restaurantId())->find($orderId);
    }

    private function findCustomerForCurrentRestaurant(?int $customerId): ?Customer
    {
        if (!$customerId) return null;

        return Customer::where('restaurant_id', $this->restaurantId())->find($customerId);
    }

    private function activeRegisterForCurrentRestaurant(): ?CashRegister
    {
        if (!$this->activeRegister) return null;

        return CashRegister::where('restaurant_id', $this->restaurantId())
            ->where('status', 'open')
            ->find($this->activeRegister->id);
    }

    private function broadcastRealtime(object $event): void
    {
        try {
            event($event);
        } catch (\Throwable $e) {
            Log::warning('No se pudo emitir el evento en tiempo real.', [
                'event' => $event::class,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function mount(): void
    {
        $user = auth()->user();
        if (!$user || !$user->hasAnyRole(['super_admin', 'manager', 'cajero', 'camarero'])) {
            abort(403, 'No tienes permiso para acceder al TPV.');
        }

        $restaurantId = $this->restaurantId();
        
        $this->selectedCategoryId = MenuCategory::where('restaurant_id', $restaurantId)
            ->orderBy('sort_order')
            ->first()?->id;

        $this->checkActiveRegister();
    }

    private function checkActiveRegister(): void
    {
        $this->activeRegister = CashRegister::where('restaurant_id', $this->restaurantId())
            ->where('status', 'open')
            ->first();

        if (!$this->activeRegister && !$this->isLocked) {
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

    // ─── PIN Lock Logic ────────────────────────────────────────────────
    public function lockPos(): void
    {
        $this->isLocked = true;
        $this->enteredPin = '';
        $this->pinError = '';
        $this->showOpenRegisterModal = false;
    }

    public function addPinDigit(string $digit): void
    {
        if (strlen($this->enteredPin) < 4) {
            $this->enteredPin .= $digit;
            $this->pinError = '';
        }

        if (strlen($this->enteredPin) === 4) {
            $this->verifyPin();
        }
    }

    public function clearPin(): void
    {
        $this->enteredPin = '';
        $this->pinError = '';
    }

    public function verifyPin()
    {
        $restaurantId = auth()->user()->restaurant_id ?? 1;
        $users = \App\Models\User::whereNotNull('pin')
            ->where('restaurant_id', $restaurantId)
            ->get();

        $matchedUser = null;
        foreach ($users as $u) {
            // Check standard hash
            try {
                if (\Hash::check($this->enteredPin, $u->pin)) {
                    $matchedUser = $u;
                    break;
                }
            } catch (\Throwable $e) {
                // If it's not a valid hash (e.g. legacy plain text), catch and fallback
            }
            // Check fallback plain text (for existing users before Fase B migration)
            if ($u->pin === $this->enteredPin) {
                // Auto-upgrade plain text PIN to secure hash
                $u->update(['pin' => $this->enteredPin]); // This will automatically get hashed because of 'hashed' cast!
                $matchedUser = $u;
                break;
            }
        }

        if ($matchedUser) {
            auth()->login($matchedUser);
            session()->regenerate();

            $this->redirectRoute('pos');
            return;
        } else {
            $this->pinError = 'PIN Incorrecto';
            $this->enteredPin = '';
        }
    }

    public function calculateCloseRegister(): void
    {
        $this->activeRegister = $this->activeRegisterForCurrentRestaurant();
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
        $this->activeRegister = $this->activeRegisterForCurrentRestaurant();
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
        if (!$this->activeRegisterForCurrentRestaurant()) return;
        
        $this->manualCashType = $type;
        $this->manualCashAmount = 0;
        $this->manualCashNotes = '';
        $this->showManualCashModal = true;
    }

    public function submitManualCash(): void
    {
        $this->activeRegister = $this->activeRegisterForCurrentRestaurant();
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
        $table = $this->findTableForCurrentRestaurant($tableId);
        if (!$table) abort(403, 'Mesa no disponible para este restaurante.');

        $this->selectedTableId = $tableId;

        // Check if table has an active order
        $activeOrder = $table->activeOrder;
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
                'course'        => $item->course ?? 1,
                'line_total'    => (float) $item->total,
            ];
        }
    }

    // ── Add dish to cart ──────────────────────────────────────────────
    public function addToCart(int $dishId): void
    {
        $dish = $this->findDishForCurrentRestaurant($dishId);
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
        $dish = $this->findDishForCurrentRestaurant($this->selectedDishForModifiers);
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
            $unitPrice = (float) $dish->dynamic_price + $extraPrice;
            $this->cart[$key] = [
                'order_item_id' => null,
                'dish_id'       => $dish->id,
                'name'          => $dish->name,
                'unit_price'    => $unitPrice,
                'quantity'      => 1,
                'notes'         => '',
                'modifiers'     => $modifiers,
                'course'        => 1,
                'line_total'    => $unitPrice,
            ];
        }
    }

    // ── Remove / decrement cart item ──────────────────────────────────
    public function removeFromCart(string $key): void
    {
        if (!isset($this->cart[$key])) return;

        // INTERCEPTOR: If item was already sent to kitchen, we need a Manager PIN to cancel it
        if ($this->cart[$key]['order_item_id']) {
            $this->showCancellationModal = true;
            $this->itemKeyToCancel = $key;
            $this->cancellationPin = '';
            $this->cancellationError = '';
            return;
        }

        if ($this->cart[$key]['quantity'] > 1) {
            $this->cart[$key]['quantity']--;
            $this->cart[$key]['line_total'] = round(
                $this->cart[$key]['quantity'] * $this->cart[$key]['unit_price'], 2
            );
        } else {
            unset($this->cart[$key]);
        }
    }

    public function addCancellationPinDigit(string $digit): void
    {
        if (strlen($this->cancellationPin) < 4) {
            $this->cancellationPin .= $digit;
            $this->cancellationError = '';
        }

        if (strlen($this->cancellationPin) === 4) {
            $this->confirmCancellation();
        }
    }

    public function confirmCancellation(): void
    {
        $restaurantId = auth()->user()->restaurant_id ?? 1;
        
        // Verify PIN belongs to a Manager or Admin with hashed check
        $managers = \App\Models\User::whereNotNull('pin')
            ->where('restaurant_id', $restaurantId)
            ->role(['manager', 'super_admin'])
            ->get();

        $manager = null;
        foreach ($managers as $m) {
            // Check standard hash
            try {
                if (\Hash::check($this->cancellationPin, $m->pin)) {
                    $manager = $m;
                    break;
                }
            } catch (\Throwable $e) {
                // If it's not a valid hash (e.g. legacy plain text), catch and fallback
            }
            // Check fallback plain text
            if ($m->pin === $this->cancellationPin) {
                // Auto-upgrade plain text PIN to secure hash
                $m->update(['pin' => $this->cancellationPin]);
                $manager = $m;
                break;
            }
        }

        if ($manager) {
            $key = $this->itemKeyToCancel;
            $orderItemId = $this->cart[$key]['order_item_id'];

            // Update DB: Mark as cancelled instead of deleting for audit
            if ($orderItemId) {
                $item = OrderItem::whereHas('order', fn ($query) =>
                    $query->where('restaurant_id', $this->restaurantId())
                )->find($orderItemId);
                if ($item) {
                    $item->update(['status' => 'cancelled']);
                }
            }

            // Remove from local cart
            unset($this->cart[$key]);

            $this->showCancellationModal = false;
            $this->itemKeyToCancel = null;
            $this->cancellationPin = '';

            // ── Check if order is now empty ─────────────────────────────────
            // If cart is empty AND the order has no more active items in DB,
            // cancel the order and free the table.
            if (empty($this->cart) && $this->currentOrderId) {
                $order = $this->findOrderForCurrentRestaurant($this->currentOrderId);
                
                if ($order) {
                    $activeItemsCount = $order->items()
                        ->whereNotIn('status', ['cancelled'])
                        ->count();

                    if ($activeItemsCount === 0) {
                        // Cancel the order entirely
                        $order->update([
                            'status'     => 'cancelled',
                            'closed_at'  => now(),
                        ]);

                        // Free the table
                        if ($this->selectedTableId) {
                            Table::where('restaurant_id', $this->restaurantId())
                                ->where('id', $this->selectedTableId)
                                ->update(['status' => 'available']);
                        }

                        // Reset POS and go back to table view
                        $this->reset(['cart', 'currentOrderId', 'selectedTableId', 'splitWays', 'tipAmount', 'tipPercentage', 'cashReceived']);
                        $this->view = 'tables';

                        session()->flash('success', '🗑️ Comanda anulada por ' . $manager->name . '. Mesa liberada.');
                        return;
                    }
                }

                // Still items remaining — just recalculate totals
                $order?->recalculateTotals();
            } elseif ($this->currentOrderId) {
                $this->findOrderForCurrentRestaurant($this->currentOrderId)?->recalculateTotals();
            }

            session()->flash('success', '✅ Plato anulado correctamente por ' . $manager->name);
        } else {
            $this->cancellationError = 'PIN de Gerente Incorrecto';
            $this->cancellationPin = '';
        }
    }

    public function closeCancellationModal(): void
    {
        $this->showCancellationModal = false;
        $this->itemKeyToCancel = null;
        $this->cancellationPin = '';
    }

    public function toggleCourse(string $key): void
    {
        if (isset($this->cart[$key])) {
            $this->cart[$key]['course'] = $this->cart[$key]['course'] == 1 ? 2 : 1;
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

    public function getGrandTotalProperty(): float
    {
        return round($this->total + $this->tipAmount, 2);
    }

    public function getChangeProperty(): float
    {
        return max(0, $this->cashReceived - ($this->grandTotal / $this->splitWays));
    }

    public function setTip(?int $percentage, ?float $amount = null): void
    {
        $this->tipPercentage = $percentage;
        if ($percentage !== null) {
            $this->tipAmount = round($this->total * ($percentage / 100), 2);
        } elseif ($amount !== null) {
            $this->tipAmount = round($amount, 2);
        } else {
            $this->tipAmount = 0;
        }
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

    public function getActiveClockingProperty()
    {
        $employee = Employee::where('user_id', auth()->id())->first();
        if (!$employee) return null;

        return Clocking::where('employee_id', $employee->id)
            ->whereNull('clocked_out_at')
            ->first();
    }

    public function fichar()
    {
        $employee = Employee::where('user_id', auth()->id())->first();

        if (!$employee) {
            Notification::make()
                ->title('Sin ficha de empleado')
                ->body('Debes estar registrado como Empleado para fichar.')
                ->danger()
                ->send();
            return;
        }

        $active = $this->activeClocking;

        if ($active) {
            $active->update([
                'clocked_out_at' => now(),
                'total_minutes' => now()->diffInMinutes($active->clocked_in_at),
            ]);

            Notification::make()
                ->title('Salida registrada')
                ->body('Has terminado tu jornada, ' . $employee->first_name . '. ¡Buen descanso!')
                ->success()
                ->send();
        } else {
            Clocking::create([
                'employee_id' => $employee->id,
                'clocked_in_at' => now(),
            ]);

            Notification::make()
                ->title('Entrada registrada')
                ->body('Has comenzado tu jornada, ' . $employee->first_name . '. ¡Buen servicio!')
                ->success()
                ->send();
        }

        $this->dispatch('refresh-pos');
    }

    // ─── CRM / Customer Methods ───────────────────────────────────────
    public function selectCustomer(?int $customerId): void
    {
        if ($customerId && !$this->findCustomerForCurrentRestaurant($customerId)) {
            abort(403, 'Cliente no disponible para este restaurante.');
        }

        $this->selectedCustomerId = $customerId;
        $this->showCustomerModal = false;
        $this->searchCustomerQuery = '';
        $this->pointsToRedeem = 0;
        $this->pointsDiscount = 0;
    }

    public function getCustomersProperty()
    {
        $restaurantId = auth()->user()->restaurant_id ?? 1;
        $query = \App\Models\Customer::where('restaurant_id', $restaurantId);
        
        if ($this->searchCustomerQuery) {
            $query->where(function($q) {
                $q->where('name', 'like', "%{$this->searchCustomerQuery}%")
                  ->orWhere('phone', 'like', "%{$this->searchCustomerQuery}%");
            });
        }
        
        return $query->latest()->limit(10)->get();
    }

    public function getSelectedCustomerRecordProperty()
    {
        return $this->findCustomerForCurrentRestaurant($this->selectedCustomerId);
    }

    public function toggleRedeemPoints(): void
    {
        if (!$this->selectedCustomerId) return;
        
        $customer = $this->selectedCustomerRecord;
        $restaurant = auth()->user()->restaurant;
        
        if ($this->pointsToRedeem > 0) {
            $this->pointsToRedeem = 0;
            $this->pointsDiscount = 0;
        } else {
            // Max points that can be used based on total and customer balance
            $maxPossibleDiscount = $this->total;
            $pointsNeededForFullDiscount = (int) ($maxPossibleDiscount * $restaurant->loyalty_redemption_rate);
            
            $this->pointsToRedeem = min($customer->loyalty_points, $pointsNeededForFullDiscount);
            $this->pointsDiscount = round($this->pointsToRedeem / $restaurant->loyalty_redemption_rate, 2);
        }
    }

    // ── Send order to kitchen ─────────────────────────────────────────
    public function sendToKitchen(?int $course = null): void
    {
        if (empty($this->cart)) return;

        $user = auth()->user();
        $restaurantId = $this->restaurantId();

        foreach ($this->cart as $item) {
            if ($item['order_item_id']) continue;

            if ($course !== null && $item['course'] !== $course) {
                continue;
            }

            if (!$this->findDishForCurrentRestaurant($item['dish_id'] ?? null)) {
                abort(403, 'Plato no disponible para este restaurante.');
            }
        }

        if (!$this->currentOrderId) {
            if ($this->selectedTableId && !$this->findTableForCurrentRestaurant($this->selectedTableId)) {
                abort(403, 'Mesa no disponible para este restaurante.');
            }

            $order = Order::create([
                'restaurant_id' => $restaurantId,
                'table_id'      => $this->selectedTableId,
                'user_id'       => $user->id,
                'type'          => $this->orderType,
                'status'        => 'confirmed',
            ]);
            $this->currentOrderId = $order->id;
        } else {
            $order = $this->findOrderForCurrentRestaurant($this->currentOrderId);
            if (!$order) abort(403, 'Pedido no disponible para este restaurante.');
        }

        $sentCount = 0;

        foreach ($this->cart as $key => $item) {
            if ($item['order_item_id']) continue; // already saved
            
            if ($course !== null && $item['course'] !== $course) {
                continue; // Skip items not matching the selected course
            }

            $dish = $this->findDishForCurrentRestaurant($item['dish_id'] ?? null);
            if (!$dish) abort(403, 'Plato no disponible para este restaurante.');

            $orderItem = OrderItem::create([
                'order_id'   => $order->id,
                'dish_id'    => $item['dish_id'],
                'name'       => $item['name'],
                'unit_price' => $item['unit_price'],
                'quantity'   => $item['quantity'],
                'total'      => $item['line_total'],
                'notes'      => $item['notes'],
                'status'     => 'sent',
                'course'     => $item['course'],
                'sent_at'    => now(),
            ]);

            $sentCount++;

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

            $this->cart[$key]['order_item_id'] = $orderItem->id;
        }

        if ($sentCount > 0) {
            $order->recalculateTotals();

            // Update table status
            if ($this->selectedTableId) {
                Table::where('restaurant_id', $restaurantId)
                    ->where('id', $this->selectedTableId)
                    ->update(['status' => 'occupied']);
            }

            // Broadcast to KDS (Reverb)
            $this->broadcastRealtime(new \App\Events\OrderSentToKitchen($order));

            session()->flash('success', $course === null ? '✅ Pedido mandado completo' : "✅ Marchando Platos del Curso {$course}");
        }
    }

    // ── Process payment ───────────────────────────────────────────────
    public function processPayment(): void
    {
        // Necesitamos caja abierta y al menos algo en el carrito
        $this->activeRegister = $this->activeRegisterForCurrentRestaurant();
        if (empty($this->cart) || !$this->activeRegister) return;

        $this->tipAmount = max(0, (float) $this->tipAmount);

        // ── Si hay items sin enviar a cocina, los enviamos ahora (cobro directo) ──
        $hasPending = collect($this->cart)->contains(fn($i) => !$i['order_item_id']);
        if ($hasPending) {
            $this->sendToKitchen();
        }

        // Tras sendToKitchen, currentOrderId ya existe
        if (!$this->currentOrderId) return;

        // ── Capturar netPaid ANTES de cualquier reset ─────────────────
        $netPaid      = $this->total - $this->pointsDiscount;
        $grandTotal   = $this->grandTotal;
        $tipAmount    = $this->tipAmount;
        $pointsDiscount = $this->pointsDiscount;
        $pointsToRedeem = $this->pointsToRedeem;
        $paymentMethod  = $this->paymentMethod;
        $selectedCustomerId = $this->selectedCustomerId;
        $selectedTableId    = $this->selectedTableId;

        $order = $this->findOrderForCurrentRestaurant($this->currentOrderId);
        if (!$order) return;

        if ($selectedCustomerId && !$this->findCustomerForCurrentRestaurant($selectedCustomerId)) {
            abort(403, 'Cliente no disponible para este restaurante.');
        }

        $selectedTable = $selectedTableId ? $this->findTableForCurrentRestaurant($selectedTableId) : null;
        if ($selectedTableId && !$selectedTable) {
            abort(403, 'Mesa no disponible para este restaurante.');
        }

        $order->update([
            'status'          => 'paid',
            'closed_at'       => now(),
            'tip_amount'      => $tipAmount,
            'customer_id'     => $selectedCustomerId,
            'discount_amount' => $pointsDiscount,
            'total'           => $grandTotal - $pointsDiscount,
        ]);

        if (in_array($paymentMethod, ['cash', 'card'])) {
            CashRegisterTransaction::create([
                'cash_register_id' => $this->activeRegister->id,
                'user_id'          => auth()->id(),
                'type'             => 'sale',
                'amount'           => $grandTotal - $pointsDiscount,
                'payment_method'   => $paymentMethod,
                'reference_type'   => Order::class,
                'reference_id'     => $order->id,
                'notes'            => 'Cobro Mesa ' . ($selectedTable?->number ?? 'Barra') . ($tipAmount > 0 ? " (Inc. propina €{$tipAmount})" : ''),
            ]);
        }

        // ─── CRM: Add Loyalty Points ─────────────────────────────────
        if ($selectedCustomerId) {
            $customer   = $this->findCustomerForCurrentRestaurant($selectedCustomerId);
            $restaurant = auth()->user()->restaurant;

            if ($customer) {
                // Deducir puntos usados
                if ($pointsToRedeem > 0) {
                    $customer->decrement('loyalty_points', $pointsToRedeem);
                }

                // Acumular puntos por la venta
                $newPoints = (int) ($netPaid * ($restaurant?->loyalty_points_per_unit ?? 1.0));

                if ($newPoints > 0) {
                    $customer->increment('loyalty_points', $newPoints);

                    Notification::make()
                        ->title('Puntos acumulados')
                        ->body("{$customer->name} ha ganado {$newPoints} puntos. Total: " . ($customer->loyalty_points) . " pts.")
                        ->success()
                        ->send();
                }
            }
        }

        // Liberar mesa
        if ($selectedTableId) {
            Table::where('restaurant_id', $this->restaurantId())
                ->where('id', $selectedTableId)
                ->update(['status' => 'available']);
        }

        // Descontar inventario automáticamente
        OrderPaid::dispatch($order);

        // Imprimir ticket
        $this->dispatch('print-receipt', url: route('pos.receipt', $order->id));

        // Reset de estado del POS
        $this->reset(['cart', 'currentOrderId', 'selectedTableId', 'showPaymentModal', 'splitWays', 'tipAmount', 'tipPercentage', 'cashReceived', 'selectedCustomerId', 'pointsToRedeem', 'pointsDiscount']);
        $this->view = 'tables';
        session()->flash('success', '✅ Pago procesado correctamente');
    }

    public function backToTables(): void
    {
        $this->view = 'tables';
        $this->reset(['cart', 'selectedTableId', 'currentOrderId', 'splitWays', 'showModifierModal', 'selectedDishForModifiers', 'tipAmount', 'tipPercentage', 'cashReceived']);
    }

    public function getActiveDishForModifiersProperty()
    {
        if (!$this->selectedDishForModifiers) return null;
        return $this->findDishForCurrentRestaurant($this->selectedDishForModifiers);
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

        return $query->orderBy('sort_order')->with('ingredients')->get();
    }

    // ── Real-Time Stock Map ────────────────────────────────────────────
    // Returns: [ dish_id => ['portions' => int, 'status' => 'ok'|'low'|'out'] ]
    public function getStockMapProperty(): array
    {
        $map = [];

        foreach ($this->dishes as $dish) {
            $stock = $dish->calculateStock();
            $map[$dish->id] = [
                'portions' => $stock['portions'],
                'status' => $stock['status'],
            ];
        }

        return $map;
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
    public function getListeners()
    {
        return [
            'refresh-pos' => '$refresh',
        ];
    }

    public function notifyOrderReady($event)
    {
        $orderNum = $event['order']['number'] ?? 'Desconocido';
        $this->dispatch('play-sound', type: 'bell');
        $this->dispatch('show-notification', message: "🔔 ¡Mesa lista para servir (Pedido #{$orderNum})!", type: 'success');
    }

    public function render()
    {
        return view('livewire.pos.pos-terminal')
            ->layout('layouts.pos');
    }
}
