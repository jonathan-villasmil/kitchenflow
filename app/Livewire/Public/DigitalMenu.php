<?php

namespace App\Livewire\Public;

use App\Models\Dish;
use App\Models\InventoryItem;
use App\Models\MenuCategory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Table;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class DigitalMenu extends Component
{
    public Table $table;
    public ?int $selectedCategoryId = null;
    public string $searchQuery = '';
    
    // Modifiers state
    public bool $showModifierModal = false;
    public ?int $selectedDishForModifiers = null;
    public array $selectedModifiers = []; 
    
    // Cart state
    public array $cart = [];
    public bool $showCart = false;

    private function findDishForTableRestaurant(?int $dishId): ?Dish
    {
        if (!$dishId) return null;

        return Dish::with('modifierGroups.modifiers')
            ->where('restaurant_id', $this->table->restaurant_id)
            ->find($dishId);
    }

    private function validateCartBelongsToTableRestaurant(): void
    {
        foreach ($this->cart as $item) {
            if (!$this->findDishForTableRestaurant($item['dish_id'] ?? null)) {
                abort(403, 'Plato no disponible para esta mesa.');
            }
        }
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

    public function mount(Table $table)
    {
        if (!$table->verifyMenuHash(request()->query('hash'))) {
            abort(403, 'Enlace de mesa no válido o expirado.');
        }

        $this->table = $table;
        
        // Cargar primera categoria por defecto
        $firstCat = MenuCategory::where('restaurant_id', $table->restaurant_id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->first();
            
        $this->selectedCategoryId = $firstCat?->id;
    }

    public function selectCategory(?int $categoryId)
    {
        if ($categoryId && !MenuCategory::where('restaurant_id', $this->table->restaurant_id)->whereKey($categoryId)->exists()) {
            abort(403, 'Categoría no disponible para esta mesa.');
        }

        $this->selectedCategoryId = $categoryId;
        $this->searchQuery = '';
    }

    public function getCategoriesProperty()
    {
        return MenuCategory::where('restaurant_id', $this->table->restaurant_id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    public function getDishesProperty()
    {
        $query = Dish::where('restaurant_id', $this->table->restaurant_id)
            ->where('is_available', true);

        if ($this->searchQuery) {
            $query->where('name', 'like', "%{$this->searchQuery}%");
        } elseif ($this->selectedCategoryId) {
            $query->where('menu_category_id', $this->selectedCategoryId);
        }

        return $query->orderBy('sort_order')->with(['modifierGroups.modifiers', 'ingredients'])->get();
    }

    /**
     * Calcula el stock disponible por plato cruzando el escandallo con el inventario.
     * Devuelve: [dish_id => ['status' => 'ok'|'low'|'out', 'portions' => int|null]]
     */
    public function getStockMapProperty(): array
    {
        $map = [];

        foreach ($this->dishes as $dish) {
            $map[$dish->id] = $dish->calculateStock();
        }

        return $map;
    }
    
    public function getActiveDishForModifiersProperty()
    {
        if (!$this->selectedDishForModifiers) return null;
        return $this->findDishForTableRestaurant($this->selectedDishForModifiers);
    }

    public function addToCart(int $dishId)
    {
        $dish = $this->findDishForTableRestaurant($dishId);
        if (!$dish || !$dish->is_available) abort(403, 'Plato no disponible para esta mesa.');

        // Verificar stock antes de aceptar el pedido
        $stockInfo = $this->stockMap[$dishId] ?? ['status' => 'ok'];
        if ($stockInfo['status'] === 'out') return;

        if ($dish->modifierGroups->count() > 0) {
            // Open modal
            $this->selectedDishForModifiers = $dish->id;
            $this->selectedModifiers = [];
            $this->showModifierModal = true;
            return;
        }

        $this->insertIntoCart($dish, []);
        $this->dispatch('item-added');
    }

    public function toggleModifier(int $groupId, int $modifierId, bool $isMultiple)
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
                $this->selectedModifiers[$groupId] = array_values($this->selectedModifiers[$groupId]);
            } else {
                $this->selectedModifiers[$groupId][] = $modifierId;
            }
        }
    }

    public function confirmModifiers()
    {
        $dish = $this->findDishForTableRestaurant($this->selectedDishForModifiers);
        if (!$dish || !$dish->is_available) abort(403, 'Plato no disponible para esta mesa.');

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
        $this->dispatch('item-added');
    }

    private function insertIntoCart(Dish $dish, array $modifiers = [], float $extraPrice = 0)
    {
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

    public function incrementCartItem(string $key)
    {
        if (isset($this->cart[$key])) {
            $this->cart[$key]['quantity']++;
            $this->cart[$key]['line_total'] = round($this->cart[$key]['quantity'] * $this->cart[$key]['unit_price'], 2);
        }
    }

    public function decrementCartItem(string $key)
    {
        if (!isset($this->cart[$key])) return;

        if ($this->cart[$key]['quantity'] > 1) {
            $this->cart[$key]['quantity']--;
            $this->cart[$key]['line_total'] = round($this->cart[$key]['quantity'] * $this->cart[$key]['unit_price'], 2);
        } else {
            unset($this->cart[$key]);
            if (empty($this->cart)) {
                $this->showCart = false;
            }
        }
    }

    public function getCartTotalProperty()
    {
        return round(array_sum(array_column($this->cart, 'line_total')), 2);
    }
    
    public function getCartCountProperty()
    {
        return array_sum(array_column($this->cart, 'quantity'));
    }

    public function toggleCart()
    {
        $this->showCart = !$this->showCart;
    }

    public function submitOrder()
    {
        if (empty($this->cart)) return;

        $this->validateCartBelongsToTableRestaurant();

        $order = $this->table->activeOrder;

        if (!$order) {
            $order = Order::create([
                'restaurant_id' => $this->table->restaurant_id,
                'table_id'      => $this->table->id,
                'user_id'       => null, // Auto-ordered by customer
                'type'          => 'dine_in',
                'status'        => 'confirmed',
            ]);
        }

        foreach ($this->cart as $key => $item) {
            $orderItem = OrderItem::create([
                'order_id'   => $order->id,
                'dish_id'    => $item['dish_id'],
                'name'       => $item['name'],
                'unit_price' => $item['unit_price'],
                'quantity'   => $item['quantity'],
                'total'      => $item['line_total'],
                'notes'      => $item['notes'],
                'status'     => 'sent',
                'course'     => 1,
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
        }

        $order->recalculateTotals();
        $this->table->update(['status' => 'occupied']);

        // Broadcast to KDS (Reverb) in real-time
        $this->broadcastRealtime(new \App\Events\OrderSentToKitchen($order));

        $this->cart = [];
        $this->showCart = false;
        
        session()->flash('success', '¡Pedido enviado a cocina! En breve estará en tu mesa.');
    }

    public function render()
    {
        return view('livewire.public.digital-menu')->layout('layouts.public');
    }
}
