<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\ExchangeRates\Pages;

use App\Filament\Admin\Resources\ExchangeRates\ExchangeRateResource;
use App\Filament\Admin\Resources\ExchangeRates\Tables\ExchangeRatesTable;
use App\Models\Currency;
use App\Models\ExchangeRate;
use App\Models\ExchangeRateHistory;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Override;

/**
 * @property Schema $form
 */
class ManageExchangeRate extends Page implements HasForms, HasTable
{
    use InteractsWithForms, InteractsWithTable;

    #[Override]
    protected static string $resource = ExchangeRateResource::class;

    #[Override]
    protected string $view = 'filament.pages.manage-exchange-rate';

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public function mount(): void
    {
        $usdToKhr = ExchangeRate::query()
            ->where('from_currency_id', Currency::USD_ID)
            ->where('to_currency_id', Currency::KHR_ID)
            ->first();
        $khrToUsd = ExchangeRate::query()
            ->where('from_currency_id', Currency::KHR_ID)
            ->where('to_currency_id', Currency::USD_ID)
            ->first();

        $this->form->fill([
            'usd_to_khr_rate' => $usdToKhr?->rate,
            'khr_to_usd_rate' => $khrToUsd?->rate,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.resources.exchange_rate.form_section'))
                    ->description(__('admin.resources.exchange_rate.form_section_desc'))
                    ->schema([
                        TextInput::make('usd_to_khr_rate')
                            ->label(__('admin.resources.exchange_rate.usd_to_khr_label'))
                            ->required()
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('khr_to_usd_rate')
                            ->label(__('admin.resources.exchange_rate.khr_to_usd_label'))
                            ->required()
                            ->numeric()
                            ->minValue(0),
                    ]),
            ])
            ->statePath('data');
    }

    /**
     * @return array<Action>
     */
    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(__('admin.resources.exchange_rate.update_button'))
                ->submit('save'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $usdToKhr = ExchangeRate::query()
            ->where('from_currency_id', Currency::USD_ID)
            ->where('to_currency_id', Currency::KHR_ID)
            ->first();
        $khrToUsd = ExchangeRate::query()
            ->where('from_currency_id', Currency::KHR_ID)
            ->where('to_currency_id', Currency::USD_ID)
            ->first();

        if ($usdToKhr) {
            $usdToKhr->update(['rate' => $data['usd_to_khr_rate']]);
            $usdToKhr->recordHistory();
        }

        if ($khrToUsd) {
            $khrToUsd->update(['rate' => $data['khr_to_usd_rate']]);
            $khrToUsd->recordHistory();
        }

        Notification::make()
            ->title(__('admin.resources.exchange_rate.update_success'))
            ->success()
            ->send();
    }

    public function table(Table $table): Table
    {
        return ExchangeRatesTable::configure($table)
            ->query(
                ExchangeRateHistory::query()->latest()
            );
    }

    #[Override]
    public function getTitle(): string
    {
        return __('admin.resources.exchange_rate.page_title');
    }
}
