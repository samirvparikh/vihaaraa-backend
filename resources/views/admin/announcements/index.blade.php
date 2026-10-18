@extends('admin.layout')
@section('title', 'Top Offer Bar')
@section('content')
    <div class="page-head">
        <div><h1>Top Offer Bar</h1><p>Messages that rotate in the strip above the storefront header.</p></div>
        <a class="btn" href="{{ route('admin.announcements.create') }}">+ Add message</a>
    </div>
    <div class="card table-wrap" style="padding:0">
        <table class="table">
            <thead><tr><th>#</th><th>Message</th><th>Link</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse ($items as $a)
                <tr>
                    <td>{{ $a->sort_order }}</td>
                    <td><strong>{{ $a->text }}</strong></td>
                    <td class="muted">{{ $a->link ?: '—' }}</td>
                    <td><span class="badge {{ $a->is_active ? 'ok' : 'off' }}">{{ $a->is_active ? 'Visible' : 'Hidden' }}</span></td>
                    <td class="actions">
                        <a class="btn outline sm" href="{{ route('admin.announcements.edit', $a) }}">Edit</a>
                        <form method="post" action="{{ route('admin.announcements.destroy', $a) }}" data-confirm="Delete this message?">@csrf @method('delete')<button class="btn danger sm">Delete</button></form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="muted">No messages yet — the offer bar is hidden.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
