<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Clusters\Settings\Pages;

use App\Filament\Vendor\Clusters\Settings;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class FinancialDetails extends Page
{
    protected static ?string $cluster = Settings::class;

    protected static ?string $slug = 'financials';

    protected static ?string $navigationLabel = 'Financials';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected string $view = 'filament.vendor.pages.financial-details';

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
                Section::make('Bank Account Details')
                    ->description('Used for weekly earnings payouts.')
                    ->schema([
                        TextInput::make('bank_name')
                            ->label('Bank Name')
                            ->placeholder('e.g. ABA Bank')
                            ->maxLength(255),
                        TextInput::make('bank_account_name')
                            ->label('Account Holder Name')
                            ->placeholder('e.g. KOY YOTRABOTH')
                            ->maxLength(255),
                        TextInput::make('bank_account_number')
                            ->label('Account Number')
                            ->maxLength(255),
                        FileUpload::make('bank_qr_code')
                            ->label('Bank QR Code')
                            ->image()
                            ->directory('vendor-verification')
                            ->columnSpanFull(),
                    ])->columns(3),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $user = Auth::user();

        $state = $this->getSchema('form')->getState();
        $user->vendorProfile()->update($state);

        Notification::make()
            ->title('Financial details updated.')
            ->success()
            ->send();
    }
}
