@extends('admin.layout')
@section('title', $item->exists ? 'Edit offer message' : 'New offer message')
@section('content')
    <div class="page-head"><h1>{{ $item->exists ? 'Edit offer message' : 'New offer message' }}</h1><a href="{{ route('admin.announcements.index') }}">← Back</a></div>
    <form class="card" style="max-width:640px" method="post" action="{{ $item->exists ? route('admin.announcements.update', $item) : route('admin.announcements.store') }}">
        @csrf @if ($item->exists) @method('put') @endif
        <x-admin.input name="text" label="Message" :value="$item->text" required maxlength="160" help="e.g. BUY 1 GET 1 FREE | SITE WIDE" />
        <x-admin.input name="link" label="Link (optional)" :value="$item->link" help="Storefront path such as /collections/best-sellers" />
        <x-admin.input name="sort_order" type="number" label="Display order" :value="$item->sort_order ?? 0" min="0" />
        <x-admin.check name="is_active" label="Visible on storefront" :checked="$item->is_active" />
        <div class="form-actions"><button class="btn">Save</button><a href="{{ route('admin.announcements.index') }}">Cancel</a></div>
    </form>
@endsection
