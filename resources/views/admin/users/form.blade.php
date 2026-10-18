@extends('admin.layout')
@section('title', $user->exists ? $user->name : 'New user')
@section('content')
    <div class="page-head"><h1>{{ $user->exists ? $user->name : 'New user' }}</h1><a href="{{ route('admin.users.index', ['role' => $user->role]) }}">← Back</a></div>
    <div class="grid-form">
        <form class="card" method="post" action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}">
            @csrf @if ($user->exists) @method('put') @endif
            <div class="row">
                <x-admin.input name="name" label="Name" :value="$user->name" required />
                <x-admin.input name="phone" label="Mobile" :value="$user->phone" maxlength="10" />
            </div>
            <x-admin.input name="email" type="email" label="Email" :value="$user->email" required />
            <x-admin.select name="role" label="Role" :options="['customer' => 'Customer — signs in on the storefront', 'admin' => 'Admin — signs in to this panel']" :value="$user->role" />
            <x-admin.input name="password" type="password" :label="$user->exists ? 'New password' : 'Password'" autocomplete="new-password" :help="$user->exists ? 'Leave blank to keep the current password.' : 'At least 8 characters.'" />
            <x-admin.check name="is_active" label="Account active" :checked="$user->is_active" />
            <div class="form-actions"><button class="btn">Save</button></div>
        </form>
        @if ($user->exists && $user->role === 'customer')
            <div class="card table-wrap">
                <h2>Recent orders</h2>
                <table class="table">
                    <tbody>
                    @forelse ($orders as $o)
                        <tr>
                            <td><a href="{{ route('admin.orders.show', $o) }}">#{{ $o->order_number }}</a><div class="small muted">{{ $o->created_at->format('d M Y') }}</div></td>
                            <td><span class="badge {{ $o->status }}">{{ $o->statusLabel() }}</span></td>
                            <td class="num">₹{{ number_format($o->total) }}</td>
                        </tr>
                    @empty
                        <tr><td class="muted">No orders yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
