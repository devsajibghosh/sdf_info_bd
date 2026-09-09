<div class="mb-3 d-flex justify-content-between">
    @isset($title)
        <h3>{{ $title }}</h3>
    @endisset

    @isset($right)
        <div class="d-flex align-items-center gap-2">
            {{ $right ?? '' }}
        </div>
    @endisset
</div>
