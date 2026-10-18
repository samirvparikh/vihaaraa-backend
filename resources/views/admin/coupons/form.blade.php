@extends('admin.layout')
@section('title', $coupon->exists ? 'Edit coupon' : 'New coupon')
@section('content')
    <div class="page-head"><h1>{{ $coupon->exists ? 'Edit coupon '.$coupon->code : 'New coupon' }}</h1><a href="{{ route('admin.coupons.index') }}">← Back</a></div>
    <form class="card" style="max-width:760px" method="post" action="{{ $coupon->exists ? route('admin.coupons.update', $coupon) : route('admin.coupons.store') }}">
        @csrf @if ($coupon->exists) @method('put') @endif
        <div class="row">
            <x-admin.input name="code" label="Code" :value="$coupon->code" required style="text-transform:uppercase" help="Letters, numbers, dashes." />
            <x-admin.input name="label" label="Label shown to customer" :value="$coupon->label" help="Optional, e.g. 15% off on ₹2,999+" />
        </div>
        <div class="row">
            <x-admin.select name="type" label="Discount type" :options="['percent' => 'Percentage (%)', 'fixed' => 'Fixed amount (₹)']" :value="$coupon->type" />
            <x-admin.input name="value" type="number" step="0.01" min="0" label="Discount value" :value="$coupon->value" required />
            <x-admin.input name="max_discount" type="number" step="0.01" min="0" label="Max discount (₹)" :value="$coupon->max_discount" help="Cap for % coupons. Optional." />
        </div>
        <div class="row">
            <x-admin.input name="min_order" type="number" step="0.01" min="0" label="Minimum order (₹)" :value="$coupon->min_order" help="After Buy 1 Get 1." />
            <x-admin.input name="usage_limit" type="number" min="1" label="Total uses allowed" :value="$coupon->usage_limit" help="Blank = unlimited." />
            <x-admin.input name="per_user_limit" type="number" min="1" label="Uses per customer" :value="$coupon->per_user_limit" help="Blank = unlimited." />
        </div>
        <div class="row">
            <x-admin.input name="starts_at" type="datetime-local" label="Starts" :value="$coupon->starts_at?->format('Y-m-d\TH:i')" />
            <x-admin.input name="expires_at" type="datetime-local" label="Expires" :value="$coupon->expires_at?->format('Y-m-d\TH:i')" />
        </div>
        <x-admin.check name="is_active" label="Active" :checked="$coupon->is_active" />
        @if ($coupon->exists)<p class="small muted">Used {{ $coupon->used_count }} times so far.</p>@endif
        <div class="form-actions"><button class="btn">Save coupon</button><a href="{{ route('admin.coupons.index') }}">Cancel</a></div>
    </form>
@endsection
