<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Clusters\Settings\Pages;

use App\Constants\StorageDirectory;
use App\Filament\Vendor\Clusters\Settings;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Override;

use function is_string;
use function ltrim;
use function str_starts_with;

class VerificationDocs extends Page
{
    #[Override]
    protected static ?string $cluster = Settings::class;

    #[Override]
    protected static ?string $slug = 'verification';

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('vendor.settings.verification_docs.label');
    }

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

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

        $profile = $user->vendorProfile;

        if (! $profile) {
            return;
        }

        $profileData = $profile->toArray();

        $imageFields = [
            'id_card_front',
            'id_card_back',
            'store_front_image',
            'organic_certificate_url',
        ];

        foreach ($imageFields as $field) {
            if (isset($profileData[$field]) && is_string($profileData[$field])) {
                $profileData[$field] = $this->getFileUploadState($profileData[$field]);
            }
        }

        $this->data = $profileData;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('vendor.settings.verification_docs.section_title'))
                    ->description(__('vendor.settings.verification_docs.section_desc'))
                    ->schema([
                        FileUpload::make('id_card_front')
                            ->label(__('vendor.settings.verification_docs.id_front'))
                            ->image()
                            ->imageEditor()
                            ->maxSize(6144)
                            ->disk('local')
                            ->directory(StorageDirectory::VENDOR_VERIFICATION)
                            ->disabled(),
                        FileUpload::make('id_card_back')
                            ->label(__('vendor.settings.verification_docs.id_back'))
                            ->image()
                            ->imageEditor()
                            ->maxSize(6144)
                            ->disk('local')
                            ->directory(StorageDirectory::VENDOR_VERIFICATION)
                            ->disabled(),
                        FileUpload::make('store_front_image')
                            ->label(__('vendor.settings.verification_docs.store_photo'))
                            ->image()
                            ->imageEditor()
                            ->maxSize(6144)
                            ->disk('local')
                            ->directory(StorageDirectory::VENDOR_VERIFICATION)
                            ->disabled(),
                        FileUpload::make('organic_certificate_url')
                            ->label(__('vendor.settings.verification_docs.organic_cert'))
                            ->maxSize(6144)
                            ->disk('local')
                            ->directory(StorageDirectory::VENDOR_VERIFICATION)
                            ->disabled(),
                    ])->columns(2),
            ])
            ->statePath('data');
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
