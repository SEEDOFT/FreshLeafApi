<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Filament\Clusters\Settings\Pages\AdminProfile;
use App\Filament\Clusters\Settings\Pages\ApplicationSettings;
use App\Filament\Pages\Auth\Login;
use App\Filament\ThemeColors;
use App\Filament\Widgets\AdminRevenueChart;
use App\Filament\Widgets\AdminStatsOverview;
use App\Http\Middleware\SetLocaleFromAcceptLanguage;
use Filament\Enums\ThemeMode;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Override;

class AdminPanelProvider extends PanelProvider
{
    #[Override]
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->authGuard('web')
            ->login(Login::class)
            ->font('Noto Sans Khmer')
            ->spa()
            ->viteTheme('resources/css/filament/panels/theme.css')
            ->maxContentWidth('full')
            ->defaultThemeMode(ThemeMode::Light)
            ->sidebarCollapsibleOnDesktop()
            ->userMenuItems([
                'profile' => MenuItem::make()->visible(false),
                'my-account' => MenuItem::make()
                    ->label(static fn (): string => __('admin.navigation.my_profile'))
                    ->icon('heroicon-o-user-circle')
                    ->url(static fn (): string => AdminProfile::getUrl()),
                'app-settings' => MenuItem::make()
                    ->label(static fn (): string => __('admin.navigation.settings'))
                    ->icon('heroicon-o-cog-6-tooth')
                    ->url(static fn (): string => ApplicationSettings::getUrl()),
            ])
            ->renderHook(
                PanelsRenderHook::BODY_END,
                static fn (): string => view('filament.hooks.panel-assets')->render(),
            )
            ->colors(ThemeColors::getPalette())
            ->navigationGroups([
                NavigationGroup::make()
                    ->label(static fn (): string => __('admin.navigation.accounts')),
                NavigationGroup::make()
                    ->label(static fn (): string => __('admin.navigation.catalog')),
                NavigationGroup::make()
                    ->label(static fn (): string => __('admin.navigation.shop')),
                NavigationGroup::make()
                    ->label(static fn (): string => __('admin.navigation.sales')),
                NavigationGroup::make()
                    ->label(static fn (): string => __('admin.navigation.logistics')),
                NavigationGroup::make()
                    ->label(static fn (): string => __('admin.navigation.financial')),
                NavigationGroup::make()
                    ->label(static fn (): string => __('admin.navigation.settings')),
                NavigationGroup::make()
                    ->label(static fn (): string => __('admin.navigation.app_control')),
            ])
            ->discoverResources(in: app_path('Filament/Admin/Resources'), for: 'App\Filament\Admin\Resources')
            ->discoverPages(in: app_path('Filament/Admin/Pages'), for: 'App\Filament\Admin\Pages')
            ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\Filament\Clusters')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets(widgets: [
                AccountWidget::class,
                AdminStatsOverview::class,
                AdminRevenueChart::class,
            ])
            ->middleware(middleware: [
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                SetLocaleFromAcceptLanguage::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
