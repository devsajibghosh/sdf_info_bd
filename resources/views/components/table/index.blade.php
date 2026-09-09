@props([
    'striped'    => true,
    'nowrap'     => false,
    'hover'      => true,
    'responsive' => true,
])

@php
    $classes = Arr::toCssClasses([
        'table',
        'table-nowrap'  => $nowrap,
        'table-hover'   => $hover,
        'table-striped' => $striped,
    ]);
@endphp

@if($responsive) <div class="table-responsive"> @endif

<table {{ $attributes->merge(['class' => $classes]) }}>

    {{ $slot }}

</table>

@if($responsive) </div> @endif
