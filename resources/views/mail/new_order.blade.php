@php
    use Illuminate\Support\Number;
    use App\Models\Order;

    $order = Order::with(['orderItems.product'])
        ->find($order ?? null);
@endphp

<x-mail::message>
    <h1 style="text-align:center; font-size:24px; margin-bottom:20px;">
        🎉 Congratulations! You have a new Order.
    </h1>

    @if($order)
        <x-mail::button :url="url('/orders/' . $order->id)">
            View Order Details
        </x-mail::button>

        <h3 style="font-size:20px; margin-top:20px; margin-bottom:15px;">Order Summary</h3>

        <x-mail::table>
            <table>
                <tbody>
                    <tr>
                        <td>Order #</td>
                        <td>#{{ $order->id }}</td>
                    </tr>
                    <tr>
                        <td>Order Date</td>
                        <td>{{ $order->created_at->format('M d, Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td>Order Total</td>
                        <td>{{ Number::currency($order->total_price ?? 0) }}</td>
                    </tr>
                    <tr>
                        <td>Payment Processing Fee</td>
                        <td>{{ Number::currency($order->online_payment_commission ?: 0) }}</td>
                    </tr>
                    <tr>
                        <td>Website Commission</td>
                        <td>{{ Number::currency($order->website_commission ?: 0) }}</td>
                    </tr>
                    <tr>
                        <td>Vendor Subtotal</td>
                        <td>{{ Number::currency($order->vendor_subtotal ?: 0) }}</td>
                    </tr>
                </tbody>
            </table>
        </x-mail::table>

        <hr style="margin:20px 0;">

        <x-mail::table>
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Quantity</th>
                        <th>Price</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->orderItems as $orderItem)
                        <tr>
                            <td>
                                <table>
                                    <tbody>
                                        <tr>
                                            <td style="padding:5px;">
                                                <img style="min-width:60px; max-width:60px;"
                                                     src="{{ $orderItem->product?->getImageForOptions($orderItem->variation_type_option_ids) ?? '' }}"
                                                     alt="">
                                            </td>
                                            <td style="font-size:13px; padding:5px;">
                                                {{ $orderItem->product?->title ?? 'N/A' }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                            <td>{{ $orderItem->quantity }}</td>
                            <td>{{ Number::currency($orderItem->price ?? 0) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </x-mail::table>
    @endif

    <x-mail::panel>
        Thank you for doing business with us.
    </x-mail::panel>

    Thanks,<br>
    {{ config('app.name') }}
</x-mail::message>
