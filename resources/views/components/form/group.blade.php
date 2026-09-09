@props([
    'for' => '',
    'label' => '',
])

<div class="mb-3">
    @if ($label)
        <label for="{{ $for }}" class="form-label">{{ __($label) }}</label>
    @endif

    {{ $slot }}

    @error($for)
        <small class="text-danger d-block mt-1">{{ $message }}</small>
    @enderror
</div>
