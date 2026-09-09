@extends('admin.layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="mb-3 d-flex justify-content-between">
            <h3>{{ $title }}</h3>

            <div class="d-flex align-items-center gap-2">
                <x-button href="{{ route('admin.website.section.list') }}">
                    <x-icons.list />
                    @lang('Section List')
                </x-button>
            </div>
        </div>


        <div class="row">
            <div class="col-12">
                <div class="card page-content-card">
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.website.section.update', $sectionKey) }}"
                            enctype="multipart/form-data" id="sectionEditForm">
                            @csrf

                            @foreach ($sectionConfig['config'] as $fieldKey => $field)
                                <div class="field-block card shadow-sm mb-4">
                                    <div class="field-block-header card-header bg-light border-bottom">
                                        <h6 class="field-block-title mb-0 fw-semibold">
                                            {{ $field['label'] ?? ucfirst(str_replace('_', ' ', $fieldKey)) }}
                                            @if ($field['type'] === 'image' && isset($field['size']))
                                                <small class="text-muted fw-normal ms-1"> (Recommended:
                                                    {{ $field['size'] }})</small>
                                            @endif
                                        </h6>
                                    </div>
                                    <div class="field-block-content card-body">
                                        {{-- Text input --}}
                                        @if ($field['type'] === 'text')
                                            <div class="form-group">
                                                <input type="text" id="content_{{ $fieldKey }}"
                                                    name="content[{{ $fieldKey }}]" class="form-control"
                                                    value="{{ old('content.' . $fieldKey, $contentData[$fieldKey] ?? '') }}">
                                            </div>

                                            {{-- Textarea --}}
                                        @elseif($field['type'] === 'textarea')
                                            <div class="form-group">
                                                <textarea id="content_{{ $fieldKey }}" name="content[{{ $fieldKey }}]" class="form-control" rows="5">{{ old('content.' . $fieldKey, $contentData[$fieldKey] ?? '') }}</textarea>
                                            </div>

                                            {{-- Single image --}}
                                        @elseif($field['type'] === 'image')
                                            @php
                                                $currentImage = $contentData[$fieldKey] ?? null;
                                            @endphp
                                            <div class="form-group">
                                                @if ($currentImage)
                                                    <div class="mb-2 current-image-preview">
                                                        <img src="{{ asset($currentImage) }}"
                                                            alt="{{ $field['label'] ?? 'Current image' }}"
                                                            class="img-thumbnail" style="max-height: 150px;">
                                                    </div>
                                                @endif
                                                <input type="file" id="content_{{ $fieldKey }}"
                                                    name="content[{{ $fieldKey }}]" class="form-control">
                                                @if ($currentImage)
                                                    <small class="form-text text-muted mt-1">Upload a new image to replace
                                                        the current one.</small>
                                                @endif
                                            </div>

                                            {{-- Group fields --}}
                                        @elseif($field['type'] === 'group')
                                            <div class="field-group-container p-3 bg-white border rounded">
                                                @foreach ($field['fields'] as $childKey => $childField)
                                                    @php
                                                        $groupValue = $contentData[$fieldKey][$childKey] ?? null;
                                                    @endphp
                                                    <div class="form-group mb-3">
                                                        <label class="form-label"
                                                            for="content_{{ $fieldKey }}_{{ $childKey }}">{{ $childField['label'] ?? ucfirst(str_replace('_', ' ', $childKey)) }}
                                                            @if ($childField['type'] === 'image' && isset($childField['size']))
                                                                <small class="text-muted fw-normal"> (Recommended:
                                                                    {{ $childField['size'] }})</small>
                                                            @endif
                                                        </label>

                                                        @if ($childField['type'] === 'image')
                                                            @if ($groupValue)
                                                                <div class="mb-2 current-image-preview">
                                                                    <img src="{{ asset($groupValue) }}"
                                                                        alt="{{ $childField['label'] ?? 'Current image' }}"
                                                                        class="img-thumbnail" style="max-height: 100px;">
                                                                </div>
                                                            @endif
                                                            <input type="file"
                                                                id="content_{{ $fieldKey }}_{{ $childKey }}"
                                                                name="content[{{ $fieldKey }}][{{ $childKey }}]"
                                                                class="form-control">
                                                            @if ($groupValue)
                                                                <small class="form-text text-muted mt-1">Upload a new image
                                                                    to replace.</small>
                                                            @endif
                                                        @elseif($childField['type'] === 'textarea')
                                                            <textarea id="content_{{ $fieldKey }}_{{ $childKey }}"
                                                                name="content[{{ $fieldKey }}][{{ $childKey }}]" class="form-control" rows="3">{{ old("content.$fieldKey.$childKey", $groupValue ?? '') }}</textarea>
                                                        @else
                                                            {{-- Assuming text --}}
                                                            <input type="text"
                                                                id="content_{{ $fieldKey }}_{{ $childKey }}"
                                                                name="content[{{ $fieldKey }}][{{ $childKey }}]"
                                                                class="form-control"
                                                                value="{{ old("content.$fieldKey.$childKey", $groupValue ?? '') }}">
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>

                                            {{-- Repeater --}}
                                        @elseif($field['type'] === 'repeater')
                                            <div class="repeater-container" data-key="{{ $fieldKey }}">
                                                <div class="repeater-items-list">
                                                    @php
                                                        $items = old(
                                                            'content.' . $fieldKey,
                                                            $contentData[$fieldKey] ?? [],
                                                        );
                                                        $itemIndex = 0;
                                                    @endphp

                                                    @if (!empty($items))
                                                        @foreach ($items as $index => $itemData)
                                                            <div
                                                                class="repeater-item card card-outline card-secondary mb-3">
                                                                <div
                                                                    class="repeater-item-header card-header bg-light py-2 px-3">
                                                                    <h6 class="repeater-item-title mb-0 fw-medium">Item
                                                                        {{ $index + 1 }}</h6>
                                                                    <button type="button"
                                                                        class="btn btn-sm btn-outline-danger remove-repeater-item">
                                                                        {{-- <i class="bi bi-x-lg me-1"></i> --}} Remove
                                                                    </button>
                                                                </div>
                                                                <div class="repeater-item-content card-body">
                                                                    @foreach ($field['fields'] as $subKey => $subField)
                                                                        <div class="form-group mb-3"> {{-- Added mb-3 --}}
                                                                            <label class="form-label"
                                                                                for="content_{{ $fieldKey }}_{{ $index }}_{{ $subKey }}">{{ $subField['label'] ?? ucfirst(str_replace('_', ' ', $subKey)) }}
                                                                                @if ($subField['type'] === 'image' && isset($subField['size']))
                                                                                    <small class="text-muted fw-normal">
                                                                                        (Recommended:
                                                                                        {{ $subField['size'] }})
                                                                                    </small>
                                                                                @endif
                                                                            </label>
                                                                            @if ($subField['type'] === 'image')
                                                                                @php $repeaterImage = $itemData[$subKey] ?? null; @endphp
                                                                                @if ($repeaterImage && is_string($repeaterImage))
                                                                                    <div class="mb-2 current-image-preview">
                                                                                        <img src="{{ asset($repeaterImage) }}"
                                                                                            alt="{{ $subField['label'] ?? 'Current image' }}"
                                                                                            class="img-thumbnail"
                                                                                            style="max-height: 80px;">
                                                                                        <input type="hidden"
                                                                                            name="content[{{ $fieldKey }}][{{ $index }}][{{ $subKey . '_existing' }}]"
                                                                                            value="{{ $repeaterImage }}">
                                                                                    </div>
                                                                                @endif
                                                                                <input type="file"
                                                                                    id="content_{{ $fieldKey }}_{{ $index }}_{{ $subKey }}"
                                                                                    name="content[{{ $fieldKey }}][{{ $index }}][{{ $subKey }}]"
                                                                                    class="form-control">
                                                                                @if ($repeaterImage && is_string($repeaterImage))
                                                                                    <small
                                                                                        class="form-text text-muted mt-1">Upload
                                                                                        new to replace.</small>
                                                                                @endif
                                                                            @elseif ($subField['type'] === 'textarea')
                                                                                <textarea id="content_{{ $fieldKey }}_{{ $index }}_{{ $subKey }}"
                                                                                    name="content[{{ $fieldKey }}][{{ $index }}][{{ $subKey }}]" class="form-control"
                                                                                    rows="3">{{ $itemData[$subKey] ?? '' }}</textarea>
                                                                            @else
                                                                                {{-- Assuming text --}}
                                                                                <input type="text"
                                                                                    id="content_{{ $fieldKey }}_{{ $index }}_{{ $subKey }}"
                                                                                    name="content[{{ $fieldKey }}][{{ $index }}][{{ $subKey }}]"
                                                                                    class="form-control"
                                                                                    value="{{ $itemData[$subKey] ?? '' }}">
                                                                            @endif
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                            @php $itemIndex = $index + 1; @endphp
                                                        @endforeach
                                                    @endif
                                                </div>
                                                <button type="button" class="btn-outline-info v2-btn add-repeater-item mt-2"
                                                    data-template-id="repeater-template-{{ $fieldKey }}">
                                                    Add {{ $field['button_label'] ?? ($field['label'] ?? 'Item') }}
                                                </button>

                                                <script type="text/template" id="repeater-template-{{ $fieldKey }}">
                                                <div class="repeater-item card card-outline card-secondary mb-3">
                                                    <div class="repeater-item-header card-header bg-light py-2 px-3">
                                                        <h6 class="repeater-item-title mb-0 fw-medium">New Item</h6>
                                                        <button type="button" class="btn btn-sm btn-outline-danger remove-repeater-item">
                                                            {{-- <i class="bi bi-x-lg me-1"></i> --}} Remove
                                                        </button>
                                                    </div>
                                                    <div class="repeater-item-content card-body">
                                                    @foreach ($field['fields'] as $subKey => $subField)
                                                        <div class="form-group mb-3">
                                                            <label class="form-label" for="content_{{ $fieldKey }}___INDEX___{{ $subKey }}">{{ $subField['label'] ?? ucfirst(str_replace('_', ' ', $subKey)) }}
                                                                @if ($subField['type'] === 'image' && isset($subField['size']))
                                                                    <small class="text-muted fw-normal"> (Recommended: {{ $subField['size'] }})</small>
                                                                @endif
                                                            </label>
                                                            @if ($subField['type'] === 'image')
                                                                <input type="file" id="content_{{ $fieldKey }}___INDEX___{{ $subKey }}" name="content[{{ $fieldKey }}][__INDEX__][{{ $subKey }}]" class="form-control">
                                                            @elseif ($subField['type'] === 'textarea')
                                                                <textarea id="content_{{ $fieldKey }}___INDEX___{{ $subKey }}" name="content[{{ $fieldKey }}][__INDEX__][{{ $subKey }}]" class="form-control" rows="3"></textarea>
                                                            @else {{-- Assuming text --}}
                                                                <input type="text" id="content_{{ $fieldKey }}___INDEX___{{ $subKey }}" name="content[{{ $fieldKey }}][__INDEX__][{{ $subKey }}]" class="form-control" value="">
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                    </div>
                                                </div>
                                            </script>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach

                            <div class="form-actions-footer">
                                <a href="{{ route('admin.website.section.list') }}" class="btn btn-outline-secondary text-light btn-danger">
                                    <i class="fa fa-circle me-1"></i> @lang('Cancel')
                                </a>
                                <button type="submit" class="btn btn-outline-info v2-btn">
                                    <i class="fa fa-check me-2"></i> @lang('Save Changes')
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Initialize next-index for each repeater
            $('.repeater-container').each(function() {
                let $container = $(this);
                let existingItemCount = $container.find('.repeater-items-list .repeater-item').length;
                $container.data('next-index', existingItemCount);
            });

            $('body').on('click', '.add-repeater-item', function() {
                let $addButton = $(this);
                let $container = $addButton.closest('.repeater-container');
                let templateId = $addButton.data('template-id');
                let template = $('#' + templateId).html();
                let currentIndex = $container.data('next-index') || 0;

                let newItemHtml = template.replace(/__INDEX__/g, currentIndex).replace(/___INDEX___/g,
                    currentIndex);
                let $newItem = $(newItemHtml);

                // Update "New Item" to "Item X" for display
                let displayItemNumber = $container.find('.repeater-items-list .repeater-item').length + 1;
                $newItem.find('.repeater-item-title').first().text('Item ' + displayItemNumber);

                $container.find('.repeater-items-list').append($newItem);
                $container.data('next-index', currentIndex + 1); // Increment for the next new item
            });

            $('body').on('click', '.remove-repeater-item', function() {
                let $itemToRemove = $(this).closest('.repeater-item');
                let $list = $itemToRemove.closest('.repeater-items-list');
                $itemToRemove.remove();

                // After removing, re-number the remaining items for display consistency
                $list.find('.repeater-item').each(function(idx) {
                    $(this).find('.repeater-item-title').first().text('Item ' + (idx + 1));
                });
                // Note: The actual form field indices (e.g., content[fieldKey][0], content[fieldKey][1])
                // will become non-sequential on the backend if items are removed from the middle.
                // This is generally fine as Laravel handles arrays with non-sequential numeric keys.
                // If sequential indexing is strictly required on POST, more complex JS would be needed
                // to rename all subsequent fields on removal. For most use cases, this is not necessary.
            });
        });
    </script>
@endpush

@php
    function hex2rgb($hex)
    {
        $hex = str_replace('#', '', $hex);
        if (strlen($hex) == 3) {
            $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }
        return "$r, $g, $b";
    }
@endphp

@push('styles')
    <style>
        :root {
            --premium-border-color: #dee2e6;
            --premium-border-radius: 0.375rem;
            --premium-shadow-sm: 0 .125rem .25rem rgba(0, 0, 0, .075);
            --premium-shadow: 0 .5rem 1rem rgba(0, 0, 0, .15);
            --premium-text-muted: #6c757d;
        }

        .repeater-item-content.card-body {
            border: 1px solid #dee2e6;
            border-top: 0 !important;
        }

        .alert-danger {
            background-color: #f8d7da;
            border-color: #f5c2c7;
            color: #842029;
            border-left-width: 0.25rem;
            border-left-color: #b02a37; 
            border-radius: var(--premium-border-radius);
        }

        .alert-danger .alert-heading {
            color: inherit;
        }

        .alert-danger ul {
            margin-bottom: 0;
        }
 
        .field-block.card { 
            border: 1px solid var(--premium-border-color);
            border-radius: var(--premium-border-radius);
            box-shadow: var(--premium-shadow-sm) !important; 
        }

        .field-block .field-block-header.card-header {
            padding: 0.75rem 1.25rem;
            background-color: #f8f9fa; 
            border-bottom: 1px solid var(--premium-border-color);
        }

        .field-block .field-block-title {
            font-size: 0.95rem; 
            color: #343a40;
        }

        .field-block .field-block-title .text-muted {
            font-size: 0.8rem;
        }

        .field-block .field-block-content.card-body {
            padding: 1.25rem;
        }

        .current-image-preview img.img-thumbnail {
            border: 1px solid var(--premium-border-color);
            padding: 0.25rem;
            background-color: #fff;
            max-width: 100%;
            border-radius: var(--premium-border-radius);
        }
 
        .field-group-container { 
            margin-top: 0.5rem; 
        }

        .field-group-container .form-group:last-child {
            margin-bottom: 0 !important;
        }


        .repeater-container {
            margin-top: 0.5rem;
        }

        .repeater-item.card {
            border-color: #ced4da;
            /* Slightly darker for nested items */
            box-shadow: none;
            /* No shadow for nested items, or very light */
            border-radius: var(--premium-border-radius);
        }

        .repeater-item .repeater-item-header.card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #e9ecef;
            /* Header for repeater item */
            border-bottom: 1px solid #ced4da;
        }

        .repeater-item .repeater-item-title {
            font-weight: 500;
            font-size: 0.9rem;
            color: #495057;
        }

        .repeater-item .remove-repeater-item {
            font-size: 0.8rem !important;
            padding: 0.25rem 0.6rem !important;
            line-height: 1.4;
        }

        .repeater-item .remove-repeater-item:hover {
            background-color: rgba(var(--bs-danger-rgb), 0.1);
        }

        .repeater-item .repeater-item-content.card-body {
            padding: 1rem;
        }

        .repeater-item .repeater-item-content .form-group:last-child {
            margin-bottom: 0;
        }

        .add-repeater-item {
            font-weight: 500;
            padding: 0.4rem 0.8rem;
            font-size: 0.85rem;
        }

        .add-repeater-item:hover {
            background-color: rgba(var(--bs-primary-rgb), 0.1);
        }
 
        .form-actions-footer {
            background-color: #f8f9fa; 
            padding: 1rem 1.5rem;
            margin: 1.5rem -1.5rem -1.5rem -1.5rem;
            /* Extend to card edges */
            border-top: 1px solid var(--premium-border-color);
            text-align: end;
            border-bottom-left-radius: var(--premium-border-radius);
            /* Match main card */
            border-bottom-right-radius: var(--premium-border-radius);
            /* Match main card */
        }

        .form-actions-footer .btn {
            min-width: 120px;
            font-size: 0.9rem;
            padding: 0.5rem 1.2rem;
            font-weight: 500;
            border-radius: var(--premium-border-radius);
            margin-left: 0.5rem;
        }

        .form-actions-footer .btn-primary {
            background-color: rgb(var(--bs-primary-rgb));
            border-color: rgb(var(--bs-primary-rgb));
        }

        .form-actions-footer .btn-primary:hover {
            background-color: darken(rgb(var(--bs-primary-rgb)), 10%);
            /* Darken function not available in CSS, manually adjust */
            border-color: darken(rgb(var(--bs-primary-rgb)), 12%);
        }


        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .page-content-card>.card-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .page-content-card>.card-header .card-title {
                margin-bottom: 0.5rem;
            }

            .field-block .field-block-header.card-header {
                /* Could also stack title and recommended size if needed */
            }

            .repeater-item .repeater-item-header.card-header {
                /* Already flex, should adapt okay, or stack if text too long */
            }

            .form-actions-footer {
                text-align: center !important;
                padding: 1rem;
                margin-left: -1rem;
                margin-right: -1rem;
                margin-bottom: -1rem;
                /* Adjust if card-body padding changes on mobile */
            }

            .form-actions-footer .btn {
                width: 100%;
                margin-left: 0;
                margin-bottom: 0.5rem;
            }

            .form-actions-footer .btn:last-child {
                margin-bottom: 0;
            }
        }
    </style>
@endpush
