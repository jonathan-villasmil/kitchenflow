<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReceiptController extends Controller
{
    public function download(Order $order)
    {
        $user = auth()->user();
        if ($order->restaurant_id !== $user->restaurant_id && !$user->hasRole('super_admin')) {
            abort(403, 'Unauthorized action.');
        }

        $order->load(['items.modifiers', 'table', 'customer', 'user', 'restaurant']);

        $pdf = Pdf::loadView('reports.receipt', [
            'order' => $order,
            'restaurant' => $order->restaurant
        ]);
        
        return $pdf->stream("Recibo-{$order->number}.pdf");
    }
}
