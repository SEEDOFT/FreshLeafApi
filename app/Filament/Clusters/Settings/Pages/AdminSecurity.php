<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Settings\Pages;

use App\Filament\Clusters\Settings;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminSecurity extends Page
{
    protected static ?string $cluster = Settings::class;

    protected static ?string $slug = 'security';

    protected static ?string $navigationLabel = 'Security';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-lock-closed';

    protected string $view = 'filament.pages.admin-security';

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
                Section::make('Change Password')
                    ->description('Ensure your account is using a long, random password to stay secure.')
                    ->schema([
                        TextInput::make('current_password')
                            ->password()
                            ->required()
                            ->currentPassword(),
                        TextInput::make('password')
                            ->password()
                            ->required()
                            ->confirmed(),
                        TextInput::make('password_confirmation')
                            ->password()
                            ->required(),
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
            ->title('Password updated successfully.')
            ->success()
            ->send();
    }
}
