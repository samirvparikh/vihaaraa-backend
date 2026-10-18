@props(['name', 'label', 'value' => null, 'type' => 'text', 'help' => null])
@php($key = str_replace(['[', ']'], ['.', ''], $name))
<label class="field {{ $errors->has($key) ? 'has-error' : '' }}">
    <span>{{ $label }}</span>
    <input type="{{ $type }}" name="{{ $name }}" value="{{ $type === 'password' ? '' : old($key, $value) }}" {{ $attributes }}>
    @if ($help)<small>{{ $help }}</small>@endif
    @error($key)<small class="error">{{ $message }}</small>@enderror
</label>
