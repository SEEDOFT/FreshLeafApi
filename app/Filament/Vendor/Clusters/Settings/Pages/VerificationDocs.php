<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Clusters\Settings\Pages;

use App\Filament\Vendor\Clusters\Settings;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Override;

class VerificationDocs extends Page
{
    #[Override]
    protected static ?string $cluster = Settings::class;

    #[Override]
    protected static ?string $slug = 'verification';

    #[Override]
    protected static ?string $navigationLabel = 'Verification';

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
            throw new AuthenticationException;
        }

        $this->data = $user->vendorProfile->toArray();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('vendor.settings.verification_docs.section_title'))
                    ->description(__('vendor.settings.verification_docs.section_desc'))
                    ->schema([
                        FileUpload::make('id_card_front')
                            ->label(new HtmlString('<strong>'.__('vendor.settings.verification_docs.id_front').'</strong>'))
                            ->image()
                            ->disk('local')
                            ->directory('vendor-verification')
                            ->disabled(static fn (User $record): bool => (bool) $record->vendorProfile->is_verified),
                        FileUpload::make('id_card_back')
                            ->label(new HtmlString('<strong>'.__('vendor.settings.verification_docs.id_back').'</strong>'))
                            ->image()
                            ->disk('local')
                            ->directory('vendor-verification')
                            ->disabled(static fn (User $record): bool => (bool) $record->vendorProfile->is_verified),
                        FileUpload::make('store_front_image')
                            ->label(new HtmlString('<strong>'.__('vendor.settings.verification_docs.store_photo').'</strong>'))
                            ->image()
                            ->disk('local')
                            ->directory('vendor-verification')
                            ->disabled(static fn (User $record): bool => (bool) $record->vendorProfile->is_verified),
                        FileUpload::make('organic_certificate_url')
                            ->label(new HtmlString('<strong>'.__('vendor.settings.verification_docs.organic_cert').'</strong>'))
                            ->disk('local')
                            ->directory('vendor-verification')
                            ->disabled(static fn (User $record): bool => (bool) $record->vendorProfile->is_verified),
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

        if ($user->vendorProfile->is_verified) {
            Notification::make()
                ->title(__('vendor.settings.verification_docs.lock_title'))
                ->body(__('vendor.settings.verification_docs.lock_body'))
                ->danger()
                ->send();

            return;
        }

        $form = $this->getSchema('form');

        if (! $form) {
            return;
        }

        $state = $form->getState();
        $user->vendorProfile()->update($state);

        Notification::make()
            ->title(__('vendor.settings.verification_docs.success_notification'))
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
                ->label(new HtmlString('<strong>'.__('shared.profile.save_changes').'</strong>'))
                ->submit('save')
                ->keyBindings(['mod+s']),
        ];
    }
}
