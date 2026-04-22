<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Address;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Models\Wallet;
use App\Policies\AddressPolicy;
use App\Policies\AuthActionPolicy;
use App\Policies\PaymentMethodPolicy;
use App\Policies\UserPolicy;
use App\Policies\WalletPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Address::class, AddressPolicy::class);
        Gate::policy(PaymentMethod::class, PaymentMethodPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Wallet::class, WalletPolicy::class);

        Gate::define('auth.logout', [AuthActionPolicy::class, 'logout']);
        Gate::define('auth.verifyPassword', [
            AuthActionPolicy::class, 'verifyPassword',
        ]);
        Gate::define('auth.updatePassword', [
            AuthActionPolicy::class, 'updatePassword',
        ]);

        if ($this->app->environment('local') && \class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }
}
