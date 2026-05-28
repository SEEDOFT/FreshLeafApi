<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\Orders\Pages;

use App\Filament\Vendor\Resources\Orders\OrderResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Override;

class ListOrders extends ListRecords
{
    #[Override]
    protected static string $resource = OrderResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            // /
        ];
    }

    /**
     * @return array<string, string>
     */
    public function getListeners(): array
    {
        $vendorId = Auth::id();

        return [
            "echo-private:vendor.orders.{$vendorId},VendorOrderUpdated" => '$refresh',
        ];
    }
}
