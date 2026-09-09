@extends('admin.layouts.app')

@section('content')
    <div class="mb-3 d-flex justify-content-between">
        <h3>@lang('Manage Website Sections')</h3>
        <div class="search-container">
            <input type="text" id="sectionSearch" class="form-control" placeholder="Search sections by key or title...">
        </div>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>@lang('Display Title')</th>
                    <th>@lang('Section Key')</th>
                    <th class="text-end">@lang('Actions')</th>
                </tr>
            </thead>
            <tbody id="sectionsTableBody">
                @forelse ($sections as $key => $section)
                    <tr class="section-row">
                        <td data-label="@lang('Display Title')" class="section-title">{{ $section['title'] ?? ucfirst($key) }}</td>
                        <td data-label="@lang('Section Key')" class="section-key">{{ $key }}</td>
                        <td data-label="@lang('Actions')" class="text-end">
                            <x-button href="{{ route('admin.website.section.edit', $key) }}" class="btn btn-sm btn-info"
                                title="{{ __('Edit') }} {{ $section['title'] ?? ucfirst($key) }}">
                                <x-icons.edit />
                                @lang('Edit')
                            </x-button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center no-data-message">@lang('No sections found.')</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div id="noResultsMessage" class="text-center no-data-message" style="display: none; padding: 20px;">
            @lang('No sections match your search criteria.')
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .search-container {
            width: 300px;
        }

        .no-data-message,
        #noResultsMessage {
            color: #777;
            font-style: italic;
            padding: 20px;
        }

        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .search-container {
                width: 100%;
                margin-top: 15px;
            }

            .sections-table thead {
                display: none;
            }

            .sections-table,
            .sections-table tbody,
            .sections-table tr,
            .sections-table td {
                display: block;
                width: 100%;
            }

            .sections-table tr {
                margin-bottom: 15px;
                border: 1px solid #e0e0e0;
                border-radius: 4px;
            }

            .sections-table td {
                text-align: right;
                padding-left: 50%;
                position: relative;
                border-bottom: none;
            }

            .sections-table td:last-child {
                border-bottom: none;
            }

            .sections-table td::before {
                content: attr(data-label);
                position: absolute;
                left: 10px;
                width: calc(50% - 20px);
                padding-right: 10px;
                font-weight: bold;
                text-align: left;
                white-space: nowrap;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            $('#sectionSearch').on('keyup input', function() {
                var searchTerm = $(this).val().toLowerCase().trim();
                var $rows = $('#sectionsTableBody tr.section-row');
                var $noResultsMessage = $('#noResultsMessage');
                var $originalNoDataMessage = $(
                    '.no-data-message:not(#noResultsMessage)'); // The one for empty table
                var visibleRowCount = 0;

                if (searchTerm === '') {
                    $rows.show();
                    $noResultsMessage.hide();

                    if ($rows.length === 0) {
                        $originalNoDataMessage.show();
                    } else {
                        $originalNoDataMessage.hide(); // Should not be visible if there are rows
                    }
                    return;
                }

                $rows.each(function() {
                    var $row = $(this);
                    var keyText = $row.find('.section-key').text().toLowerCase();
                    var titleText = $row.find('.section-title').text().toLowerCase();

                    if (keyText.includes(searchTerm) || titleText.includes(searchTerm)) {
                        $row.show();
                        visibleRowCount++;
                    } else {
                        $row.hide();
                    }
                });

                if (visibleRowCount > 0) {
                    $noResultsMessage.hide();
                    $originalNoDataMessage.hide();
                } else {
                    if ($rows.length > 0) {
                        $noResultsMessage.show();
                    }

                    $originalNoDataMessage.hide();
                }
            });

            if ($('#sectionsTableBody tr.section-row').length === 0 && $('#sectionsTableBody .no-data-message')
                .length > 0) {
                $('#sectionsTableBody .no-data-message').show();
            } else {
                $('#sectionsTableBody .no-data-message').hide();
            }
        });
    </script>
@endpush
