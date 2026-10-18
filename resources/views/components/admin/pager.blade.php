@props(['items'])
@if ($items->hasPages())
    <nav class="pager">
        <span class="muted" style="border:0;background:none">Showing {{ $items->firstItem() }}–{{ $items->lastItem() }} of {{ $items->total() }}</span>
        @if ($items->onFirstPage())<span class="disabled">‹ Prev</span>@else<a href="{{ $items->previousPageUrl() }}">‹ Prev</a>@endif
        <span class="current">{{ $items->currentPage() }} / {{ $items->lastPage() }}</span>
        @if ($items->hasMorePages())<a href="{{ $items->nextPageUrl() }}">Next ›</a>@else<span class="disabled">Next ›</span>@endif
    </nav>
@endif
