@props(['name', 'label', 'options' => [], 'value' => null, 'placeholder' => null, 'help' => null])
@php($current = (string) old($name, $value))
<label class="field {{ $errors->has($name) ? 'has-error' : '' }}">
    <span>{{ $label }}</span>
    <select name="{{ $name }}" {{ $attributes }}>
        @if ($placeholder !== null)<option value="">{{ $placeholder }}</option>@endif
        @foreach ($options as $optValue => $optLabel)
            <option value="{{ $optValue }}" @selected($current === (string) $optValue)>{{ $optLabel }}</option>
        @endforeach
    </select>
    @if ($help)<small>{{ $help }}</small>@endif
    @error($name)<small class="error">{{ $message }}</small>@enderror
</label>
