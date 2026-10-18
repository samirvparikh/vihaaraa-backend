@extends('admin.layout')
@section('title', 'Coupon Codes')
@section('content')
    <div class="page-head">
        <div><h1>Coupon Codes</h1><p>Discount codes customers enter in the cart. Applied after Buy 1 Get 1.</p></div>
        <a class="btn" href="{{ route('admin.coupons.create') }}">+ New coupon</a>
    </div>
    <div class="card table-wrap" style="padding:0">
        <table class="table">
            <thead><tr><th>Code</th><th>Discount</th><th>Min. order</th><th>Used</th><th>Valid</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse ($coupons as $c)
                @php($reason = $c->unavailableReason())
                <tr>
                    <td><strong>{{ $c->code }}</strong><div class="small muted">{{ $c->displayLabel() }}</div></td>
                    <td>{{ $c->type === 'percent' ? rtrim(rtrim(number_format($c->value, 2), '0'), '.').'%' : '₹'.number_format($c->value) }}@if ($c->max_discount)<div class="small muted">max ₹{{ number_format($c->max_discount) }}</div>@endif</td>
                    <td>{{ $c->min_order > 0 ? '₹'.number_format($c->min_order) : '—' }}</td>
                    <td>{{ $c->used_count }}{{ $c->usage_limit ? ' / '.$c->usage_limit : '' }}</td>
                    <td class="small">{{ $c->starts_at?->format('d M Y') ?? 'Now' }} → {{ $c->expires_at?->format('d M Y') ?? 'No expiry' }}</td>
                    <td><span class="badge {{ $reason ? 'off' : 'ok' }}" title="{{ $reason }}">{{ $reason ? 'Unavailable' : 'Live' }}</span></td>
                    <td class="actions">
                        <a class="btn outline sm" href="{{ route('admin.coupons.edit', $c) }}">Edit</a>
                        <form method="post" action="{{ route('admin.coupons.destroy', $c) }}" data-confirm="Delete coupon {{ $c->code }}?">@csrf @method('delete')<button class="btn danger sm">Delete</button></form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="muted">No coupons yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
