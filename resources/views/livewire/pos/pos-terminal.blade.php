<div>
    @if($view === 'tables')
        <!-- WIZARD STEP 1: SELECT TABLE -->
        <div class="p-6 h-full flex flex-col">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Seleccionar Mesa</h1>
                <div class="flex gap-4">
                    <button wire:click="$set('orderType', 'takeaway')" class="px-4 py-2 rounded-lg {{ $orderType === 'takeaway' ? 'bg-orange-500' : 'bg-gray-800' }}">Para llevar</button>
                    <button wire:click="$set('orderType', 'delivery')" class="px-4 py-2 rounded-lg {{ $orderType === 'delivery' ? 'bg-orange-500' : 'bg-gray-800' }}">A domicilio</button>
                    <button wire:click="selectTable(0)" class="px-4 py-2 bg-blue-600 rounded-lg">Sin Mesa (Directo)</button>
                    @if($this->activeRegister)
                        <div class="flex gap-2 mr-4 border-r border-gray-700 pr-4">
                            <button wire:click="openManualCashModal('cash_in')" class="px-3 py-2 bg-green-900/50 text-green-400 border border-green-700 hover:bg-green-800 rounded-lg font-bold transition flex items-center gap-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg> Ingreso</button>
                            <button wire:click="openManualCashModal('cash_out')" class="px-3 py-2 bg-red-900/50 text-red-400 border border-red-700 hover:bg-red-800 rounded-lg font-bold transition flex items-center gap-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg> Retiro</button>
                        </div>
                        <button wire:click="calculateCloseRegister" class="px-4 py-2 bg-purple-600 text-white rounded-lg font-bold shadow-lg hover:bg-purple-500 transition">Cerrar Caja (Reporte Z)</button>
                    @endif
                    @if(auth()->user() && !auth()->user()->hasRole('camarero'))
                        <a href="{{ url('/admin') }}" data-navigate-ignore="true" class="px-4 py-2 bg-gray-700 rounded-lg text-gray-300 hover:bg-gray-600 transition">Volver Admin</a>
                    @else
                        <!-- Direct logout for camareros -->
                        <form method="POST" action="{{ route('filament.admin.auth.logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-red-600 rounded-lg text-white hover:bg-red-500 transition">Salir</button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 overflow-y-auto">
                @foreach($this->tables as $table)
                    <button wire:click="selectTable({{ $table->id }})" 
                        class="p-6 rounded-xl border-2 flex flex-col items-center justify-center gap-2 transition hover:scale-105
                        {{ $table->status === 'available' ? 'border-gray-700 bg-gray-800 hover:border-green-500' : '' }}
                        {{ $table->status === 'occupied' ? 'border-orange-500 bg-orange-900/30' : '' }}
                        {{ $table->status === 'reserved' ? 'border-yellow-500 bg-yellow-900/30' : '' }}
                        {{ $table->status === 'cleaning' ? 'border-blue-500 bg-blue-900/30' : '' }}">
                        <div class="text-3xl font-bold">{{ $table->number }}</div>
                        <div class="text-sm text-gray-400">
                            @if($table->status === 'available') 🟢 Libre ({{ $table->capacity }} pax)
                            @elseif($table->status === 'occupied') 🔴 Ocupada
                            @elseif($table->status === 'reserved') 🟡 Reservada
                            @elseif($table->status === 'cleaning') 🧹 Limpiando
                            @endif
                        </div>
                        @if($table->activeOrder)
                            <div class="mt-2 text-orange-400 font-bold">€{{ number_format($table->activeOrder->total, 2) }}</div>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>

    @elseif($view === 'pos')
        <!-- WIZARD STEP 2: POS TERMINAL -->
        <div class="flex h-screen overflow-hidden">
            
            <!-- LEFT PANEL: CART -->
            <div class="w-1/3 bg-gray-900 border-r border-gray-800 flex flex-col">
                <div class="p-4 bg-gray-950 flex justify-between items-center border-b border-gray-800">
                    <div>
                        <h2 class="font-bold text-lg">Mesa {{ $selectedTableId ? App\Models\Table::find($selectedTableId)->number : '---' }}</h2>
                        <div class="text-sm text-gray-400">Orden {{ $currentOrderId ? '#'.str_pad($currentOrderId, 5, '0', STR_PAD_LEFT) : 'Nueva' }}</div>
                    </div>
                    <button wire:click="backToTables" class="p-2 text-gray-400 hover:text-white rounded-lg hover:bg-gray-800">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto p-2">
                    @forelse($cart as $key => $item)
                        <div class="flex gap-2 p-3 bg-gray-800 rounded-lg mb-2 relative group {{ $item['order_item_id'] ? 'border-l-4 border-green-500' : 'border-l-4 border-orange-500' }}">
                            <div class="flex flex-col gap-1">
                                <button wire:click="addToCart({{ $item['dish_id'] }})" class="w-8 h-8 bg-gray-700 rounded flex items-center justify-center font-bold">+</button>
                                <div class="w-8 h-8 flex items-center justify-center font-bold">{{ $item['quantity'] }}</div>
                                <button wire:click="removeFromCart('{{ $key }}')" class="w-8 h-8 bg-gray-700 rounded flex items-center justify-center font-bold">-</button>
                            </div>
                            <div class="flex-1">
                                <div class="font-bold whitespace-normal leading-tight">{{ $item['name'] }}</div>
                                @if(!empty($item['modifiers']))
                                    <div class="text-xs text-orange-400 mt-1 space-y-0.5">
                                        @foreach($item['modifiers'] as $mod)
                                            <div>+ {{ $mod['name'] }}</div>
                                        @endforeach
                                    </div>
                                @endif
                                <div class="text-sm text-gray-400 mt-1">€{{ number_format($item['unit_price'], 2) }}</div>
                                @if($item['order_item_id'])
                                    <span class="text-xs bg-green-900 text-green-300 px-2 py-0.5 rounded mt-1 inline-block">Enviado</span>
                                @else
                                    <span class="text-xs bg-orange-900 text-orange-300 px-2 py-0.5 rounded mt-1 inline-block">Pendiente</span>
                                @endif
                            </div>
                            <div class="font-bold">€{{ number_format($item['line_total'], 2) }}</div>
                        </div>
                    @empty
                        <div class="h-full flex items-center justify-center text-gray-500">
                            La cuenta está vacía
                        </div>
                    @endforelse
                </div>

                <!-- CART TOTALS -->
                <div class="bg-gray-950 p-4 border-t border-gray-800">
                    <div class="flex justify-between text-gray-400 mb-1">
                        <span>Subtotal</span>
                        <span>€{{ number_format($this->subtotal, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-gray-400 mb-3">
                        <span>Impuestos</span>
                        <span>€{{ number_format($this->tax, 2) }}</span>
                    </div>
                    <div class="flex justify-between font-bold text-2xl text-orange-500 mb-4">
                        <span>Total</span>
                        <span>€{{ number_format($this->total, 2) }}</span>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <button wire:click="sendToKitchen" class="py-4 bg-orange-600 hover:bg-orange-500 rounded-xl font-bold flex items-center justify-center gap-2">
                            Marchar
                        </button>
                        <button wire:click="$set('showPaymentModal', true)" @if(empty($cart)) disabled @endif class="py-4 bg-green-600 hover:bg-green-500 disabled:opacity-50 disabled:bg-gray-800 rounded-xl font-bold flex items-center justify-center gap-2">
                            Cobrar
                        </button>
                    </div>
                </div>
            </div>

            <!-- RIGHT PANEL: MENU -->
            <div class="flex-1 flex flex-col bg-gray-950">
                <!-- CATEGORIES -->
                <div class="px-4 py-4 border-b border-gray-800">
                    <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 xl:grid-cols-8 gap-3">
                        <button wire:click="selectCategory(null)" class="h-20 w-full rounded-xl font-bold flex flex-col items-center justify-center transition border-2 {{ is_null($selectedCategoryId) ? 'border-orange-500 bg-orange-500 text-white' : 'border-gray-700 bg-gray-800 text-gray-400 hover:border-gray-500' }}">Todos</button>
                        @foreach($this->categories as $category)
                            <button wire:click="selectCategory({{ $category->id }})" class="h-20 w-full rounded-xl font-bold transition border-2 relative overflow-hidden group {{ $selectedCategoryId === $category->id ? 'border-orange-500 shadow-[0_0_15px_rgba(249,115,22,0.5)]' : 'border-gray-700 hover:border-gray-500' }}">
                                @if($category->image)
                                    <div class="absolute inset-0 bg-cover bg-center transition duration-500 group-hover:scale-110" style="background-image: url('{{ Storage::url($category->image) }}')"></div>
                                    <div class="absolute inset-0 bg-black/60 group-hover:bg-black/50 transition"></div>
                                @else
                                    <div class="absolute inset-0 bg-gray-800"></div>
                                @endif
                                <div class="absolute inset-0 flex flex-col items-center justify-center p-2 text-center text-white text-sm whitespace-normal leading-tight z-10 {{ $selectedCategoryId === $category->id ? 'text-orange-100' : '' }}">
                                    @if($category->icon) <span class="text-xl mb-1">{{ $category->icon }}</span> @endif
                                    <span>{{ $category->name }}</span>
                                </div>
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- DISHES GRID -->
                <div class="flex-1 p-4 overflow-y-auto">
                    <!-- SEARCH -->
                    <div class="mb-4 relative">
                        <input wire:model.live="searchQuery" type="text" placeholder="Buscar plato..." class="w-full bg-gray-900 border-gray-700 rounded-xl px-4 py-3 text-white focus:border-orange-500 focus:ring-orange-500">
                    </div>

                    <div class="grid grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                        @foreach($this->dishes as $dish)
                            <button wire:click="addToCart({{ $dish->id }})" class="bg-gray-800 rounded-xl overflow-hidden hover:ring-2 hover:ring-orange-500 transition text-left flex flex-col h-full active:scale-95 border-2 border-gray-700 hover:border-orange-500">
                                @if($dish->image)
                                    <img src="{{ Storage::url($dish->image) }}" alt="{{ $dish->name }}" class="w-full h-40 object-cover shrink-0">
                                @else
                                    <div class="w-full h-40 shrink-0 bg-gray-700 flex items-center justify-center text-gray-500">Sin foto</div>
                                @endif
                                <div class="p-4 flex-1 flex flex-col justify-between bg-gray-900 w-full border-t border-gray-800">
                                    <div class="font-bold text-lg leading-tight line-clamp-2">{{ $dish->name }}</div>
                                    <div class="text-orange-400 font-bold mt-2 text-xl">€{{ number_format($dish->price, 2) }}</div>
                                </div>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
            
            <!-- MODIFIER MODAL -->
            @if($showModifierModal && $this->activeDishForModifiers)
            <div class="fixed inset-0 bg-black/80 flex items-center justify-center z-50">
                <div class="bg-gray-900 rounded-2xl w-full max-w-2xl overflow-hidden border border-gray-700 shadow-2xl flex flex-col max-h-[90vh]">
                    <div class="p-6 border-b border-gray-800 flex justify-between items-center bg-gray-950 shrink-0">
                        <div>
                            <h3 class="text-2xl font-bold">Personalizar</h3>
                            <div class="text-orange-500 font-bold text-lg">{{ $this->activeDishForModifiers->name }}</div>
                        </div>
                        <button wire:click="$set('showModifierModal', false)" class="text-gray-400 hover:text-white w-10 h-10 flex items-center justify-center rounded-full bg-gray-800 hover:bg-gray-700 transition">✕</button>
                    </div>

                    <!-- ERROR MESSAGE FOR REQUIRED GROUPS -->
                    @if(session()->has('error'))
                        <div class="bg-red-900/50 border-l-4 border-red-500 px-6 py-3 shrink-0">
                            <div class="text-red-200 font-bold">{{ session('error') }}</div>
                        </div>
                    @endif

                    <div class="p-6 overflow-y-auto flex-1 bg-gray-900 space-y-8">
                        @foreach($this->activeDishForModifiers->modifierGroups as $group)
                            <div class="border border-gray-800 rounded-xl overflow-hidden bg-gray-950">
                                <div class="bg-gray-800/50 px-4 py-3 border-b border-gray-800 flex justify-between items-center">
                                    <h4 class="font-bold text-lg">{{ $group->name }}</h4>
                                    <div class="flex gap-2 text-xs font-bold uppercase tracking-wider">
                                        @if($group->is_required)
                                            <span class="bg-orange-500/20 text-orange-400 px-2 py-1 rounded">Obligatorio</span>
                                        @else
                                            <span class="bg-gray-700 text-gray-400 px-2 py-1 rounded">Opcional</span>
                                        @endif
                                        @if(!$group->is_multiple_choice)
                                            <span class="bg-blue-500/20 text-blue-400 px-2 py-1 rounded">Elige 1</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="p-2 space-y-1">
                                    @foreach($group->modifiers as $mod)
                                        @php
                                            $isSelected = false;
                                            if (isset($selectedModifiers[$group->id]) && in_array($mod->id, $selectedModifiers[$group->id])) {
                                                $isSelected = true;
                                            }
                                        @endphp
                                        <button wire:click="toggleModifier({{ $group->id }}, {{ $mod->id }}, {{ $group->is_multiple_choice ? 'true' : 'false' }})" 
                                            class="w-full flex justify-between items-center p-4 rounded-lg transition {{ $isSelected ? 'bg-orange-500/20 border border-orange-500/50 text-white' : 'hover:bg-gray-800 border border-transparent text-gray-300' }}">
                                            <div class="flex items-center gap-3">
                                                <div class="w-6 h-6 rounded {{ $group->is_multiple_choice ? 'border-2 border-gray-600' : 'border-2 border-gray-600 rounded-full' }} flex items-center justify-center transition-colors {{ $isSelected ? ($group->is_multiple_choice ? 'bg-orange-500 border-orange-500' : 'border-orange-500') : '' }}">
                                                    @if($isSelected)
                                                        @if($group->is_multiple_choice)
                                                            <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                                        @else
                                                            <div class="w-3 h-3 bg-orange-500 rounded-full"></div>
                                                        @endif
                                                    @endif
                                                </div>
                                                <span class="font-medium text-lg">{{ $mod->name }}</span>
                                            </div>
                                            @if($mod->price > 0)
                                                <span class="font-bold {{ $isSelected ? 'text-orange-400' : 'text-gray-400' }}">+€{{ number_format($mod->price, 2) }}</span>
                                            @endif
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="p-6 border-t border-gray-800 bg-gray-950 shrink-0">
                        <button wire:click="confirmModifiers" class="w-full py-4 bg-orange-600 hover:bg-orange-500 rounded-xl font-bold text-xl transition flex justify-center items-center gap-2">
                            Añadir al Pedido
                        </button>
                    </div>
                </div>
            </div>
            @endif

            <!-- PAYMENT MODAL -->
            @if($showPaymentModal)
            <div class="fixed inset-0 bg-black/80 flex items-center justify-center z-50">
                <div class="bg-gray-900 rounded-2xl w-full max-w-lg overflow-hidden border border-gray-700 shadow-2xl">
                    <div class="p-6 border-b border-gray-800 flex justify-between items-center">
                        <h3 class="text-2xl font-bold">Cobrar Cuenta</h3>
                        <button wire:click="$set('showPaymentModal', false)" class="text-gray-400 hover:text-white">✕</button>
                    </div>
                    <div class="p-6">
                        <div class="text-center mb-6">
                            <div class="text-gray-400">Total a pagar</div>
                            <div class="text-5xl font-bold text-orange-500">€{{ number_format($this->total, 2) }}</div>
                        </div>

                        <!-- SPLIT BILL CALCULATOR -->
                        <div class="mb-6 flex flex-col gap-2">
                            <div class="flex items-center justify-between bg-gray-950 p-4 rounded-xl border border-gray-800">
                                <span class="text-gray-400 font-bold text-lg">Dividir cuenta:</span>
                                <div class="flex items-center gap-4">
                                    <button wire:click="decrementSplit" class="w-10 h-10 bg-gray-700 hover:bg-gray-600 rounded-lg text-white font-bold text-2xl transition">-</button>
                                    <span class="text-2xl font-bold w-6 text-center">{{ $splitWays }}</span>
                                    <button wire:click="incrementSplit" class="w-10 h-10 bg-gray-700 hover:bg-gray-600 rounded-lg text-white font-bold text-2xl transition">+</button>
                                </div>
                            </div>

                            @if($splitWays > 1)
                            <div class="flex justify-between items-center bg-yellow-900/30 border border-yellow-700/50 p-4 rounded-xl">
                                <span class="text-yellow-500 font-bold">Cada persona paga</span>
                                <span class="text-3xl font-bold text-yellow-400">€{{ number_format($this->total / $splitWays, 2) }}</span>
                            </div>
                            @endif
                        </div>

                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <button wire:click="$set('paymentMethod', 'cash')" class="p-4 rounded-xl font-bold border-2 transition {{ $paymentMethod === 'cash' ? 'border-orange-500 bg-orange-900/40 text-orange-400' : 'border-gray-700 hover:bg-gray-800' }}">💵 Efectivo</button>
                            <button wire:click="$set('paymentMethod', 'card')" class="p-4 rounded-xl font-bold border-2 transition {{ $paymentMethod === 'card' ? 'border-orange-500 bg-orange-900/40 text-orange-400' : 'border-gray-700 hover:bg-gray-800' }}">💳 Tarjeta</button>
                        </div>

                        @if($paymentMethod === 'cash')
                        <div class="mb-6">
                            <label class="text-sm text-gray-400 mb-2 block">Efectivo recibido</label>
                            <input wire:model.live="cashReceived" type="number" step="0.01" class="w-full bg-gray-800 border-gray-700 rounded-xl px-4 py-4 text-2xl font-bold text-center mb-4">
                            
                            <!-- QUICK CASH BUTTONS -->
                            <div class="grid grid-cols-4 gap-2 mb-4">
                                @foreach([5, 10, 20, 50] as $bill)
                                    <button wire:click="$set('cashReceived', {{ $bill }})" class="bg-gray-700 py-2 rounded font-bold hover:bg-gray-600">€{{ $bill }}</button>
                                @endforeach
                            </div>

                            <div class="flex justify-between bg-gray-950 p-4 rounded-xl border border-gray-800">
                                <span class="text-gray-400">Cambio a devolver</span>
                                <span class="font-bold text-xl {{ $this->change > 0 ? 'text-green-500' : 'text-gray-500' }}">€{{ number_format($this->change, 2) }}</span>
                            </div>
                        </div>
                        @endif

                        <button wire:click="processPayment" class="w-full py-4 bg-green-600 hover:bg-green-500 rounded-xl font-bold text-xl transition">
                            Confirmar Cobro
                        </button>
                    </div>
                </div>
            </div>
            @endif

        </div>
    @endif

    <!-- OPEN REGISTER MODAL -->
    @if($showOpenRegisterModal)
    <div class="fixed inset-0 bg-black/90 flex items-center justify-center z-[100] backdrop-blur-sm">
        <div class="bg-gray-900 rounded-2xl w-full max-w-md overflow-hidden border-2 border-orange-500 shadow-[0_0_30px_rgba(249,115,22,0.3)]">
            <div class="p-6 border-b border-gray-800 bg-gray-950 text-center">
                <div class="w-16 h-16 bg-orange-500/20 text-orange-500 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <h3 class="text-2xl font-bold">Apertura de Caja</h3>
                <p class="text-gray-400 mt-2">Introduce el fondo inicial de monedas y billetes para comenzar el turno.</p>
            </div>
            <div class="p-6">
                <form wire:submit.prevent="openRegister">
                    <div class="mb-6">
                        <label class="text-sm text-gray-400 mb-2 block font-bold">Fondo de Caja (Efectivo Físico)</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-2xl text-gray-500">€</span>
                            <input wire:model="openingAmount" type="number" step="0.01" min="0" required autofocus 
                                class="w-full bg-gray-800 border-2 border-gray-700 focus:border-orange-500 rounded-xl pl-12 pr-4 py-4 text-3xl font-bold text-white transition placeholder-gray-600" placeholder="0.00">
                        </div>
                    </div>
                    
                    <button type="submit" class="w-full py-4 bg-orange-600 hover:bg-orange-500 rounded-xl font-bold text-xl transition flex justify-center items-center gap-2">
                        <span>Iniciar Turno y Abrir Caja</span>
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </button>
                    
                    @if(auth()->user() && !auth()->user()->hasRole('camarero'))
                        <a href="{{ url('/admin') }}" data-navigate-ignore="true" class="block text-center mt-4 text-gray-500 hover:text-white transition">Volver al Panel</a>
                    @endif
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- CLOSE REGISTER MODAL (Z-REPORT) -->
    @if($showCloseRegisterModal && $activeRegister)
    <div class="fixed inset-0 bg-black/90 flex items-center justify-center z-[100] backdrop-blur-sm">
        <div class="bg-gray-900 rounded-2xl w-full max-w-lg overflow-hidden border border-gray-700 shadow-2xl flex flex-col max-h-[90vh]">
            <div class="p-6 border-b border-gray-800 bg-gray-950 flex justify-between items-center shrink-0">
                <div>
                    <h3 class="text-2xl font-bold">Cierre de Caja (Reporte Z)</h3>
                    <div class="text-gray-400">Turno abierto a las {{ $activeRegister->opened_at->format('H:i') }}</div>
                </div>
                <button wire:click="$set('showCloseRegisterModal', false)" class="text-gray-400 hover:text-white w-10 h-10 flex items-center justify-center rounded-full bg-gray-800 hover:bg-gray-700 transition">✕</button>
            </div>
            
            <div class="p-6 overflow-y-auto flex-1 space-y-6">
                <!-- Resumen de Movimientos -->
                <div class="bg-gray-800 rounded-xl p-4 border border-gray-700 space-y-2">
                    <div class="flex justify-between text-gray-400">
                        <span>Fondo inicial:</span>
                        <span class="font-mono text-white">€{{ number_format($activeRegister->opening_amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-gray-400">
                        <span>Ventas en Efectivo:</span>
                        <span class="font-mono text-green-400">+ €{{ number_format($cashSales, 2) }}</span>
                    </div>
                    @if($cashIn > 0)
                    <div class="flex justify-between text-gray-400">
                        <span>Entradas manuales:</span>
                        <span class="font-mono text-green-400">+ €{{ number_format($cashIn, 2) }}</span>
                    </div>
                    @endif
                    @if($cashOut > 0)
                    <div class="flex justify-between text-gray-400">
                        <span>Salidas (Pagos/Gastos):</span>
                        <span class="font-mono text-red-400">- €{{ number_format($cashOut, 2) }}</span>
                    </div>
                    @endif
                    
                    <div class="pt-2 border-t border-gray-700 flex justify-between items-center mt-2">
                        <span class="font-bold">Efectivo Esperado (Sistema):</span>
                        <span class="text-xl font-bold font-mono">€{{ number_format($expectedAmount, 2) }}</span>
                    </div>
                </div>

                <!-- Efectivo Real -->
                <form wire:submit.prevent="closeRegister">
                    <div class="mb-4">
                        <label class="text-sm text-gray-400 mb-2 block font-bold">Efectivo Físico en Cajón (Contado)</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-2xl text-gray-500">€</span>
                            <input wire:model.live="closingAmount" type="number" step="0.01" min="0" required autofocus 
                                class="w-full bg-gray-950 border-2 {{ ($closingAmount - $expectedAmount) == 0 ? 'border-green-500' : 'border-red-500' }} rounded-xl pl-12 pr-4 py-4 text-3xl font-bold text-white transition placeholder-gray-600">
                        </div>
                    </div>
                    
                    @php
                        $diff = $closingAmount - $expectedAmount;
                    @endphp
                    
                    @if($diff != 0)
                        <div class="p-3 mb-6 rounded-lg {{ $diff > 0 ? 'bg-orange-900/50 text-orange-200 border border-orange-700' : 'bg-red-900/50 text-red-200 border border-red-700' }}">
                            <div class="font-bold flex items-center gap-2">
                                ⚠️ Atención: Hay un descuadre
                            </div>
                            <div class="text-sm">
                                Se registrará un {{ $diff > 0 ? 'SOBRANTE' : 'FALTANTE' }} de <strong>€{{ number_format(abs($diff), 2) }}</strong>.
                            </div>
                        </div>
                    @else
                        <div class="p-3 mb-6 rounded-lg bg-green-900/30 text-green-300 border border-green-800 text-center font-bold">
                            ✅ La caja cuadra perfectamente.
                        </div>
                    @endif

                    <button type="submit" class="w-full py-4 bg-purple-600 hover:bg-purple-500 rounded-xl font-bold text-xl transition flex justify-center items-center gap-2 text-white">
                        <span>Cerrar Turno e Imprimir Z</span>
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- MANUAL CASH IN/OUT MODAL -->
    @if($showManualCashModal && $activeRegister)
    <div class="fixed inset-0 bg-black/90 flex items-center justify-center z-[100] backdrop-blur-sm">
        <div class="bg-gray-900 rounded-2xl w-full max-w-md overflow-hidden border {{ $manualCashType === 'cash_in' ? 'border-green-500 shadow-[0_0_30px_rgba(34,197,94,0.3)]' : 'border-red-500 shadow-[0_0_30px_rgba(239,68,68,0.3)]' }}">
            <div class="p-6 border-b border-gray-800 bg-gray-950 flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center {{ $manualCashType === 'cash_in' ? 'bg-green-500/20 text-green-500' : 'bg-red-500/20 text-red-500' }}">
                        @if($manualCashType === 'cash_in')
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        @else
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                        @endif
                    </div>
                    <h3 class="text-2xl font-bold">{{ $manualCashType === 'cash_in' ? 'Nuevo Ingreso' : 'Retiro de Caja' }}</h3>
                </div>
                <button wire:click="$set('showManualCashModal', false)" class="text-gray-400 hover:text-white w-10 h-10 flex items-center justify-center rounded-full bg-gray-800 hover:bg-gray-700 transition">✕</button>
            </div>
            <div class="p-6 bg-gray-900">
                <form wire:submit.prevent="submitManualCash">
                    <div class="mb-4">
                        <label class="text-sm text-gray-400 mb-2 block font-bold">Importe (Efectivo)</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-2xl text-gray-500">€</span>
                            <input wire:model="manualCashAmount" type="number" step="0.01" min="0.01" required autofocus 
                                class="w-full bg-gray-950 border-2 border-gray-700 {{ $manualCashType === 'cash_in' ? 'focus:border-green-500' : 'focus:border-red-500' }} rounded-xl pl-12 pr-4 py-4 text-3xl font-bold text-white transition placeholder-gray-600" placeholder="0.00">
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="text-sm text-gray-400 mb-2 block font-bold">Concepto / Motivo</label>
                        <input wire:model="manualCashNotes" type="text" required 
                            class="w-full bg-gray-800 border-gray-700 rounded-xl px-4 py-3 text-white focus:border-orange-500 focus:ring-orange-500" 
                            placeholder="{{ $manualCashType === 'cash_in' ? 'Ej: Cambio traído del banco' : 'Ej: Pago a proveedor de pan' }}">
                        @error('manualCashNotes') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                    </div>
                    
                    <button type="submit" class="w-full py-4 rounded-xl font-bold text-xl transition flex justify-center items-center gap-2 text-white {{ $manualCashType === 'cash_in' ? 'bg-green-600 hover:bg-green-500' : 'bg-red-600 hover:bg-red-500' }}">
                        <span>Registrar {{ $manualCashType === 'cash_in' ? 'Ingreso' : 'Retiro' }}</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- NOTIFICATIONS -->
    @if(session()->has('success'))
        <div class="fixed bottom-4 right-4 bg-green-600 text-white px-6 py-4 rounded-xl shadow-lg font-bold z-50 animate-bounce">
            {{ session('success') }}
        </div>
        <script>setTimeout(() => { document.querySelector('.animate-bounce').style.display = 'none'; }, 3000);</script>
    @endif

    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('print-z-report', (event) => {
                // Open the PDF in a new window/tab for printing
                window.open(event[0].url, '_blank', 'width=400,height=600');
            });
        });
    </script>
</div>
