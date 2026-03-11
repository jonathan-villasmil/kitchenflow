<?php

namespace App\Providers\Filament;

use App\Http\Middleware\RoleRedirectMiddleware;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
use Filament\Navigation\NavigationGroup;
use App\Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('KitchenFlow')
            ->brandLogo(asset('images/logo.svg'))
            ->favicon(asset('images/favicon.ico'))
            ->darkMode(true)
            ->colors([
                'primary' => Color::Orange,
                'gray'    => Color::Slate,
                'info'    => Color::Blue,
                'success' => Color::Emerald,
                'warning' => Color::Amber,
                'danger'  => Color::Rose,
            ])
            ->navigationGroups([
                NavigationGroup::make('Restaurante'),
                NavigationGroup::make('Cocina'),
                NavigationGroup::make('Carta & Menú'),
                NavigationGroup::make('Inventario'),
                NavigationGroup::make('Personas'),
                NavigationGroup::make('Configuración'),
            ])
            ->navigationItems([
                NavigationItem::make('Terminal POS')
                    ->url(fn (): string => route('pos'))
                    ->icon('heroicon-o-computer-desktop')
                    ->group('Operaciones')
                    ->sort(1),
                NavigationItem::make('Pantalla de Cocina (KDS)')
                    ->url(fn (): string => route('kds'))
                    ->icon('heroicon-o-fire')
                    ->group('Operaciones')
                    ->sort(2),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
            ])
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => Blade::render('<meta name="theme-color" content="#f97316"/><link rel="apple-touch-icon" href="{{ asset(\'pwa-logo.png\') }}"><link rel="manifest" href="{{ asset(\'manifest.json\') }}"> @laravelPwa')
            )
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                RoleRedirectMiddleware::class,
            ]);
    }
}
