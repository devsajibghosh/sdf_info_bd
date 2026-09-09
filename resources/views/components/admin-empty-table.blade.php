@props([
    'title' => __('No data available'),
    'message' => __('There is no data to display at the moment.'),
    'button_text' => null,
    'button_route' => null,
])

<tr>
    <td colspan="100%">
        <div class="text-center py-5">
            <div class="mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-database">
                    <ellipse cx="12" cy="5" rx="9" ry="3"></ellipse>
                    <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path>
                    <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path>
                </svg>
            </div>
            <h5 class="mb-1">{{ $title }}</h5>
            <p class="text-muted mb-3">{{ $message }}</p>

            @if ($button_text && $button_route)
                <a href="{{ $button_route }}" class="btn btn-primary">
                    {{ $button_text }}
                </a>
            @endif
        </div>
    </td>
</tr>

@pushOnce('styles')
    <style>
        .feather {
            color: #adb5bd;
        }
        .text-center h5 {
            font-weight: 500;
        }
    </style>
@endPushOnce