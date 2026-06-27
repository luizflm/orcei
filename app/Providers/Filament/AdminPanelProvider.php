<?php

declare(strict_types = 1);

namespace App\Providers\Filament;

use App\Filament\Auth\Register;
use App\Http\Middleware\SetLocale;
use Filament\Actions\Action;
use Filament\Enums\{DatabaseNotificationsPosition, UserMenuPosition};
use Filament\Http\Middleware\{Authenticate, AuthenticateSession, DisableBladeIconComponents, DispatchServingFilamentEvent};
use Filament\Navigation\NavigationItem;
use Filament\{Panel, PanelProvider};
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;

use Illuminate\Cookie\Middleware\{AddQueuedCookiesToResponse, EncryptCookies};
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('')
            ->login()
            ->registration(Register::class)
            ->profile()
            ->userMenu(position: UserMenuPosition::Sidebar)
            ->userMenuItems([
                Action::make('language')
                    ->view('filament.user-menu.language-switcher'),
            ])
            ->colors([
                'primary' => [
                    50  => '#f0fdfa',
                    100 => '#ccfbf1',
                    200 => '#99f6e4',
                    300 => '#5eead4',
                    400 => '#14b8a6',
                    500 => '#0d9488',
                    600 => '#0f766e',
                    700 => '#115e59',
                    800 => '#134e4a',
                    900 => '#0c3b37',
                    950 => '#042f2e',
                ],
                'gray' => Color::Gray,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([])
            ->navigationItems([
                NavigationItem::make('Horizon')
                    ->group(fn (): string => __('nav.group.administration'))
                    ->url('/horizon', shouldOpenInNewTab: true)
                    ->icon(Heroicon::OutlinedCpuChip)
                    ->visible(fn (): bool => auth()->user()?->isAdmin() ?? false),
            ])
            ->databaseNotifications(position: DatabaseNotificationsPosition::Sidebar)
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                SetLocale::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
