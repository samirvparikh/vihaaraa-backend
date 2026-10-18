@extends('admin.layout')
@section('title', 'Collections')
@section('content')
    <div class="page-head">
        <div><h1>Collections</h1><p>New Launches, Best Sellers, Demi-Fine, Ethnic, Western, 925 Silver and more.</p></div>
        <a class="btn" href="{{ route('admin.collections.create') }}">+ New collection</a>
    </div>
    <div class="card table-wrap" style="padding:0">
        <table class="table">
            <thead><tr><th>#</th><th>Title</th><th>URL</th><th>Products</th><th>Menu</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @foreach ($collections as $c)
                <tr>
                    <td>{{ $c->sort_order }}</td>
                    <td><strong>{{ $c->title }}</strong><div class="small muted">{{ \App\Models\Collection::RULES[$c->rule] }}</div></td>
                    <td class="muted">/collections/{{ $c->handle }}</td>
                    <td class="num">{{ $c->product_total }}</td>
                    <td>{{ \App\Models\Collection::MENU_GROUPS[$c->menu_group] ?? '—' }}</td>
                    <td><span class="badge {{ $c->is_active ? 'ok' : 'off' }}">{{ $c->is_active ? 'Active' : 'Hidden' }}</span></td>
                    <td class="actions">
                        <a class="btn outline sm" href="{{ route('admin.collections.edit', $c) }}">Edit</a>
                        <form method="post" action="{{ route('admin.collections.destroy', $c) }}" data-confirm="Delete this collection? Products are kept.">@csrf @method('delete')<button class="btn danger sm">Delete</button></form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
