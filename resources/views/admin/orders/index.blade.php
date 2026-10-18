@extends('admin.layout')
@section('title', 'Orders')
@section('content')
    <div class="page-head">
        <div><h1>Orders</h1><p>{{ $orders->total() }} orders</p></div>
        <a class="btn outline" href="{{ route('admin.orders.export', request()->query()) }}">Export CSV</a>
    </div>

    <nav class="tabs">
        <a href="{{ route('admin.orders.index', request()->except('status', 'page')) }}" class="{{ request('status') ? '' : 'active' }}">All ({{ $counts->sum() }})</a>
        @foreach (\App\Models\Order::STATUSES as $key => $label)
            <a href="{{ route('admin.orders.index', ['status' => $key] + request()->except('status', 'page')) }}" class="{{ request('status') === $key ? 'active' : '' }}">{{ $label }} ({{ $counts[$key] ?? 0 }})</a>
        @endforeach
    </nav>

    <form class="filters" method="get">
        @if (request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Order no., name, email, phone">
        <select name="payment"><option value="">Any payment</option>@foreach (\App\Models\Order::PAYMENT_METHODS as $k => $v)<option value="{{ $k }}" @selected(request('payment') === $k)>{{ $v }}</option>@endforeach</select>
        <input type="date" name="from" value="{{ request('from') }}" title="From">
        <input type="date" name="to" value="{{ request('to') }}" title="To">
        <button class="btn outline">Filter</button>
        @if (request()->hasAny(['q', 'payment', 'from', 'to']))<a href="{{ route('admin.orders.index', request()->only('status')) }}">Reset</a>@endif
    </form>

    <div class="card table-wrap" style="padding:0">
        <table class="table">
            <thead><tr><th>Order</th><th>Date</th><th>Customer</th><th class="num">Items</th><th>Payment</th><th>Status</th><th class="num">Total</th></tr></thead>
            <tbody>
            @forelse ($orders as $o)
                <tr>
                    <td><a href="{{ route('admin.orders.show', $o) }}"><strong>#{{ $o->order_number }}</strong></a></td>
                    <td>{{ $o->created_at->format('d M Y') }}<div class="small muted">{{ $o->created_at->format('h:i A') }}</div></td>
                    <td>{{ $o->name }}<div class="small muted">{{ $o->phone }} · {{ $o->city }}</div></td>
                    <td class="num">{{ $o->item_count }}</td>
                    <td>{{ \App\Models\Order::PAYMENT_METHODS[$o->payment_method] ?? $o->payment_method }}<br><span class="badge {{ $o->payment_status }}">{{ ucfirst($o->payment_status) }}</span></td>
                    <td><span class="badge {{ $o->status }}">{{ $o->statusLabel() }}</span></td>
                    <td class="num"><strong>₹{{ number_format($o->total) }}</strong></td>
                </tr>
            @empty
                <tr><td colspan="7" class="muted">No orders found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <x-admin.pager :items="$orders" />
@endsection
