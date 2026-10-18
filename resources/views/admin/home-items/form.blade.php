@extends('admin.layout')
@section('title', $sectionName)
@section('content')
    @php($back = route('admin.home-items.index', ['section' => $item->section]))
    <div class="page-head"><h1>{{ $item->exists ? 'Edit' : 'Add to' }} {{ $sectionName }}</h1><a href="{{ $back }}">← Back</a></div>
    <form method="post" enctype="multipart/form-data" action="{{ $item->exists ? route('admin.home-items.update', $item) : route('admin.home-items.store') }}" class="grid-form">
        @csrf
        @if ($item->exists) @method('put') @else <input type="hidden" name="section" value="{{ $item->section }}"> @endif
        <div class="card">
            @if ($item->section === 'look')
                <x-admin.select name="product_id" label="Product" :options="$products" :value="$item->product_id" placeholder="— Choose a product —" required />
                <p class="small muted">The card shows the product name and price and links to its page.</p>
            @else
                <x-admin.input name="label" label="Label" :value="$item->label" required help="Text under the {{ $item->section === 'circles' ? 'circle' : 'tile' }}, e.g. New Launches" />
                <x-admin.input name="link" label="Link" :value="$item->link" help="e.g. /collections/new-launches or /collections/earrings" />
                @if ($item->section === 'handpicked')
                    <div class="row">
                        <x-admin.input name="line1" label="Overlay line 1" :value="$item->line1" />
                        <x-admin.input name="line2" label="Overlay line 2" :value="$item->line2" />
                    </div>
                    <x-admin.check name="italic_first" label="Show line 1 in italics (otherwise line 2)" :checked="$item->italic_first" />
                    <x-admin.select name="bg" label="Tile colour" :options="$backdrops" :value="$item->bg" />
                @endif
            @endif
            @if ($item->section !== 'handpicked')<input type="hidden" name="bg" value="{{ $item->bg ?: 'blush' }}">@endif
            <x-admin.input name="sort_order" type="number" label="Display order" :value="$item->sort_order ?? 0" min="0" />
            <x-admin.check name="is_active" label="Visible on storefront" :checked="$item->is_active" />
            <div class="form-actions"><button class="btn">Save</button><a href="{{ $back }}">Cancel</a></div>
        </div>
        <div class="card">
            <x-admin.image name="image" label="Image" :path="$item->image" help="{{ $item->section === 'circles' ? 'Square, at least 300 × 300 px.' : 'Portrait, around 600 × 800 px.' }}" />
            @if ($item->section !== 'look')
                <x-admin.select name="product_id" label="Or show this product's picture" :options="$products" :value="$item->product_id" placeholder="— None —" />
            @endif
        </div>
    </form>
@endsection
