<?php

declare(strict_types=1);

namespace App\Filament\Admin\Clusters\Settings\Pages;

use App\Filament\Admin\Clusters\Settings;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\HtmlString;
use Override;

class AdminSecurity extends Page
{
    public bool $passwordVerified = false;

    #[Override]
    protected static ?string $cluster = Settings::class;

    #[Override]
    protected static ?string $slug = 'security';

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('admin.resources.security.label');
    }

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-lock-closed';

    #[Override]
    protected string $view = 'filament.pages.shared.form-page';

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $this->data = [];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.resources.security.change_password'))
                    ->description(__('admin.resources.security.change_password_desc'))
                    ->schema([
                        TextInput::make('current_password')
                            ->label(new HtmlString('<strong>'.__('admin.resources.security.current_password').'</strong>'))
                            ->password()
                            ->required(fn (): bool => ! $this->passwordVerified)
                            ->dehydrated(fn (mixed $state): bool => filled($state))
                            ->currentPassword()
                            ->columnSpan(1),
                        Action::make('check_password')
                            ->label(new HtmlString('<strong>'.__('admin.resources.security.check_password').'</strong>'))
                            ->action('checkPassword')
                            ->color('primary')
                            ->extraAttributes(['class' => 'flex items-end mt-7']),
                        TextInput::make('password')
                            ->label(new HtmlString('<strong>'.__('admin.resources.security.password').'</strong>'))
                            ->password()
                            ->required(fn (): bool => $this->passwordVerified)
                            ->dehydrated(fn (mixed $state): bool => filled($state))
                            ->confirmed()
                            ->columnSpan(1),
                        TextInput::make('password_confirmation')
                            ->label(new HtmlString('<strong>'.__('admin.resources.security.password_confirmation').'</strong>'))
                            ->password()
                            ->required(fn (): bool => $this->passwordVerified)
                            ->dehydrated(fn (mixed $state): bool => filled($state))
                            ->columnSpan(1),
                    ])->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $user = Auth::user();

        if (! $user) {
            return;
        }

        $form = $this->getSchema('form');

        if (! $form) {
            return;
        }

        $state = $form->getState();

        if ($this->passwordVerified && filled($state['password'] ?? null)) {
            $user->update([
                'password' => Hash::make($state['password']),
            ]);

            $this->passwordVerified = false;
            $this->data['current_password'] = null;
            $this->data['password'] = null;
            $this->data['password_confirmation'] = null;
        }

        Notification::make()
            ->title(__('admin.settings.app_settings.success_notification'))
            ->success()
            ->send();
    }

    /**
     * @return array<Action>
     */
    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(new HtmlString('<strong>'.__('admin.resources.security.update_password').'</strong>'))
                ->submit('save')
                ->keyBindings([]),
        ];
    }

    public function checkPassword(): void
    {
        $user = Auth::user();

        if (! $user) {
            return;
        }

        $currentPassword = $this->data['current_password'] ?? '';

        if (! Hash::check($currentPassword, $user->password)) {
            Notification::make()
                ->title(__('admin.resources.security.password_incorrect'))
                ->danger()
                ->send();

            $this->addError('data.current_password', __('admin.resources.security.password_incorrect'));

            $this->passwordVerified = false;

            return;
        }

        $this->passwordVerified = true;

        Notification::make()
            ->title(__('admin.resources.security.password_verified'))
            ->success()
            ->send();
    }
}
