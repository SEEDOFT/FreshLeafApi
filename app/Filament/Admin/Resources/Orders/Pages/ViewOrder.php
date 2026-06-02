<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Orders\Pages;

use App\Filament\Admin\Resources\Orders\OrderResource;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\PaymentMethodType;
use App\Models\PaymentStatus;
use App\Services\InvoicePdfService;
use App\Services\VendorPayoutService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
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
            Action::make('release_vendor_funds')
                ->label('Release Vendor Funds')
                ->icon('heroicon-o-currency-dollar')
                ->color('success')
                ->requiresConfirmation()
                ->visible(function (Order $record) {
                    if ($record->is_vendor_paid) {
                        return false;
                    }
                    if ($record->order_status_id !== OrderStatus::DELIVERED_ID) {
                        return false;
                    }
                    if ($record->payment_status_id !== PaymentStatus::COMPLETED_ID) {
                        return false;
                    }

                    $isCod = $record->payments()
                        ->whereHas(
                            'paymentMethod',
                            fn ($q) => $q->where('payment_method_type_id', PaymentMethodType::COD_ID)
                        )->exists();

                    return ! $isCod;
                })
                ->action(function (Order $record, VendorPayoutService $payoutService) {
                    if ($payoutService->payoutOrder($record)) {
                        Notification::make()
                            ->title('Funds released to vendor successfully.')
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Failed to release funds.')
                            ->danger()
                            ->send();
                    }
                }),
            EditAction::make(),
        ];
    }
}
