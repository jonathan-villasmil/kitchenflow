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
                                <div class="font-bold">{{ $item['name'] }}</div>
                                <div class="text-sm text-gray-400">€{{ number_format($item['unit_price'], 2) }}</div>
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
                <div class="p-4 border-b border-gray-800 overflow-x-auto whitespace-nowrap hide-scrollbar flex gap-2">
                    <button wire:click="selectCategory(null)" class="px-6 py-3 rounded-xl font-bold transition {{ is_null($selectedCategoryId) ? 'bg-orange-500 text-white' : 'bg-gray-800 text-gray-400 hover:bg-gray-700' }}">Todos</button>
                    @foreach($this->categories as $category)
                        <button wire:click="selectCategory({{ $category->id }})" class="px-6 py-3 rounded-xl font-bold transition {{ $selectedCategoryId === $category->id ? 'bg-orange-500 text-white' : 'bg-gray-800 text-gray-400 hover:bg-gray-700' }}">
                            {{ $category->name }}
                        </button>
                    @endforeach
                </div>

                <!-- DISHES GRID -->
                <div class="flex-1 p-4 overflow-y-auto">
                    <!-- SEARCH -->
                    <div class="mb-4 relative">
                        <input wire:model.live="searchQuery" type="text" placeholder="Buscar plato..." class="w-full bg-gray-900 border-gray-700 rounded-xl px-4 py-3 text-white focus:border-orange-500 focus:ring-orange-500">
                    </div>

                    <div class="grid grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                        @foreach($this->dishes as $dish)
                            <button wire:click="addToCart({{ $dish->id }})" class="bg-gray-800 rounded-xl overflow-hidden hover:ring-2 hover:ring-orange-500 transition text-left flex flex-col h-full active:scale-95">
                                @if($dish->image)
                                    <div class="h-32 bg-gray-900 w-full bg-cover bg-center" style="background-image: url('{{ Storage::url($dish->image) }}')"></div>
                                @else
                                    <div class="h-32 bg-gray-700 w-full flex items-center justify-center text-gray-500">Sin foto</div>
                                @endif
                                <div class="p-3 flex-1 flex flex-col justify-between">
                                    <div class="font-bold leading-tight line-clamp-2">{{ $dish->name }}</div>
                                    <div class="text-orange-400 font-bold mt-2">€{{ number_format($dish->price, 2) }}</div>
                                </div>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
            
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

    <!-- NOTIFICATIONS -->
    @if(session()->has('success'))
        <div class="fixed bottom-4 right-4 bg-green-600 text-white px-6 py-4 rounded-xl shadow-lg font-bold z-50 animate-bounce">
            {{ session('success') }}
        </div>
        <script>setTimeout(() => { document.querySelector('.animate-bounce').style.display = 'none'; }, 3000);</script>
    @endif
</div>
