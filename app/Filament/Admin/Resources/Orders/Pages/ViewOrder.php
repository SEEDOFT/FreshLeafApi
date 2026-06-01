<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Orders\Pages;

use App\Filament\Admin\Resources\Orders\OrderResource;
use App\Models\Order;
use App\Services\InvoicePdfService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\URL;
use Override;

class ViewOrder extends ViewRecord
{
    #[Override]
    protected static string $resource = OrderResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            Action::make('inspect_invoice')
                ->label(__('admin.resources.order.inspect_invoice'))
                ->icon('heroicon-o-eye')
                ->color('info')
                ->url(fn (Order $record) => URL::temporarySignedRoute(
                    'v1.orders.invoice.download',
                    now()->addMinutes(30),
                    ['id' => $record->id, 'inline' => 1]
                ))
                ->openUrlInNewTab(),
            Action::make('download_invoice')
                ->label(__('admin.resources.order.download_invoice'))
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->action(function (Order $record) {
                    $pdfContent = InvoicePdfService::generate($record);

                    return response()->streamDownload(
                        fn () => print $pdfContent,
                        "invoice-{$record->order_number}.pdf"
                    );
                }),
            EditAction::make(),
        ];
    }
}
