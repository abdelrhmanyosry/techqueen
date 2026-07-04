<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
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
            ->path('admin')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->login()
            ->brandName('TechQueen')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                \App\Filament\Widgets\DeliveryAndWorkloadReminderWidget::class,
            ])
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
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->renderHook(
                \Filament\View\PanelsRenderHook::USER_MENU_BEFORE,
                fn (): string => \Illuminate\Support\Facades\Blade::render('
                    <div class="flex items-center gap-x-2">
                        @livewire(\'sync-from-remote-button\')
                        @livewire(\'hide-prices-toggle\')
                    </div>
                '),
            )
            ->renderHook(
                \Filament\View\PanelsRenderHook::BODY_END,
                fn (): string => '<script>
                    window.addEventListener("keydown", function (e) {
                        if (["INPUT", "SELECT", "TEXTAREA"].includes(document.activeElement.tagName) || document.activeElement.isContentEditable) {
                            return;
                        }
                        if (e.metaKey || e.ctrlKey || e.altKey || e.key.length !== 1) {
                            return;
                        }
                        const searchInput = document.querySelector("input[type=\'search\']") || 
                                            document.querySelector(".fi-ta-search-input input") || 
                                            document.querySelector(".fi-ta-search input");
                        if (searchInput) {
                            searchInput.focus();
                        }
                    });
                </script>'
            );
    }
}
