@extends('admin.layout')
@section('title', 'Newsletter')
@section('content')
    <div class="page-head">
        <div><h1>Newsletter subscribers</h1><p>{{ $subscribers->total() }} emails from the footer sign-up form.</p></div>
        <a class="btn outline" href="{{ route('admin.subscribers.export') }}">Export CSV</a>
    </div>
    <div class="card table-wrap" style="padding:0">
        <table class="table">
            <thead><tr><th>Email</th><th>Subscribed</th><th></th></tr></thead>
            <tbody>
            @forelse ($subscribers as $s)
                <tr>
                    <td>{{ $s->email }}</td>
                    <td>{{ $s->created_at->format('d M Y, h:i A') }}</td>
                    <td class="actions"><form method="post" action="{{ route('admin.subscribers.destroy', $s) }}" data-confirm="Remove {{ $s->email }}?">@csrf @method('delete')<button class="btn danger sm">Remove</button></form></td>
                </tr>
            @empty
                <tr><td colspan="3" class="muted">No subscribers yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <x-admin.pager :items="$subscribers" />
@endsection
