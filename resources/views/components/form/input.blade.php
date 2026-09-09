@props([
    'type' => 'text',
    'name',
    'value' => null,
])

<input 
    type="{{ $type }}" 
    name="{{ $name }}" 
    id="{{ $id ?? $name }}"
    value="{{ old($name, $value) }}"
    {{ $attributes->merge(['class' => 'form-control']) }} 
/>
