<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Settings\Pages;

use App\Filament\Clusters\Settings;
use App\Models\Setting;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ApplicationSettings extends Page
{
    protected static ?string $cluster = Settings::class;

    protected static ?string $slug = 'app-settings';

    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.app_control');
    }

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-8-tooth';

    protected string $view = 'filament.pages.application-settings';

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $this->data = [
            'email_notifications' => app_setting('email_notifications', true),
            'sms_alerts' => app_setting('sms_alerts', true),
            'timezone' => app_setting('timezone', 'Asia/Phnom_Penh'),
            'enable_ai_assistant_admin' => app_setting('enable_ai_assistant_admin', true),
            'enable_ai_assistant_vendor' => app_setting('enable_ai_assistant_vendor', true),
            'commission_percentage' => app_setting('commission_percentage', 10.00),
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.settings.app_settings.revenue_model'))
                    ->description(__('admin.settings.app_settings.revenue_model_desc'))
                    ->schema([
                        TextInput::make('commission_percentage')
                            ->label(__('admin.settings.app_settings.commission_fee'))
                            ->numeric()
                            ->suffix('%')
                            ->helperText(__('admin.settings.app_settings.commission_fee_helper'))
                            ->required(),
                    ]),

                Section::make(__('admin.settings.app_settings.localization'))
                    ->description(__('admin.settings.app_settings.localization_desc'))
                    ->schema([
                        TextInput::make('timezone')
                            ->label(__('admin.settings.app_settings.timezone'))
                            ->required(),
                    ])->columns(2),

                Section::make(__('admin.settings.app_settings.notifications'))
                    ->description(__('admin.settings.app_settings.notifications_desc'))
                    ->schema([
                        Toggle::make('email_notifications')
                            ->label(__('admin.settings.app_settings.enable_email')),
                        Toggle::make('sms_alerts')
                            ->label(__('admin.settings.app_settings.enable_sms')),
                    ])->columns(2),

                Section::make(__('admin.settings.app_settings.ai_control'))
                    ->description(__('admin.settings.app_settings.ai_control_desc'))
                    ->schema([
                        Toggle::make('enable_ai_assistant_admin')
                            ->label(__('admin.settings.app_settings.ai_admin')),
                        Toggle::make('enable_ai_assistant_vendor')
                            ->label(__('admin.settings.app_settings.ai_vendor')),
                    ])->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->getSchema('form')->getState();

        foreach ($state as $key => $value) {
            Setting::set($key, $value, 'application');
        }

        Notification::make()
            ->title(__('admin.settings.app_settings.success_notification'))
            ->success()
            ->send();
    }
}
