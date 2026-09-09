@extends('admin.layouts.app')

@section('content')
    <x-page-header :page_title="$title" />

    {{-- Filter Section --}}
    <div class="filter-card">
        <form action="{{ route('admin.report.user_by_area') }}" method="GET" class="filter-form">
            <div class="filter-item">
                <label for="division">@lang('Division')</label>
                <select name="division" id="division" class="form-select select2">
                    <option value="">@lang('All Divisions')</option>
                    {{-- Divisions are still loaded initially --}}
                    @foreach($divisions as $division)
                        <option value="{{ $division }}" @selected(request('division') == $division)>{{ $division }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-item">
                <label for="district">@lang('District')</label>
                {{-- District dropdown will be populated by JavaScript --}}
                <select name="district" id="district" class="form-select select2">
                    <option value="">@lang('All Districts')</option>
                </select>
            </div>
            <div class="filter-item">
                <label for="upazila">@lang('Upazila')</label>
                {{-- Upazila dropdown will be populated by JavaScript --}}
                <select name="upazila" id="upazila" class="form-select select2">
                    <option value="">@lang('All Upazilas')</option>
                </select>
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn btn-outline-info v2-btn">
                    <i class="fas fa-filter"></i> @lang('Filter')
                </button>
                <a href="{{ route('admin.report.user_by_area') }}" class="btn btn-outline-danger v2-btn">
                    <i class="fas fa-sync-alt"></i> @lang('Reset')
                </a>
            </div>
        </form>
    </div>
    
    <div class="alert alert-primary" role="alert">
        <p class="m-0">@lang('Number of members is'): {{ $widget['count'] }}</p>
    </div>


    {{-- Table Section --}}
    <div class="card-table-wrapper">
        <div class="table-responsive">
            <table class="table modern-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>@lang('Phone Number')</th>
                        <th>@lang('Full Name')</th>
                        <th>@lang('District')</th>
                        <th>@lang('Upazila')</th>
                        <th>@lang('Union')</th>
                        <th>@lang('Action')</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($members as $member)
                        <tr>
                            <td data-label="#">{{ $loop->iteration + $members->firstItem() - 1 }}</td>
                            <td data-label="@lang('Username')">{{ $member->phone_number ?? '-' }}</td>
                            <td data-label="@lang('Full Name')">{{ $member->name ?? '-' }}</td>
                            <td data-label="@lang('District')">{{ $member->district ?? 'N/A' }}</td>
                            <td data-label="@lang('Upazila')">{{ $member->upazila ?? 'N/A' }}</td>
                            <td data-label="@lang('Thana / Post Office')">{{ $member->post_office ?? 'N/A' }}</td>
                            <td>
                                <x-button href="{{ route('admin.user.details', $member->id) }}">
                                    <x-icons.eye />
                                </x-button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7"> {{-- Changed colspan to 7 to match the number of columns --}}
                                <div class="empty-state">
                                    <i class="fas fa-users"></i>
                                    <h2>@lang('No Members Found')</h2>
                                    <p>@lang('There are no members matching your current filter criteria.')</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    @if ($members->hasPages())
        <div class="pagination-wrapper">
            {{-- withQueryString() is crucial to keep filters active when changing pages --}}
            {!! $members->withQueryString()->links() !!}
        </div>
    @endif
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<style>
    :root { 
        --card-bg: #ffffff; 
    }
 
    .filter-card {
        background-color: var(--card-bg);
        border-radius: 8px; /* Standardized border-radius */
        box-shadow: 0 4px 6px rgba(0,0,0,0.1); /* Standardized shadow */
        padding: 1.5rem;
        margin-bottom: 2rem;
        border: 1px solid #e0e0e0; /* Standardized border-color */
    }
    .filter-form {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        gap: 1.5rem;
    }
    .filter-item {
        flex: 1 1 200px;
    }
    .filter-item label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        font-size: 0.9rem;
        color: #333; /* Standardized heading-color */
    }
    .filter-item .form-select {
        width: 100%;
        padding: 0.65rem 1rem;
        border-radius: 6px;
        border: 1px solid #ced4da;
    }
    .filter-actions {
        display: flex;
        gap: 0.5rem;
    } 
    .card-table-wrapper {
        background-color: var(--card-bg);
        border-radius: 8px; /* Standardized border-radius */
        box-shadow: 0 4px 6px rgba(0,0,0,0.1); /* Standardized shadow */
        padding: 1rem;
        border: 1px solid #e0e0e0; /* Standardized border-color */
    } 
</style>
@endpush 

@push('scripts')
<script>
$(document).ready(function() { 
    const locationData = @json($locations ?? []);
 
    const $divisionSelect = $('#division');
    const $districtSelect = $('#district');
    const $upazilaSelect = $('#upazila');
 
    const oldDivision = "{{ request('division') }}";
    const oldDistrict = "{{ request('district') }}";
    const oldUpazila = "{{ request('upazila') }}";

    function populateDistricts(selectedDivision) {
        $districtSelect.empty().append('<option value="">@lang('All Districts')</option>');
        $upazilaSelect.empty().append('<option value="">@lang('All Upazilas')</option>');

        if (selectedDivision && locationData[selectedDivision]) {
            const districts = Object.keys(locationData[selectedDivision]).sort();
            districts.forEach(district => {
                $districtSelect.append(`<option value="${district}">${district}</option>`);
            });
        }
        // Restore the previously selected district if it exists
        if (oldDistrict) {
            $districtSelect.val(oldDistrict);
        }
    }

    function populateUpazilas(selectedDivision, selectedDistrict) {
        $upazilaSelect.empty().append('<option value="">@lang('All Upazilas')</option>');

        if (selectedDivision && selectedDistrict && locationData[selectedDivision]?.[selectedDistrict]) {
            const upazilas = locationData[selectedDivision][selectedDistrict].sort();
            upazilas.forEach(upazila => {
                $upazilaSelect.append(`<option value="${upazila}">${upazila}</option>`);
            });
        }
        // Restore the previously selected upazila if it exists
        if (oldUpazila) {
            $upazilaSelect.val(oldUpazila);
        }
    }

    // Event handler for when the division changes
    $divisionSelect.on('change', function() {
        const selectedDivision = $(this).val();
        populateDistricts(selectedDivision);
        // Trigger a change on district to populate upazilas if a district was auto-selected
        $districtSelect.trigger('change');
    });

    // Event handler for when the district changes
    $districtSelect.on('change', function() {
        const selectedDivision = $divisionSelect.val();
        const selectedDistrict = $(this).val();
        populateUpazilas(selectedDivision, selectedDistrict);
    });

    // --- Initial Population on Page Load ---
    // If there's an old division selected, trigger the change to load its districts
    if (oldDivision) {
        $divisionSelect.trigger('change');
    }
});
</script>
@endpush