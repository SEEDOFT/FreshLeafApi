<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $order->order_number }}</title>
    <style>
        body { font-family: 'Khmer OS Battambang', sans-serif; font-size: 12px; }
        .invoice-box { max-width: 800px; margin: auto; padding: 30px; border: 1px solid #eee; }
        .invoice-box table { width: 100%; line-height: inherit; text-align: left; }
        .invoice-box table td { padding: 5px; vertical-align: top; }
        .invoice-box table tr td:nth-child(2) { text-align: right; }
        .invoice-box table tr.top table td { padding-bottom: 20px; }
        .invoice-box table tr.information table td { padding-bottom: 40px; }
        .invoice-box table tr.heading td { background: #eee; border-bottom: 1px solid #ddd; fontWeight: bold; }
        .invoice-box table tr.details td { padding-bottom: 20px; }
        .invoice-box table tr.item td { border-bottom: 1px solid #eee; }
        .invoice-box table tr.item.last td { border-bottom: none; }
        .invoice-box table tr.total td:nth-child(2) { border-top: 2px solid #eee; fontWeight: bold; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <div class="invoice-box">
        <table cellpadding="0" cellspacing="0">
            <tr class="top">
                <td colspan="4">
                    <table>
                        <tr>
                            <td class="title">
                                <h2>FreshLeaf Organics</h2>
                            </td>
                            <td>
                                Invoice #: {{ $order->order_number }}<br>
                                Created: {{ $order->created_at->format('M d, Y') }}<br>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr class="information">
                <td colspan="4">
                    <table>
                        <tr>
                            <td>
                                <strong>Vendor:</strong><br>
                                {{ $order->vendor?->business_name }}<br>
                                {{ $order->vendor?->address }}
                            </td>
                            <td>
                                <strong>Customer:</strong><br>
                                {{ $order->delivery_contact_name }}<br>
                                {{ $order->delivery_contact_phone }}<br>
                                {{ $order->delivery_address }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr class="heading">
                <td>Item</td>
                <td class="text-right">Price</td>
                <td class="text-right">Qty</td>
                <td class="text-right">Subtotal</td>
            </tr>

            @foreach($order->items as $item)
                <tr class="item">
                    <td>{{ $item->product?->name_en }}</td>
                    <td class="text-right">{{ format_currency($item->unit_price_snapshot) }}</td>
                    <td class="text-right">{{ $item->qty }}</td>
                    <td class="text-right">{{ format_currency($item->subtotal) }}</td>
                </tr>
            @endforeach

            <tr class="total">
                <td colspan="3"></td>
                <td class="text-right">
                   Subtotal: {{ format_currency($order->subtotal) }}
                </td>
            </tr>
            @if($order->discount_amount > 0)
            <tr>
                <td colspan="3"></td>
                <td class="text-right">
                   Discount: -{{ format_currency($order->discount_amount) }}
                </td>
            </tr>
            @endif
            <tr>
                <td colspan="3"></td>
                <td class="text-right">
                   Delivery: {{ format_currency($order->delivery_fee) }}
                </td>
            </tr>
            <tr>
                <td colspan="3"></td>
                <td class="text-right">
                   Tax: {{ format_currency($order->tax_amount) }}
                </td>
            </tr>
            <tr class="total">
                <td colspan="3"></td>
                <td class="text-right">
                   <strong>Total: {{ format_currency($order->total_amount) }}</strong>
                </td>
            </tr>

            @if($order->currency?->code === 'KHR')
                <tr>
                    <td colspan="4" class="text-right" style="padding-top: 10px;">
                        <small>
                            (Rate: $1 = {{ format_number($order->exchangeRateHistory?->rate ?? 4000) }} KHR)
                        </small>
                        <br>
                        <strong>Paid: {{ format_currency($order->payment->amount, 'KHR', 0) }}</strong>
                    </td>
                </tr>
            @endif
        </table>
    </div>
</body>
</html>
