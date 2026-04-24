<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Clusters\Settings\Pages;

use App\Filament\Vendor\Clusters\Settings;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class VerificationDocs extends Page
{
    protected static ?string $cluster = Settings::class;

    protected static ?string $slug = 'verification';

    protected static ?string $navigationLabel = 'Verification';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    protected string $view = 'filament.vendor.pages.verification-docs';

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
                Section::make('Identity & Store Verification')
                    ->description('Upload documents to verify your identity and organic status. These cannot be changed once verified.')
                    ->schema([
                        FileUpload::make('id_card_front')
                            ->label('ID Card (Front)')
                            ->image()
                            ->directory('vendor-verification')
                            ->disabled(fn ($record) => Auth::user()->vendorProfile?->is_verified),
                        FileUpload::make('id_card_back')
                            ->label('ID Card (Back)')
                            ->image()
                            ->directory('vendor-verification')
                            ->disabled(fn ($record) => Auth::user()->vendorProfile?->is_verified),
                        FileUpload::make('store_front_image')
                            ->label('Farm / Store Photo')
                            ->image()
                            ->directory('vendor-verification')
                            ->disabled(fn ($record) => Auth::user()->vendorProfile?->is_verified),
                        FileUpload::make('organic_certificate_url')
                            ->label('Organic Certificate (Optional)')
                            ->directory('vendor-verification')
                            ->disabled(fn ($record) => Auth::user()->vendorProfile?->is_verified),
                    ])->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $user = Auth::user();

        if ($user->vendorProfile?->is_verified) {
            Notification::make()
                ->title('Verification Lock')
                ->body('You cannot change verification documents once verified.')
                ->danger()
                ->send();

            return;
        }

        $state = $this->getSchema('form')->getState();
        $user->vendorProfile()->update($state);

        Notification::make()
            ->title('Verification documents updated.')
            ->success()
            ->send();
    }
}
