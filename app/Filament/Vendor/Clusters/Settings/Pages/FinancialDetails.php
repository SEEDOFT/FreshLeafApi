<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Clusters\Settings\Pages;

use App\Filament\Vendor\Clusters\Settings;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Override;

class FinancialDetails extends Page
{
    #[Override]
    protected static ?string $cluster = Settings::class;

    #[Override]
    protected static ?string $slug = 'financials';

    #[Override]
    protected static ?string $navigationLabel = 'Financials';

    #[Override]
    protected string $view = 'filament.pages.shared.form-page';

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
                Section::make(__('admin.vendor_settings.financial_details.label'))
                    ->description('Used for weekly earnings payouts.')
                    ->schema([
                        TextInput::make('bank_name')
                            ->label(__('admin.vendor_settings.financial_details.bank_name'))
                            ->placeholder('e.g. ABA Bank')
                            ->maxLength(255),
                        TextInput::make('bank_account_name')
                            ->label(__('admin.vendor_settings.financial_details.account_holder'))
                            ->placeholder('e.g. KOY YOTRABOTH')
                            ->maxLength(255),
                        TextInput::make('bank_account_number')
                            ->label(__('admin.vendor_settings.financial_details.account_number'))
                            ->maxLength(255),
                        FileUpload::make('bank_qr_code')
                            ->label(__('admin.vendor_settings.financial_details.qr_code'))
                            ->image()
                            ->disk('local')
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
            ->title(__('admin.vendor_settings.financial_details.success_notification'))
            ->success()
            ->send();
    }

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
