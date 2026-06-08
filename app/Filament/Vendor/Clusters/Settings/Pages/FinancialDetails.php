<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Clusters\Settings\Pages;

use App\Constants\StorageDirectory;
use App\Filament\Vendor\Clusters\Settings;
use App\Models\PaymentMethod;
use App\Models\PaymentMethodType;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Override;

use function is_array;
use function is_string;
use function ltrim;
use function reset;
use function str_contains;
use function str_starts_with;

class FinancialDetails extends Page
{
    #[Override]
    protected static ?string $cluster = Settings::class;

    #[Override]
    protected static ?string $slug = 'financials';

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('vendor.settings.financial_details.label');
    }

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-currency-dollar';

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

        /** @var PaymentMethod|null $financial */
        $financial = $user->vendorFinancialDetails;
        $financialData = $financial ? $financial->toArray() : [];

        if (isset($financialData['qr_code']) && is_string($financialData['qr_code'])) {
            $financialData['qr_code'] = $this->getFileUploadState($financialData['qr_code']);
        }

        if (isset($financialData['bank_name']) && is_string($financialData['bank_name'])) {
            $financialData['bank_name'] = match (true) {
                str_contains($financialData['bank_name'], 'ABA') => PaymentMethodType::ABA_ID,
                str_contains($financialData['bank_name'], 'ACLEDA') => PaymentMethodType::ACLEDA_ID,
                str_contains($financialData['bank_name'], 'WING') => PaymentMethodType::WING_ID,
                default => $financialData['bank_name'],
            };
        }

        if (isset($financialData['payment_method_type_id'])) {
            $financialData['bank_name'] ??= $financialData['payment_method_type_id'];
        }

        $this->data = $financialData;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('vendor.settings.financial_details.label'))
                    ->description(__('vendor.settings.financial_details.description'))
                    ->schema([
                        Grid::make(2)
                            ->columnSpan(1)
                            ->schema([
                                Select::make('bank_name')
                                    ->label(__('vendor.settings.financial_details.bank_name'))
                                    ->placeholder(__('vendor.settings.financial_details.select_bank'))
                                    ->options([
                                        PaymentMethodType::ABA_ID => PaymentMethodType::ABA,
                                        PaymentMethodType::ACLEDA_ID => PaymentMethodType::ACLEDA,
                                        PaymentMethodType::WING_ID => PaymentMethodType::WING,
                                    ])
                                    ->dehydrated(),
                                TextInput::make('account_name')
                                    ->label(__('vendor.settings.financial_details.account_holder'))
                                    ->dehydrated()
                                    ->maxLength(255),
                                TextInput::make('account_number')
                                    ->label(__('vendor.settings.financial_details.account_number'))
                                    ->dehydrated()
                                    ->columnSpanFull()
                                    ->maxLength(255),
                            ]),
                        Grid::make(1)
                            ->columnSpan(1)
                            ->schema([
                                FileUpload::make('qr_code')
                                    ->label(__('vendor.settings.financial_details.qr_code'))
                                    ->image()
                                    ->maxSize(6144)
                                    ->imagePreviewHeight('400px')
                                    ->disk('local')
                                    ->directory(StorageDirectory::VENDOR_VERIFICATION)
                                    ->columnSpanFull(),
                            ]),
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

        if (isset($state['bank_name'])) {
            $bankId = (int) $state['bank_name'];
            $state['bank_name'] = match ($bankId) {
                PaymentMethodType::ABA_ID => PaymentMethodType::ABA,
                PaymentMethodType::ACLEDA_ID => PaymentMethodType::ACLEDA,
                PaymentMethodType::WING_ID => PaymentMethodType::WING,
                default => null,
            };
            $state['payment_method_type_id'] = $bankId;
        } else {
            unset($state['bank_name'], $state['payment_method_type_id']);
        }

        if (isset($state['qr_code'])) {
            $path = is_array($state['qr_code'])
                ? reset($state['qr_code'])
                : $state['qr_code'];

            if (is_string($path) && $path !== '') {
                // Store the full relative path (e.g. vendor_verifications/ULID.jpg)
                $state['qr_code'] = $path;
            } else {
                // FileUpload returned empty — preserve the existing DB value
                unset($state['qr_code']);
            }
        } else {
            unset($state['qr_code']);
        }

        $user->vendorFinancialDetails()
            ->updateOrCreate(
                ['user_id' => $user->id],
                $state,
            );

        Notification::make()
            ->title(__('vendor.settings.financial_details.success_notification'))
            ->success()
            ->send();
    }

    /**
     * @return array<Action>
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

    /**
     * Resolve a stored filename into the full path the FileUpload
     * component expects for displaying existing files.
     *
     * Handles both legacy basename-only values (e.g. "image.jpg")
     * and full relative paths (e.g. "vendor_verifications/ULID.jpg").
     *
     * @return array<string>
     */
    private function getFileUploadState(string $path): array
    {
        $path = ltrim($path, '/');

        $fullPath = str_starts_with($path, StorageDirectory::VENDOR_VERIFICATION)
            ? $path : StorageDirectory::VENDOR_VERIFICATION.'/'.$path;

        // Only return the path if the file actually exists on disk,
        // otherwise return an empty array so FileUpload shows no preview.
        if (! Storage::disk('local')->exists($fullPath)) {
            return [];
        }

        return [$fullPath];
    }
}
