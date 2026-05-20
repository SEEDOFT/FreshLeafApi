<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Clusters\Settings\Pages;

use App\Constants\StorageDirectory;
use App\Filament\Vendor\Clusters\Settings;
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
use Illuminate\Support\HtmlString;
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

        if (isset($this->data['image']) && is_string($this->data['image'])) {
            $this->data['image'] = $this->getImageUploadState($this->data['image']);
        }

        if ($user->vendorProfile) {
            $this->data['vendorProfile'] = $user->vendorProfile->toArray();
        }
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('vendor.settings.vendor_profile.general_info'))
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                FileUpload::make('image')
                                    ->label(new HtmlString('<strong>'.__('vendor.settings.vendor_profile.avatar').'</strong>'))
                                    ->avatar()
                                    ->imageEditor()
                                    ->directory(StorageDirectory::USERS)
                                    ->alignCenter()
                                    ->columnSpan(1),
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('first_name')
                                            ->label(new HtmlString('<strong>'.__('vendor.settings.vendor_profile.first_name').'</strong>'))
                                            ->required(fn (string $operation): bool => $operation === 'create')
                                            ->dehydrated(fn (mixed $state): bool => filled($state))
                                            ->maxLength(255),
                                        TextInput::make('last_name')
                                            ->label(new HtmlString('<strong>'.__('vendor.settings.vendor_profile.last_name').'</strong>'))
                                            ->required(fn (string $operation): bool => $operation === 'create')
                                            ->dehydrated(fn (mixed $state): bool => filled($state))
                                            ->maxLength(255),
                                        TextInput::make('email')
                                            ->label(new HtmlString('<strong>'.__('vendor.settings.vendor_profile.email').'</strong>'))
                                            ->email()
                                            ->required(fn (string $operation): bool => $operation === 'create')
                                            ->dehydrated(fn (mixed $state): bool => filled($state))
                                            ->maxLength(255),
                                        TextInput::make('phone_number')
                                            ->label(new HtmlString('<strong>'.__('vendor.settings.vendor_profile.phone').'</strong>'))
                                            ->tel()
                                            ->required(fn (string $operation): bool => $operation === 'create')
                                            ->dehydrated(fn (mixed $state): bool => filled($state)),
                                    ])
                                    ->columnSpan(3),
                            ]),
                    ]),
                Section::make(__('vendor.settings.vendor_profile.preferences'))
                    ->description(__('vendor.settings.vendor_profile.preferences_desc'))
                    ->schema([
                        Select::make('vendorProfile.locale')
                            ->label(new HtmlString('<strong>'.__('vendor.settings.vendor_profile.language').'</strong>'))
                            ->options([
                                'km' => 'Khmer (ភាសាខ្មែរ)',
                                'en' => 'English (ភាសាអង់គ្លេស)',
                            ])
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (mixed $state): bool => filled($state)),
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

        // Unwrap image from array before saving to DB
        if (isset($state['image']) && is_array($state['image'])) {
            $state['image'] = reset($state['image']) ?: null;
        }

        $user->update($state);

        Notification::make()
            ->title(__('vendor.settings.vendor_profile.success_notification'))
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
                ->label(new HtmlString('<strong>'.__('shared.profile.save_changes').'</strong>'))
                ->submit('save')
                ->keyBindings(['mod+s']),
        ];
    }

    protected function getNameFormComponent(): Component
    {
        return Grid::make(2)
            ->schema([
                TextInput::make('first_name')
                    ->label(new HtmlString('<strong>'.__('vendor.settings.vendor_profile.first_name').'</strong>'))
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (mixed $state): bool => filled($state))
                    ->maxLength(255),
                TextInput::make('last_name')
                    ->label(new HtmlString('<strong>'.__('vendor.settings.vendor_profile.last_name').'</strong>'))
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (mixed $state): bool => filled($state))
                    ->maxLength(255),
            ]);
    }

    /**
     * @return array<string>
     */
    private function getImageUploadState(string $image): array
    {
        $path = ltrim($image, '/');

        return [
            str_starts_with($path, StorageDirectory::USERS)
                ? $path : StorageDirectory::USERS.'/'.$path,
        ];
    }

    /**
     * @param  array<mixed>|string  $image
     */
    private function getImageDatabaseState(array|string $image): ?string
    {
        $path = is_array($image) ? reset($image) : $image;

        if (! is_string($path) || $path === '') {
            return null;
        }

        return basename($path);
    }
}
