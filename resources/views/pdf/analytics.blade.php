<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Informe de Ventas - KitchenFlow</title>
    <style>
        body { font-family: sans-serif; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #f59e0b; padding-bottom: 10px; }
        .date { font-size: 14px; color: #666; }
        .summary-box { border: 1px solid #ddd; padding: 15px; margin-bottom: 20px; border-radius: 5px; background: #fafafa; }
        .summary-box h3 { margin-top: 0; color: #f59e0b; }
        .metrics { display: table; width: 100%; margin-bottom: 30px; }
        .metric { display: table-cell; width: 33%; text-align: center; padding: 10px; border: 1px solid #ddd; background: #fff; }
        .metric-title { font-size: 12px; text-transform: uppercase; color: #666; }
        .metric-value { font-size: 24px; font-weight: bold; margin-top: 5px; color: #111; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; font-size: 14px; }
        th { background-color: #f59e0b; color: #fff; }
        .text-right { text-align: right; }
        .badge { padding: 3px 8px; border-radius: 12px; font-size: 11px; font-weight: bold; color: white; }
        .badge-paid { background-color: #10b981; }
        .badge-pending { background-color: #6b7280; }
        .badge-kitchen { background-color: #f59e0b; }
        .badge-cancelled { background-color: #ef4444; }
    </style>
</head>
<body>

    <div class="header">
        <h1>KitchenFlow - Informe de Operaciones</h1>
        <div class="date">Fecha de Emisión: {{ now()->format('d/m/Y H:i') }}</div>
    </div>

    <div class="metrics">
        <div class="metric">
            <div class="metric-title">Ingresos de Hoy</div>
            <div class="metric-value">€ {{ number_format($todayRevenue, 2) }}</div>
        </div>
        <div class="metric">
            <div class="metric-title">Pedidos Activos</div>
            <div class="metric-value">{{ $activeOrders }}</div>
        </div>
        <div class="metric">
            <div class="metric-title">Total Clientes</div>
            <div class="metric-value">{{ $totalCustomers }}</div>
        </div>
    </div>

    <div class="summary-box">
        <h3>Top 5 Platos (Histórico)</h3>
        <table>
            <thead>
                <tr>
                    <th>Plato</th>
                    <th class="text-right">Unidades Vendidas</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($topDishes as $item)
                <tr>
                    <td>{{ $item->dish->name ?? 'Desconocido' }}</td>
                    <td class="text-right">{{ $item->total_sold }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="summary-box">
        <h3>Ventas del Día ({{ today()->format('d/m/Y') }})</h3>
        <table>
            <thead>
                <tr>
                    <th>Nº Pedido</th>
                    <th>Hora</th>
                    <th>Cliente</th>
                    <th>Estado</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($todayOrders as $order)
                <tr>
                    <td>{{ $order->number }}</td>
                    <td>{{ $order->created_at->format('H:i') }}</td>
                    <td>{{ $order->customer->name ?? 'Anónimo' }}</td>
                    <td>
                        <span class="badge badge-{{ match($order->status->value ?? $order->status) { 'paid', 'served' => 'paid', 'kitchen' => 'kitchen', 'cancelled' => 'cancelled', default => 'pending' } }}">
                            {{ strtoupper($order->status->value ?? $order->status) }}
                        </span>
                    </td>
                    <td class="text-right">€ {{ number_format($order->total, 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align: center;">No hay pedidos registrados hoy.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</body>
</html>
