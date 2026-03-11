<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recibo - #{{ $order->number }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            margin: 0;
            padding: 10px;
            width: 280px; /* Approx 80mm */
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .text-lg { font-size: 16px; }
        .mb-2 { margin-bottom: 8px; }
        .mb-4 { margin-bottom: 16px; }
        .divider {
            border-top: 1px dashed #000;
            margin: 10px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }
        th, td {
            text-align: left;
            padding: 3px 0;
            vertical-align: top;
        }
        .text-right { text-align: right; }
        .flex-between {
            display: table;
            width: 100%;
        }
        .flex-between > div {
            display: table-cell;
        }
        .flex-between > div:last-child {
            text-align: right;
        }
        .mod-text {
            font-size: 10px;
            color: #555;
            padding-left: 10px;
        }
    </style>
</head>
<body>

    <div class="text-center mb-4">
        @if($restaurant->logo)
            <!-- Logo if exists -->
        @endif
        <h1 class="text-lg font-bold" style="margin: 0;">{{ $restaurant->name ?? 'KITCHENFLOW' }}</h1>
        <div>{{ $restaurant->address ?? 'Dirección del restaurante' }}</div>
        <div>NIF: {{ $restaurant->nif ?? 'B12345678' }}</div>
        <div>Tel: {{ $restaurant->phone ?? '900 123 456' }}</div>
        <div class="divider"></div>
        <h2 class="font-bold text-lg" style="margin: 5px 0;">TICKET DE VENTA</h2>
        <div>Pedido: #{{ $order->number }}</div>
        <div>Fecha: {{ $order->closed_at ? $order->closed_at->format('d/m/Y H:i') : now()->format('d/m/Y H:i') }}</div>
        <div>Camarero: {{ $order->user->name ?? 'Staff' }}</div>
        <div>Mesa: {{ $order->table ? $order->table->number : 'Barra' }}</div>
    </div>

    <div class="divider"></div>

    <table>
        <thead>
            <tr>
                <th style="width: 15%">Cant</th>
                <th style="width: 60%">Concepto</th>
                <th style="width: 25%" class="text-right">Importe</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
                <tr>
                    <td>{{ $item->quantity }}x</td>
                    <td>
                        {{ $item->name }}
                        @if($item->modifiers->count() > 0)
                            <div class="mod-text">
                                @foreach($item->modifiers as $mod)
                                    <div>+ {{ $mod->modifier_name }}</div>
                                @endforeach
                            </div>
                        @endif
                    </td>
                    <td class="text-right">€{{ number_format($item->total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="divider"></div>

    <div class="flex-between mb-2">
        <div>Subtotal:</div>
        <div>€{{ number_format($order->subtotal, 2) }}</div>
    </div>
    <div class="flex-between mb-2">
        <div>Impuestos ({{ $restaurant->tax_rate ?? 10 }}%):</div>
        <div>€{{ number_format($order->tax_amount, 2) }}</div>
    </div>
    
    @if($order->tip_amount > 0)
    <div class="flex-between mb-2 text-gray-600">
        <div>Propina voluntaria:</div>
        <div>€{{ number_format($order->tip_amount, 2) }}</div>
    </div>
    @endif

    <div class="flex-between font-bold text-lg mb-2">
        <div>TOTAL:</div>
        <div>€{{ number_format($order->total + $order->tip_amount, 2) }}</div>
    </div>

    <div class="divider"></div>

    <div class="text-center" style="margin-top: 20px;">
        <p class="font-bold">¡GRACIAS POR SU VISITA!</p>
        <p>Software POS por KitchenFlow</p>
    </div>

</body>
</html>
