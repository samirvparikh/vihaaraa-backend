@extends('admin.layout')
@section('title', 'Home Sections')
@section('content')
    <div class="page-head">
        <div><h1>Home Sections</h1><p>Everything shown on the storefront home page below the image slider.</p></div>
        @if ($section !== 'bestsellers')
            <a class="btn" href="{{ route('admin.home-items.create', ['section' => $section]) }}">+ Add to {{ $sections[$section] }}</a>
        @endif
    </div>

    <nav class="tabs">
        @foreach ($sections as $key => $name)
            <a href="{{ route('admin.home-items.index', ['section' => $key]) }}" class="{{ $section === $key ? 'active' : '' }}">{{ $name }}</a>
        @endforeach
    </nav>

    @if ($section === 'bestsellers')
        <form class="card" method="post" action="{{ route('admin.home-items.headings') }}" style="max-width:640px">
            @csrf
            <p class="muted">This section lists products ticked as <b>Best Seller</b>, in their display order. Tick or untick the flag on a product to add or remove it.</p>
            <div class="row">
                <x-admin.input name="bestsellers_title" label="Section heading" :value="$settings['bestsellers_title']" required />
                <x-admin.input name="bestsellers_limit" type="number" label="Products to show" :value="$settings['bestsellers_limit']" min="1" max="24" required />
            </div>
            <button class="btn">Save</button>
        </form>
        <div class="card table-wrap" style="padding:0">
            <table class="table">
                <thead><tr><th>Order</th><th>Product</th><th class="num">Price</th><th>Status</th><th></th></tr></thead>
                <tbody>
                @forelse ($bestsellers as $p)
                    <tr>
                        <td>{{ $p->sort_order }}</td>
                        <td><strong>{{ $p->name }}</strong><div class="small muted">{{ $p->sku }}</div></td>
                        <td class="num">₹{{ number_format($p->price) }}</td>
                        <td><span class="badge {{ $p->is_active ? 'ok' : 'off' }}">{{ $p->is_active ? 'Active' : 'Hidden' }}</span></td>
                        <td class="actions"><a class="btn outline sm" href="{{ route('admin.products.edit', $p) }}">Edit product</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="muted">No products are marked as Best Seller.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    @else
        @if ($section !== 'circles')
            @php($titleKey = $section === 'look' ? 'look_title' : 'handpicked_title')
            <form class="card" method="post" action="{{ route('admin.home-items.headings') }}" style="max-width:640px;display:flex;gap:12px;align-items:end">
                @csrf
                <div style="flex:1"><x-admin.input :name="$titleKey" label="Section heading" :value="$settings[$titleKey]" required /></div>
                <button class="btn" style="margin-bottom:14px">Save heading</button>
            </form>
        @endif
        <div class="card table-wrap" style="padding:0">
            <table class="table">
                <thead><tr><th>#</th><th>Image</th><th>Label</th><th>Links to</th><th>Status</th><th></th></tr></thead>
                <tbody>
                @forelse ($items as $i)
                    <tr>
                        <td>{{ $i->sort_order }}</td>
                        <td>
                            @if ($i->image)<img class="thumb" src="{{ \App\Support\Media::url($i->image) }}" alt="">
                            @else<span class="small muted">Illustration of<br>{{ $i->product?->name ?? '—' }}</span>@endif
                        </td>
                        <td><strong>{{ $i->label ?: $i->product?->name }}</strong>@if ($i->line1)<div class="small muted">{{ $i->line1 }} / {{ $i->line2 }}</div>@endif</td>
                        <td class="muted">{{ $i->link ?: ($i->product ? '/product/'.$i->product->slug : '—') }}</td>
                        <td><span class="badge {{ $i->is_active ? 'ok' : 'off' }}">{{ $i->is_active ? 'Visible' : 'Hidden' }}</span></td>
                        <td class="actions">
                            <a class="btn outline sm" href="{{ route('admin.home-items.edit', $i) }}">Edit</a>
                            <form method="post" action="{{ route('admin.home-items.destroy', $i) }}" data-confirm="Delete this item?">@csrf @method('delete')<button class="btn danger sm">Delete</button></form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="muted">Nothing here yet — this section is hidden on the home page.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    @endif
@endsection
