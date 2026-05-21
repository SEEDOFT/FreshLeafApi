<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Clusters\Settings\Pages;

use App\Constants\StorageDirectory;
use App\Filament\Vendor\Clusters\Settings;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Override;

use function basename;
use function is_array;
use function is_string;
use function ltrim;
use function reset;
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

        $financial = $user->vendorFinancialDetails;
        $financialData = $financial ? $financial->toArray() : [];

        if (isset($financialData['qr_code']) && is_string($financialData['qr_code'])) {
            $financialData['qr_code'] = $this->getFileUploadState($financialData['qr_code']);
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
                                TextInput::make('bank_name')
                                    ->label(new HtmlString('<strong>'.__('vendor.settings.financial_details.bank_name').'</strong>'))
                                    ->dehydrated(),
                                TextInput::make('account_name')
                                    ->label(new HtmlString('<strong>'.__('vendor.settings.financial_details.account_holder').'</strong>'))
                                    ->dehydrated()
                                    ->maxLength(255),
                                TextInput::make('account_number')
                                    ->label(new HtmlString('<strong>'.__('vendor.settings.financial_details.account_number').'</strong>'))
                                    ->dehydrated()
                                    ->columnSpanFull()
                                    ->maxLength(255),
                            ]),
                        Grid::make(1)
                            ->columnSpan(1)
                            ->schema([
                                FileUpload::make('qr_code')
                                    ->label(new HtmlString('<strong>'.__('vendor.settings.financial_details.qr_code').'</strong>'))
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

        if (isset($state['qr_code']) && is_array($state['qr_code'])) {
            $value = reset($state['qr_code']);
            $state['qr_code'] = is_string($value) ? basename($value) : null;
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
                ->label(new HtmlString('<strong>'.__('shared.profile.save_changes').'</strong>'))
                ->submit('save')
                ->keyBindings(['mod+s']),
        ];
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

        return [
            str_starts_with($path, StorageDirectory::VENDOR_VERIFICATION)
                ? $path : StorageDirectory::VENDOR_VERIFICATION.'/'.$path,
        ];
    }
}
