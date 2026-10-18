@extends('admin.layout')
@section('title', 'Pages')
@section('content')
    <div class="page-head">
        <div><h1>Pages</h1><p>Shipping, returns, care guide and other info pages.</p></div>
        <a class="btn" href="{{ route('admin.pages.create') }}">+ New page</a>
    </div>
    <div class="card table-wrap" style="padding:0">
        <table class="table">
            <thead><tr><th>Title</th><th>URL</th><th>Footer</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @foreach ($pages as $p)
                <tr>
                    <td><strong>{{ $p->title }}</strong></td>
                    <td class="muted">/pages/{{ $p->slug }}</td>
                    <td>{{ $p->show_in_footer ? 'Yes' : '—' }}</td>
                    <td><span class="badge {{ $p->is_active ? 'ok' : 'off' }}">{{ $p->is_active ? 'Published' : 'Hidden' }}</span></td>
                    <td class="actions">
                        <a class="btn outline sm" href="{{ route('admin.pages.edit', $p) }}">Edit</a>
                        <form method="post" action="{{ route('admin.pages.destroy', $p) }}" data-confirm="Delete this page?">@csrf @method('delete')<button class="btn danger sm">Delete</button></form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
