<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Filament\Admin\Clusters\Settings\Pages\AdminProfile;
use App\Filament\Admin\Clusters\Settings\Pages\ApplicationSettings;
use App\Filament\Admin\Pages\Auth\Login;
use App\Filament\Admin\Widgets\AdminRevenueChart;
use App\Filament\Admin\Widgets\AdminStatsOverview;
use App\Filament\ThemeColors;
use App\Filament\Widgets\CustomAccountWidget;
use App\Http\Middleware\SetLocaleFromAcceptLanguage;
use Filament\Actions\Action;
use Filament\Enums\ThemeMode;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Enums\Width;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Override;

class AdminPanelProvider extends PanelProvider
{
    #[Override]
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->bootUsing(fn () => App::setLocale(Auth::user()?->adminProfile->locale ?? config('app.locale')))
            ->default()
            ->id('admin')
            ->path('admin')
            ->authGuard('web')
            ->login(Login::class)
            ->font('Noto Sans Khmer')
            ->spa()
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->maxContentWidth(Width::Full)
            ->defaultThemeMode(ThemeMode::System)
            ->sidebarCollapsibleOnDesktop()
            ->userMenuItems([
                'my-account' => Action::make('my-account')
                    ->label(fn (): string => __('admin.navigation.my_profile'))
                    ->icon('heroicon-o-user-circle')
                    ->url(fn (): string => AdminProfile::getUrl(panel: 'admin')),
                'app-settings' => Action::make('app-settings')
                    ->label(fn (): string => __('admin.navigation.settings'))
                    ->icon('heroicon-o-cog-6-tooth')
                    ->url(fn (): string => ApplicationSettings::getUrl(panel: 'admin')),
            ])
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn (): string => view('filament.hooks.panel-assets')->render(),
            )
            ->colors(ThemeColors::getPalette())
            ->navigationGroups([
                NavigationGroup::make()
                    ->label(fn (): string => __('admin.navigation.accounts')),
                NavigationGroup::make()
                    ->label(fn (): string => __('admin.navigation.catalog')),
                NavigationGroup::make()
                    ->label(fn (): string => __('admin.navigation.shop')),
                NavigationGroup::make()
                    ->label(fn (): string => __('admin.navigation.sales')),
                NavigationGroup::make()
                    ->label(fn (): string => __('admin.navigation.logistics')),
                NavigationGroup::make()
                    ->label(fn (): string => __('admin.navigation.financial')),
                NavigationGroup::make()
                    ->label(fn (): string => __('admin.navigation.settings')),
                NavigationGroup::make()
                    ->label(fn (): string => __('admin.navigation.app_control')),
            ])
            ->discoverResources(in: app_path('Filament/Admin/Resources'), for: 'App\Filament\Admin\Resources')
            ->discoverPages(in: app_path('Filament/Admin/Pages'), for: 'App\Filament\Admin\Pages')
            ->discoverClusters(in: app_path('Filament/Admin/Clusters'), for: 'App\Filament\Admin\Clusters')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Admin/Widgets'), for: 'App\Filament\Admin\Widgets')
            ->widgets(widgets: [
                AdminStatsOverview::class,
                AdminRevenueChart::class,
                CustomAccountWidget::class,
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
