<?php

declare(strict_types=1);

namespace App\Filament\Resources\Vendors\Pages;

use App\Filament\Resources\Vendors\VendorResource;
use App\Models\UserStatus;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Override;

class ListVendors extends ListRecords
{
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
                ->modifyQueryUsing(fn (Builder $query) => $query->where('user_status_id', UserStatus::PENDING))
                ->icon('heroicon-m-clock')
                ->badge(fn () => (clone $this->getTableQuery())->where('user_status_id', UserStatus::PENDING)->count()),
            'active' => Tab::make('Active')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('user_status_id', UserStatus::ACTIVE))
                ->icon('heroicon-m-check-circle'),
            'inactive' => Tab::make('Inactive')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('user_status_id', UserStatus::INACTIVE))
                ->icon('heroicon-m-x-circle'),
        ];
    }
}
