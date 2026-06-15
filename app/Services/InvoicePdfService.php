<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\Mpdf;

class InvoicePdfService
{
    /**
     * Generate PDF
     */
    public static function generate(Order $order): string
    {
        $defaultConfig = (new ConfigVariables)->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new FontVariables)->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $mpdf = new Mpdf([
            'fontDir' => array_merge($fontDirs, [
                storage_path('fonts'),
            ]),
            'fontdata' => $fontData + [
                'battambang' => [
                    'R' => 'Battambang-Regular.ttf',
                    'B' => 'Battambang-Bold.ttf',
                    'useOTL' => 0xFF,
                    'useKashida' => 75,
                ],
            ],
            'default_font' => 'battambang',
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
        ]);

        $order->load([
            'user',
            'vendor.vendorProfile',
            'address',
            'status',
            'type',
            'currency',
            'payment.paymentMethod',
            'exchangeRateHistory',
            'items.vendorInventory.product.productCategory',
            'items.vendorInventory.unit',
            'items.vendorInventory.packagingType',
            'items.vendorInventory.activeDiscount',
        ]);

        $html = view('pdf.invoice', ['order' => $order])->render();
        $mpdf->WriteHTML($html);

        return $mpdf->Output('', 'S');
    }
}
