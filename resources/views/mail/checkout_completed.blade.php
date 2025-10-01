<x-mail::message>
    <h1 style="text-align:center; font-size:24px; margin-bottom:20px;">
        ✅ Payment Completed Successfully
    </h1>

    @forelse ($orders as $order)
        <h3 style="font-size:20px; margin-top:20px; margin-bottom:15px;">Order Summary</h3>

        <x-mail::table>
            <table>
                <tbody>
                    <tr>
                        <td>Seller</td>
                        <td>{{ $order->vendorUser?->vendor?->store_name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td>Order #</td>
                        <td>#{{ $order->id }}</td>
                    </tr>
                    <tr>
                        <td>Items</td>
                        <td>{{ $order->orderItems?->count() ?? 0 }}</td>
                    </tr>
                    <tr>
                        <td>Total</td>
                        <td>{{ \Illuminate\Support\Number::currency($order->total_price ?? 0) }}</td>
                    </tr>
                    <tr>
                        <td>Payment Fee</td>
                        <td>{{ \Illuminate\Support\Number::currency($order->online_payment_commission ?? 0) }}</td>
                    </tr>
                    <tr>
                        <td>Website Commission</td>
                        <td>{{ \Illuminate\Support\Number::currency($order->website_commission ?? 0) }}</td>
                    </tr>
                    <tr>
                        <td>Vendor Subtotal</td>
                        <td>{{ \Illuminate\Support\Number::currency($order->vendor_subtotal ?? 0) }}</td>
                    </tr>
                </tbody>
            </table>
        </x-mail::table>

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
                    @foreach ($order->orderItems ?? [] as $orderItem)
                        <tr>
                            <td>
                                <table>
                                    <tr>
                                        <td style="padding:5px;">
                                            <img style="min-width:60px; max-width:60px;"
                                                 src="{{ optional($orderItem->product)->getImageForOptions($orderItem->variation_type_option_ids) ?? '' }}"
                                                 alt="{{ $orderItem->product?->title ?? 'Product Image' }}">
                                        </td>
                                        <td style="font-size:13px; padding:5px;">
                                            {{ $orderItem->product?->title ?? 'N/A' }}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td>{{ $orderItem->quantity ?? 0 }}</td>
                            <td>{{ \Illuminate\Support\Number::currency($orderItem->price ?? 0) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </x-mail::table>

        <x-mail::button :url="url('/orders/' . $order->id)">
            View Order Details
        </x-mail::button>

        <hr style="margin:20px 0;">
    @empty
        <p>No orders found.</p>
    @endforelse

    <x-mail::subcopy>
        If you have any questions, please contact our support team.
    </x-mail::subcopy>

    <x-mail::panel>
        Thank you for your purchase!
    </x-mail::panel>

    Thanks,<br>
    {{ config('app.name') }}
</x-mail::message>
