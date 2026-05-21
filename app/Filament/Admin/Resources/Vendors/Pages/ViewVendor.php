<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Vendors\Pages;

use App\Filament\Admin\Resources\Vendors\VendorResource;
use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Override;

class ViewVendor extends ViewRecord
{
    #[Override]
    protected static string $resource = VendorResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            Action::make('approveVendor')
                ->label(new HtmlString('<strong>'.__('admin.resources.vendor.approve').'</strong>'))
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(
                    fn (User $record) => $record->vendorProfile &&
                    $record->user_type_id === UserType::VENDOR_ID &&
                    $record->user_status_id === UserStatus::PENDING_ID &&
                    ! $record->vendorProfile->is_verified
                )
                ->action(static function (User $record, array $data) {
                    $record->vendorProfile->update([
                        'is_verified' => true,
                        'approved_at' => now(),
                        'approved_by_admin_id' => Auth::id(),
                        'approve_reason' => $data['note'] ?? null,
                    ]);
                    $record->update([
                        'user_type_id' => UserType::VENDOR_ID,
                        'user_status_id' => UserStatus::ACTIVE_ID,
                    ]);

                    Notification::make()
                        ->title(__('admin.resources.vendor.notifications.approved'))
                        ->success()
                        ->send();
                })
                ->form([
                    Textarea::make('note')
                        ->label(new HtmlString('<strong>'.__('admin.resources.vendor.approval_note').'</strong>')),
                ])
                ->requiresConfirmation(),

            Action::make('rejectVendor')
                ->label(new HtmlString('<strong>'.__('admin.resources.vendor.reject').'</strong>'))
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(
                    fn (User $record) => $record->vendorProfile &&
                    $record->user_type_id === UserType::VENDOR_ID &&
                    $record->user_status_id === UserStatus::PENDING_ID &&
                    ! $record->vendorProfile->is_verified
                )
                ->action(static function (User $record, array $data) {
                    $record->vendorProfile->update([
                        'is_verified' => false,
                        'rejected_at' => now(),
                        'rejected_by_admin_id' => Auth::id(),
                        'reject_reason' => $data['reason'],
                    ]);

                    $record->update([
                        'user_type_id' => UserType::VENDOR_ID,
                        'user_status_id' => UserStatus::REJECTED_ID,
                    ]);

                    Notification::make()
                        ->title(__('admin.resources.vendor.notifications.rejected'))
                        ->danger()
                        ->send();
                })
                ->form([
                    Textarea::make('reason')
                        ->label(new HtmlString('<strong>'.__('admin.resources.vendor.rejection_reason').'</strong>'))
                        ->required(fn (string $operation): bool => $operation === 'create')
                        ->dehydrated(fn (mixed $state): bool => filled($state)),
                ])
                ->requiresConfirmation(),
        ];
    }
}
