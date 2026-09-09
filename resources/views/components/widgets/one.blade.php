@props(['title', 'value', 'icon', 'subtitle' => null, 'link' => null])

<a @if ($link) href="{{ $link }}" @endif class="widget one">
    <div class="widget-icon">
        <x-dynamic-component :component="'icons.'.$icon" />
    </div>
    <div class="widget-content">
        <h3>{{ $value }}</h3>
        <p>{{ $title }}</p>
        @if($subtitle)
        <small>{{ $subtitle }}</small>
        @endif
    </div>
</a>
