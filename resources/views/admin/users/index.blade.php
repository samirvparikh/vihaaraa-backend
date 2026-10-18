@extends('admin.layout')
@section('title', $role === 'admin' ? 'Admin users' : 'Customers')
@section('content')
    <div class="page-head">
        <div><h1>{{ $role === 'admin' ? 'Admin users' : 'Customers' }}</h1>
            <p>{{ $role === 'admin' ? 'People who can sign in to this admin panel.' : 'Shoppers who sign in on the storefront.' }}</p></div>
        <a class="btn" href="{{ route('admin.users.create', ['role' => $role]) }}">+ New {{ $role === 'admin' ? 'admin' : 'customer' }}</a>
    </div>
    <nav class="tabs">
        <a href="{{ route('admin.users.index') }}" class="{{ $role === 'customer' ? 'active' : '' }}">Customers</a>
        <a href="{{ route('admin.users.index', ['role' => 'admin']) }}" class="{{ $role === 'admin' ? 'active' : '' }}">Admin users</a>
    </nav>
    <form class="filters" method="get">
        @if ($role === 'admin')<input type="hidden" name="role" value="admin">@endif
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Name, email or phone">
        <button class="btn outline">Search</button>
    </form>
    <div class="card table-wrap" style="padding:0">
        <table class="table">
            <thead><tr><th>Name</th><th>Email</th><th>Phone</th>@if ($role === 'customer')<th class="num">Orders</th><th class="num">Spent</th>@endif<th>Joined</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse ($users as $u)
                <tr>
                    <td><strong>{{ $u->name }}</strong></td>
                    <td>{{ $u->email }}</td>
                    <td>{{ $u->phone ?: '—' }}</td>
                    @if ($role === 'customer')
                        <td class="num">{{ $u->orders_count }}</td>
                        <td class="num">₹{{ number_format($u->orders_sum_total ?? 0) }}</td>
                    @endif
                    <td>{{ $u->created_at->format('d M Y') }}</td>
                    <td><span class="badge {{ $u->is_active ? 'ok' : 'off' }}">{{ $u->is_active ? 'Active' : 'Disabled' }}</span></td>
                    <td class="actions">
                        <a class="btn outline sm" href="{{ route('admin.users.edit', $u) }}">Edit</a>
                        @unless ($u->is(auth()->user()))
                            <form method="post" action="{{ route('admin.users.destroy', $u) }}" data-confirm="Delete {{ $u->email }}? Their orders are kept.">@csrf @method('delete')<button class="btn danger sm">Delete</button></form>
                        @endunless
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="muted">No users found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <x-admin.pager :items="$users" />
@endsection
