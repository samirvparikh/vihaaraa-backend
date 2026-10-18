@extends('admin.layout')
@section('title', 'Categories')
@section('content')
    <div class="page-head">
        <div><h1>Categories</h1><p>Product types — necklaces, earrings, bangles… Each has a storefront page at /collections/{slug}.</p></div>
        <a class="btn" href="{{ route('admin.categories.create') }}">+ New category</a>
    </div>
    <div class="card table-wrap" style="padding:0">
        <table class="table">
            <thead><tr><th>#</th><th>Name</th><th>URL</th><th class="num">Products</th><th>Menu</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @foreach ($categories as $c)
                <tr>
                    <td>{{ $c->sort_order }}</td>
                    <td><strong>{{ $c->name }}</strong></td>
                    <td class="muted">/collections/{{ $c->slug }}</td>
                    <td class="num"><a href="{{ route('admin.products.index', ['category' => $c->id]) }}">{{ $c->products_count }}</a></td>
                    <td>{{ $c->show_in_menu ? 'Yes' : '—' }}</td>
                    <td><span class="badge {{ $c->is_active ? 'ok' : 'off' }}">{{ $c->is_active ? 'Active' : 'Hidden' }}</span></td>
                    <td class="actions">
                        <a class="btn outline sm" href="{{ route('admin.categories.edit', $c) }}">Edit</a>
                        <form method="post" action="{{ route('admin.categories.destroy', $c) }}" data-confirm="Delete this category?">@csrf @method('delete')<button class="btn danger sm">Delete</button></form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
