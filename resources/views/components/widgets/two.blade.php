@props(['title', 'value', 'icon', 'progress' => 50, 'color' => '#3b82f6'])

<div class="widget two">
    <div class="widget-icon">
        <x-icons.{{ $icon }} />
    </div>
    <div class="widget-content">
        <h3>{{ $value }}</h3>
        <p>{{ $title }}</p>
        <div class="progress-bar">
            <div class="progress-fill" style="width: {{ $progress }}%; background-color: {{ $color }};"></div>
        </div>
    </div>
</div>
