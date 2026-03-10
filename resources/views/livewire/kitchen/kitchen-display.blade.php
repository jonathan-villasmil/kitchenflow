<div>
    <!-- HEADER -->
    <div class="h-16 bg-gray-900 border-b border-gray-800 flex justify-between items-center px-6">
        <h1 class="text-2xl font-bold flex items-center gap-3">
            <span>🧑‍🍳 KDS</span>
            <span class="bg-gray-800 text-orange-400 px-3 py-1 rounded text-sm tracking-widest uppercase">
                {{ match($station) { 'hot' => '🔥 Caliente', 'cold' => '❄️ Fría', 'bar' => '🍹 Barra', 'bakery' => '🥐 Panadería', default => $station } }}
            </span>
        </h1>

        <div class="flex gap-4">
            <select wire:model.live="station" class="bg-gray-800 border-gray-700 rounded-lg text-white">
                <option value="hot">🔥 Cocina Caliente</option>
                <option value="cold">❄️ Cocina Fría</option>
                <option value="bar">🍹 Barra</option>
                <option value="bakery">🥐 Panadería</option>
            </select>

            <button wire:click="$toggle('soundEnabled')" class="p-2 rounded-lg {{ $soundEnabled ? 'bg-green-600' : 'bg-red-600' }}">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"></path></svg>
            </button>
            @if(auth()->user() && !auth()->user()->hasRole('cocinero'))
                <a href="{{ url('/admin') }}" data-navigate-ignore="true" class="px-4 py-2 bg-gray-800 hover:bg-gray-700 rounded-lg text-gray-300">Volver Admin</a>
            @else
                <!-- Direct logout for cocineros -->
                <form method="POST" action="{{ route('filament.admin.auth.logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-red-600 rounded-lg text-white hover:bg-red-500 transition">Salir</button>
                </form>
            @endif
        </div>
    </div>

    <!-- TICKETS GRID -->
    <div class="p-6 h-[calc(100vh-4rem)] overflow-y-auto overflow-x-hidden" wire:poll.5s>
        <div class="flex flex-wrap gap-4 items-start">
            
            @forelse($this->activeOrders as $order)
                @php
                    $minutesWaiting = $order->created_at->diffInMinutes(now());
                    $urgent = $minutesWaiting > 15;
                    $warning = $minutesWaiting > 10;
                @endphp

                <div class="w-80 bg-gray-900 border-2 rounded-xl flex flex-col overflow-hidden shadow-2xl transition-all
                            {{ $urgent ? 'border-red-500 animate-pulse' : ($warning ? 'border-orange-500' : 'border-gray-700') }}">
                    
                    <!-- Ticket Header -->
                    <div class="p-4 {{ $urgent ? 'bg-red-900/50' : ($warning ? 'bg-orange-900/50' : 'bg-gray-800') }}">
                        <div class="flex justify-between items-start mb-2">
                            <span class="font-mono text-xl font-bold text-white">#{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</span>
                            <span class="font-bold text-lg px-2 py-0.5 rounded bg-gray-950/50">
                                {{ $order->table ? 'Mesa '.$order->table->number : ($order->type === 'takeaway' ? 'Llevar' : 'Delivery') }}
                            </span>
                        </div>
                        <div class="flex justify-between text-sm text-gray-400">
                            <span>{{ $order->user->name ?? 'Sistema' }}</span>
                            <span class="{{ $urgent ? 'text-red-400 font-bold' : '' }}">{{ $minutesWaiting }} min</span>
                        </div>
                    </div>

                    <!-- Ticket Items -->
                    <div class="flex-1 p-2 bg-gray-950/50">
                        @foreach($order->items as $item)
                            <button wire:click="markAsReady({{ $item->id }})" class="w-full text-left p-3 mb-2 rounded-lg bg-gray-800 hover:bg-gray-700 border-l-4 border-orange-500 transition active:scale-95 group">
                                <div class="flex gap-3">
                                    <div class="font-mono text-xl font-bold">{{ $item->quantity }}</div>
                                    <div class="flex-1">
                                        <div class="font-bold text-lg group-hover:text-orange-400">{{ $item->name }}</div>
                                        @if($item->notes)
                                            <div class="text-sm text-yellow-500 bg-yellow-900/20 p-2 mt-1 rounded italic">
                                                ⚠️ {{ $item->notes }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </button>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="w-full h-full flex flex-col items-center justify-center text-gray-500 gap-4 opacity-50 absolute inset-0">
                    <svg class="w-24 h-24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
                    <div class="text-2xl font-bold">No hay pedidos pendientes en esta estación</div>
                </div>
            @endforelse

        </div>
    </div>
</div>
