<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Invoice / វិក្កយបត្រ - {{ $order->order_number }}</title>
    <style>
        body {
            font-family: 'battambang', sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.4;
        }
        .header {
            width: 100%;
            margin-bottom: 25px;
            border-bottom: 2px solid #22c55e;
            padding-bottom: 15px;
        }
        .header td {
            vertical-align: top;
        }
        .title {
            font-size: 26px;
            font-weight: bold;
            color: #1a1a1a;
            margin-bottom: 5px;
        }
        .subtitle {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
        }
        .company-info {
            text-align: right;
        }
        .logo {
            max-width: 140px;
            max-height: 60px;
            margin-bottom: 8px;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #22c55e;
        }
        .details-container {
            width: 100%;
            margin-bottom: 20px;
        }
        .details-container td {
            vertical-align: top;
            width: 50%;
            padding: 0 10px 0 0;
        }
        .section-title {
            font-weight: bold;
            font-size: 13px;
            color: #1a1a1a;
            margin-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 4px;
            text-transform: uppercase;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .items-table th {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            padding: 10px 8px;
            text-align: left;
            font-weight: bold;
            color: #374151;
        }
        .items-table td {
            border: 1px solid #e5e7eb;
            padding: 10px 8px;
            vertical-align: top;
        }
        .totals-table {
            width: 45%;
            float: right;
            border-collapse: collapse;
        }
        .totals-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #f3f4f6;
        }
        .totals-table .total-row {
            font-weight: bold;
            font-size: 15px;
            border-bottom: none;
            border-top: 2px solid #22c55e;
            color: #111;
        }
        .footer {
            margin-top: 60px;
            text-align: center;
            color: #6b7280;
            font-size: 10px;
            border-top: 1px solid #e5e7eb;
            padding-top: 15px;
        }
        .text-right {
            text-align: right;
        }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            background: #f3f4f6;
            color: #374151;
        }
        .notes-section {
            margin-top: 30px;
            padding: 12px;
            background: #f9fafb;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }
        .notes-title {
            font-weight: bold;
            margin-bottom: 5px;
            color: #374151;
        }
        .signature-grid {
            margin-top: 50px;
            width: 100%;
        }
        .signature-grid td {
            width: 33%;
            text-align: center;
            vertical-align: bottom;
        }
        .sig-line {
            border-top: 1px solid #333;
            margin-top: 50px;
            padding-top: 5px;
            width: 80%;
            margin-left: auto;
            margin-right: auto;
        }
    </style>
</head>
<body>

    <table class="header">
        <tr>
            <td>
                <div class="title">INVOICE / វិក្កយបត្រ</div>
                <div class="subtitle">Order Number: <strong>#{{ $order->order_number }}</strong></div>
                <div style="margin-bottom: 4px;">Order Date / កាលបរិច្ឆេទ: <strong>{{ $order->place_order_date?->format('d M Y, h:i A') ?? $order->created_at->format('d M Y, h:i A') }}</strong></div>
                <div>Status / ស្ថានភាព: <span class="badge">{{ $order->status->name }}</span></div>
            </td>
            <td class="company-info">
                @if(file_exists(public_path('images/logo.png')))
                    <img src="{{ public_path('images/logo.png') }}" class="logo" alt="FreshLeaf Organics">
                @endif
                <div class="company-name">FreshLeaf Organics</div>
                <div style="font-size: 10px; color: #666;">
                    Phnom Penh, Cambodia<br>
                    support@freshleaf.com | www.freshleaf.com
                </div>
            </td>
        </tr>
    </table>

    <table class="details-container">
        <tr>
            <td>
                <div class="section-title">Billing & Shipping / អាសយដ្ឋានដឹកជញ្ជូន</div>
                <strong>{{ $order->user->fullName }}</strong><br>
                Phone: {{ $order->address->phone }}<br>
                {{ $order->address->address_line_1 }}<br>
                @if($order->address->address_line_2)
                    {{ $order->address->address_line_2 }}<br>
                @endif
                {{ $order->address->city }}, {{ $order->address->province }}
            </td>
            <td>
                <div class="section-title">Vendor Information / ព័ត៌មានអ្នកលក់</div>
                <strong>{{ $order->vendor?->vendorProfile?->business_name ?? $order->vendor?->fullName }}</strong><br>
                Phone: {{ $order->vendor?->vendorProfile?->contact_phone ?? 'N/A' }}<br>
                @if($order->vendor?->vendorProfile?->address)
                    Location: {{ $order->vendor->vendorProfile->address }}<br>
                @endif
                <div style="margin-top: 8px;">
                    Type: {{ $order->type->name }}<br>
                    Payment Method: <strong>{{ $order->payment?->paymentMethod?->name ?? 'Wallet / Transfer' }}</strong>
                </div>
            </td>
        </tr>
    </table>

    <table class="details-container">
        <tr>
            <td colspan="2" style="background: #ecfdf5; padding: 10px; border-radius: 6px; border: 1px solid #d1fae5;">
                <div style="font-weight: bold; color: #065f46;">Delivery Schedule / កាលវិភាគដឹកជញ្ជូន</div>
                <div style="font-size: 12px; margin-top: 4px;">
                    Date: <strong>{{ $order->delivery_date?->format('d M Y') ?? 'N/A' }}</strong> &nbsp; | &nbsp; 
                    Time Slot: <strong>{{ $order->delivery_slot ?? 'Standard' }}</strong>
                </div>
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 40%;">Item / មុខទំនិញ</th>
                <th>Unit & Packaging / ឯកតានិងការវេចខ្ចប់</th>
                <th class="text-right">Price / តម្លៃ</th>
                <th class="text-right">Qty / ចំនួន</th>
                <th class="text-right">Total / សរុប</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            @php
                $product = $item->vendorInventory?->product;
                $unit = $item->vendorInventory?->unit?->translated_name ?? $item->unit_snapshot;
                $packaging = $item->vendorInventory?->packagingType?->translated_name ?? 'Standard';
                $discount = $item->vendorInventory?->activeDiscount;
            @endphp
            <tr>
                <td>
                    <div style="font-weight: bold; font-size: 12px;">{{ $product?->name_en ?? $item->product_name_snapshot }}</div>
                    <div style="color: #4b5563;">{{ $product?->name_km }}</div>
                    @if($discount)
                        <div style="font-size: 9px; color: #059669; margin-top: 2px;">
                            Discount: {{ (float)$discount->discount_percentage }}% Off
                        </div>
                    @endif
                </td>
                <td>
                    <div style="font-weight: bold;">{{ $unit }}</div>
                    <div style="font-size: 9px; color: #666;">Pkg: {{ $packaging }}</div>
                </td>
                <td class="text-right">
                    @if($discount)
                        <div style="text-decoration: line-through; color: #9ca3af; font-size: 9px;">
                            {{ \App\Models\Order::formatMoney($item->vendorInventory->price, $order->currency) }}
                        </div>
                    @endif
                    {{ \App\Models\Order::formatMoney($item->unit_price_snapshot, $order->currency) }}
                </td>
                <td class="text-right">{{ $item->quantity }}</td>
                <td class="text-right">{{ \App\Models\Order::formatMoney($item->subtotal, $order->currency) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals-table">
        <tr>
            <td>Subtotal / សរុបរង:</td>
            <td class="text-right">{{ \App\Models\Order::formatMoney($order->subtotal, $order->currency) }}</td>
        </tr>
        @if($order->discount_amount > 0)
        <tr>
            <td style="color: #059669;">Discount / បញ្ចុះតម្លៃ:</td>
            <td class="text-right" style="color: #059669;">-{{ \App\Models\Order::formatMoney($order->discount_amount, $order->currency) }}</td>
        </tr>
        @endif
        @if($order->delivery_fee > 0)
        <tr>
            <td>Delivery Fee / ថ្លៃដឹកជញ្ជូន:</td>
            <td class="text-right">{{ \App\Models\Order::formatMoney($order->delivery_fee, $order->currency) }}</td>
        </tr>
        @endif
        @if($order->tax_amount > 0)
        <tr>
            <td>Tax / ពន្ធ:</td>
            <td class="text-right">{{ \App\Models\Order::formatMoney($order->tax_amount, $order->currency) }}</td>
        </tr>
        @endif
        <tr class="total-row">
            <td>Total Payable / សរុប:</td>
            <td class="text-right">{{ \App\Models\Order::formatMoney($order->total_amount, $order->currency) }}</td>
        </tr>

        @if($order->currency && $order->currency->id !== \App\Models\Currency::USD_ID)
        <tr>
            <td style="font-size: 10px; color: #6b7280; border-top: 1px dashed #e5e7eb;">
                Conversion Rate:<br>
                Paid in {{ $order->currency->name }}
            </td>
            <td class="text-right" style="font-size: 12px; font-weight: bold; border-top: 1px dashed #e5e7eb;">
                $1 = {{ number_format($order->exchangeRateHistory?->rate ?? 4000) }} {{ $order->currency->symbol }}<br>
                {{ number_format($order->payment?->amount ?? 0, 0) }} {{ $order->currency->symbol }}
            </td>
        </tr>
        @endif
    </table>

    <div style="clear: both;"></div>

    @if($order->notes)
    <div class="notes-section">
        <div class="notes-title">Order Notes / សម្គាល់:</div>
        <div style="color: #4b5563;">{{ $order->notes }}</div>
    </div>
    @endif

    <table class="signature-grid">
        <tr>
            <td>
                <div class="sig-line">Vendor Signature</div>
                <div style="font-size: 9px; color: #999;">Authorized Stamp</div>
            </td>
            <td></td>
            <td>
                <div class="sig-line">Customer Signature</div>
                <div style="font-size: 9px; color: #999;">Received with Thanks</div>
            </td>
        </tr>
    </table>

    <div class="footer">
        <div style="font-weight: bold; font-size: 11px; margin-bottom: 5px; color: #22c55e;">Thank you for supporting local farmers!</div>
        សូមអរគុណសម្រាប់ការគាំទ្រកសិករក្នុងស្រុក! / FreshLeaf Organics Marketplace<br>
        For inquiries, please contact +855 12 345 678 or support@freshleaf.com
    </div>

</body>
</html>
