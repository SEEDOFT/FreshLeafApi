<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Clusters\Settings\Pages;

use App\Filament\Forms\Components\PhoneNumberInput;
use App\Filament\Vendor\Clusters\Settings;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
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
use Override;

use function filled;

class BusinessProfile extends Page
{
    #[Override]
    protected static ?string $cluster = Settings::class;

    #[Override]
    protected static ?string $slug = 'business';

    #[Override]
    protected static ?string $navigationLabel = 'Business Info';

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

        $data = $user instanceof User && $user->vendorProfile
            ? $user->vendorProfile->toArray() : [];

        $this->getSchema('form')?->fill($data);
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
                            ->placeholder('Describe your farm or organic vegetables...')
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
        $user->vendorProfile()->update($state);

        Notification::make()
            ->title(__('vendor.settings.business_profile.success_notification'))
            ->success()
            ->send();
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
