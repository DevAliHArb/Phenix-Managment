@component('mail::message')
# Thank you for your order

Hi {{ $order->first_name }} {{ $order->mid_name }} {{ $order->last_name }},

Just to let you know — we've received your order #{{ $order->id }}, and it is now being processed:

## [Order #{{ $order->id }}] ({{ $order->created_at->format('F d, Y') }})

<table style="width: 100%; border-collapse: collapse;">
    <thead>
        <tr>
            <th style="border: 1px solid #ddd; padding: 8px;">SKU</th>
            <th style="border: 1px solid #ddd; padding: 8px;">Product</th>
            <th style="border: 1px solid #ddd; padding: 8px;">Variants</th>
            <th style="border: 1px solid #ddd; padding: 8px;">Price</th>
            <th style="border: 1px solid #ddd; padding: 8px;">Qty</th>
            <th style="border: 1px solid #ddd; padding: 8px;">Total Price</th>
        </tr>
    </thead>
    <tbody>
        @foreach($orderItems as $item)
        <tr>
            <td style="border: 1px solid #ddd; padding: 8px; text-align: center">{{ $item['product_ean'] }}</td>
            <td style="border: 1px solid #ddd; padding: 8px; text-align: center">{{ $item['product_name'] }}</td>
                <td style="border: 1px solid #ddd; padding: 8px; text-align: center">
                    {{ is_array($item['variants']) && !empty($item['variants']) ? implode(', ', $item['variants']) : "-" }}
                </td>
            <td style="border: 1px solid #ddd; padding: 8px; text-align: center">${{ number_format($item['price'], 2) }}</td>
            <td style="border: 1px solid #ddd; padding: 8px; text-align: center">{{ $item['quantity'] }}</td>
            <td style="border: 1px solid #ddd; padding: 8px; text-align: center">${{ number_format($item['total_price'], 2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>


<table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
    <tbody>
        <tr>
            <th style="border: 1px solid #ddd; padding: 8px; text-align: start">Sub-total:</th>
            <td style="border: 1px solid #ddd; padding: 8px;">${{ number_format($order->base_price, 2) }}</td>
        </tr>
        <tr>
            <th style="border: 1px solid #ddd; padding: 8px; text-align: start">TVA:</th>
            <td style="border: 1px solid #ddd; padding: 8px;">${{ number_format($order->total_tva, 2) }}</td>
        </tr>
        <tr>
            <th style="border: 1px solid #ddd; padding: 8px; text-align: start">Shipping Cost:</th>
            <td style="border: 1px solid #ddd; padding: 8px;">${{ number_format($order->shipping_cost, 2) }}</td>
        </tr>
        <tr>
            <th style="border: 1px solid #ddd; padding: 8px; text-align: start">Total:</th>
            <td style="border: 1px solid #ddd; padding: 8px;">${{ number_format($order->total_price, 2) }}</td>
        </tr>
        @if ($order->review)
        <tr>
            <th style="border: 1px solid #ddd; padding: 8px; text-align: start">Note:</th>
            <td style="border: 1px solid #ddd; padding: 8px;">{{ $order->review }}</td>
        </tr>
        @endif
        <tr>
            <th style="border: 1px solid #ddd; padding: 8px; text-align: start">District:</th>
            <td style="border: 1px solid #ddd; padding: 8px;">{{ $order->city }}</td>
        </tr>
    </tbody>
</table>
<br/>
## Billing address

<table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
    <tbody>
        <tr>
            <td style="border: 1px solid #ddd; padding: 8px;">{{ $order->first_name }} {{ $order->mid_name }} {{ $order->last_name }}</td>
        </tr>
        <tr>
            <td style="border: 1px solid #ddd; padding: 8px;">{{ $order->address }}</td>
        </tr>
        <tr>
            <td style="border: 1px solid #ddd; padding: 8px;">{{ $order->city }}</td>
        </tr>
        <tr>
            <td style="border: 1px solid #ddd; padding: 8px;">{{ $order->phone }}</td>
        </tr>
        <tr>
            <td style="border: 1px solid #ddd; padding: 8px;">{{ $order->email }}</td>
        </tr>
    </tbody>
</table>

{{-- @component('mail::button', ['url' => url('/orders/' . $order->id)])
View Order
@endcomponent --}}

<br/>
<br/>
Thanks.<br>
{{ $siteName }}
@endcomponent
