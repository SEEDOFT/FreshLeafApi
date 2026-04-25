<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Settings\Pages;

use App\Filament\Clusters\Settings;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class AdminProfile extends Page
{
    protected static ?string $cluster = Settings::class;

    protected static ?string $slug = 'profile';

    protected static ?string $navigationLabel = 'My Profile';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-circle';

    protected string $view = 'filament.pages.admin-profile';

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $user = Auth::user();
        if (! $user) {
            return;
        }

        $this->data = $user->toArray();

        // Wrap image in array for FileUpload component if it's a string
        if (isset($this->data['image']) && is_string($this->data['image'])) {
            $this->data['image'] = [$this->data['image']];
        }

        if ($user->adminProfile) {
            $this->data['adminProfile'] = $user->adminProfile->toArray();
        }
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('General Information')
                    ->description('Update your basic profile information and avatar.')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                FileUpload::make('image')
                                    ->label('Avatar')
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

                Section::make('Preferences')
                    ->description('Customize your dashboard language.')
                    ->schema([
                        Select::make('locale')
                            ->label('Display Language')
                            ->options([
                                'km' => 'Khmer (ភាសាខ្មែរ)',
                                'en' => 'English',
                            ])
                            ->required()
                            ->native(false),
                    ])->columns(2),

                Section::make('Professional Details')
                    ->description('Manage your role and position within the company.')
                    ->schema([
                        TextInput::make('adminProfile.job_title')
                            ->label('Job Title')
                            ->placeholder('e.g. Operations Manager')
                            ->maxLength(255),
                        TextInput::make('adminProfile.department')
                            ->label('Department')
                            ->placeholder('e.g. Logistics')
                            ->maxLength(255),
                        TextInput::make('adminProfile.office_phone')
                            ->label('Office Phone')
                            ->tel()
                            ->maxLength(255),
                    ])->columns(3),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $user = Auth::user();
        if (! $user) {
            return;
        }

        $state = $this->getSchema('form')->getState();

        // Unwrap image from array before saving to DB
        if (isset($state['image']) && is_array($state['image'])) {
            $state['image'] = reset($state['image']) ?: null;
        }

        $adminProfileData = $state['adminProfile'] ?? [];
        unset($state['adminProfile']);

        $user->update($state);

        if ($user->adminProfile) {
            $user->adminProfile->update($adminProfileData);
        }

        Notification::make()
            ->title('Profile updated successfully.')
            ->success()
            ->send();

        // Refresh to apply language change if locale changed
        $this->redirect(static::getUrl());
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
