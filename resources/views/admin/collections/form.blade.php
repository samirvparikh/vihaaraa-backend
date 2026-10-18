@extends('admin.layout')
@section('title', $collection->exists ? 'Edit collection' : 'New collection')
@section('content')
    <div class="page-head"><h1>{{ $collection->exists ? 'Edit '.$collection->title : 'New collection' }}</h1><a href="{{ route('admin.collections.index') }}">← Back</a></div>
    <form method="post" enctype="multipart/form-data" action="{{ $collection->exists ? route('admin.collections.update', $collection) : route('admin.collections.store') }}">
        @csrf @if ($collection->exists) @method('put') @endif
        <div class="grid-form">
            <div class="card">
                <div class="row">
                    <x-admin.input name="title" label="Title" :value="$collection->title" required />
                    <x-admin.input name="handle" label="URL handle" :value="$collection->handle" help="Leave blank to generate from the title." />
                </div>
                <x-admin.input name="blurb" label="Short description" :value="$collection->blurb" />
                <div class="row">
                    <x-admin.select name="rule" label="Which products?" :options="\App\Models\Collection::RULES" :value="$collection->rule" id="rule" />
                    <x-admin.select name="menu_group" label="Menu group" :options="\App\Models\Collection::MENU_GROUPS" :value="$collection->menu_group" placeholder="— Not in menu —" />
                </div>
                <x-admin.input name="sort_order" type="number" label="Display order" :value="$collection->sort_order ?? 0" min="0" />
                <x-admin.check name="is_active" label="Active" :checked="$collection->is_active" />
            </div>
            <div class="card">
                <x-admin.image name="image" label="Banner image" :path="$collection->image" />
            </div>
        </div>
        <div class="card" id="manual-products">
            <h2>Products in this collection</h2>
            <p class="muted small">Used when “Which products?” is set to manual. You can also tick collections on each product's page.</p>
            <input type="search" placeholder="Filter products…" style="max-width:320px;margin-bottom:12px" oninput="var q=this.value.toLowerCase();document.querySelectorAll('#manual-products .check').forEach(function(l){l.style.display=l.textContent.toLowerCase().includes(q)?'':'none'})">
            <div class="checks">
                @php($sel = old('products', $selected))
                @foreach ($products as $p)
                    <label class="check small">
                        <input type="checkbox" name="products[]" value="{{ $p->id }}" @checked(in_array($p->id, $sel))>
                        {{ $p->name }} <span class="muted">{{ $p->sku }}{{ $p->is_active ? '' : ' · hidden' }}</span>
                    </label>
                @endforeach
            </div>
        </div>
        <div class="form-actions"><button class="btn">Save collection</button><a href="{{ route('admin.collections.index') }}">Cancel</a></div>
    </form>
    <script>
        (function () {
            var rule = document.getElementById('rule'), box = document.getElementById('manual-products');
            function sync() { box.style.display = rule.value === 'manual' ? '' : 'none'; }
            rule.addEventListener('change', sync); sync();
        })();
    </script>
@endsection
