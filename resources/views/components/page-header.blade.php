@props([
    'page_title' => '',
    'back_route' => null,
    'add_route'  => null,
    'search' => false
])

<div class="d-flex justify-content-between flex-wrap align-items-center mb-3">
    <h3 class="mb-3">
        {{ $page_title }}
    </h3>

    <div class="d-flex flex-wrap gap-2">
        @if($search)
            <x-admin-search />
        @endif

        {{ $slot }}
        
        @isset($add_route)
            <x-button href="{{ $add_route }}">
                <x-icons.add />
                @lang('Add New')
            </x-button>
        @endisset

        @isset($back_route)
            <x-button href="{{ $back_route }}?page={{ request()->get('page', 1) }}">
                <x-icons.back-v1 />
                @lang('Back')
            </x-button>
        @endisset

    </div>
</div>
