<?php
$register = App\Models\CashRegister::find(6);
$html = view('reports.z-report', [
    'register' => $register,
    'cashSales' => 15.95,
    'cardSales' => 25.00,
    'cashIn' => 50,
    'cashOut' => 25,
    'refunds' => 0
])->render();
file_put_contents(public_path('ticket-z-ejemplo.html'), $html);
echo "HTML generado exitosamente en public/ticket-z-ejemplo.html\n";
