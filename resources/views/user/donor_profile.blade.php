@extends('user.layouts.main')

@section('content')
<div class="container py-5">
    <h2 class="mb-4">
        {{ isset($donor) ? __('Update Profile') : __('Create Profile') }}
    </h2>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('user.donor.profile.submit', @$donor->id) }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row gy-4">
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label>@lang('Name')</label>
                            <input 
                                type="text" 
                                class="form-control" 
                                name="name"
                                value="{{ old('name', $donor->name ?? '') }}"
                            />
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label>@lang('Phone Number')</label>
                            <input 
                                type="text" 
                                class="form-control"
                                readonly
                                disabled
                                name="phone_number"
                                value="{{ $donor->phone_number }}"
                            />
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label>@lang('Father Name')</label>
                            <input 
                                type="text" 
                                class="form-control" 
                                name="father_name"
                                value="{{ old('father_name', $donor->father_name ?? '') }}"
                            />
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label>@lang('Mother Name')</label>
                            <input 
                                type="text" 
                                class="form-control" 
                                name="mother_name"
                                value="{{ old('mother_name', $donor->mother_name ?? '') }}"
                            />
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="form-group">
                            <label>@lang('Address')</label>
                            <textarea 
                                class="form-control" 
                                name="address"
                                rows="3"
                            >{{ old('address', $donor->address ?? '') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> {{-- if using Bootstrap icons --}}
                        @lang('Save')
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
