<?php

namespace App\Livewire\Pos;

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
    public array $cart = []; // [dish_id => ['dish' => [...], 'quantity' => int, 'notes' => '', 'modifiers' => []]]
    public ?int $currentOrderId = null;
    public string $notes = '';

    // ─── UI State ──────────────────────────────────────────────────────
    public string $view = 'tables'; // tables, pos, payment
    public bool $showPaymentModal = false;
    public string $paymentMethod = 'cash';
    public float $cashReceived = 0;

    public function mount(): void
    {
        $restaurant = auth()->user()->restaurant;
        if ($restaurant) {
            $this->selectedCategoryId = MenuCategory::where('restaurant_id', $restaurant->id)
                ->orderBy('sort_order')
                ->first()?->id;
        }
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
        foreach ($order->items()->where('status', '!=', 'cancelled')->get() as $item) {
            $key = "item_{$item->id}";
            $this->cart[$key] = [
                'order_item_id' => $item->id,
                'dish_id'       => $item->dish_id,
                'name'          => $item->name,
                'unit_price'    => (float) $item->unit_price,
                'quantity'      => $item->quantity,
                'notes'         => $item->notes ?? '',
                'line_total'    => (float) $item->total,
            ];
        }
    }

    // ── Add dish to cart ──────────────────────────────────────────────
    public function addToCart(int $dishId): void
    {
        $dish = Dish::find($dishId);
        if (!$dish || !$dish->is_available) return;

        $key = "dish_{$dishId}";
        if (isset($this->cart[$key])) {
            $this->cart[$key]['quantity']++;
            $this->cart[$key]['line_total'] = round($this->cart[$key]['quantity'] * $this->cart[$key]['unit_price'], 2);
        } else {
            $this->cart[$key] = [
                'order_item_id' => null,
                'dish_id'       => $dish->id,
                'name'          => $dish->name,
                'unit_price'    => (float) $dish->price,
                'quantity'      => 1,
                'notes'         => '',
                'line_total'    => (float) $dish->price,
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
        $restaurant = auth()->user()->restaurant;
        $rate = $restaurant ? (float) $restaurant->tax_rate / 100 : 0.10;
        return round($this->subtotal * $rate, 2);
    }

    public function getTotalProperty(): float
    {
        return round($this->subtotal + $this->tax, 2);
    }

    public function getChangeProperty(): float
    {
        return max(0, $this->cashReceived - $this->total);
    }

    // ── Send order to kitchen ─────────────────────────────────────────
    public function sendToKitchen(): void
    {
        if (empty($this->cart)) return;

        $user       = auth()->user();
        $restaurant = $user->restaurant;

        if (!$this->currentOrderId) {
            $order = Order::create([
                'restaurant_id' => $restaurant->id,
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
        if (!$this->currentOrderId) return;

        $order = Order::find($this->currentOrderId);
        $order->update(['status' => 'paid', 'closed_at' => now()]);

        // Free the table
        if ($this->selectedTableId) {
            Table::where('id', $this->selectedTableId)->update(['status' => 'available']);
        }

        $this->reset(['cart', 'currentOrderId', 'selectedTableId', 'showPaymentModal']);
        $this->view = 'tables';
        session()->flash('success', '✅ Pago procesado correctamente');
    }

    public function backToTables(): void
    {
        $this->view = 'tables';
        $this->reset(['cart', 'selectedTableId', 'currentOrderId'], );
    }

    // ── Dishes for current category/search ────────────────────────────
    public function getDishesProperty()
    {
        $restaurant = auth()->user()->restaurant;
        $query = Dish::where('restaurant_id', $restaurant->id)
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
        $restaurant = auth()->user()->restaurant;
        return MenuCategory::where('restaurant_id', $restaurant->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    public function getTablesProperty()
    {
        $restaurant = auth()->user()->restaurant;
        return Table::where('restaurant_id', $restaurant->id)
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
