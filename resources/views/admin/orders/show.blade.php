@extends('admin.layout')
@section('title', 'Order #'.$order->order_number)
@section('content')
    @php($inr = fn ($n) => '₹'.number_format($n))
    <div class="page-head">
        <div>
            <h1>Order #{{ $order->order_number }}</h1>
            <p>Placed {{ $order->created_at->format('d M Y, h:i A') }} · <span class="badge {{ $order->status }}">{{ $order->statusLabel() }}</span></p>
        </div>
        <a href="{{ route('admin.orders.index') }}">← All orders</a>
    </div>

    <div class="grid-form">
        <div>
            <div class="card table-wrap">
                <h2>Items</h2>
                <table class="table">
                    <thead><tr><th>Product</th><th class="num">Price</th><th class="num">Qty</th><th class="num">Line total</th></tr></thead>
                    <tbody>
                    @foreach ($order->items as $i)
                        <tr>
                            <td>
                                @if ($i->product)<a href="{{ route('admin.products.edit', $i->product) }}">{{ $i->name }}</a>@else{{ $i->name }}@endif
                                <div class="small muted">{{ $i->sku }}{{ $i->size ? ' · Size '.$i->size : '' }}</div>
                            </td>
                            <td class="num">{{ $inr($i->price) }}<div class="small muted"><s>{{ $inr($i->mrp) }}</s></div></td>
                            <td class="num">{{ $i->qty }}</td>
                            <td class="num">{{ $inr($i->price * $i->qty) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <div class="totals" style="margin-top:14px">
                    <div><span>MRP total</span><span>{{ $inr($order->mrp_total) }}</span></div>
                    <div><span>Subtotal ({{ $order->item_count }} items)</span><span>{{ $inr($order->subtotal) }}</span></div>
                    @if ($order->b1g1_discount > 0)<div><span>Buy 1 Get 1</span><span>− {{ $inr($order->b1g1_discount) }}</span></div>@endif
                    @if ($order->coupon_discount > 0)<div><span>Coupon ({{ $order->coupon_code }})</span><span>− {{ $inr($order->coupon_discount) }}</span></div>@endif
                    <div><span>Shipping</span><span>{{ $order->shipping ? $inr($order->shipping) : 'FREE' }}</span></div>
                    <div class="grand"><span>Total</span><span>{{ $inr($order->total) }}</span></div>
                </div>
            </div>

            <div class="grid-2">
                <div class="card">
                    <h2>Customer</h2>
                    <dl class="info">
                        <dt>Name</dt><dd>{{ $order->name }}</dd>
                        <dt>Phone</dt><dd><a href="tel:{{ $order->phone }}">{{ $order->phone }}</a></dd>
                        <dt>Email</dt><dd><a href="mailto:{{ $order->email }}">{{ $order->email }}</a></dd>
                        <dt>Account</dt><dd>@if ($order->user)<a href="{{ route('admin.users.edit', $order->user) }}">{{ $order->user->email }}</a>@else Guest @endif</dd>
                    </dl>
                </div>
                <div class="card">
                    <h2>Ship to</h2>
                    <p style="margin:0">{{ $order->address }}<br>@if ($order->landmark){{ $order->landmark }}<br>@endif{{ $order->city }}, {{ $order->state }} – {{ $order->pincode }}</p>
                </div>
            </div>
        </div>

        <form class="card" method="post" action="{{ route('admin.orders.update', $order) }}">
            @csrf @method('put')
            <h2>Manage order</h2>
            <dl class="info" style="margin-bottom:14px">
                <dt>Payment</dt><dd>{{ \App\Models\Order::PAYMENT_METHODS[$order->payment_method] ?? $order->payment_method }}</dd>
            </dl>
            <x-admin.select name="status" label="Order status" :options="\App\Models\Order::STATUSES" :value="$order->status" :disabled="$order->status === 'cancelled'" />
            @if ($order->status === 'cancelled')<input type="hidden" name="status" value="cancelled">@endif
            <x-admin.select name="payment_status" label="Payment status" :options="\App\Models\Order::PAYMENT_STATUSES" :value="$order->payment_status" />
            <x-admin.input name="tracking_number" label="Courier / tracking number" :value="$order->tracking_number" help="Shown to the customer in My Orders." />
            <x-admin.textarea name="admin_note" label="Internal note" :value="$order->admin_note" rows="4" help="Only visible to admins." />
            <p class="small muted">Setting the status to Cancelled returns the items to stock.</p>
            <button class="btn">Update order</button>
        </form>
    </div>
@endsection
