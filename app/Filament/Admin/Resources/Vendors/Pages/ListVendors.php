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
            'all' => Tab::make('All'),
            'pending' => Tab::make('Pending Approval')
                ->modifyQueryUsing(static fn (Builder $query) => $query->where('user_status_id', UserStatus::PENDING))
                ->icon('heroicon-m-clock')
                ->badge(function (): int {
                    $query = $this->getTableQuery();

                    return $query
                        ? (clone $query)->where('user_status_id', UserStatus::PENDING)->count()
                        : 0;
                }),
            'active' => Tab::make('Active')
                ->modifyQueryUsing(static fn (Builder $query) => $query->where('user_status_id', UserStatus::ACTIVE))
                ->icon('heroicon-m-check-circle'),
            'inactive' => Tab::make('Inactive')
                ->modifyQueryUsing(static fn (Builder $query) => $query->where('user_status_id', UserStatus::INACTIVE))
                ->icon('heroicon-m-x-circle'),
        ];
    }
}
