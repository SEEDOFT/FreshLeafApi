<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Order;
use App\Models\UserDevice;
use App\Observers\OrderObserver;
use Filament\Infolists\Components\TextEntry;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Messaging\SendReport;
use NotificationChannels\Fcm\FcmChannel;

use function class_exists;

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
        FilamentIcon::register([
            'panels::sidebar.collapse-button' => 'heroicon-o-bars-3',
            'panels::sidebar.expand-button' => 'heroicon-o-bars-3',
        ]);

        Order::observe(OrderObserver::class);

        TextColumn::configureUsing(function (TextColumn $column): void {
            $column->placeholder(__('admin.resources.general.not_provided'));
        });

        TextEntry::configureUsing(function (TextEntry $entry): void {
            $entry->placeholder(__('admin.resources.general.not_provided'));
        });

        Event::listen(NotificationFailed::class, function (NotificationFailed $event): void {
            if ($event->channel !== FcmChannel::class) {
                return;
            }

            $report = $event->data['report'] ?? null;

            if (! $report instanceof SendReport) {
                Log::warning('FCM notification failed without a send report', [
                    'notification' => $event->notification::class,
                    'notifiable' => $event->notifiable::class,
                ]);

                return;
            }

            $token = $report->target()->value();
            $error = $report->error();

            Log::warning('FCM notification failed', [
                'notification' => $event->notification::class,
                'notifiable' => $event->notifiable::class,
                'token' => $token,
                'error' => $error?->getMessage(),
                'error_class' => $error !== null ? $error::class : null,
                'errors' => $error?->errors() ?? [],
            ]);

            if ($report->messageTargetWasInvalid() || $report->messageWasSentToUnknownToken()) {
                UserDevice::where('device_token', $token)->update(['is_active' => false]);
            }
        });

        if (
            $this->app->environment('local') &&
            class_exists(\Laravel\Telescope\TelescopeServiceProvider::class
            )) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }
}
