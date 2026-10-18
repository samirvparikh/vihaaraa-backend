@props(['name', 'label', 'value' => null, 'help' => null])
<label class="field {{ $errors->has($name) ? 'has-error' : '' }}">
    <span>{{ $label }}</span>
    <textarea name="{{ $name }}" {{ $attributes }}>{{ old($name, $value) }}</textarea>
    @if ($help)<small>{{ $help }}</small>@endif
    @error($name)<small class="error">{{ $message }}</small>@enderror
</label>
