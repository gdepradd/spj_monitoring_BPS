@props(['value'])
<label {{ $attributes->merge(['class' => 'block text-sm font-medium text-ui-text']) }}>
    {{ $value ?? $slot }}
</label>
