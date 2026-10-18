@extends('admin.layout')
@section('title', 'Dashboard')
@section('content')
    <div class="page-head">
        <div>
            <h1>Dashboard</h1>
            <p>Welcome back, {{ auth()->user()->name }}.</p>
        </div>
        <a class="btn" href="{{ route('admin.products.create') }}">+ Add product</a>
    </div>

    <div class="stats">
        @foreach ($stats as $label => $value)
            <div class="stat"><span>{{ $label }}</span><strong>{{ $value }}</strong></div>
        @endforeach
    </div>

    <div class="grid-2">
        <div class="card">
            <div class="page-head" style="margin-bottom:8px"><h2 style="margin:0">Recent orders</h2><a href="{{ route('admin.orders.index') }}">View all</a></div>
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Order</th><th>Customer</th><th>Status</th><th class="num">Total</th></tr></thead>
                    <tbody>
                    @forelse ($recentOrders as $o)
                        <tr>
                            <td><a href="{{ route('admin.orders.show', $o) }}">#{{ $o->order_number }}</a><div class="small muted">{{ $o->created_at->format('d M, h:i A') }}</div></td>
                            <td>{{ $o->name }}</td>
                            <td><span class="badge {{ $o->status }}">{{ $o->statusLabel() }}</span></td>
                            <td class="num">₹{{ number_format($o->total) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="muted">No orders yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card">
            <div class="page-head" style="margin-bottom:8px"><h2 style="margin:0">Low stock</h2><a href="{{ route('admin.products.index', ['stock' => 'low']) }}">View all</a></div>
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Product</th><th class="num">Stock</th></tr></thead>
                    <tbody>
                    @forelse ($lowStock as $p)
                        <tr>
                            <td><a href="{{ route('admin.products.edit', $p) }}">{{ $p->name }}</a><div class="small muted">{{ $p->sku }}</div></td>
                            <td class="num"><span class="badge {{ $p->stock ? 'pending' : 'off' }}">{{ $p->stock }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="muted">Every product has more than 5 in stock.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
