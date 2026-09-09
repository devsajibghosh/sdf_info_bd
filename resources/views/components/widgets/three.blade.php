@props(['title', 'value', 'icon'])

<div class="widget three text-center">
    <div class="widget-icon center">
        <x-icons.{{ $icon }} />
    </div>
    <div class="widget-content">
        <h3>{{ $value }}</h3>
        <p>{{ $title }}</p>
    </div>
</div>
