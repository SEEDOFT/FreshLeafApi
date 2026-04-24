<?php

declare(strict_types=1);

namespace App\Filament\Resources\Vendors\Pages;

use App\Filament\Resources\Vendors\VendorResource;
use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;
use Override;

class ViewVendor extends ViewRecord
{
    protected static string $resource = VendorResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            Action::make('approveVendor')
                ->label('Approve')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn (User $record) => $record->isType(UserType::VENDOR) && $record->vendorProfile && ! $record->vendorProfile->is_verified)
                ->action(function (User $record, array $data) {
                    $record->vendorProfile->update([
                        'is_verified' => true,
                        'approved_at' => now(),
                        'approved_by_admin_id' => Auth::id(),
                        'approve_reason' => $data['note'] ?? null,
                    ]);
                    $record->update(['user_status_id' => UserStatus::ACTIVE]);

                    Notification::make()
                        ->title('Vendor approved successfully.')
                        ->success()
                        ->send();
                })
                ->form([
                    Textarea::make('note')
                        ->label('Approval Note (Optional)'),
                ])
                ->requiresConfirmation(),

            Action::make('rejectVendor')
                ->label('Reject')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn (User $record) => $record->isType(UserType::VENDOR) && $record->vendorProfile && ! $record->vendorProfile->is_verified)
                ->action(function (User $record, array $data) {
                    $record->vendorProfile->update([
                        'is_verified' => false,
                        'rejected_at' => now(),
                        'rejected_by_admin_id' => Auth::id(),
                        'reject_reason' => $data['reason'],
                    ]);

                    Notification::make()
                        ->title('Vendor rejected.')
                        ->danger()
                        ->send();
                })
                ->form([
                    Textarea::make('reason')
                        ->label('Rejection Reason')
                        ->required(),
                ])
                ->requiresConfirmation(),
        ];
    }
}
