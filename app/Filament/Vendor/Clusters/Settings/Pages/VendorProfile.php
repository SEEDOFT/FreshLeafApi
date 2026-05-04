<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Clusters\Settings\Pages;

use App\Filament\Vendor\Clusters\Settings;
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

class VendorProfile extends Page
{
    #[Override]
    protected static ?string $cluster = Settings::class;

    #[Override]
    protected static ?string $slug = 'profile';

    #[Override]
    protected static ?string $navigationLabel = 'My Profile';

    #[Override]
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-circle';

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
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.vendor_settings.vendor_profile.general_info'))
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                FileUpload::make('image')
                                    ->label(__('admin.vendor_settings.vendor_profile.avatar'))
                                    ->avatar()
                                    ->imageEditor()
                                    ->directory('avatars')
                                    ->alignCenter()
                                    ->columnSpan(1),

                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('first_name')
                                            ->label(__('admin.vendor_settings.vendor_profile.first_name'))
                                            ->required(static fn (string $operation): bool => $operation === 'create')->dehydrated(static fn ($state): bool => filled($state))
                                            ->maxLength(255),
                                        TextInput::make('last_name')
                                            ->label(__('admin.vendor_settings.vendor_profile.last_name'))
                                            ->required(static fn (string $operation): bool => $operation === 'create')->dehydrated(static fn ($state): bool => filled($state))
                                            ->maxLength(255),
                                        TextInput::make('email')
                                            ->label(__('admin.vendor_settings.vendor_profile.email'))
                                            ->email()
                                            ->required(static fn (string $operation): bool => $operation === 'create')->dehydrated(static fn ($state): bool => filled($state))
                                            ->maxLength(255),
                                        TextInput::make('phone_number')
                                            ->label(__('admin.vendor_settings.vendor_profile.phone'))
                                            ->tel()
                                            ->required(static fn (string $operation): bool => $operation === 'create')->dehydrated(static fn ($state): bool => filled($state)),
                                    ])
                                    ->columnSpan(3),
                            ]),
                    ]),

                Section::make(__('admin.vendor_settings.vendor_profile.preferences'))
                    ->description(__('admin.vendor_settings.vendor_profile.preferences_desc'))
                    ->schema([
                        Select::make('locale')
                            ->label(__('admin.vendor_settings.vendor_profile.language'))
                            ->options([
                                'km' => 'Khmer (ភាសាខ្មែរ)',
                                'en' => 'English (ភាសាអង់គ្លេស)',
                            ])
                            ->required(static fn (string $operation): bool => $operation === 'create')->dehydrated(static fn ($state): bool => filled($state))
                            ->native(false)
                            ->searchable()
                            ->preload(),
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

        $state = $this->getSchema('form')->getState();

        // Unwrap image from array before saving to DB
        if (isset($state['image']) && is_array($state['image'])) {
            $state['image'] = reset($state['image']) ?: null;
        }

        $user->update($state);

        Notification::make()
            ->title(__('admin.vendor_settings.vendor_profile.success_notification'))
            ->success()
            ->send();

        // Refresh to apply language change if locale changed
        $this->redirect(static::getUrl());
    }

    /**
     * @return Action[]
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
                    ->label(__('admin.vendor_settings.vendor_profile.first_name'))
                    ->required(static fn (string $operation): bool => $operation === 'create')->dehydrated(static fn ($state): bool => filled($state))
                    ->maxLength(255),
                TextInput::make('last_name')
                    ->label(__('admin.vendor_settings.vendor_profile.last_name'))
                    ->required(static fn (string $operation): bool => $operation === 'create')->dehydrated(static fn ($state): bool => filled($state))
                    ->maxLength(255),
            ]);
    }
}
