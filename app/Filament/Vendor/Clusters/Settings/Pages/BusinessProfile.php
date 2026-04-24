<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Clusters\Settings\Pages;

use App\Filament\Vendor\Clusters\Settings;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class BusinessProfile extends Page
{
    protected static ?string $cluster = Settings::class;

    protected static ?string $slug = 'business';

    protected static ?string $navigationLabel = 'Business Info';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-storefront';

    protected string $view = 'filament.vendor.pages.business-profile';

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $this->data = Auth::user()->vendorProfile?->toArray() ?? [];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Store Information')
                    ->description('Publicly visible details about your business.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('business_name')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('contact_phone')
                                    ->tel()
                                    ->maxLength(255),
                                TextInput::make('city')
                                    ->maxLength(255),
                                TextInput::make('province')
                                    ->maxLength(255),
                            ]),
                        TextInput::make('address')
                            ->columnSpanFull()
                            ->maxLength(255),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $user = Auth::user();

        $state = $this->getSchema('form')->getState();
        $user->vendorProfile()->update($state);

        Notification::make()
            ->title('Business profile updated.')
            ->success()
            ->send();
    }
}
