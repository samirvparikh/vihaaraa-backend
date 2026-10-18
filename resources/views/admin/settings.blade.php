@extends('admin.layout')
@section('title', 'Shop Settings')
@section('content')
    <div class="page-head"><div><h1>Shop Settings</h1><p>Rules the cart and checkout follow, and the contact details shown in the footer.</p></div></div>
    <form method="post" action="{{ route('admin.settings.update') }}" style="max-width:860px">
        @csrf @method('put')
        <div class="card">
            <h2>Cart & checkout</h2>
            <div class="row">
                <x-admin.input name="free_shipping_min" type="number" min="0" label="Free shipping above (₹)" :value="$s['free_shipping_min']" required />
                <x-admin.input name="shipping_fee" type="number" min="0" label="Shipping fee below that (₹)" :value="$s['shipping_fee']" required />
            </div>
            <div class="row">
                <x-admin.input name="cod_limit" type="number" min="0" label="Cash on Delivery limit (₹)" :value="$s['cod_limit']" required help="COD is hidden for orders above this." />
                <x-admin.input name="max_qty" type="number" min="1" max="99" label="Max quantity per item" :value="$s['max_qty']" required />
            </div>
            <x-admin.check name="b1g1_enabled" label="Buy 1 Get 1 Free — the lower-priced item of each pair is free" :checked="(bool) $s['b1g1_enabled']" />
        </div>
        <div class="card">
            <h2>Contact & social</h2>
            <div class="row">
                <x-admin.input name="contact_email" type="email" label="Support email" :value="$s['contact_email']" required />
                <x-admin.input name="contact_whatsapp" label="WhatsApp number" :value="$s['contact_whatsapp']" required />
            </div>
            <x-admin.input name="contact_hours" label="Support hours text" :value="$s['contact_hours']" required />
            <div class="row">
                <x-admin.input name="instagram_url" label="Instagram URL" :value="$s['instagram_url']" />
                <x-admin.input name="facebook_url" label="Facebook URL" :value="$s['facebook_url']" />
            </div>
            <div class="row">
                <x-admin.input name="youtube_url" label="YouTube URL" :value="$s['youtube_url']" />
                <x-admin.input name="pinterest_url" label="Pinterest URL" :value="$s['pinterest_url']" />
            </div>
        </div>
        <button class="btn">Save settings</button>
    </form>
@endsection
