<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Clusters\Settings\Pages;

use App\Constants\StorageDirectory;
use App\Filament\Forms\Components\PhoneNumberInput;
use App\Filament\Vendor\Clusters\Settings;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Override;

use function filled;
use function is_string;
use function ltrim;
use function str_starts_with;

class BusinessProfile extends Page
{
    #[Override]
    protected static ?string $cluster = Settings::class;

    #[Override]
    protected static ?string $slug = 'business';

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('vendor.settings.business_profile.label');
    }

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-office-2';

    #[Override]
    protected string $view = 'filament.pages.shared.form-page';

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $user = Auth::user();

        if ($user instanceof User && $user->vendorProfile) {
            $data = $user->vendorProfile->toArray();

            if (isset($data['store_front_image']) && is_string($data['store_front_image'])) {
                $data['store_front_image'] = $this->getFileUploadState($data['store_front_image']);
            }

            $this->getSchema('form')?->fill($data);
        }
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('vendor.settings.business_profile.store_info'))
                    ->description(__('vendor.settings.business_profile.store_info_desc'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('business_name')
                                    ->label(__('shared.vendor.business_name'))
                                    ->required(fn (string $operation): bool => $operation === 'create')
                                    ->dehydrated(fn (mixed $state): bool => filled($state))
                                    ->maxLength(255),
                                PhoneNumberInput::make('contact_phone')
                                    ->label(__('shared.form.fields.contact_phone'))
                                    ->required(fn (string $operation): bool => $operation === 'create')
                                    ->dehydrated(fn (mixed $state): bool => filled($state)),
                                TextInput::make('village')
                                    ->label(__('shared.form.fields.village'))
                                    ->dehydrated(fn (mixed $state): bool => filled($state))
                                    ->maxLength(255),
                                TextInput::make('commune')
                                    ->label(__('shared.form.fields.commune'))
                                    ->dehydrated(fn (mixed $state): bool => filled($state))
                                    ->maxLength(255),
                                TextInput::make('district')
                                    ->label(__('shared.form.fields.district'))
                                    ->dehydrated(fn (mixed $state): bool => filled($state))
                                    ->maxLength(255),
                                TextInput::make('province')
                                    ->label(__('shared.form.fields.province'))
                                    ->dehydrated(fn (mixed $state): bool => filled($state))
                                    ->maxLength(255),
                            ]),
                        TextInput::make('address')
                            ->label(__('shared.form.fields.address'))
                            ->dehydrated(fn (mixed $state): bool => filled($state))
                            ->columnSpanFull()
                            ->maxLength(255),
                        Textarea::make('shop_description')
                            ->label(__('vendor.settings.business_profile.description'))
                            ->dehydrated(fn (mixed $state): bool => filled($state))
                            ->placeholder(__('vendor.settings.business_profile.description_placeholder'))
                            ->columnSpanFull(),
                        Grid::make(3)
                            ->schema([
                                TimePicker::make('opening_time')
                                    ->label(__('vendor.settings.business_profile.opening_time'))
                                    ->dehydrated(fn (mixed $state): bool => filled($state)),
                                TimePicker::make('closing_time')
                                    ->label(__('vendor.settings.business_profile.closing_time'))
                                    ->dehydrated(fn (mixed $state): bool => filled($state)),
                                Toggle::make('is_open')
                                    ->label(__('vendor.settings.business_profile.is_open'))
                                    ->dehydrated(fn (mixed $state): bool => filled($state))
                                    ->inline(false),
                            ]),
                    ]),

                Section::make(__('admin.resources.vendor.store_photo'))
                    ->schema([
                        FileUpload::make('store_front_image')
                            ->label(__('admin.resources.vendor.store_photo'))
                            ->image()
                            ->imageEditor()
                            ->maxSize(6144)
                            ->disk('public')
                            ->directory(StorageDirectory::SHOPS)
                            ->dehydrated(fn (mixed $state): bool => filled($state)),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $user = Auth::user();
        $form = $this->getSchema('form');

        if (! $user instanceof User || ! $form) {
            return;
        }

        $state = $form->getState();

        if (isset($state['store_front_image']) && is_array($state['store_front_image'])) {
            $state['store_front_image'] = collect($state['store_front_image'])->first();
        }

        $user->vendorProfile()->update($state);

        Notification::make()
            ->title(__('vendor.settings.business_profile.success_notification'))
            ->success()
            ->send();
    }

    /**
     * Resolve a stored filename into the full path the FileUpload
     * component expects for displaying existing files.
     *
     * @return array<string>
     */
    private function getFileUploadState(string $path): array
    {
        $path = ltrim($path, '/');

        // Check if path already contains the directory
        if (str_starts_with($path, StorageDirectory::SHOPS)) {
            $fullPath = $path;
        } elseif (str_starts_with($path, StorageDirectory::VENDOR_VERIFICATION)) {
            // Support legacy path if migrating
            $fullPath = $path;
        } else {
            $fullPath = StorageDirectory::SHOPS.'/'.$path;
        }

        // Try public disk first (our new target)
        if (Storage::disk('public')->exists($fullPath)) {
            return [$fullPath];
        }

        // Fallback to local disk for legacy files
        if (Storage::disk('local')->exists($fullPath)) {
            return [$fullPath];
        }

        return [];
    }

    /**
     * @return Action[]
     */
    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(__('shared.form.save_changes'))
                ->submit('save')
                ->keyBindings(['mod+s']),
        ];
    }
}
