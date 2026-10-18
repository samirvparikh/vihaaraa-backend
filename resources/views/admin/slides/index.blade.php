@extends('admin.layout')
@section('title', 'Image Slider')
@section('content')
    <div class="page-head">
        <div><h1>Image Slider</h1><p>The large rotating banner at the top of the home page.</p></div>
        <a class="btn" href="{{ route('admin.slides.create') }}">+ Add slide</a>
    </div>
    <div class="card table-wrap" style="padding:0">
        <table class="table">
            <thead><tr><th>#</th><th>Image</th><th>Slide</th><th>Button</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse ($slides as $s)
                <tr>
                    <td>{{ $s->sort_order }}</td>
                    <td>
                        @if ($s->image)<img class="thumb" style="width:90px" src="{{ \App\Support\Media::url($s->image) }}" alt="">
                        @else<span class="small muted">Illustration of<br>{{ $s->product?->name ?? '—' }}</span>@endif
                    </td>
                    <td><span class="small muted">{{ $s->eyebrow }}</span><br><strong>{{ $s->title }}</strong><div class="small muted">{{ $s->subtitle }}</div></td>
                    <td>{{ $s->cta_text }}<div class="small muted">{{ $s->cta_link }}</div></td>
                    <td><span class="badge {{ $s->is_active ? 'ok' : 'off' }}">{{ $s->is_active ? 'Visible' : 'Hidden' }}</span></td>
                    <td class="actions">
                        <a class="btn outline sm" href="{{ route('admin.slides.edit', $s) }}">Edit</a>
                        <form method="post" action="{{ route('admin.slides.destroy', $s) }}" data-confirm="Delete this slide?">@csrf @method('delete')<button class="btn danger sm">Delete</button></form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="muted">No slides yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
