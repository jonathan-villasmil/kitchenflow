<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Z - Caja #{{ $register->id }}</title>
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
        }
        th, td {
            text-align: left;
            padding: 2px 0;
        }
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
        .alert {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
            margin-top: 10px;
        }
    </style>
</head>
<body>

    <div class="text-center mb-4">
        <h1 class="text-lg font-bold" style="margin: 0;">KITCHENFLOW POS</h1>
        <div>Restaurante Local</div>
        <div class="divider"></div>
        <h2 class="font-bold" style="margin: 5px 0;">REPORTE Z (CIERRE DE CAJA)</h2>
        <div>Caja ID: #{{ $register->id }}</div>
        <div>Fecha: {{ $register->closed_at ? $register->closed_at->format('d/m/Y') : now()->format('d/m/Y') }}</div>
    </div>

    <div class="divider"></div>

    <div class="mb-2"><strong>Apertura:</strong> {{ $register->opened_at->format('H:i') }} (por {{ $register->opener->name ?? 'Admin' }})</div>
    <div class="mb-2"><strong>Cierre:</strong> {{ $register->closed_at ? $register->closed_at->format('H:i') : 'Activa' }} (por {{ $register->closer->name ?? 'Admin' }})</div>

    <div class="divider"></div>

    <h3 class="font-bold" style="margin: 5px 0;">RESUMEN DE EFECTIVO</h3>
    
    <div class="flex-between">
        <div>Fondo Inicial:</div>
        <div>€{{ number_format($register->opening_amount, 2) }}</div>
    </div>
    <div class="flex-between">
        <div>Ventas Efectivo:</div>
        <div>+€{{ number_format($cashSales, 2) }}</div>
    </div>
    <div class="flex-between">
        <div>Entradas Manuales:</div>
        <div>+€{{ number_format($cashIn, 2) }}</div>
    </div>
    <div class="flex-between">
        <div>Salidas (Gastos):</div>
        <div>-€{{ number_format($cashOut, 2) }}</div>
    </div>
    <div class="flex-between">
        <div>Devoluciones:</div>
        <div>-€{{ number_format($refunds, 2) }}</div>
    </div>

    <div class="divider"></div>

    <div class="flex-between font-bold text-lg mb-2">
        <div>Efectivo Esperado:</div>
        <div>€{{ number_format($register->expected_amount, 2) }}</div>
    </div>
    <div class="flex-between font-bold mb-2">
        <div>Efectivo Físico Contado:</div>
        <div>€{{ number_format($register->closing_amount, 2) }}</div>
    </div>

    @php
        $diff = $register->closing_amount - $register->expected_amount;
    @endphp

    @if($diff != 0)
        <div class="alert font-bold">
            *** ATENCIÓN: DESCUADRE ***<br>
            Diferencia de €{{ number_format($diff, 2) }}
        </div>
    @else
        <div class="alert font-bold" style="border-style: solid;">
            *** CAJA CUADRADA ***
        </div>
    @endif

    <div class="divider"></div>

    <h3 class="font-bold" style="margin: 5px 0;">VENTAS CON TARJETA</h3>
    <div class="flex-between">
        <div>Total Tarjeta:</div>
        <div>€{{ number_format($cardSales, 2) }}</div>
    </div>

    <div class="divider"></div>

    <h3 class="font-bold" style="margin: 5px 0;">PROPINAS RECAUDADAS</h3>
    <div class="flex-between">
        <div>Propinas incluidas en ventas:</div>
        <div>€{{ number_format($tips, 2) }}</div>
    </div>
    <div style="font-size: 10px; color: #555; text-align: center; margin-top: 5px;">
        (El importe en caja ya incluye estas propinas)
    </div>

    <div class="divider"></div>

    @if($register->notes)
        <div class="mb-4">
            <strong>Notas de Cierre:</strong><br>
            {!! nl2br(e($register->notes)) !!}
        </div>
        <div class="divider"></div>
    @endif

    <div class="text-center" style="margin-top: 20px;">
        <p>Documento no válido como factura.</p>
        <p>Software KitchenFlow</p>
    </div>

</body>
</html>
