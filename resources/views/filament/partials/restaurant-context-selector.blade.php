@php
    $user = auth()->user();
@endphp

@if($user?->hasRole('super_admin'))
    <form
        method="POST"
        action="{{ route('admin.restaurant-context.update') }}"
        style="display:flex;align-items:center;gap:8px;margin-left:12px;"
    >
        @csrf
        <label for="admin-restaurant-context" style="font-size:12px;font-weight:700;color:#64748b;">
            Restaurante
        </label>
        <select
            id="admin-restaurant-context"
            name="restaurant_id"
            onchange="this.form.submit()"
            style="height:34px;min-width:190px;border:1px solid #cbd5e1;border-radius:8px;background:white;color:#0f172a;font-size:13px;padding:0 10px;"
        >
            <option value="">Todos</option>
            @foreach(\App\Support\AdminRestaurantContext::restaurantOptions() as $restaurantId => $restaurantName)
                <option value="{{ $restaurantId }}" @selected(\App\Support\AdminRestaurantContext::selectedId() === (int) $restaurantId)>
                    {{ $restaurantName }}
                </option>
            @endforeach
        </select>
    </form>
@elseif($user?->restaurant)
    <div style="margin-left:12px;font-size:12px;font-weight:700;color:#64748b;">
        Restaurante: <span style="color:#0f172a;">{{ $user->restaurant->name }}</span>
    </div>
@endif
