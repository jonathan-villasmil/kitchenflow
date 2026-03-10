<?php
$register = App\Models\CashRegister::find(6);
$pdf = Barryvdh\DomPDF\Facade\Pdf::loadView('reports.z-report', [
    'register' => $register,
    'cashSales' => 15.95,
    'cardSales' => 25.00,
    'cashIn' => 50,
    'cashOut' => 25,
    'refunds' => 0
]);
$pdf->save(public_path('ticket-z-ejemplo.pdf'));
echo "PDF generado exitosamente.\n";
