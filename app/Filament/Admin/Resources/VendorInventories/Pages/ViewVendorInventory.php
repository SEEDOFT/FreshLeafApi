<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\VendorInventories\Pages;

use App\Filament\Admin\Resources\VendorInventories\VendorInventoryResource;
use App\Models\VendorInventory;
use App\Models\VendorInventoryStatus;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Override;

class ViewVendorInventory extends ViewRecord
{
    #[Override]
    protected static string $resource = VendorInventoryResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            Action::make('approve')
                ->label(__('admin.resources.vendor_inventory.approve'))
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn (VendorInventory $record): bool => (int) $record->inventory_status_id === VendorInventoryStatus::PENDING_REVIEW_ID)
                ->action(function (VendorInventory $record): void {
                    $record->update([
                        'inventory_status_id' => VendorInventoryStatus::AVAILABLE_ID,
                    ]);

                    Notification::make()
                        ->title('Inventory Approved')
                        ->body('Your inventory for '.$record->product->name_en.' has been approved.')
                        ->success()
                        ->sendToDatabase($record->vendor)
                        ->broadcast($record->vendor);
                })
                ->requiresConfirmation()
                ->modalHeading(__('admin.resources.vendor_inventory.approve_heading'))
                ->modalDescription(__('admin.resources.vendor_inventory.approve_description'))
                ->modalSubmitActionLabel(__('admin.resources.vendor_inventory.approve_submit')),
        ];
    }
}
