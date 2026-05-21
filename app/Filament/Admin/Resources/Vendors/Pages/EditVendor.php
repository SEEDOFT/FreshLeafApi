<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Vendors\Pages;

use App\Filament\Admin\Resources\Vendors\VendorResource;
use App\Models\User;
use App\Models\UserStatus;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Carbon;
use Override;

class EditVendor extends EditRecord
{
    #[Override]
    protected static string $resource = VendorResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->requiresConfirmation()
                ->visible(fn (User $record): bool => $record->user_status_id !== UserStatus::DELETED_ID)
                ->action(function (User $record) {
                    $record->update([
                        'user_status_id' => UserStatus::DELETED_ID,
                        'deleted_at' => Carbon::now(),
                    ]);
                }),
        ];
    }
}
