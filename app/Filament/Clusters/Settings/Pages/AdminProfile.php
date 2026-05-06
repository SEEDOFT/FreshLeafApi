<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Settings\Pages;

use App\Filament\Clusters\Settings;
use BackedEnum;
use Filament\Actions\Action;
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
use Override;

use function __;
use function is_array;
use function is_string;
use function reset;

class AdminProfile extends Page
{
    #[Override]
    protected static ?string $cluster = Settings::class;

    #[Override]
    protected static ?string $slug = 'profile';

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.my_profile');
    }

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-circle';

    #[Override]
    protected string $view = 'filament.pages.shared.form-page';

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
                Section::make(__('admin.profile.general_info'))
                    ->description(__('admin.profile.general_info_desc'))
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                FileUpload::make('image')
                                    ->label(__('admin.profile.avatar'))
                                    ->avatar()
                                    ->imageEditor()
                                    ->disk('public')
                                    ->directory('users')
                                    ->maxSize(6144)
                                    ->alignCenter()
                                    ->columnSpan(1),
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('first_name')
                                            ->label(__('admin.profile.first_name'))
                                            ->required(static fn (string $operation): bool => $operation === 'create')
                                            ->dehydrated(static fn ($state): bool => filled($state))
                                            ->maxLength(255),
                                        TextInput::make('last_name')
                                            ->label(__('admin.profile.last_name'))
                                            ->required(static fn (string $operation): bool => $operation === 'create')
                                            ->dehydrated(static fn ($state): bool => filled($state))
                                            ->maxLength(255),
                                        TextInput::make('email')
                                            ->label(__('admin.profile.email'))
                                            ->email()
                                            ->required(static fn (string $operation): bool => $operation === 'create')
                                            ->dehydrated(static fn ($state): bool => filled($state))
                                            ->maxLength(255),
                                        TextInput::make('phone_number')
                                            ->label(__('admin.profile.phone'))
                                            ->tel(),
                                    ])
                                    ->columnSpan(3),
                            ]),
                    ]),

                Section::make(__('admin.profile.preferences'))
                    ->description(__('admin.profile.preferences_desc'))
                    ->schema([
                        Select::make('adminProfile.locale')
                            ->label(__('admin.profile.display_language'))
                            ->options([
                                'km' => 'Khmer (ភាសាខ្មែរ)',
                                'en' => 'English (ភាសាអង់គ្លេស)',
                            ])
                            ->required(static fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(static fn ($state): bool => filled($state))
                            ->native(false)
                            ->searchable()
                            ->preload(),
                    ])->columns(2),

                Section::make(__('admin.profile.professional_details'))
                    ->description(__('admin.profile.professional_details_desc'))
                    ->schema([
                        TextInput::make('adminProfile.job_title')
                            ->label(__('admin.profile.job_title'))
                            ->placeholder('e.g. Operations Manager')
                            ->maxLength(255),
                        TextInput::make('adminProfile.department')
                            ->label(__('admin.profile.department'))
                            ->placeholder('e.g. Logistics')
                            ->maxLength(255),
                        TextInput::make('adminProfile.office_phone')
                            ->label(__('admin.profile.office_phone'))
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

        $form = $this->getSchema('form');
        if (! $form) {
            return;
        }

        $state = $form->getState();

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
            ->title(__('admin.profile.success_notification'))
            ->success()
            ->send();

        // Refresh to apply language change
        $this->redirect(static::getUrl());
    }

    /**
     * @return array<Action>
     */
    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(__('admin.profile.save_changes'))
                ->submit('save')
                ->keyBindings(['mod+s']),
        ];
    }

    protected function getNameFormComponent(): Component
    {
        return Grid::make(2)
            ->schema([
                TextInput::make('first_name')
                    ->label(__('admin.profile.first_name'))
                    ->required(static fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(static fn ($state): bool => filled($state))
                    ->maxLength(255),
                TextInput::make('last_name')
                    ->label(__('admin.profile.last_name'))
                    ->required(static fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(static fn ($state): bool => filled($state))
                    ->maxLength(255),
            ]);
    }
}
