<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\CommissionFees\Pages;

use App\Filament\Admin\Resources\CommissionFees\CommissionFeeResource;
use App\Models\CommissionFee;
use App\Models\CommissionFeeHistory;
use App\Models\User;
use App\Models\UserType;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Override;

/**
 * @property Schema $form
 */
class ManageCommissionFee extends Page implements HasForms, HasTable
{
    use InteractsWithForms, InteractsWithTable;

    #[Override]
    protected static string $resource = CommissionFeeResource::class;

    #[Override]
    protected string $view = 'filament.pages.manage-commission-fee';

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public function mount(): void
    {
        $commission = CommissionFee::current();

        $this->form->fill([
            'rate' => $commission->rate,
            'description' => $commission->description,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.commission_fee.form_section'))
                    ->description(__('admin.commission_fee.form_section_desc'))
                    ->schema([
                        TextInput::make('rate')
                            ->label(__('admin.commission_fee.rate_label'))
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%'),
                        Textarea::make('description')
                            ->label(__('admin.commission_fee.description_label'))
                            ->rows(3),
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
                ->label(__('admin.commission_fee.update_button'))
                ->submit('save'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $commission = CommissionFee::current();

        $commission->update([
            'rate' => $data['rate'],
            'description' => $data['description'],
        ]);

        $commission->recordHistory();

        $vendors = User::where('user_type_id', UserType::VENDOR_ID)->get();

        if ($vendors->isNotEmpty()) {
            $body = __('admin.commission_fee.notification_updated_body', ['rate' => $commission->rate]);

            if ($commission->description) {
                $body .= "\n".$commission->description;
            }

            Notification::make()
                ->title(__('admin.commission_fee.notification_updated_title'))
                ->body($body)
                ->icon('heroicon-o-receipt-percent')
                ->success()
                ->sendToDatabase($vendors)
                ->broadcast($vendors);
        }

        Notification::make()
            ->title(__('admin.commission_fee.update_success'))
            ->success()
            ->send();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                CommissionFeeHistory::query()
                    ->where('commission_fee_id', CommissionFee::ID)
                    ->latest()
            )
            ->columns([
                TextColumn::make('rate')
                    ->label(__('admin.commission_fee.rate_label'))
                    ->suffix('%')
                    ->sortable(),
                TextColumn::make('description')
                    ->limit(50)
                    ->wrap(),
                TextColumn::make('created_at')
                    ->label(__('admin.commission_fee.updated_at_column'))
                    ->dateTime('h:i A, d M Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    #[Override]
    public function getTitle(): string
    {
        return __('admin.commission_fee.page_title');
    }
}
