<?php

namespace App\Http\Controllers;

use App\Support\AdminRestaurantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminRestaurantContextController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->hasRole('super_admin'), 403);

        $data = $request->validate([
            'restaurant_id' => ['nullable'],
        ]);

        $restaurantId = filled($data['restaurant_id'] ?? null)
            ? (int) $data['restaurant_id']
            : null;

        AdminRestaurantContext::setForSuperAdmin($restaurantId);

        return back();
    }
}
