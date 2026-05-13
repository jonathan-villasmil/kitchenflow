<x-filament-panels::page>

    {{-- ── Presets rápidos ─────────────────────────────────────────────── --}}
    <div style="background:white;border:1px solid #e5e7eb;border-radius:16px;padding:12px 20px;margin-bottom:4px;box-shadow:0 1px 3px rgba(0,0,0,.05);">
        <div style="display:flex;flex-wrap:wrap;align-items:center;gap:8px;">
            <span style="font-size:12px;font-weight:600;color:#6b7280;margin-right:4px;">Periodo rápido:</span>

            @foreach([
                'today' => 'Hoy',
                'week'  => 'Esta semana',
                'month' => 'Este mes',
                'year'  => 'Este año',
            ] as $preset => $label)
                <button
                    wire:click="applyPreset('{{ $preset }}')"
                    style="
                        padding: 5px 14px;
                        border-radius: 20px;
                        font-size: 12px;
                        font-weight: 600;
                        border: 1.5px solid {{ $datePreset === $preset ? '#f97316' : '#e5e7eb' }};
                        background: {{ $datePreset === $preset ? '#f97316' : 'white' }};
                        color: {{ $datePreset === $preset ? 'white' : '#4b5563' }};
                        cursor: pointer;
                    ">
                    {{ $label }}
                </button>
            @endforeach

            <span style="margin-left:auto;font-size:11px;color:#9ca3af;">
                O usa los campos de fecha de abajo para un rango personalizado
            </span>
        </div>
    </div>

</x-filament-panels::page>
