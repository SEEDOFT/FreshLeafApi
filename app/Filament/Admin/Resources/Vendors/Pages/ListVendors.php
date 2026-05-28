<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Vendors\Pages;

use App\Filament\Admin\Resources\Vendors\VendorResource;
use App\Models\UserStatus;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Override;

class ListVendors extends ListRecords
{
    #[Override]
    protected static string $resource = VendorResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    #[Override]
    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('admin.resources.vendor.tab_all')),
            'pending' => Tab::make(__('admin.resources.vendor.tab_pending'))
                ->modifyQueryUsing(
                    static fn (Builder $query) => $query->where('user_status_id', UserStatus::PENDING_ID)
                )
                ->icon('heroicon-m-clock')
                ->badge(function (): int {
                    $query = $this->getTableQuery();

                    return $query
                        ? (clone $query)->where('user_status_id', UserStatus::PENDING_ID)->count()
                        : 0;
                }),
            'active' => Tab::make(__('admin.resources.vendor.tab_active'))
                ->modifyQueryUsing(
                    static fn (Builder $query) => $query->where('user_status_id', UserStatus::ACTIVE_ID)
                )
                ->icon('heroicon-m-check-circle'),
            'inactive' => Tab::make(__('admin.resources.vendor.tab_inactive'))
                ->modifyQueryUsing(
                    static fn (Builder $query) => $query->where('user_status_id', UserStatus::INACTIVE_ID)
                )
                ->icon('heroicon-m-x-circle'),
        ];
    }
}
