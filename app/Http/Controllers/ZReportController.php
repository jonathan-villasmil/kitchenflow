<?php

namespace App\Http\Controllers;

use App\Models\CashRegister;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ZReportController extends Controller
{
    public function download(CashRegister $register)
    {
        // Ensure the user has permission to view Z reports
        if (!auth()->user()->hasRole(['super_admin', 'manager', 'cajero'])) {
            abort(403, 'Unauthorized action.');
        }

        // Load transactions grouped by type
        $transactions = $register->transactions->load('reference');
        $sales = $transactions->where('type', 'sale');
        
        $data = [
            'register' => $register,
            'cashSales' => $sales->where('payment_method', 'cash')->sum('amount'),
            'cardSales' => $sales->where('payment_method', 'card')->sum('amount'),
            'cashIn' => $transactions->where('type', 'cash_in')->sum('amount'),
            'cashOut' => $transactions->where('type', 'cash_out')->sum('amount'),
            'refunds' => $transactions->where('type', 'refund')->sum('amount'),
            'tips' => $sales->sum(function ($transaction) {
                return $transaction->reference?->tip_amount ?? 0;
            }),
        ];

        // We load a view tailored for 80mm thermal receipt printers
        $pdf = Pdf::loadView('reports.z-report', $data);
        
        // Return inline to be printed or downloaded
        return $pdf->stream("Z-Report-{$register->id}.pdf");
    }
}
