@props(['name', 'label', 'checked' => false])
{{-- Hidden zero so an unticked box still submits a value. --}}
<input type="hidden" name="{{ $name }}" value="0">
<label class="check">
    <input type="checkbox" name="{{ $name }}" value="1" @checked(old($name, $checked)) {{ $attributes }}>
    {{ $label }}
</label>
