@extends('admin.layout')
@section('title', $slide->exists ? 'Edit slide' : 'New slide')
@section('content')
    <div class="page-head"><h1>{{ $slide->exists ? 'Edit slide' : 'New slide' }}</h1><a href="{{ route('admin.slides.index') }}">← Back</a></div>
    <form method="post" enctype="multipart/form-data" action="{{ $slide->exists ? route('admin.slides.update', $slide) : route('admin.slides.store') }}" class="grid-form">
        @csrf @if ($slide->exists) @method('put') @endif
        <div class="card">
            <x-admin.input name="eyebrow" label="Small heading" :value="$slide->eyebrow" help="e.g. The Festive Edit" />
            <x-admin.input name="title" label="Title" :value="$slide->title" required />
            <x-admin.input name="subtitle" label="Sub-title" :value="$slide->subtitle" />
            <div class="row">
                <x-admin.input name="cta_text" label="Button text" :value="$slide->cta_text" required />
                <x-admin.input name="cta_link" label="Button link" :value="$slide->cta_link" required help="e.g. /collections/ethnic" />
            </div>
            <div class="row">
                <x-admin.select name="theme" label="Background colour" :options="$themes" :value="$slide->theme" />
                <x-admin.input name="sort_order" type="number" label="Display order" :value="$slide->sort_order ?? 0" min="0" />
            </div>
            <x-admin.check name="is_active" label="Visible on storefront" :checked="$slide->is_active" />
            <div class="form-actions"><button class="btn">Save slide</button><a href="{{ route('admin.slides.index') }}">Cancel</a></div>
        </div>
        <div class="card">
            <x-admin.image name="image" label="Banner image (desktop)" :path="$slide->image" wide help="Recommended 1600 × 600 px. Shown instead of the product illustration." />
            <x-admin.image name="mobile_image" label="Banner image (mobile, optional)" :path="$slide->mobile_image" wide help="Recommended 800 × 900 px." />
            <x-admin.select name="product_id" label="Featured product (used when no banner image)" :options="$products" :value="$slide->product_id" placeholder="— None —" />
        </div>
    </form>
@endsection
