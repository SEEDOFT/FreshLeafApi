<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\Orders\Pages;

use App\Filament\Vendor\Resources\Orders\Actions\OrderActions;
use App\Filament\Vendor\Resources\Orders\OrderResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Override;

class ViewOrder extends ViewRecord
{
    #[Override]
    protected static string $resource = OrderResource::class;

    /**
     * {@inheritDoc}
     *
     * @return array<Action>
     */
    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            OrderActions::accept('page'),
            OrderActions::prepare('page'),
            OrderActions::outForDelivery('page'),
            OrderActions::deliver('page'),
            OrderActions::cancel('page'),
        ];
    }
}
