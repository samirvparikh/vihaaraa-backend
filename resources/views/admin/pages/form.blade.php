@extends('admin.layout')
@section('title', $page->exists ? 'Edit page' : 'New page')
@section('content')
    <div class="page-head"><h1>{{ $page->exists ? 'Edit page' : 'New page' }}</h1><a href="{{ route('admin.pages.index') }}">← Back</a></div>
    <form class="card" style="max-width:820px" method="post" action="{{ $page->exists ? route('admin.pages.update', $page) : route('admin.pages.store') }}">
        @csrf @if ($page->exists) @method('put') @endif
        <div class="row">
            <x-admin.input name="title" label="Title" :value="$page->title" required />
            <x-admin.input name="slug" label="URL slug" :value="$page->slug" required help="Letters, numbers and dashes — shown at /pages/your-slug" />
        </div>
        <x-admin.textarea name="body" label="Content" :value="$page->body" rows="14" required help="Plain text. Leave a blank line between paragraphs." />
        <x-admin.input name="sort_order" type="number" label="Display order" :value="$page->sort_order ?? 0" min="0" />
        <x-admin.check name="show_in_footer" label="Link in the footer “Care” column" :checked="$page->show_in_footer" />
        <x-admin.check name="is_active" label="Published" :checked="$page->is_active" />
        <div class="form-actions"><button class="btn">Save page</button><a href="{{ route('admin.pages.index') }}">Cancel</a></div>
    </form>
@endsection
