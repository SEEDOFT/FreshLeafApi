<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Invoice / វិក្កយបត្រ - {{ $order->order_number }}</title>
    <style>
        {!! file_get_contents(resource_path('css/pdf/invoice.css')) !!}
    </style>
</head>

<body>

    <table class="header">
        <tr>
            <td>
                <div class="title">INVOICE / វិក្កយបត្រ</div>
                <div class="subtitle">#{{ $order->order_number }}</div>
                <div>Date / កាលបរិច្ឆេទ:
                    {{ $order->place_order_date?->format('d/m/Y h:i A') ?? $order->created_at->format('d/m/Y h:i A') }}
                </div>
            </td>
            <td class="company-info">
                <img src="{{ public_path('images/logo.png') }}" class="logo" alt="FreshLeaf Organics Logo">
                <div class="company-name">FreshLeaf Organics</div>
                Online Marketplace<br>
                Phnom Penh, Cambodia<br>
                Email: support@freshleaf.com
            </td>
        </tr>
    </table>

    <table class="details-container">
        <tr>
            <td>
                <div class="section-title">Customer / អតិថិជន</div>
                <strong>{{ $order->user->fullName }}</strong><br>
                {{ $order->address->phone }}<br>
                {{ $order->address->address_line_1 }}<br>
                @if($order->address->address_line_2)
                    {{ $order->address->address_line_2 }}<br>
                @endif
                {{ $order->address->city }}, {{ $order->address->province }}
            </td>
            <td>
                <div class="section-title">Vendor / អ្នកលក់</div>
                <strong>{{ $order->vendor->fullName ?? 'N/A' }}</strong><br>
                Order Type / ប្រភេទការបញ្ជាទិញ: {{ $order->type->name }}<br>
                Payment / ការបង់ប្រាក់: {{ $order->paymentStatus->name }}<br>
                Currency / រូបិយប័ណ្ណ: {{ $order->currency?->name ?? 'USD' }}
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th>Item / មុខទំនិញ</th>
                <th>Qty / ចំនួន</th>
                <th class="text-right">Price / តម្លៃ</th>
                <th class="text-right">Amount / សរុប</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
                <tr>
                    <td>{{ $item->product_name_snapshot }}</td>
                    <td>{{ $item->quantity }} {{ $item->unit_snapshot }}</td>
                    <td class="text-right">${{ number_format($item->unit_price_snapshot, 2) }}</td>
                    <td class="text-right">${{ number_format($item->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals-table">
        <tr>
            <td>Subtotal / សរុបរង:</td>
            <td class="text-right">${{ number_format($order->subtotal, 2) }}</td>
        </tr>
        @if($order->discount_amount > 0)
            <tr>
                <td>Discount / បញ្ចុះតម្លៃ:</td>
                <td class="text-right">-${{ number_format($order->discount_amount, 2) }}</td>
            </tr>
        @endif
        @if($order->delivery_fee > 0)
            <tr>
                <td>Delivery Fee / ថ្លៃដឹកជញ្ជូន:</td>
                <td class="text-right">${{ number_format($order->delivery_fee, 2) }}</td>
            </tr>
        @endif
        @if($order->tax_amount > 0)
            <tr>
                <td>Tax / ពន្ធ:</td>
                <td class="text-right">${{ number_format($order->tax_amount, 2) }}</td>
            </tr>
        @endif
        <tr class="total-row">
            <td>Total / សរុប:</td>
            <td class="text-right">${{ number_format($order->total_amount, 2) }}</td>
        </tr>
        @if($order->payment && $order->currency && $order->currency->id !== \App\Models\Currency::USD_ID)
            <tr>
                <td class="currency-note">
                    Paid in {{ $order->currency->name }} / បានបង់ជា {{ $order->currency->name }}:<br>
                    (Rate: $1 = {{ number_format($order->exchangeRateHistory?->rate ?? 4000) }}
                    {{ $order->currency->symbol }})
                </td>
                <td class="text-right currency-amount">
                    <strong>{{ number_format($order->payment->amount, 0) }} {{ $order->currency->symbol }}</strong>
                </td>
            </tr>
        @endif
    </table>

    <div class="clearfix"></div>

    <div class="footer">
        Thank you for shopping with FreshLeaf! / សូមអរគុណសម្រាប់ការជាវជាមួយ FreshLeaf!<br>
        If you have any questions concerning this invoice, please contact support.
    </div>

</body>

</html>