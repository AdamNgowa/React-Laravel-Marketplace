<x-mail::message>
    <h1 style="text-align:center; font-size:24px; margin-bottom:20px;">
        Payment Completed Successfully
    </h1>

    @foreach ($orders as $order)
        <h3 style="font-size:20px; margin-top:20px; margin-bottom:15px;">Order Summary</h3>

        <x-mail::table>
            <table>
                <tbody>
                    <tr>
                        <td>Seller</td>
                        <td>
                            <a href="{{ url('/') }}">
                                {{ $order->vendorUser->vendor->store_name }}
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td>Order #</td>
                        <td>#{{ $order->id }}</td>
                    </tr>
                    <tr>
                        <td>Items</td>
                        <td>{{ $order->orderItems->count() }}</td>
                    </tr>
                    <tr>
                        <td>Total</td>
                        <td>{{ Number::currency($order->total_price) }}</td>
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
                    @foreach ($order->orderItems as $orderItem)
                        <tr>
                            <td>
                                <table>
                                    <tbody>
                                        <tr>
                                            <td style="padding:5px;">
                                                <img style="min-width:60px; max-width:60px;" 
                                                     src="{{ $orderItem->product->getImageForOptions($orderItem->variation_type_option_ids) }}" 
                                                     alt="">
                                            </td>
                                            <td style="font-size:13px; padding:5px;">
                                                {{ $orderItem->product->title }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                            <td>{{ $orderItem->quantity }}</td>
                            <td>{{ Number::currency($orderItem->price) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </x-mail::table>

        <x-mail::button :url="url('/orders/' . $order->id)">
            View Order Details
        </x-mail::button>
    @endforeach

    <x-mail::subcopy>
        If you have any questions, please contact our support team.
    </x-mail::subcopy>

    <x-mail::panel>
        Thank you for your purchase!
    </x-mail::panel>

    Thanks,<br>
    {{ config('app.name') }}
</x-mail::message>
