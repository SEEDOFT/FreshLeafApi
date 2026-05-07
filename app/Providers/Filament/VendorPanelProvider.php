<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Filament\ThemeColors;
use App\Filament\Vendor\Clusters\Settings\Pages\BusinessProfile;
use App\Filament\Vendor\Clusters\Settings\Pages\VendorProfile;
use App\Filament\Vendor\Pages\Auth\Login;
use App\Filament\Vendor\Pages\Auth\Register;
use App\Filament\Vendor\Widgets\VendorEarningsChart;
use App\Filament\Vendor\Widgets\VendorStatsOverview;
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

class VendorPanelProvider extends PanelProvider
{
    #[Override]
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('vendor')
            ->path('vendor')
            ->authGuard('web')
            ->login(Login::class)
            ->registration(Register::class)
            ->font('Noto Sans Khmer')
            ->spa()
            ->viteTheme('resources/css/filament/panels/theme.css')
            ->maxContentWidth('full')
            ->defaultThemeMode(ThemeMode::System)
            ->sidebarCollapsibleOnDesktop()
            ->userMenuItems([
                'profile' => MenuItem::make()->visible(false),
                'my-account' => MenuItem::make()
                    ->label('My Profile')
                    ->icon('heroicon-o-user-circle')
                    ->url(static fn (): string => VendorProfile::getUrl()),
                'app-settings' => MenuItem::make()
                    ->label('Store Settings')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->url(static fn (): string => BusinessProfile::getUrl()),
            ])
            ->renderHook(
                PanelsRenderHook::BODY_END,
                static fn (): string => view('filament.hooks.panel-assets')->render(),
            )
            ->colors(ThemeColors::getPalette())
            ->navigationGroups([
                NavigationGroup::make()
                    ->label(static fn (): string => __('admin.navigation.shop')),
                NavigationGroup::make()
                    ->label(static fn (): string => __('admin.navigation.catalog')),
                NavigationGroup::make()
                    ->label(static fn (): string => __('admin.navigation.sales')),
                NavigationGroup::make()
                    ->label(static fn (): string => __('admin.navigation.financial')),
                NavigationGroup::make()
                    ->label(static fn (): string => __('admin.navigation.settings')),
            ])
            ->discoverResources(in: app_path('Filament/Vendor/Resources'), for: 'App\Filament\Vendor\Resources')
            ->discoverPages(in: app_path('Filament/Vendor/Pages'), for: 'App\Filament\Vendor\Pages')
            ->discoverClusters(in: app_path('Filament/Vendor/Clusters'), for: 'App\Filament\Vendor\Clusters')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Vendor/Widgets'), for: 'App\Filament\Vendor\Widgets')
            ->widgets([
                AccountWidget::class,
                VendorStatsOverview::class,
                VendorEarningsChart::class,
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
                SetLocaleFromAcceptLanguage::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
