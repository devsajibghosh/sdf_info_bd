@extends('admin.layouts.app')

@section('content')
    @php
        if (!isset($user)) {
            $user = new \App\Models\User();
        }
    @endphp

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-3">{{ isset($user) ? __('Edit Member') : __('Add New Member') }}</h3>

        <x-button href="{{ route('admin.user.list') }}">
            <x-icons.back-v1 />
            @lang('Back')
        </x-button>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.user.save', $user?->id ?? null) }}" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- Account Details Section --}}
                <fieldset class="mb-4 p-3 border rounded">
                    <legend class="w-auto h5">@lang('Account Details')</legend>
                    <div class="row gy-4">
                        <div class="col-md-6 col-lg-4">
                            <div class="form-group">
                                <label for="username" class="form-label">@lang('Username')</label>
                                <input type="text" id="username" name="username"
                                    class="form-control @error('username') is-invalid @enderror"
                                    value="{{ old('username', $user->username ?? '') }}" placeholder="@lang('Enter username')" />
                                @error('username')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4">
                            <div class="form-group">
                                <label for="email" class="form-label">@lang('Email')</label>
                                <input type="email" id="email" name="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    value="{{ old('email', $user->email ?? '') }}" placeholder="@lang('Enter email address')" />
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4">
                            <div class="form-group">
                                <label for="email" class="form-label">@lang('Member Category')</label>
                                <select name="category_id" id="category_id" class="form-control">
                                    @foreach ($categories as $category)
                                        <option @selected($user?->category_id == $category->id) value="{{ old('category_id', $category->id) }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-4">
                        {{-- Password --}}
                        <div class="col-md-6 col-lg-4">
                            <div class="form-group">
                                <label for="password" class="form-label">@lang('Password')</label>
                                <input type="password" id="password" name="password"
                                    class="form-control @error('password') is-invalid @enderror"
                                    placeholder="@lang('Enter a password')" />
                                @if (isset($user))
                                    <small class="text-muted">@lang('Leave blank to keep current password.')</small>
                                @endif
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Password Confirmation --}}
                        <div class="col-md-6 col-lg-4">
                            <div class="form-group">
                                <label for="password_confirmation" class="form-label">@lang('Confirm Password')</label>
                                <input type="password" id="password_confirmation" name="password_confirmation"
                                    class="form-control" placeholder="@lang('Confirm the password')" />
                            </div>
                        </div>
                    </div>
                </fieldset>

                {{-- Personal Details Section --}}
                <fieldset class="mb-4 p-3 border rounded">
                    <legend class="w-auto h5">@lang('Personal Details')</legend>
                    <div class="row gy-4">
                        <div class="col-md-6 col-lg-3">
                            <div class="form-group">
                                <label for="first_name" class="form-label">@lang('First Name')</label>
                                <input type="text" id="first_name" name="first_name"
                                    class="form-control @error('first_name') is-invalid @enderror"
                                    value="{{ old('first_name', $user->first_name ?? '') }}" required />
                                @error('first_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-3">
                            <div class="form-group">
                                <label for="last_name" class="form-label">@lang('Last Name')</label>
                                <input type="text" id="last_name" name="last_name"
                                    class="form-control @error('last_name') is-invalid @enderror"
                                    value="{{ old('last_name', $user->last_name ?? '') }}" required />
                                @error('last_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-3">
                            <div class="form-group">
                                <label for="father_name" class="form-label">@lang('Father\'s Name')</label>
                                <input type="text" id="father_name" name="father_name"
                                    class="form-control @error('father_name') is-invalid @enderror"
                                    value="{{ old('father_name', $user->father_name ?? '') }}" />
                                @error('father_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-3">
                            <div class="form-group">
                                <label for="mother_name" class="form-label">@lang('Mother\'s Name')</label>
                                <input type="text" id="mother_name" name="mother_name"
                                    class="form-control @error('mother_name') is-invalid @enderror"
                                    value="{{ old('mother_name', $user->mother_name ?? '') }}" />
                                @error('mother_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-3">
                            <div class="form-group">
                                <label for="date_of_birth" class="form-label">@lang('Date of Birth')</label>
                                <input type="date" id="date_of_birth" name="date_of_birth"
                                    class="form-control @error('date_of_birth') is-invalid @enderror"
                                    value="{{ old('date_of_birth', $user->date_of_birth ? \Carbon\Carbon::parse($user->date_of_birth)->format('Y-m-d') : '') }}"
                                     />
                                @error('date_of_birth')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-3">
                            <div class="form-group">
                                <label for="current_age" class="form-label">@lang('Current Age')</label>
                                <input type="number" id="current_age" name="current_age"
                                    class="form-control @error('current_age') is-invalid @enderror"
                                    value="{{ old('current_age', $user->current_age ?? '') }}" readonly />
                                @error('current_age')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-3">
                            <div class="form-group">
                                <label for="gender" class="form-label">@lang('Gender')</label>
                                <select name="gender" id="gender"
                                    class="form-select @error('gender') is-invalid @enderror" >
                                    <option value="" disabled @selected(!old('gender', $user->gender ?? ''))>@lang('Select Gender')
                                    </option>
                                    <option @selected(old('gender', $user->gender ?? '') == 'male') value="male">@lang('Male')</option>
                                    <option @selected(old('gender', $user->gender ?? '') == 'female') value="female">@lang('Female')</option>
                                    <option @selected(old('gender', $user->gender ?? '') == 'other') value="other">@lang('Other')</option>
                                </select>
                                @error('gender')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-3">
                            <div class="form-group">
                                <label for="blood_group" class="form-label">@lang('Blood Group')</label>
                                <select name="blood_group" id="blood_group"
                                    class="form-select @error('blood_group') is-invalid @enderror">
                                    <option value="" disabled @selected(!old('blood_group', $user->blood_group ?? ''))>@lang('Select Blood Group')
                                    </option>
                                    <option @selected(old('blood_group', $user->blood_group ?? '') == 'A+') value="A+">A+</option>
                                    <option @selected(old('blood_group', $user->blood_group ?? '') == 'A-') value="A-">A-</option>
                                    <option @selected(old('blood_group', $user->blood_group ?? '') == 'B+') value="B+">B+</option>
                                    <option @selected(old('blood_group', $user->blood_group ?? '') == 'B-') value="B-">B-</option>
                                    <option @selected(old('blood_group', $user->blood_group ?? '') == 'O+') value="O+">O+</option>
                                    <option @selected(old('blood_group', $user->blood_group ?? '') == 'O-') value="O-">O-</option>
                                    <option @selected(old('blood_group', $user->blood_group ?? '') == 'AB+') value="AB+">AB+</option>
                                    <option @selected(old('blood_group', $user->blood_group ?? '') == 'AB-') value="AB-">AB-</option>
                                </select>
                                @error('blood_group')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </fieldset>

                {{-- Identification & Contact Section --}}
                <fieldset class="mb-4 p-3 border rounded">
                    <legend class="w-auto h5">@lang('Identification & Contact')</legend>
                    <div class="row gy-4">
                        <div class="col-md-6 col-lg-3">
                            <div class="form-group">
                                <label for="id_type" class="form-label">@lang('ID Type')</label>
                                <select name="id_type" id="id_type"
                                    class="form-select @error('id_type') is-invalid @enderror">
                                    <option value="" disabled @selected(!old('id_type', $user->id_type ?? ''))>@lang('Select ID Type')
                                    </option>
                                    <option @selected(old('id_type', $user->id_type ?? '') == 'nid') value="nid">@lang('NID')</option>
                                    <option @selected(old('id_type', $user->id_type ?? '') == 'birth_certificate') value="birth_certificate">@lang('Birth Certificate')
                                    </option>
                                    <option @selected(old('id_type', $user->id_type ?? '') == 'passport') value="passport">@lang('Passport')</option>
                                </select>
                                @error('id_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-3">
                            <div class="form-group">
                                <label for="id_number" class="form-label">@lang('ID Number')</label>
                                <input type="text" id="id_number" name="id_number"
                                    class="form-control @error('id_number') is-invalid @enderror"
                                    value="{{ old('id_number', $user->id_number ?? '') }}" />
                                @error('id_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-3">
                            <div class="form-group">
                                <label for="phone_number" class="form-label">@lang('Phone Number')</label>
                                <input type="text" id="phone_number" name="phone_number"
                                    class="form-control @error('phone_number') is-invalid @enderror"
                                    value="{{ old('phone_number', $user->phone_number ?? '') }}" />
                                @error('phone_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-3">
                            <div class="form-group">
                                <label for="whatsapp_number" class="form-label">@lang('Whatsapp Number')</label>
                                <input type="text" id="whatsapp_number" name="whatsapp_number"
                                    class="form-control @error('whatsapp_number') is-invalid @enderror"
                                    value="{{ old('whatsapp_number', $user->whatsapp_number ?? '') }}" />
                                @error('whatsapp_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-3">
                            <div class="form-group">
                                <label for="country" class="form-label">@lang('Present Country')</label>
                                <select name="country" id="country"
                                    class="form-select @error('country') is-invalid @enderror">
                                    @foreach (countries() as $countryData)
                                        <option @selected(old('country', $user->country ?? 'BD') == $countryData['code']) value="{{ $countryData['code'] }}">
                                            {{ __($countryData['name']) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('country')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-3">
                            <div class="form-group">
                                <label for="facebook_id_link" class="form-label">@lang('Facebook ID link')</label>
                                <input type="url" id="facebook_id_link" name="facebook_id_link"
                                    class="form-control @error('facebook_id_link') is-invalid @enderror"
                                    value="{{ old('facebook_id_link', $user->facebook_id_link ?? '') }}" />
                                @error('facebook_id_link')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-3">
                            <div class="form-group">
                                <label for="image" class="form-label">@lang('Profile Image')</label>
                                <input type="file" id="image" name="image"
                                    class="form-control @error('image') is-invalid @enderror" accept="image/*" />
                                <small class="text-muted">{{ imageSize('user') }}</small>
                                @error('image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @if (isset($user->image_path) && $user->image_path)
                                    <img src="{{ asset('storage/' . $user->image_path) }}" alt="Profile Image"
                                        class="img-thumbnail mt-2" width="100">
                                @endif
                            </div>
                        </div>
                    </div>
                </fieldset>

                {{-- Professional & Marital Status --}}
                <fieldset class="mb-4 p-3 border rounded">
                    <legend class="w-auto h5">@lang('Professional & Marital Status')</legend>
                    <div class="row gy-4">
                        <div class="col-md-6 col-lg-3">
                            <div class="form-group">
                                <label for="occupation" class="form-label">@lang('Occupation')</label>
                                <select name="occupation" id="occupation"
                                    class="form-select @error('occupation') is-invalid @enderror">
                                    <option value="" disabled @selected(!old('occupation', $user->occupation ?? ''))>@lang('Select Occupation')
                                    </option>
                                    <option @selected(old('occupation', $user->occupation ?? '') == 'Teacher') value="Teacher">@lang('Teacher')</option>
                                    <option @selected(old('occupation', $user->occupation ?? '') == 'Doctor') value="Doctor">@lang('Doctor')</option>
                                    <option @selected(old('occupation', $user->occupation ?? '') == 'Engineer') value="Engineer">@lang('Engineer')</option>
                                    <option @selected(old('occupation', $user->occupation ?? '') == 'Farmer') value="Farmer">@lang('Farmer')</option>
                                    <option @selected(old('occupation', $user->occupation ?? '') == 'Businessman') value="Businessman">@lang('Businessman')</option>
                                    <option @selected(old('occupation', $user->occupation ?? '') == 'Student') value="Student">@lang('Student')</option>
                                    <option @selected(old('occupation', $user->occupation ?? '') == 'Government Employee') value="Government Employee">@lang('Government Employee')
                                    </option>
                                    <option @selected(old('occupation', $user->occupation ?? '') == 'Private Service') value="Private Service">@lang('Private Service')
                                    </option>
                                    <option @selected(old('occupation', $user->occupation ?? '') == 'Housewife') value="Housewife">@lang('Housewife')</option>
                                    <option @selected(old('occupation', $user->occupation ?? '') == 'Unemployed') value="Unemployed">@lang('Unemployed')</option>
                                    <option @selected(old('occupation', $user->occupation ?? '') == 'Other') value="Other">@lang('Other')</option>
                                </select>
                                @error('occupation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-3">
                            <div class="form-group">
                                <label for="marital_status" class="form-label">@lang('Marital Status')</label>
                                <select name="marital_status" id="marital_status"
                                    class="form-select @error('marital_status') is-invalid @enderror">
                                    <option value="" disabled @selected(!old('marital_status', $user->marital_status ?? ''))>@lang('Select Status')
                                    </option>
                                    <option @selected(old('marital_status', $user->marital_status ?? '') == 'Single') value="Single">@lang('Single')</option>
                                    <option @selected(old('marital_status', $user->marital_status ?? '') == 'Married') value="Married">@lang('Married')</option>
                                    <option @selected(old('marital_status', $user->marital_status ?? '') == 'Widowed') value="Widowed">@lang('Widowed')</option>
                                    <option @selected(old('marital_status', $user->marital_status ?? '') == 'Separated') value="Separated">@lang('Separated')</option>
                                </select>
                                @error('marital_status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </fieldset>

                {{-- Address Details Section --}}
                <fieldset class="mb-4 p-3 border rounded">
                    <legend class="w-auto h5">@lang('Address Details')</legend>
                    <div class="row gy-4">
                        <div class="col-md-6 col-lg-3">
                            <div class="form-group">
                                <label for="division" class="form-label">@lang('Division')</label>
                                <select name="division" id="division"
                                    class="form-select @error('division') is-invalid @enderror">
                                    <option value="" disabled selected>@lang('Select Division')</option>
                                </select>
                                @error('division')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-3">
                            <div class="form-group">
                                <label for="district" class="form-label">@lang('District')</label>
                                <select name="district" id="district"
                                    class="form-select @error('district') is-invalid @enderror">
                                    <option value="" disabled selected>@lang('Select District')</option>
                                </select>
                                @error('district')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-3">
                            <div class="form-group">
                                <label for="upazila" class="form-label">@lang('Upazila/City Corporation')</label>
                                <select name="upazila" id="upazila"
                                    class="form-select @error('upazila') is-invalid @enderror">
                                    <option value="" disabled selected>@lang('Select Upazila/City')</option>
                                </select>
                                @error('upazila')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-3">
                            <div class="form-group">
                                <label for="post_office" class="form-label">@lang('Union/Pouroshova')</label>
                                <select name="post_office" id="post_office"
                                    class="form-select @error('post_office') is-invalid @enderror">
                                    <option value="" disabled selected>@lang('Select Union/Pouroshova')</option>
                                </select>
                                @error('post_office')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <div class="form-group">
                                <label for="ward" class="form-label">@lang('Ward No')</label>
                                <select name="ward" id="ward"
                                    class="form-select @error('ward') is-invalid @enderror" >
                                    <option value="" disabled selected>@lang('Select Ward')</option>
                                </select>
                                @error('ward')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </fieldset>

                {{-- Additional Information Section --}}
                <fieldset class="mb-4 p-3 border rounded">
                    <legend class="w-auto h5">@lang('Additional Information')</legend>
                    <div class="row gy-4">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label class="form-label d-block">@lang('Joining Media')</label>
                                @php
                                    $joiningOptions = [
                                        'Existing Member',
                                        'Website',
                                        'Facebook Group',
                                        'Facebook Page',
                                        'YouTube',
                                        'WhatsApp',
                                        'Banner',
                                        'Leaflet',
                                        'Other',
                                    ];
                                    $currentJoiningMedia = old('joining_media', $user->joining_media ?? '');
                                @endphp
                                <div class="d-flex gap-3 flex-wrap">
                                    @foreach ($joiningOptions as $option)
                                        <div class="form-check">
                                            <input class="form-check-input @error('joining_media') is-invalid @enderror"
                                                type="radio" name="joining_media"
                                                id="joining_media_{{ \Illuminate\Support\Str::slug($option) }}"
                                                value="{{ $option }}" @checked($currentJoiningMedia == $option)>
                                            <label class="form-check-label"
                                                for="joining_media_{{ \Illuminate\Support\Str::slug($option) }}">
                                                @lang($option)
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                @error('joining_media')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4">
                            <div class="form-group">
                                <label for="reference" class="form-label">@lang('Reference Person/Phone Number')</label>
                                <input type="text" id="reference" name="reference"
                                    class="form-control @error('reference') is-invalid @enderror"
                                    value="{{ old('reference', $user->reference ?? '') }}" />
                                @error('reference')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4">
                            <div class="form-group">
                                <label for="political_relation" class="form-label">@lang('Political Relation')</label>
                                <select name="political_relation" id="political_relation" class="form-select @error('political_relation') is-invalid @enderror" required>
                                    <option value="" disabled @selected(!old('political_relation', $user->political_relation ?? ''))>@lang('Select Option')
                                    </option>

                                    <option @selected(old('political_relation', $user->political_relation ?? '') == 1) value="1">@lang('Yes')</option>
                                    <option @selected(old('political_relation', $user->political_relation ?? '') == 0) value="0">@lang('No')</option>
                                </select>
                                @error('political_relation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-12 col-lg-4">
                            <div class="form-group">
                                <label for="post_and_politics" class="form-label">@lang('If Yes, Post & Politics')</label>
                                <input type="text" id="post_and_politics" name="post_and_politics"
                                    class="form-control @error('post_and_politics') is-invalid @enderror"
                                    value="{{ old('post_and_politics', $user->post_and_politics ?? '') }}" />
                                @error('post_and_politics')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-group mb-3">
                                <label class="form-label">@lang('Monthly Fee') <span class="text-danger">*</span></label>
                                <div>
                                    @foreach ([50, 100, 200, 500, 1000, 2000, 3000, 5000] as $fee)
                                        @php
                                            $selected =
                                                old('monthly_fee') == $fee ||
                                                (isset($user) && $user->monthly_fee == $fee);
                                        @endphp
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input @error('monthly_fee') is-invalid @enderror"
                                                type="radio" name="monthly_fee" id="monthly_fee_{{ $fee }}"
                                                value="{{ $fee }}" {{ $selected ? 'checked' : '' }} />
                                            <label class="form-check-label"
                                                for="monthly_fee_{{ $fee }}">{{ $fee }} tk</label>
                                        </div>
                                    @endforeach
                                </div>
                                @error('monthly_fee')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>


                    </div>
                </fieldset>
                
                <div class="d-flex justify-content-end mt-4">
                    <x-button type="submit">
                        <x-icons.save />
                        @lang('Save User')
                    </x-button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/select2.min.css') }}">
    <style>
        .form-label {
            font-weight: 500;
        }

        fieldset legend {
            font-weight: 600;
        }

        .select2-container--default .select2-selection--single {
            height: calc(1.5em + .75rem + 2px);
            padding: .375rem .75rem;
            border: 1px solid #ced4da;
            border-radius: .25rem;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 1.5em;
            padding-left: 0;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: calc(1.5em + .75rem);
            right: .5rem;
        }

        .is-invalid+.select2-container--default .select2-selection--single {
            border-color: #dc3545;
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            $('[name="joining_media"]').on('change', function() {
                const val = $(this).val();
                if ('Existing Member' == val) {
                    $('[name="reference"]').attr('required', 'required');
                } else {
                    $('[name="reference"]').removeAttr('required');
                }
            });

            $('select:not(#division, #district, #upazila, #post_office, #ward)').select2({
                width: '100%',
                theme: 'default'
            });

            $('#division, #district, #upazila, #post_office, #ward').select2({
                width: '100%',
                theme: 'default'
            });

            function calculateAge(dobString) {
                if (!dobString) return '';
                const dob = new Date(dobString);
                const today = new Date();
                let age = today.getFullYear() - dob.getFullYear();
                const m = today.getMonth() - dob.getMonth();
                if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) {
                    age--;
                }
                return age >= 0 ? age : '';
            }

            $('#date_of_birth').on('change blur', function() {
                const dob = $(this).val();
                const age = calculateAge(dob);
                $('#current_age').val(age);
            }).trigger('blur');

            $('#political_relation').on('change', function() {
                if ($(this).val() === 'yes') {
                    $('#post_and_politics').prop('required', true);
                    $('label[for="post_and_politics"]').append(
                        ' <span class="text-danger political-required">*</span>');
                } else {
                    $('#post_and_politics').prop('required', false);
                    $('.political-required').remove();
                    $('#post_and_politics').removeClass('is-invalid');
                    $('#post_and_politics').next('.invalid-feedback').remove();
                }
            }).trigger('change');
        });

        const addressData = @json(addressData());
        const $division = $('#division');
        const $district = $('#district');
        const $upazila = $('#upazila');
        const $postOffice = $('#post_office');
        const $ward = $('#ward');

        const divisions = Object.keys(addressData);
        const divHtml = divisions.map(div => `<option value="${div}">${div}</option>`).join('');
        $division.append(divHtml);

        function resetSelect($el, placeholder) {
            $el.empty().append(`<option value="" disabled selected>@lang('${placeholder}')</option>`).trigger('change');
        }

        $division.on('change', function() {
            const division = $(this).val();
            resetSelect($district, 'Select District');
            resetSelect($upazila, 'Select Upazila/City');
            resetSelect($postOffice, 'Select Union/Pouroshova');
            resetSelect($ward, 'Select Ward');

            if (addressData[division]) {
                let districtOptions = '<option value="" disabled selected>@lang('Select District')</option>';
                $.each(addressData[division], function(district) {
                    districtOptions += `<option value="${district}">${district}</option>`;
                });
                $district.html(districtOptions);
            }
        });

        $district.on('change', function() {
            const division = $division.val();
            const district = $(this).val();
            resetSelect($upazila, 'Select Upazila/City');
            resetSelect($postOffice, 'Select Union/Pouroshova');
            resetSelect($ward, 'Select Ward');

            if (addressData[division] && addressData[division][district]) {
                let upazilaOptions = '<option value="" disabled selected>@lang('Select Upazila/City')</option>';
                $.each(addressData[division][district], function(upazila) {
                    upazilaOptions += `<option value="${upazila}">${upazila}</option>`;
                });
                $upazila.html(upazilaOptions);
            }
        });

        $upazila.on('change', function() {
            const division = $division.val();
            const district = $district.val();
            const upazila = $(this).val();
            resetSelect($postOffice, 'Select Union/Pouroshova');
            resetSelect($ward, 'Select Ward');

            if (addressData[division]?.[district]?.[upazila]) {
                let postOfficeOptions = '<option value="" disabled selected>@lang('Select Union/Pouroshova')</option>';
                $.each(addressData[division][district][upazila], function(postOffice) {
                    postOfficeOptions += `<option value="${postOffice}">${postOffice}</option>`;
                });
                $postOffice.html(postOfficeOptions);
            }
        });

        $postOffice.on('change', function() {
            const division = $division.val();
            const district = $district.val();
            const upazila = $upazila.val();
            const postOffice = $(this).val();
            resetSelect($ward, 'Select Ward');

            const wards = addressData[division]?.[district]?.[upazila]?.[postOffice] || [];
            if (wards.length > 0) {
                let wardOptions = '<option value="" disabled selected>@lang('Select Ward')</option>';
                $.each(wards, function(index, ward) {
                    wardOptions += `<option value="${ward}">${ward}</option>`;
                });
                $ward.html(wardOptions);
            }
        });

        // --- Logic to pre-select address on edit page ---
        let oldDivision = "{{ old('division', $user->division ?? '') }}";
        let oldDistrict = "{{ old('district', $user->district ?? '') }}";
        let oldUpazila = "{{ old('upazila', $user->upazila ?? '') }}";
        let oldPostOffice = "{{ old('post_office', $user->post_office ?? '') }}";
        let oldWard = "{{ old('ward', $user->ward ?? '') }}";

        if (oldDivision) {
            $division.val(oldDivision).trigger('change');

            // Use setTimeout to allow dependent dropdowns to populate
            setTimeout(function() {
                if (oldDistrict) {
                    $district.val(oldDistrict).trigger('change');
                    setTimeout(function() {
                        if (oldUpazila) {
                            $upazila.val(oldUpazila).trigger('change');
                            setTimeout(function() {
                                if (oldPostOffice) {
                                    $postOffice.val(oldPostOffice).trigger('change');
                                    setTimeout(function() {
                                        if (oldWard) {
                                            $ward.val(oldWard).trigger('change');
                                        }
                                    }, 200);
                                }
                            }, 200);
                        }
                    }, 200);
                }
            }, 200);
        }
    </script>
@endpush
