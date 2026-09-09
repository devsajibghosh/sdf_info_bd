@extends('admin.layouts.app')

@section('title', 'Edit Page Layout: ' . $page->title)

@section('content')
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h3 class="mb-3 text-xl">@lang('Edit Page')</h3>

        <x-button href="{{ route('admin.website.page.list') }}">
            <x-icons.back-v1 />
            @lang('Back')
        </x-button>
    </div>

    <div class="container-fluid page-layout-editor">
        <form method="POST" action="{{ route('admin.website.page.update', $page->id) }}" id="pageLayoutForm">
            @csrf
            @if ($page->privacy)
                <div class="form-group">
                    <label for="content" class="form-label">@lang('Page Content')</label>
                    <textarea class="form-control" name="content" id="content-editor" rows="6" placeholder="@lang('Enter page content')">{{ old('body', $page->content ?? '') }}</textarea>
                </div>

                <div class="text-end mt-3">
                    <x-button type="submit">
                        <x-icons.save />
                        @lang('Save')
                    </x-button>
                </div>
            @else
                <x-card class="mb-4">
                    @if (!$page->is_default)
                        <div class="form-group mb-3">
                            <label for="title" class="form-label">
                                @lang('Page Title')
                            </label>
                            <input type="text" value="{{ old('title', $page->title) }}" class="form-control"
                                name="title" id="title" />
                        </div>
                    @endif

                    <h6 class="mb-3">@lang('SEO Settings')</h6>

                    <div class="form-group mb-3">
                        <label for="meta_title" class="form-label">@lang('Meta Title')</label>
                        <input type="text" name="seo_content[meta_title]" id="meta_title" class="form-control"
                            value="{{ old('seo_content.meta_title', $page->seo_content->meta_title ?? '') }}">
                    </div>

                    <div class="form-group mb-3">
                        <label for="meta_description" class="form-label">@lang('Meta Description')</label>
                        <textarea placeholder="@lang('Enter meta description for this page')" name="seo_content[meta_description]" id="meta_description"
                            class="form-control" rows="3">{{ old('seo_content.meta_description', $page->seo_content->meta_description ?? '') }}</textarea>
                    </div>

                    <div class="form-group mb-3">
                        <label for="meta_keywords" class="form-label">@lang('Meta Keywords')</label>
                        <input placeholder="@lang('Add meta keywords separted by commas')" type="text" name="seo_content[meta_keywords]"
                            id="meta_keywords" class="form-control"
                            value="{{ old('seo_content.meta_keywords', $page->seo_content->meta_keywords ?? '') }}" />
                    </div>
                </x-card>

                <div class="row">
                    <div class="col-md-7">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title mb-0">
                                    @lang('Page Layout'): {{ $page->title }}
                                </h4>
                                <small class="text-muted">@lang('Drag sections from the right panel here and arrange them in the desired order.')</small>
                            </div>
                            <div class="card-body">
                                <div id="pageSectionsList" class="list-group sortable-list selected-sections-list">
                                    @php
                                        $pageSectionsOrder = $page->sections ?? [];
                                        $allSectionsConfig = System::sections();
                                    @endphp
                                    @if (!empty($pageSectionsOrder))
                                        @foreach ($pageSectionsOrder as $sectionKey)
                                            @if (isset($allSectionsConfig[$sectionKey]))
                                                @php $sectionConfig = $allSectionsConfig[$sectionKey]; @endphp
                                                <div class="list-group-item sortable-item"
                                                    data-section-key="{{ $sectionKey }}">
                                                    <div class="item-content">
                                                        {{-- <i class="bi bi-grip-vertical item-handle me-2"></i> --}}
                                                        <strong
                                                            class="item-title">{{ $sectionConfig['name'] ?? ucfirst(str_replace('_', ' ', $sectionKey)) }}</strong>
                                                        <small
                                                            class="item-key text-muted d-block">{{ $sectionKey }}</small>
                                                    </div>
                                                    <button type="button"
                                                        class="btn btn-sm btn-outline-danger remove-section-btn ms-auto">
                                                        {{-- <i class="bi bi-x-lg"></i> --}} @lang('Remove')
                                                    </button>
                                                    <input type="hidden" name="ordered_sections[]"
                                                        value="{{ $sectionKey }}">
                                                </div>
                                            @endif
                                        @endforeach
                                    @else
                                        <div class="empty-list-placeholder p-4 text-center text-muted">
                                            {{-- <i class="bi bi-plus-square-dotted fs-1 mb-2"></i> --}}
                                            <p>@lang('Drag sections here to build your page.')</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Right Column: Available Sections (Source for Dragging) --}}
                    <div class="col-md-5">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title mb-0">
                                    {{-- <i class="bi bi-puzzle me-2"></i> --}}
                                    @lang('Available Sections')
                                </h4>
                            </div>
                            <div class="card-body">
                                <div id="availableSectionsList" class="list-group sortable-list available-sections-list">
                                    @foreach ($sections as $key => $sectionConfig)
                                        <div class="list-group-item sortable-item available-item"
                                            data-section-key="{{ $key }}">
                                            <div class="item-content">
                                                {{-- <i class="bi bi-grip-vertical item-handle me-2"></i> --}}
                                                <strong
                                                    class="item-title">{{ $sectionConfig['name'] ?? ucfirst(str_replace('_', ' ', $key)) }}</strong>
                                                <small class="item-key text-muted d-block">{{ $key }}</small>
                                            </div>
                                            {{-- No remove button here, these are just sources --}}
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-actions-footer mt-4">
                    <a href="{{ route('admin.website.page.list') }}" class="btn btn-outline-secondary">
                        @lang('Cancel')
                    </a>

                    <x-button type="submit">
                        <x-icons.save />
                        @lang('Save')
                    </x-button>
                </div>
            @endif

        </form> {{-- End of form --}}
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/shared/css/tagify.css') }}">
    <style>
        .page-layout-editor .card-title {
            font-weight: 600;
        }

        .sortable-list {
            min-height: 150px;
            /* Ensure drop area is visible */
            border: 1px dashed #ccc;
            border-radius: 0.375rem;
            padding: 10px;
            background-color: #f8f9fa;
        }

        .selected-sections-list.sortable-list-empty {
            /* Style when empty */
            /* display: flex;
                                            align-items: center;
                                            justify-content: center; */
        }

        .sortable-item {
            padding: 0.75rem 1rem;
            margin-bottom: 0.5rem;
            background-color: #fff;
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
            cursor: grab;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            transition: background-color 0.2s ease, box-shadow 0.2s ease;
        }

        .sortable-item:last-child {
            margin-bottom: 0;
        }

        .sortable-item .item-content {
            flex-grow: 1;
        }

        .sortable-item .item-title {
            font-weight: 500;
        }

        .sortable-item .item-key {
            font-size: 0.8em;
        }

        .item-handle {
            color: #adb5bd;
            margin-right: 0.5rem;
        }

        .sortable-item:hover {
            background-color: #f1f3f5;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .sortable-ghost {
            /* Class applied by SortableJS to the dragged item's placeholder */
            opacity: 0.4;
            background: #c8ebfb;
            border-style: dashed;
        }

        .sortable-chosen {
            /* Class applied to the item being dragged */
            background: #e9ecef;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .available-item:hover {
            cursor: grab;
        }

        .remove-section-btn {
            font-size: 0.8rem !important;
            padding: 0.2rem 0.5rem !important;
            opacity: 0.6;
            transition: opacity 0.2s ease;
        }

        .sortable-item:hover .remove-section-btn {
            opacity: 1;
        }

        .empty-list-placeholder {
            color: #6c757d;
        }

        .empty-list-placeholder i {
            font-size: 2.5rem;
            display: block;
        }

        /* If using Bootstrap icons, uncomment these */
        /* @import url("https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css"); */

        .form-actions-footer {
            /* Copied from previous example, ensure consistency */
            background-color: #f8f9fa;
            padding: 1rem 1.5rem;
            border-top: 1px solid #dee2e6;
            text-align: end;
            margin-top: 1.5rem;
            /* Adjust margins if your main card has padding */
            margin-left: calc(var(--bs-gutter-x) * -0.5);
            margin-right: calc(var(--bs-gutter-x) * -0.5);
            margin-bottom: calc(var(--bs-gutter-x) * -0.5);
            /* Assuming main container is .container-fluid */
            border-bottom-left-radius: var(--bs-card-inner-border-radius);
            border-bottom-right-radius: var(--bs-card-inner-border-radius);
        }

        .form-actions-footer .btn {
            min-width: 120px;
            font-size: 0.9rem;
            padding: 0.5rem 1.2rem;
            font-weight: 500;
            border-radius: 0.375rem;
            /* premium-border-radius */
            margin-left: 0.5rem;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.ckeditor.com/ckeditor5/12.3.1/classic/ckeditor.js"></script>
    <script src="{{ asset('assets/shared/js/tagify.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            SystemHelper.initEditor('#content-editor');

            var input = document.querySelector('[name="seo_content[meta_keywords]"]');

            new Tagify(input)

            const pageSectionsListEl = document.getElementById('pageSectionsList');
            const availableSectionsListEl = document.getElementById('availableSectionsList');
            const pageLayoutForm = document.getElementById('pageLayoutForm');
            const emptyPlaceholderHtml = `<div class="empty-list-placeholder p-4 text-center text-muted">
                                    {{-- <i class="bi bi-plus-square-dotted fs-1 mb-2"></i> --}}
                                    <p>Drag sections here to build your page.</p>
                                 </div>`;

            function updateHiddenInputs() {
                if (pageSectionsListEl.children.length === 1 && pageSectionsListEl.firstElementChild.classList
                    .contains('empty-list-placeholder')) {} else if (pageSectionsListEl.children.length === 0 || (
                        pageSectionsListEl.children.length > 0 &&
                        pageSectionsListEl.querySelector('.sortable-item') === null)) {
                    pageSectionsListEl.innerHTML = emptyPlaceholderHtml;
                } else {
                    // Remove placeholder if items exist
                    const placeholder = pageSectionsListEl.querySelector('.empty-list-placeholder');
                    if (placeholder) {
                        placeholder.remove();
                    }
                }
            }

            // Initialize Sortable for AVAILABLE sections (Right Panel)
            new Sortable(availableSectionsListEl, {
                group: {
                    name: 'sectionsGroup',
                    pull: 'clone', // Clone items when dragging to the other list
                    put: false // Do not allow items to be dropped back into this list
                },
                animation: 150,
                sort: false, // Do not sort items in the available list itself
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                // handle: '.item-handle', // Uncomment if you add a specific drag handle
            });

            // Initialize Sortable for PAGE sections (Left Panel)
            const pageSectionsSortable = new Sortable(pageSectionsListEl, {
                group: 'sectionsGroup', // Same group name to allow dropping
                animation: 150,
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                // handle: '.item-handle', // Uncomment if you add a specific drag handle
                onAdd: function(evt) {
                    const itemEl = evt.item; // The dragged element from available list
                    const sectionKey = itemEl.dataset.sectionKey;
                    const sectionConfig = @json($sections); // Get all section configs
                    const sectionName = sectionConfig[sectionKey]?.name || sectionKey.replace(/_/g, ' ')
                        .replace(/\b\w/g, l => l.toUpperCase());

                    // We need to transform the cloned item from "Available" to a "Page Section" item
                    // This includes adding a remove button and the hidden input
                    itemEl.classList.remove(
                        'available-item'); // Remove class specific to available items

                    // Create the inner structure for the new item in the selected list
                    itemEl.innerHTML = `
                <div class="item-content">
                    {{-- <i class="bi bi-grip-vertical item-handle me-2"></i> --}}
                    <strong class="item-title">${sectionName}</strong>
                    <small class="item-key text-muted d-block">${sectionKey}</small>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger remove-section-btn ms-auto">
                    {{-- <i class="bi bi-x-lg"></i> --}} Remove
                </button>
                <input type="hidden" name="ordered_sections[]" value="${sectionKey}">
            `;
                    updateHiddenInputs(); // Check empty state
                },
                onUpdate: function(evt) {
                    // Called when sorting within the list is completed
                    // No specific action needed here if hidden inputs are part of items and DOM order is used
                    updateHiddenInputs
                        (); // Check empty state (shouldn't be necessary on reorder but good practice)
                },
                onRemove: function(evt) {
                    // This is called if an item is dragged *out* of this list.
                    // We don't want that, but if it happens, update state.
                    updateHiddenInputs();
                }
            });

            // Event listener for REMOVE button
            pageSectionsListEl.addEventListener('click', function(e) {
                if (e.target && (e.target.classList.contains('remove-section-btn') || e.target.closest(
                        '.remove-section-btn'))) {
                    e.target.closest('.sortable-item').remove();
                    updateHiddenInputs(); // Update after removal and check empty state
                }
            });

            // Call initially to set placeholder if list is empty
            updateHiddenInputs();
        });
    </script>
@endpush
