<div>
    <!-- CABECERA FIXED -->
    <header class="fixed top-0 w-full bg-white/80 backdrop-blur-md z-30 border-b border-gray-100 dark:bg-gray-900/80 dark:border-gray-800 transition-all duration-300">
        <div class="max-w-3xl mx-auto px-4 h-16 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full bg-orange-600 flex flex-col justify-center items-center drop-shadow-md">
                    <span class="text-white font-black text-xs">KF</span>
                </div>
                <div>
                    <h1 class="font-bold text-gray-900 dark:text-white leading-tight">Mesa {{ $table->number }}</h1>
                    <p class="text-[10px] text-gray-500 font-medium uppercase tracking-wider">Carta Digital V.I.P</p>
                </div>
            </div>
            
            <button wire:click="toggleCart" class="relative p-2 bg-gray-100 dark:bg-gray-800 rounded-full hover:scale-105 active:scale-95 transition">
                <span class="text-xl">🧺</span>
                @if($this->cartCount > 0)
                <span class="absolute -top-1 -right-1 bg-red-500 text-white w-5 h-5 rounded-full text-[10px] font-bold flex items-center justify-center animate-bounce shadow-sm">
                    {{ $this->cartCount }}
                </span>
                @endif
            </button>
        </div>

        <!-- CATEGORIAS -->
        <div class="max-w-3xl mx-auto px-4 pb-3 overflow-x-auto no-scrollbar flex gap-2">
            <button wire:click="selectCategory(null)" class="shrink-0 px-4 py-1.5 rounded-full text-sm font-bold transition duration-200 {{ is_null($selectedCategoryId) ? 'bg-orange-600 text-white shadow-md shadow-orange-600/30' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300' }}">
                Todos
            </button>
            @foreach($this->categories as $category)
                <button wire:click="selectCategory({{ $category->id }})" class="shrink-0 px-4 py-1.5 rounded-full text-sm font-bold transition duration-200 {{ $selectedCategoryId === $category->id ? 'bg-orange-600 text-white shadow-md shadow-orange-600/30' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300' }}">
                    {{ $category->name }}
                </button>
            @endforeach
        </div>
    </header>

    <!-- SPACER PARA LA CABECERA (h-16 + p-3 aprox = 28 * 0.25rem = 7rem) -->
    <div class="pt-28 pb-32 max-w-3xl mx-auto px-4">
        
        @if(session('success'))
            <div class="mb-6 p-4 rounded-2xl bg-green-50 border border-green-200 text-green-800 text-sm font-medium animate-in fade-in slide-in-from-top-4 duration-300 flex items-center gap-3 shadow-sm">
                <span class="text-xl">✨</span>
                {{ session('success') }}
            </div>
        @endif

        <!-- БUSCADOR -->
        <div class="relative mb-6">
            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">🔍</span>
            <input wire:model.live.debounce.300ms="searchQuery" type="text" placeholder="Buscar un plato..." 
                class="w-full bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-800 rounded-2xl pl-11 pr-4 py-3.5 focus:border-orange-500 focus:ring-0 transition shadow-sm font-medium text-gray-900 dark:text-white placeholder:text-gray-400">
        </div>

        <!-- LISTA DE PLATOS -->
        <div class="grid gap-4">
            @forelse($this->dishes as $dish)
                @php
                    $stockInfo = $this->stockMap[$dish->id] ?? ['status' => 'ok', 'portions' => null];
                    $isOut = $stockInfo['status'] === 'out';
                    $isLow = $stockInfo['status'] === 'low';
                @endphp
                <div class="bg-white dark:bg-gray-900 rounded-3xl p-4 flex gap-4 shadow-sm border border-gray-100 dark:border-gray-800 overflow-hidden relative group {{ $isOut ? 'opacity-60' : '' }}">
                    {{-- Badge de stock (esquina superior derecha) --}}
                    @if($isOut)
                        <div class="absolute top-3 right-3 z-10 px-2.5 py-1 bg-red-500 text-white text-[10px] font-black rounded-full shadow-md tracking-wide uppercase">
                            🚫 Agotado
                        </div>
                    @elseif($isLow)
                        <div class="absolute top-3 right-3 z-10 px-2.5 py-1 bg-amber-400 text-gray-900 text-[10px] font-black rounded-full shadow-md tracking-wide uppercase animate-pulse">
                            ⚠️ Últimas {{ $stockInfo['portions'] }}
                        </div>
                    @endif

                    <!-- Image -->
                    <div class="w-24 h-24 shrink-0 rounded-2xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-4xl overflow-hidden {{ ($dish->image ? '' : 'opacity-80') . ($isOut ? ' grayscale' : '') }}">
                        @if($dish->image)
                            <img src="{{ Storage::url($dish->image) }}" class="w-full h-full object-cover">
                        @else
                            🍽️
                        @endif
                    </div>
                    
                    <div class="flex-1 flex flex-col justify-between py-1">
                        <div>
                            <h3 class="font-bold text-gray-900 dark:text-white leading-tight mb-1">{{ $dish->name }}</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2 leading-relaxed">{{ $dish->description ?? 'Plato delicioso preparado al momento.' }}</p>
                        </div>
                        
                        <div class="flex items-end justify-between mt-2">
                            <span class="font-black text-lg text-gray-900 dark:text-white flex items-center gap-1">
                                <span class="text-sm text-orange-500">€</span>{{ number_format($dish->dynamic_price, 2) }}
                            </span>
                            
                            @if($isOut)
                                <div class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-400 flex items-center justify-center cursor-not-allowed" title="Sin stock">
                                    <span class="text-lg">✕</span>
                                </div>
                            @else
                                <button wire:click="addToCart({{ $dish->id }})" 
                                    class="w-10 h-10 rounded-full bg-orange-50 hover:bg-orange-100 text-orange-600 dark:bg-orange-500/10 dark:text-orange-400 dark:hover:bg-orange-500/20 font-black text-xl flex items-center justify-center transition active:scale-95">
                                    +
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-16 px-4 bg-white dark:bg-gray-900 rounded-3xl border border-dashed border-gray-200 dark:border-gray-800">
                    <span class="text-5xl block mb-4">🤷‍♂️</span>
                    <h3 class="font-bold text-gray-500 mb-1">No encontramos platos</h3>
                    <p class="text-sm text-gray-400">Intenta buscar otra cosa o cambia de categoría.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- BOTON FLOTANTE VER CARRITO -->
    @if($this->cartCount > 0 && !$showCart)
        <div class="fixed bottom-6 inset-x-0 px-4 z-40 animate-in slide-in-from-bottom-10 fade-in duration-300 max-w-3xl mx-auto cursor-pointer" wire:click="toggleCart">
            <div class="bg-gray-900 dark:bg-white text-white dark:text-gray-900 rounded-full p-1.5 pl-6 flex items-center justify-between shadow-2xl shadow-gray-900/20 ring-1 ring-gray-900/5">
                <div class="font-medium text-sm flex gap-3">
                    <span class="font-bold bg-white/20 dark:bg-gray-900/10 px-2 py-0.5 rounded-full">{{ $this->cartCount }}</span>
                    <span>Ver mi pedido</span>
                </div>
                <div class="bg-orange-500 text-white font-black text-sm px-6 py-3 rounded-full">
                    €{{ number_format($this->cartTotal, 2) }}
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL DE CARRITO -->
    @if($showCart)
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 animate-in fade-in flex flex-col justify-end">
            <div class="bg-gray-50 dark:bg-gray-950 w-full max-w-3xl mx-auto rounded-t-3xl pt-2 pb-6 px-4 flex flex-col max-h-[85vh] animate-in slide-in-from-bottom-full duration-300 shadow-2xl">
                <!-- Mango / Drag handle -->
                <div class="w-12 h-1.5 bg-gray-300 dark:bg-gray-700 rounded-full mx-auto mb-4 cursor-pointer" wire:click="toggleCart"></div>
                
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">Tu Pedido</h2>
                    <button wire:click="toggleCart" class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-200 dark:bg-gray-800 text-gray-600 dark:text-gray-400 font-bold hover:scale-105 active:scale-95 transition">✕</button>
                </div>

                <div class="flex-1 overflow-y-auto no-scrollbar space-y-4 mb-4">
                    @forelse($cart as $key => $item)
                        <div class="flex gap-4 p-4 bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm">
                            <div class="flex-1">
                                <div class="font-bold text-gray-900 dark:text-white leading-tight">{{ $item['name'] }}</div>
                                @if(!empty($item['modifiers']))
                                    <ul class="text-[10px] text-gray-500 dark:text-gray-400 mt-1 space-y-0.5 font-medium">
                                        @foreach($item['modifiers'] as $mod)
                                            <li>+ {{ $mod['name'] }} @if($mod['price'] > 0) <span class="bg-gray-100 dark:bg-gray-800 text-gray-500 rounded px-1">€{{ number_format($mod['price'], 2) }}</span> @endif</li>
                                        @endforeach
                                    </ul>
                                @endif
                                <div class="text-orange-500 font-black mt-2">€{{ number_format($item['line_total'], 2) }}</div>
                            </div>
                            
                            <!-- Controles de cantidad -->
                            <div class="flex items-center bg-gray-50 dark:bg-gray-950 rounded-full border border-gray-200 dark:border-gray-800 h-10 w-24">
                                <button wire:click="decrementCartItem('{{ $key }}')" class="flex-1 flex justify-center items-center font-black text-gray-500 hover:text-orange-500 transition">-</button>
                                <span class="font-bold text-gray-900 dark:text-white text-sm w-4 text-center">{{ $item['quantity'] }}</span>
                                <button wire:click="incrementCartItem('{{ $key }}')" class="flex-1 flex justify-center items-center font-black text-gray-500 hover:text-orange-500 transition">+</button>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <span class="text-4xl block mb-2 opacity-50">🛒</span>
                            <p class="text-gray-400 font-medium text-sm">Aún no has añadido nada al pedido.</p>
                        </div>
                    @endforelse
                </div>

                @if(!empty($cart))
                <div class="pt-4 border-t border-gray-200 dark:border-gray-800">
                    <div class="flex justify-between items-end mb-6">
                        <span class="text-gray-500 font-medium">Total (Estimado)</span>
                        <span class="text-4xl font-black text-gray-900 dark:text-white tracking-tight">€{{ number_format($this->cartTotal, 2) }}</span>
                    </div>
                    <button wire:click="submitOrder" class="w-full py-4 bg-gray-900 dark:bg-white text-white dark:text-gray-900 rounded-2xl font-black text-lg transition flex justify-center items-center gap-2 hover:scale-[1.02] active:scale-[0.98] shadow-xl shadow-gray-900/10">
                        Enviar a Cocina
                        <span class="text-xl">👨‍🍳</span>
                    </button>
                    <p class="text-center text-xs text-gray-400 font-medium mt-4">Pagarás al final con el camarero. Al enviar, confirmamos que todo marcha.</p>
                </div>
                @endif
            </div>
        </div>
    @endif

    <!-- MODAL DE MODIFICADORES -->
    @if($showModifierModal && $this->activeDishForModifiers)
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[60] flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-900 rounded-3xl w-full max-w-md overflow-hidden animate-in zoom-in-95 duration-200 shadow-2xl">
                <!-- Cover Area -->
                <div class="h-32 bg-orange-100 dark:bg-orange-950 flex flex-col items-center justify-center text-center p-6 relative">
                    <button wire:click="$set('showModifierModal', false)" class="absolute top-4 right-4 w-8 h-8 bg-white/20 hover:bg-white/30 backdrop-blur-md rounded-full text-gray-800 dark:text-white font-bold transition flex justify-center items-center">✕</button>
                    <span class="text-4xl mb-2">✨</span>
                    <h3 class="text-xl font-black text-gray-900 dark:text-orange-400 leading-tight">{{ $this->activeDishForModifiers->name }}</h3>
                </div>
                
                @if(session('error'))
                    <div class="bg-red-50 dark:bg-red-950 text-red-600 dark:text-red-400 text-xs font-bold text-center py-2 px-4 border-b border-red-100 dark:border-red-900">
                        {{ session('error') }}
                    </div>
                @endif

                <div class="p-6 max-h-[50vh] overflow-y-auto no-scrollbar space-y-6">
                    @foreach($this->activeDishForModifiers->modifierGroups as $group)
                        <div class="space-y-3">
                            <h4 class="font-bold text-sm text-gray-900 dark:text-white flex items-center gap-2 uppercase tracking-wide">
                                {{ $group->name }}
                                @if($group->is_required) <span class="bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-500 rounded text-[9px] px-1.5 py-0.5">Obligatorio</span> @endif
                                @if($group->is_multiple_choice) <span class="text-gray-400 text-[10px] lowercase font-normal">(Varias opciones permitidas)</span> @endif
                            </h4>
                            <div class="grid grid-cols-2 gap-2">
                                @foreach($group->modifiers as $mod)
                                    @php
                                        $isSelected = isset($this->selectedModifiers[$group->id]) && in_array($mod->id, $this->selectedModifiers[$group->id]);
                                    @endphp
                                    <button wire:click="toggleModifier({{ $group->id }}, {{ $mod->id }}, {{ $group->is_multiple_choice ? 'true' : 'false' }})" 
                                            class="p-3 rounded-2xl text-left transition border-2 text-sm font-bold flex flex-col gap-1 {{ $isSelected ? 'border-orange-500 bg-orange-50 dark:bg-orange-500/10 text-gray-900 dark:text-white' : 'border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-950 text-gray-500 hover:border-gray-200 dark:hover:border-gray-700' }}">
                                        <div class="flex justify-between items-center w-full">
                                            <span class="truncate pr-2">{{ $mod->name }}</span>
                                            @if($isSelected)
                                                <span class="text-orange-500">✓</span>
                                            @endif
                                        </div>
                                        @if($mod->price > 0)
                                            <span class="text-xs font-black {{ $isSelected ? 'text-orange-500' : 'text-gray-400' }}">+€{{ number_format($mod->price, 2) }}</span>
                                        @endif
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="p-4 border-t border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-950">
                    <button wire:click="confirmModifiers" class="w-full py-3.5 bg-orange-600 hover:bg-orange-500 text-white rounded-2xl font-black text-lg transition shadow-xl shadow-orange-600/20 active:scale-[0.98]">
                        Confirmar y Añadir
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
