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
            ->spa()
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
                fn (): string => \Illuminate\Support\Facades\Blade::render('
                    <!-- Page Loading Indicator Overlay -->
                    <div id="page-loading-indicator">
                        <div id="page-loading-card">
                            <div class="spinner-container">
                                <div class="pulse-dot"></div>
                                <div class="spin-ring outer-ring"></div>
                                <div class="spin-ring inner-ring"></div>
                            </div>
                            <span class="loading-text">{{ __(\'Loading...\') }}</span>
                        </div>
                    </div>

                    <style>
                        #page-loading-indicator {
                            position: fixed;
                            inset: 0;
                            z-index: 999999;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            background: rgba(15, 23, 42, 0.4);
                            backdrop-filter: blur(5px);
                            -webkit-backdrop-filter: blur(5px);
                            opacity: 0;
                            pointer-events: none;
                            transition: opacity 0.25s cubic-bezier(0.4, 0, 0.2, 1);
                        }

                        #page-loading-indicator.active {
                            opacity: 1;
                            pointer-events: auto;
                        }

                        #page-loading-card {
                            display: flex;
                            flex-direction: column;
                            align-items: center;
                            gap: 1.25rem;
                            padding: 2.25rem 2.5rem;
                            border-radius: 1.25rem;
                            background: rgba(255, 255, 255, 0.9);
                            border: 1px solid rgba(226, 232, 240, 0.8);
                            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
                            transform: scale(0.92);
                            transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
                        }

                        /* Dark mode support */
                        .dark #page-loading-card,
                        [class*="dark"] #page-loading-card {
                            background: rgba(15, 23, 42, 0.9);
                            border: 1px solid rgba(51, 65, 85, 0.8);
                            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
                        }

                        #page-loading-indicator.active #page-loading-card {
                            transform: scale(1);
                        }

                        .spinner-container {
                            position: relative;
                            width: 3.5rem;
                            height: 3.5rem;
                        }

                        .pulse-dot {
                            position: absolute;
                            inset: 0;
                            margin: auto;
                            width: 0.85rem;
                            height: 0.85rem;
                            background: #ffb900;
                            border-radius: 50%;
                            box-shadow: 0 0 12px #ffb900;
                            animation: pulse-glowing 1.5s ease-in-out infinite;
                        }

                        .spin-ring {
                            position: absolute;
                            inset: 0;
                            border-radius: 50%;
                            border: 3.5px solid transparent;
                        }

                        .outer-ring {
                            border-top-color: #ffb900;
                            border-right-color: #ffb900;
                            animation: spin-clockwise 0.9s linear infinite;
                        }

                        .inner-ring {
                            inset: 0.35rem;
                            border-bottom-color: #d97706;
                            border-left-color: #d97706;
                            animation: spin-counter-clockwise 0.7s linear infinite;
                        }

                        .loading-text {
                            font-family: inherit;
                            font-size: 0.75rem;
                            font-weight: 800;
                            color: #475569;
                            text-transform: uppercase;
                            letter-spacing: 0.2em;
                            animation: text-pulse 1.5s ease-in-out infinite;
                        }

                        .dark .loading-text,
                        [class*="dark"] .loading-text {
                            color: #cbd5e1;
                        }

                        @keyframes pulse-glowing {
                            0%, 100% {
                                transform: scale(0.8);
                                opacity: 0.5;
                                box-shadow: 0 0 8px rgba(255, 185, 0, 0.4);
                            }
                            50% {
                                transform: scale(1.1);
                                opacity: 1;
                                box-shadow: 0 0 20px rgba(255, 185, 0, 0.8);
                            }
                        }

                        @keyframes spin-clockwise {
                            0% { transform: rotate(0deg); }
                            100% { transform: rotate(360deg); }
                        }

                        @keyframes spin-counter-clockwise {
                            0% { transform: rotate(360deg); }
                            100% { transform: rotate(0deg); }
                        }

                        @keyframes text-pulse {
                            0%, 100% { opacity: 0.5; }
                            50% { opacity: 1; }
                        }
                    </style>

                    <script>
                        (function() {
                            const loader = document.getElementById("page-loading-indicator");
                            if (loader) {
                                document.addEventListener("livewire:navigate", () => {
                                    loader.classList.add("active");
                                });

                                document.addEventListener("livewire:navigated", () => {
                                    setTimeout(() => {
                                        loader.classList.remove("active");
                                    }, 120);
                                });
                            }
                        })();
                    </script>

                    <script>
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
                    </script>

                    <!-- Global Lightbox Modal -->
                    <div x-data="{ open: false, src: \'\' }"
                         x-on:open-lightbox.window="src = $event.detail.src; open = true"
                         x-show="open"
                         x-transition.opacity
                         class="fixed inset-0 z-[100000] flex items-center justify-center bg-black/80 backdrop-blur-md"
                         style="display: none;"
                         @click="open = false"
                         @keydown.escape.window="open = false"
                    >
                        <div class="relative max-w-5xl max-h-[90vh] p-4 flex items-center justify-center" @click.stop>
                            <!-- Close Button -->
                            <button @click="open = false" class="absolute top-6 right-6 text-white/80 hover:text-white p-2 rounded-full bg-black/40 hover:bg-black/60 transition shadow-lg">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                            <!-- Lightbox Image -->
                            <img :src="src" class="max-w-full max-h-[85vh] rounded-lg shadow-2xl border border-white/10 object-contain animate-[zoom-in_0.2s_ease-out]" />
                        </div>
                    </div>

                    <style>
                        @keyframes zoom-in {
                            from { transform: scale(0.95); opacity: 0; }
                            to { transform: scale(1); opacity: 1; }
                        }
                    </style>
                '),
            );
    }
}
