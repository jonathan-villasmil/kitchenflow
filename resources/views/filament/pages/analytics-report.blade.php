<x-filament-panels::page>

    <div style="background:white;border:1px solid #e5e7eb;border-radius:16px;overflow:hidden;margin-bottom:8px;box-shadow:0 1px 3px rgba(0,0,0,.06);">

        {{-- Cabecera --}}
        <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 20px;background:#f9fafb;border-bottom:1px solid #f3f4f6;">
            <span style="font-size:13px;font-weight:600;color:#374151;">
                📅 Periodo de análisis
            </span>

            <span style="font-size:12px;color:#6b7280;background:white;border:1px solid #e5e7eb;border-radius:8px;padding:4px 12px;">
                @if($startDate === $endDate)
                    {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }}
                @else
                    {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }}
                    &rarr;
                    {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
                    &nbsp;·&nbsp;
                    <strong style="color:#f97316;">{{ \Carbon\Carbon::parse($startDate)->diffInDays($endDate) + 1 }} días</strong>
                @endif
            </span>
        </div>

        {{-- Controles --}}
        <div style="display:flex;flex-wrap:wrap;align-items:center;gap:12px;padding:14px 20px;">

            {{-- Presets --}}
            <div style="display:flex;flex-wrap:wrap;gap:6px;">
                @foreach([
                    'today'  => 'Hoy',
                    'week'   => 'Esta semana',
                    'month'  => 'Este mes',
                    'year'   => 'Este año',
                    'custom' => '✎ Personalizado',
                ] as $value => $label)
                    <button
                        wire:click="$set('dateRange', '{{ $value }}')"
                        style="
                            padding: 6px 14px;
                            border-radius: 10px;
                            font-size: 13px;
                            font-weight: 600;
                            border: 1.5px solid {{ $dateRange === $value ? '#f97316' : '#e5e7eb' }};
                            background: {{ $dateRange === $value ? '#f97316' : 'white' }};
                            color: {{ $dateRange === $value ? 'white' : '#4b5563' }};
                            cursor: pointer;
                            transition: all .15s;
                        ">
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            {{-- Separador --}}
            <div style="width:1px;height:32px;background:#e5e7eb;"></div>

            {{-- Date inputs --}}
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                <div style="display:flex;align-items:center;gap:8px;background:#f9fafb;border:1.5px solid #e5e7eb;border-radius:10px;padding:6px 12px;">
                    <span style="font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.05em;">Desde</span>
                    <input
                        type="date"
                        wire:model.live="startDate"
                        max="{{ $endDate }}"
                        style="border:none;background:transparent;font-size:13px;font-weight:600;color:#1f2937;outline:none;cursor:pointer;"
                    >
                </div>

                <span style="color:#9ca3af;font-size:16px;">&rarr;</span>

                <div style="display:flex;align-items:center;gap:8px;background:#f9fafb;border:1.5px solid #e5e7eb;border-radius:10px;padding:6px 12px;">
                    <span style="font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.05em;">Hasta</span>
                    <input
                        type="date"
                        wire:model.live="endDate"
                        min="{{ $startDate }}"
                        max="{{ today()->toDateString() }}"
                        style="border:none;background:transparent;font-size:13px;font-weight:600;color:#1f2937;outline:none;cursor:pointer;"
                    >
                </div>
            </div>

        </div>
    </div>

</x-filament-panels::page>
