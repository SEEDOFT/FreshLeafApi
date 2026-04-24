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

    protected static ?string $navigationLabel = 'Application Control';

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
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Notification Preferences')
                    ->description('Global configuration for system alerts.')
                    ->schema([
                        Toggle::make('email_notifications')
                            ->label('Enable Email Notifications'),
                        Toggle::make('sms_alerts')
                            ->label('Enable SMS Alerts for New Vendors'),
                        TextInput::make('timezone')
                            ->label('Preferred Timezone')
                            ->required(),
                    ])->columns(2),

                Section::make('AI Assistant Control')
                    ->description('Enable or disable the floating AI assistant for panel users.')
                    ->schema([
                        Toggle::make('enable_ai_assistant_admin')
                            ->label('Enable Assistant for Admin Panel'),
                        Toggle::make('enable_ai_assistant_vendor')
                            ->label('Enable Assistant for Vendor Panel'),
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
            ->title('Settings updated successfully.')
            ->success()
            ->send();
    }
}
