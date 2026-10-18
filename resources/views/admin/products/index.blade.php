@extends('admin.layout')
@section('title', 'Products')
@section('content')
    <div class="page-head">
        <div><h1>Products</h1><p>{{ $products->total() }} products</p></div>
        <a class="btn" href="{{ route('admin.products.create') }}">+ Add product</a>
    </div>

    <form class="filters" method="get">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Search name, SKU, plating…">
        <select name="category"><option value="">All categories</option>@foreach ($categories as $id => $name)<option value="{{ $id }}" @selected(request('category') == $id)>{{ $name }}</option>@endforeach</select>
        <select name="collection"><option value="">All collections</option>@foreach ($collections as $id => $name)<option value="{{ $id }}" @selected(request('collection') == $id)>{{ $name }}</option>@endforeach</select>
        <select name="flag">
            <option value="">Any status</option>
            <option value="new" @selected(request('flag') === 'new')>New launches</option>
            <option value="bestseller" @selected(request('flag') === 'bestseller')>Best sellers</option>
            <option value="hidden" @selected(request('flag') === 'hidden')>Hidden</option>
        </select>
        <label class="check" style="margin:0"><input type="checkbox" name="stock" value="low" @checked(request('stock') === 'low')> Low stock</label>
        <button class="btn outline">Filter</button>
        @if (request()->hasAny(['q', 'category', 'collection', 'flag', 'stock']))<a href="{{ route('admin.products.index') }}">Reset</a>@endif
    </form>

    <div class="card table-wrap" style="padding:0">
        <table class="table">
            <thead><tr><th></th><th>Product</th><th>Category</th><th class="num">Price</th><th class="num">MRP</th><th class="num">Stock</th><th>Flags</th><th></th></tr></thead>
            <tbody>
            @forelse ($products as $p)
                <tr>
                    <td>@if ($p->image)<img class="thumb" src="{{ $p->imageUrl() }}" alt="">@else<div class="thumb"></div>@endif</td>
                    <td><a href="{{ route('admin.products.edit', $p) }}"><strong>{{ $p->name }}</strong></a><div class="small muted">{{ $p->sku }} · {{ $p->plating }}</div></td>
                    <td>{{ $p->category?->name ?? '—' }}</td>
                    <td class="num">₹{{ number_format($p->price) }}</td>
                    <td class="num muted"><s>₹{{ number_format($p->mrp) }}</s></td>
                    <td class="num"><span class="badge {{ $p->stock > 5 ? 'ok' : ($p->stock ? 'pending' : 'off') }}">{{ $p->stock }}</span></td>
                    <td>
                        @if (! $p->is_active)<span class="badge off">Hidden</span>@endif
                        @if ($p->is_new)<span class="badge shipped">New</span>@endif
                        @if ($p->is_bestseller)<span class="badge gold">Best Seller</span>@endif
                    </td>
                    <td class="actions">
                        <a class="btn outline sm" href="{{ route('admin.products.edit', $p) }}">Edit</a>
                        <form method="post" action="{{ route('admin.products.destroy', $p) }}" data-confirm="Delete {{ $p->name }}? This cannot be undone.">@csrf @method('delete')<button class="btn danger sm">Delete</button></form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="muted">No products match.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <x-admin.pager :items="$products" />
@endsection
