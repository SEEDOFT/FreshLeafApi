<?php

declare(strict_types=1);

namespace App\Filament\Admin\Clusters\Settings\Pages;

use App\Constants\StorageDirectory;
use App\Filament\Admin\Clusters\Settings;
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
use function basename;
use function is_array;
use function is_string;
use function ltrim;
use function reset;
use function str_starts_with;

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
     * @var array<mixed> | null
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
                                    ->label(new HtmlString('<strong>'.__('admin.profile.avatar').'</strong>'))
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
                                            ->label(new HtmlString('<strong>'.__('admin.profile.first_name').'</strong>'))
                                            ->required(fn (string $operation): bool => $operation === 'create')
                                            ->dehydrated(fn (mixed $state): bool => filled($state))
                                            ->maxLength(255),
                                        TextInput::make('last_name')
                                            ->label(new HtmlString('<strong>'.__('admin.profile.last_name').'</strong>'))
                                            ->required(fn (string $operation): bool => $operation === 'create')
                                            ->dehydrated(fn (mixed $state): bool => filled($state))
                                            ->maxLength(255),
                                        TextInput::make('email')
                                            ->label(new HtmlString('<strong>'.__('admin.profile.email').'</strong>'))
                                            ->email()
                                            ->required(fn (string $operation): bool => $operation === 'create')
                                            ->dehydrated(fn (mixed $state): bool => filled($state))
                                            ->maxLength(255),
                                        TextInput::make('phone_number')
                                            ->label(new HtmlString('<strong>'.__('admin.profile.phone').'</strong>'))
                                            ->tel(),
                                    ])
                                    ->columnSpan(3),
                            ]),
                    ]),

                Section::make(__('admin.profile.preferences'))
                    ->description(__('admin.profile.preferences_desc'))
                    ->schema([
                        Select::make('adminProfile.locale')
                            ->label(new HtmlString('<strong>'.__('admin.profile.display_language').'</strong>'))
                            ->options([
                                'km' => 'Khmer (ភាសាខ្មែរ)',
                                'en' => 'English (ភាសាអង់គ្លេស)',
                            ])
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (mixed $state): bool => filled($state)),
                    ])->columns(2),

                Section::make(__('admin.profile.professional_details'))
                    ->description(__('admin.profile.professional_details_desc'))
                    ->schema([
                        TextInput::make('adminProfile.job_title')
                            ->label(new HtmlString('<strong>'.__('admin.profile.job_title').'</strong>'))
                            ->placeholder('e.g. Operations Manager')
                            ->maxLength(255),
                        TextInput::make('adminProfile.department')
                            ->label(new HtmlString('<strong>'.__('admin.profile.department').'</strong>'))
                            ->placeholder('e.g. Logistics')
                            ->maxLength(255),
                        TextInput::make('adminProfile.office_phone')
                            ->label(new HtmlString('<strong>'.__('admin.profile.office_phone').'</strong>'))
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

        if (isset($state['image']) && is_array($state['image'])) {
            $state['image'] = $this->getImageDatabaseState($state['image']);
        } elseif (isset($state['image']) && is_string($state['image'])) {
            $state['image'] = $this->getImageDatabaseState($state['image']);
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
                ->label(new HtmlString('<strong>'.__('admin.profile.save_changes').'</strong>'))
                ->submit('save')
                ->keyBindings(['mod+s']),
        ];
    }

    protected function getNameFormComponent(): Component
    {
        return Grid::make(2)
            ->schema([
                TextInput::make('first_name')
                    ->label(new HtmlString('<strong>'.__('admin.profile.first_name').'</strong>'))
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (mixed $state): bool => filled($state))
                    ->maxLength(255),
                TextInput::make('last_name')
                    ->label(new HtmlString('<strong>'.__('admin.profile.last_name').'</strong>'))
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
