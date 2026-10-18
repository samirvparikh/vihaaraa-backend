@extends('admin.layout')
@section('title', $category->exists ? 'Edit category' : 'New category')
@section('content')
    <div class="page-head"><h1>{{ $category->exists ? 'Edit category' : 'New category' }}</h1><a href="{{ route('admin.categories.index') }}">← Back</a></div>
    <form method="post" enctype="multipart/form-data" action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}" class="grid-form">
        @csrf @if ($category->exists) @method('put') @endif
        <div class="card">
            <div class="row">
                <x-admin.input name="name" label="Name" :value="$category->name" required />
                <x-admin.input name="slug" label="URL slug" :value="$category->slug" help="Leave blank to generate from the name." />
            </div>
            <x-admin.input name="blurb" label="Short description" :value="$category->blurb" />
            <x-admin.input name="sort_order" type="number" label="Display order" :value="$category->sort_order ?? 0" min="0" />
            <x-admin.check name="show_in_menu" label="Show in “Shop by Category” menu" :checked="$category->show_in_menu" />
            <x-admin.check name="is_active" label="Active" :checked="$category->is_active" />
            <div class="form-actions"><button class="btn">Save</button><a href="{{ route('admin.categories.index') }}">Cancel</a></div>
        </div>
        <div class="card">
            <x-admin.image name="image" label="Banner image" :path="$category->image" />
        </div>
    </form>
@endsection
