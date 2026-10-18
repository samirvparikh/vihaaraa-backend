@extends('admin.layout')
@section('title', 'My Account')
@section('content')
    <div class="page-head"><h1>My Account</h1></div>
    <form class="card" style="max-width:560px" method="post" action="{{ route('admin.profile.update') }}">
        @csrf @method('put')
        <x-admin.input name="name" label="Name" :value="$user->name" required />
        <x-admin.input name="email" type="email" label="Email" :value="$user->email" required />
        <h2 style="margin-top:10px">Change password</h2>
        <x-admin.input name="current_password" type="password" label="Current password" autocomplete="current-password" />
        <x-admin.input name="password" type="password" label="New password" autocomplete="new-password" help="At least 8 characters. Leave blank to keep the current one." />
        <x-admin.input name="password_confirmation" type="password" label="Confirm new password" autocomplete="new-password" />
        <button class="btn">Save</button>
    </form>
@endsection
