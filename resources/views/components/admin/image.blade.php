@props(['name', 'label', 'path' => null, 'help' => 'JPG, PNG or WebP, up to 4 MB.', 'wide' => false])
<div class="field {{ $errors->has($name) ? 'has-error' : '' }}">
    <span>{{ $label }}</span>
    @if ($path)
        <img src="{{ \App\Support\Media::url($path) }}" alt="" class="image-preview {{ $wide ? 'wide' : '' }}">
        <label class="check small"><input type="checkbox" name="remove_{{ $name }}" value="1"> Remove current image</label>
    @endif
    <input type="file" name="{{ $name }}" accept="image/*">
    @if ($help)<small>{{ $help }}</small>@endif
    @error($name)<small class="error">{{ $message }}</small>@enderror
</div>
