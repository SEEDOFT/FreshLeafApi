<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Clusters\Settings\Pages;

use App\Filament\Vendor\Clusters\Settings;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class VendorProfile extends Page
{
    protected static ?string $cluster = Settings::class;

    protected static ?string $slug = 'profile';

    protected static ?string $navigationLabel = 'My Profile';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-circle';

    protected string $view = 'filament.vendor.pages.business-profile';

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $user = Auth::user();
        $this->data = $user->toArray();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('General Information')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                FileUpload::make('image')
                                    ->label('Store / Owner Avatar')
                                    ->avatar()
                                    ->imageEditor()
                                    ->directory('avatars')
                                    ->alignCenter()
                                    ->columnSpan(1),

                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('first_name')
                                            ->required()
                                            ->maxLength(255),
                                        TextInput::make('last_name')
                                            ->required()
                                            ->maxLength(255),
                                        TextInput::make('email')
                                            ->email()
                                            ->required()
                                            ->maxLength(255),
                                        TextInput::make('phone_number')
                                            ->label('Phone Number')
                                            ->tel()
                                            ->required(),
                                    ])
                                    ->columnSpan(3),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $user = Auth::user();
        $state = $this->getSchema('form')->getState();

        $user->update($state);

        Notification::make()
            ->title('Profile updated successfully.')
            ->success()
            ->send();
    }

    protected function getNameFormComponent(): Component
    {
        return Grid::make(2)
            ->schema([
                TextInput::make('first_name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('last_name')
                    ->required()
                    ->maxLength(255),
            ]);
    }
}
