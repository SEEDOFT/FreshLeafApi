<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Clusters\Settings\Pages;

use App\Filament\Vendor\Clusters\Settings;
use App\Models\User;
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

class BusinessProfile extends Page
{
    protected static ?string $cluster = Settings::class;

    protected static ?string $slug = 'business';

    #[Override]
    protected static ?string $navigationLabel = 'Business Info';

    #[Override]
    protected string $view = 'filament.pages.shared.form-page';

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $user = Auth::user();

        $this->data = $user instanceof User
            ? $user->vendorProfile?->toArray() ?? []
            : [];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.vendor_settings.business_profile.store_info'))
                    ->description(__('admin.vendor_settings.business_profile.store_info_desc'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('business_name')
                                    ->label(__('admin.resources.vendor.business_name'))
                                    ->required(static fn (string $operation): bool => $operation === 'create')->dehydrated(static fn (mixed $state): bool => filled($state))
                                    ->maxLength(255),
                                TextInput::make('contact_phone')
                                    ->label(__('admin.resources.vendor.contact_phone'))
                                    ->tel()
                                    ->maxLength(255),
                                TextInput::make('city')
                                    ->label(__('admin.resources.vendor.city'))
                                    ->maxLength(255),
                                TextInput::make('province')
                                    ->label(__('admin.resources.vendor.province'))
                                    ->maxLength(255),
                            ]),
                        TextInput::make('address')
                            ->label(__('admin.resources.vendor.address'))
                            ->columnSpanFull()
                            ->maxLength(255),
                        Textarea::make('shop_description')
                            ->label(__('admin.vendor_settings.business_profile.description'))
                            ->placeholder('Describe your farm or organic vegetables...')
                            ->columnSpanFull(),
                        Grid::make(3)
                            ->schema([
                                TimePicker::make('opening_time')
                                    ->label(__('admin.vendor_settings.business_profile.opening_time')),
                                TimePicker::make('closing_time')
                                    ->label(__('admin.vendor_settings.business_profile.closing_time')),
                                Toggle::make('is_open')
                                    ->label(__('admin.vendor_settings.business_profile.is_open'))
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
            ->title(__('admin.vendor_settings.business_profile.success_notification'))
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
                ->label(__('admin.profile.save_changes'))
                ->submit('save')
                ->keyBindings(['mod+s']),
        ];
    }
}
