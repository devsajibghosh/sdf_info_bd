@extends('user.layouts.main')

@section('content')
<div class="dashboard-container py-5">
    
    
    <div class="container">
        {{-- Filter Section --}}
    <div class="filter-card mb-3">
        <form action="{{ route('user.notices') }}" method="GET" class="filter-form">
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
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter"></i> @lang('Filter')
                </button>
                <a href="{{ route('user.notices') }}" class="btn btn-danger">
                    <i class="fas fa-sync-alt"></i> @lang('Reset')
                </a>
            </div>
        </form>
    </div>
    
    <div class="alert alert-primary mb-3" role="alert">
        <p class="m-0">@lang('Number of members is'): {{ $widget['count'] }}</p>
    </div>

    
        <div class="row">
            <div class="col-12">
                <h2 class="dashboard-title">{{ $title }}</h2>
                <div class="notice-board">
                    @forelse ($notices as $notice)
                        <div class="notice-card">
                            <div class="notice-header">
                                <h5 class="notice-title">{{ $notice->title }}</h5>
                                <span class="notice-date">{{ $notice->created_at->format('F d, Y') }}</span>
                            </div>
                            <div class="notice-body">
                                @php echo $notice->description @endphp
                            </div>
                        </div>
                    @empty
                        <div class="alert alert-info">
                            There are currently no notices to display.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
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
    .dashboard-title {
        margin-bottom: 2rem;
        font-size: 2rem;
        font-weight: 500;
        color: #333;
    }

    .notice-board {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .notice-card {
        background-color: #ffffff;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        transition: box-shadow 0.3s ease-in-out;
    }

    .notice-card:hover {
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
    }

    .notice-header {
        padding: 1.25rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #e0e0e0;
        background-color: #f8f9fa;
        border-top-left-radius: 8px;
        border-top-right-radius: 8px;
    }

    .notice-title {
        margin-bottom: 0;
        font-size: 1.2rem;
        font-weight: 600;
        color: #0056b3;
    }

    .notice-date {
        font-size: 0.85rem;
        color: #6c757d;
        font-weight: 500;
    }

    .notice-body {
        padding: 1.25rem;
    }

    .notice-body p {
        margin-bottom: 0;
        color: #495057;
        line-height: 1.6;
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