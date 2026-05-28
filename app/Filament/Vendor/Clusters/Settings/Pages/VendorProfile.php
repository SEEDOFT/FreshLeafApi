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
use Override;

use function __;
use function basename;
use function is_array;
use function is_string;
use function ltrim;
use function reset;
use function str_starts_with;

class VendorProfile extends Page
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

        $data = $user->toArray();

        if (isset($data['image']) && is_string($data['image'])) {
            $data['image'] = $this->getImageUploadState($data['image']);
        }

        if ($user->vendorProfile) {
            $data['vendorProfile'] = $user->vendorProfile->toArray();
        }

        $this->getSchema('form')?->fill($data);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('vendor.settings.vendor_profile.general_info'))
                    ->description(__('vendor.settings.vendor_profile.general_info_desc'))
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                FileUpload::make('image')
                                    ->label(__('vendor.settings.vendor_profile.avatar'))
                                    ->avatar()
                                    ->image()
                                    ->imageEditor()
                                    ->maxSize(6144)
                                    ->disk('public')
                                    ->directory(StorageDirectory::USERS)
                                    ->alignCenter(),
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('first_name')
                                            ->label(__('vendor.settings.vendor_profile.first_name'))
                                            ->required(fn (string $operation): bool => $operation === 'create')
                                            ->dehydrated(fn (mixed $state): bool => filled($state))
                                            ->maxLength(255),
                                        TextInput::make('last_name')
                                            ->label(__('vendor.settings.vendor_profile.last_name'))
                                            ->required(fn (string $operation): bool => $operation === 'create')
                                            ->dehydrated(fn (mixed $state): bool => filled($state))
                                            ->maxLength(255),
                                        TextInput::make('email')
                                            ->label(__('vendor.settings.vendor_profile.email'))
                                            ->email()
                                            ->required(fn (string $operation): bool => $operation === 'create')
                                            ->dehydrated(fn (mixed $state): bool => filled($state))
                                            ->maxLength(255),
                                        TextInput::make('phone_number')
                                            ->label(__('vendor.settings.vendor_profile.phone'))
                                            ->tel(),
                                    ])
                                    ->columnSpan(3),
                            ]),
                    ]),
                Section::make(__('vendor.settings.vendor_profile.preferences'))
                    ->description(__('vendor.settings.vendor_profile.preferences_desc'))
                    ->schema([
                        Select::make('vendorProfile.locale')
                            ->label(__('vendor.settings.vendor_profile.language'))
                            ->options([
                                'km' => __('admin.profile.locale_khmer'),
                                'en' => __('admin.profile.locale_english'),
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

        if (isset($state['image']) && is_array($state['image'])) {
            $state['image'] = $this->getImageDatabaseState($state['image']);
        } elseif (isset($state['image']) && is_string($state['image'])) {
            $state['image'] = $this->getImageDatabaseState($state['image']);
        }

        $vendorProfileData = $state['vendorProfile'] ?? [];
        unset($state['vendorProfile']);

        $user->update($state);

        if ($user->vendorProfile) {
            $user->vendorProfile->update($vendorProfileData);
        }

        Notification::make()
            ->title(__('vendor.settings.vendor_profile.success_notification'))
            ->success()
            ->send();

        // Refresh to apply language change
        $this->redirect(static::getUrl());
    }

    /**
     * @return Action[]
     */
    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(__('shared.profile.save_changes'))
                ->submit('save')
                ->keyBindings(['mod+s']),
        ];
    }

    protected function getNameFormComponent(): Component
    {
        return Grid::make(2)
            ->schema([
                TextInput::make('first_name')
                    ->label(__('vendor.settings.vendor_profile.first_name'))
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (mixed $state): bool => filled($state))
                    ->maxLength(255),
                TextInput::make('last_name')
                    ->label(__('vendor.settings.vendor_profile.last_name'))
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
