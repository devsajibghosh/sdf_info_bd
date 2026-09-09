@php
    $tag = 'button';
    if (isset($attributes['href'])) $tag = 'a';

    $confirmDelete = isset($attributes['confirmDelete']) ? true : false;
    $buttonId = 'btn-' . uniqid();
@endphp

@if ($confirmDelete && $attributes->get('href'))
    <form id="form-{{ $buttonId }}" method="POST" action="{{ $attributes->get('href') }}" style="display:inline;">
        @csrf
        <button
            type="button"
            {{ $attributes->except(['href', 'confirmDelete'])->merge(['class' => 'btn btn-outline-info v2-btn']) }}
            data-bs-toggle="modal"
            data-bs-target="#confirmDeleteModal-{{ $buttonId }}"
        >
            {{ $slot }}
        </button>
    </form>

    <div class="modal fade" id="confirmDeleteModal-{{ $buttonId }}" tabindex="-1" aria-labelledby="confirmDeleteModalLabel-{{ $buttonId }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="confirmDeleteModalLabel-{{ $buttonId }}">@lang('Confirm')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @lang('Are you sure you to do this action? This action cannot be undone.')
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('Cancel')</button>
                    <button type="button" class="btn btn-danger" onclick="document.getElementById('form-{{ $buttonId }}').submit()">
                        @lang('Yes')
                    </button>
                </div>
            </div>
        </div>
    </div>
@else
    <{{ $tag }} {{ $attributes->merge(['class' => 'btn btn-outline-info v2-btn']) }}>
        {{ $slot }}
    </{{ $tag }}>
@endif
