@props([
    'id'     => 'modalId',
    'title'  => 'Modal Title',
    'form'   => false,
    'method' => 'POST',
    'action' => '',
    'size'   => 'md'
])

<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}Label" aria-hidden="true">
    <div class="modal-dialog modal-{{ $size }}">
        <div class="modal-content">

            @if($form)
                <form method="{{ $method }}" action="{{ $action }}" enctype="multipart/form-data">
            @endif

            <div class="modal-header">
                <h1 class="modal-title fs-5" id="{{ $id }}Label">{{ $title }}</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                {{ $slot }}
            </div>

            <div class="modal-footer">
                {{ $footer ?? '' }}
            </div>

            @if($form)
                </form>
            @endif

        </div>
    </div>
</div>
