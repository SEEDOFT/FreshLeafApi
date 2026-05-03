<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Settings\Pages;

use App\Filament\Clusters\Settings;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Override;

class AdminSecurity extends Page
{
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
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-lock-closed';

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
                            ->label(__('admin.resources.security.current_password'))
                            ->password()
                            ->required(static fn (string $operation): bool => $operation === 'create')->dehydrated(static fn ($state): bool => filled($state))
                            ->currentPassword(),
                        TextInput::make('password')
                            ->label(__('admin.resources.security.password'))
                            ->password()
                            ->required(static fn (string $operation): bool => $operation === 'create')->dehydrated(static fn ($state): bool => filled($state))
                            ->confirmed(),
                        TextInput::make('password_confirmation')
                            ->label(__('admin.resources.security.password_confirmation'))
                            ->password()
                            ->required(static fn (string $operation): bool => $operation === 'create')->dehydrated(static fn ($state): bool => filled($state)),
                    ])->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $user = Auth::user();
        $state = $this->getSchema('form')->getState();

        $user->update([
            'password' => Hash::make($state['password']),
        ]);

        $this->data = [];

        Notification::make()
            ->title(__('admin.resources.security.success_notification'))
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
                ->label(__('admin.resources.security.update_password'))
                ->submit('save')
                ->keyBindings(['mod+s']),
        ];
    }
}
