@extends('user.layouts.main')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">@lang('Update Your Profile')</h1>
    </div>

    <form action="{{ route('user.setting.profile.save') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row">
            {{-- Main Form Fields --}}
            <div class="col-12 col-lg-8">

                {{-- Account Details --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">@lang('Account Details')</h6>
                    </div>
                    <div class="card-body">
                        <div class="row gy-3">
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="username" class="form-label">@lang('Username')</label>
                                    <input type="text" id="username" name="username" class="form-control" value="{{ old('username', $user->username) }}" readonly disabled />
                                    <small class="text-muted">@lang('Username cannot be changed.')</small>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="phone_number" class="form-label">@lang('Phone Number')</label>
                                    <input type="text" id="phone_number" name="phone_number" class="form-control @error('phone_number') is-invalid @enderror" value="{{ old('phone_number', $user->phone_number) }}" readonly disabled />
                                    <small class="text-muted">@lang('Phone number cannot be changed.')</small>
                                    @error('phone_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Personal Details --}}
                <div class="card shadow-sm mb-4">
                     <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">@lang('Personal Details')</h6>
                    </div>
                    <div class="card-body">
                        <div class="row gy-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label" for="first_name">@lang('First Name') <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('first_name') is-invalid @enderror" name="first_name" id="first_name" value="{{ old('first_name', $user->first_name) }}" required />
                                @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label" for="last_name">@lang('Last Name') <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('last_name') is-invalid @enderror" name="last_name" id="last_name" value="{{ old('last_name', $user->last_name) }}" required />
                                @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label" for="father_name">@lang("Father's Name") <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('father_name') is-invalid @enderror" name="father_name" id="father_name" value="{{ old('father_name', $user->father_name) }}"  />
                                @error('father_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label" for="mother_name">@lang("Mother's Name") <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('mother_name') is-invalid @enderror" name="mother_name" id="mother_name" value="{{ old('mother_name', $user->mother_name) }}"  />
                                @error('mother_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="date_of_birth" class="form-label">@lang('Date of Birth') <span class="text-danger">*</span></label>
                                <input type="date" id="date_of_birth" name="date_of_birth" class="form-control @error('date_of_birth') is-invalid @enderror" value="{{ old('date_of_birth', $user->date_of_birth ? \Carbon\Carbon::parse($user->date_of_birth)->format('Y-m-d') : '') }}"  />
                                @error('date_of_birth') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                             <div class="col-12 col-md-6">
                                <label for="current_age" class="form-label">@lang('Current Age') <span class="text-danger">*</span></label>
                                <input type="number" id="current_age" name="current_age" class="form-control" value="{{ old('current_age', $user->current_age) }}" readonly />
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="gender" class="form-label">@lang('Gender') <span class="text-danger">*</span></label>
                                <select name="gender" id="gender" class="form-select @error('gender') is-invalid @enderror" >
                                    <option value="" disabled @selected(!old('gender', $user->gender))>@lang('Select Gender')</option>
                                    <option @selected(old('gender', $user->gender) == 'male') value="male">@lang('Male')</option>
                                    <option @selected(old('gender', $user->gender) == 'female') value="female">@lang('Female')</option>
                                    <option @selected(old('gender', $user->gender) == 'other') value="other">@lang('Other')</option>
                                </select>
                                @error('gender') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="blood_group" class="form-label">@lang('Blood Group')</label>
                                <select name="blood_group" id="blood_group" class="form-select @error('blood_group') is-invalid @enderror">
                                    <option value="" disabled @selected(!old('blood_group', $user->blood_group))>@lang('Select Blood Group')</option>
                                    <option @selected(old('blood_group', $user->blood_group) == 'A+') value="A+">A+</option>
                                    <option @selected(old('blood_group', $user->blood_group) == 'A-') value="A-">A-</option>
                                    <option @selected(old('blood_group', $user->blood_group) == 'B+') value="B+">B+</option>
                                    <option @selected(old('blood_group', $user->blood_group) == 'B-') value="B-">B-</option>
                                    <option @selected(old('blood_group', $user->blood_group) == 'O+') value="O+">O+</option>
                                    <option @selected(old('blood_group', $user->blood_group) == 'O-') value="O-">O-</option>
                                    <option @selected(old('blood_group', $user->blood_group) == 'AB+') value="AB+">AB+</option>
                                    <option @selected(old('blood_group', $user->blood_group) == 'AB-') value="AB-">AB-</option>
                                </select>
                                @error('blood_group') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Identification & Contact --}}
                <div class="card shadow-sm mb-4">
                     <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">@lang('Identification & Contact')</h6>
                    </div>
                    <div class="card-body">
                        <div class="row gy-3">
                            <div class="col-12 col-md-6">
                                <label for="id_type" class="form-label">@lang('ID Type') <span class="text-danger">*</span></label>
                                <select name="id_type" id="id_type" class="form-select @error('id_type') is-invalid @enderror">
                                    <option value="" disabled @selected(!old('id_type', $user->id_type))>@lang('Select ID Type')</option>
                                    <option @selected(old('id_type', $user->id_type) == 'nid') value="nid">@lang('NID')</option>
                                    <option @selected(old('id_type', $user->id_type) == 'birth_certificate') value="birth_certificate">@lang('Birth Certificate')</option>
                                    <option @selected(old('id_type', $user->id_type) == 'passport') value="passport">@lang('Passport')</option>
                                </select>
                                @error('id_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="id_number" class="form-label">@lang('ID Number') <span class="text-danger">*</span></label>
                                <input type="text" id="id_number" name="id_number" class="form-control @error('id_number') is-invalid @enderror" value="{{ old('id_number', $user->id_number) }}" />
                                @error('id_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="whatsapp_number" class="form-label">@lang('Whatsapp Number')</label>
                                <input type="text" id="whatsapp_number" name="whatsapp_number" class="form-control @error('whatsapp_number') is-invalid @enderror" value="{{ old('whatsapp_number', $user->whatsapp_number) }}" />
                                @error('whatsapp_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                             <div class="col-12 col-md-6">
                                <label for="facebook_id_link" class="form-label">@lang('Facebook ID link')</label>
                                <input type="url" id="facebook_id_link" name="facebook_id_link" class="form-control @error('facebook_id_link') is-invalid @enderror" value="{{ old('facebook_id_link', $user->facebook_id_link) }}" />
                                @error('facebook_id_link') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                             <div class="col-12 col-md-6">
                                <div class="form-group mb-3">
                                    <label for="nid_front" class="form-label">@lang('NID Front')</label>
                                    <input type="file" id="nid_front" name="nid_front"
                                        class="form-control @error('nid_front') is-invalid @enderror" accept="image/*" />
                                    @error('nid_front')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="form-group mb-3">
                                    <label for="nid_back" class="form-label">@lang('NID Back')</label>
                                    <input type="file" id="nid_back" name="nid_back"
                                        class="form-control @error('nid_back') is-invalid @enderror" accept="image/*" />
                                    @error('nid_back')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Address Details --}}
                <div class="card shadow-sm mb-4">
                     <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">@lang('Address Details')</h6>
                    </div>
                    <div class="card-body">
                         <div class="row gy-3">
                            <div class="col-12 col-md-6">
                                <label for="country" class="form-label">@lang('Country') <span class="text-danger">*</span></label>
                                <select name="country" id="country" class="form-select @error('country') is-invalid @enderror" required>
                                    @foreach (countries() as $countryData)
                                        <option @selected(old('country', $user->country ?? 'BD') == $countryData['code']) value="{{ $countryData['code'] }}">
                                            {{ __($countryData['name']) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('country') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="division" class="form-label">@lang('Division') <span class="text-danger">*</span></label>
                                <select name="division" id="division" class="form-select @error('division') is-invalid @enderror" required>
                                    <option value="" disabled selected>@lang('Select Division')</option>
                                </select>
                                @error('division') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="district" class="form-label">@lang('District') <span class="text-danger">*</span></label>
                                <select name="district" id="district" class="form-select @error('district') is-invalid @enderror" required>
                                    <option value="" disabled selected>@lang('Select District')</option>
                                </select>
                                @error('district') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="upazila" class="form-label">@lang('Upazila/City Corporation') <span class="text-danger">*</span></label>
                                <select name="upazila" id="upazila" class="form-select @error('upazila') is-invalid @enderror" required>
                                    <option value="" disabled selected>@lang('Select Upazila/City')</option>
                                </select>
                                @error('upazila') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="post_office" class="form-label">@lang('Union/Pouroshova') <span class="text-danger">*</span></label>
                                <select name="post_office" id="post_office" class="form-select @error('post_office') is-invalid @enderror" required>
                                    <option value="" disabled selected>@lang('Select Union/Pouroshova')</option>
                                </select>
                                @error('post_office') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="ward" class="form-label">@lang('Ward No')</label>
                                <select name="ward" id="ward" class="form-select @error('ward') is-invalid @enderror">
                                    <option value="" disabled selected>@lang('Select Ward')</option>
                                </select>
                                @error('ward') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                             <div class="col-12">
                                <label for="present_address" class="form-label">@lang('Present Address')</label>
                                <input
                                    type="text"
                                    id="present_address"
                                    name="present_address"
                                    placeholder="@lang('House, Road, Village, Post, Thana, District')"
                                    value="{{ old('present_address', $user->present_address) }}"
                                    class="form-control"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Professional & Marital Status --}}
                <div class="card shadow-sm mb-4">
                     <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">@lang('Professional & Marital Status')</h6>
                    </div>
                    <div class="card-body">
                        <div class="row gy-3">
                            <div class="col-12 col-md-6">
                                <label for="occupation" class="form-label">@lang('Occupation') <span class="text-danger">*</span></label>
                                <select name="occupation" required id="occupation" class="form-select @error('occupation') is-invalid @enderror">
                                    <option value="" disabled @selected(!old('occupation', $user->occupation))>@lang('Select Occupation')</option>
                                    <option @selected(old('occupation', $user->occupation) == 'Actor') value="Actor">@lang('Actor')</option>
                                    <option @selected(old('occupation', $user->occupation) == 'Assistant Professor') value="Assistant Professor">@lang('Assistant Professor')</option>
                                    <option @selected(old('occupation', $user->occupation) == 'Associate Professor') value="Associate Professor">@lang('Associate Professor')</option>
                                    <option @selected(old('occupation', $user->occupation) == 'Bank Job') value="Bank Job">@lang('Bank Job')</option>
                                    <option @selected(old('occupation', $user->occupation) == 'Barber') value="Barber">@lang('Barber')</option>
                                    <option @selected(old('occupation', $user->occupation) == 'College Teacher') value="College Teacher">@lang('College Teacher')</option>
                                    <option @selected(old('occupation', $user->occupation) == 'Driver') value="Driver">@lang('Driver')</option>
                                    <option @selected(old('occupation', $user->occupation) == 'Doctor') value="Doctor">@lang('Doctor')</option>
                                    <option @selected(old('occupation', $user->occupation) == 'Engineer') value="Engineer">@lang('Engineer')</option>
                                    <option @selected(old('occupation', $user->occupation) == 'Expart') value="Expart">@lang('Expart')</option>
                                    <option @selected(old('occupation', $user->occupation) == 'Farmer') value="Farmer">@lang('Farmer')</option>
                                    <option @selected(old('occupation', $user->occupation) == 'Garments') value="Garments">@lang('Garments')</option>
                                    <option @selected(old('occupation', $user->occupation) == 'Goverment Job') value="Goverment Job">@lang('Goverment Job')</option>
                                    <option @selected(old('occupation', $user->occupation) == 'High School Teacher') value="High School Teacher">@lang('High School Teacher')</option>
                                    <option @selected(old('occupation', $user->occupation) == 'Health Services') value="Health Services">@lang('Health Services')</option>
                                    <option @selected(old('occupation', $user->occupation) == 'House Wife') value="House Wife">@lang('House Wife')</option>
                                    <option @selected(old('occupation', $user->occupation) == 'Journalists') value="Journalists">@lang('Journalists')</option>
                                    <option @selected(old('occupation', $user->occupation) == 'Lawyer') value="Lawyer">@lang('Lawyer')</option>
                                    <option @selected(old('occupation', $user->occupation) == 'Lecturer') value="Lecturer">@lang('Lecturer')</option>
                                    <option @selected(old('occupation', $user->occupation) == 'Military') value="Military">@lang('Military')</option>
                                    <option @selected(old('occupation', $user->occupation) == 'NGO Job') value="NGO Job">@lang('NGO Job')</option>
                                    <option @selected(old('occupation', $user->occupation) == 'Pharmaceutical Job') value="Pharmaceutical Job">@lang('Pharmaceutical Job')</option>
                                    <option @selected(old('occupation', $user->occupation) == 'Primary School Teacher') value="Primary School Teacher">@lang('Primary School Teacher')</option>
                                    <option @selected(old('occupation', $user->occupation) == 'Privet Job') value="Privet Job">@lang('Privet Job')</option>
                                    <option @selected(old('occupation', $user->occupation) == 'Professor') value="Professor">@lang('Professor')</option>
                                    <option @selected(old('occupation', $user->occupation) == 'Police') value="Police">@lang('Police')</option>
                                    <option @selected(old('occupation', $user->occupation) == 'Politician') value="Politician">@lang('Politician')</option>
                                    <option @selected(old('occupation', $user->occupation) == 'Retirement') value="Retirement">@lang('Retirement')</option>
                                    <option @selected(old('occupation', $user->occupation) == 'Self Business') value="Self Business">@lang('Self Business')</option>
                                    <option @selected(old('occupation', $user->occupation) == 'Social worker') value="Social worker">@lang('Social worker')</option>
                                    <option @selected(old('occupation', $user->occupation) == 'Student') value="Student">@lang('Student')</option>
                                    <option @selected(old('occupation', $user->occupation) == 'Tailor') value="Tailor">@lang('Tailor')</option>
                                    <option @selected(old('occupation', $user->occupation) == 'Telecommunication Job') value="Telecommunication Job">@lang('Telecommunication Job')</option>
                                    <option @selected(old('occupation', $user->occupation) == 'Others') value="Others">@lang('Others')</option>
                                </select>
                                @error('occupation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="marital_status" class="form-label">@lang('Marital Status') <span class="text-danger">*</span></label>
                                <select name="marital_status" id="marital_status" class="form-select @error('marital_status') is-invalid @enderror" required>
                                    <option value="" disabled @selected(!old('marital_status', $user->marital_status))>@lang('Select Status')</option>
                                    <option @selected(old('marital_status', $user->marital_status) == 'Single') value="Single">@lang('Single')</option>
                                    <option @selected(old('marital_status', $user->marital_status) == 'Married') value="Married">@lang('Married')</option>
                                    <option @selected(old('marital_status', $user->marital_status) == 'Widowed') value="Widowed">@lang('Widowed')</option>
                                    <option @selected(old('marital_status', $user->marital_status) == 'Separated') value="Separated">@lang('Separated')</option>
                                </select>
                                @error('marital_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Additional Information --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">@lang('Additional Information')</h6>
                    </div>
                    <div class="card-body">
                        <div class="row gy-3">
                            <div class="col-12">
                                <div class="form-group mb-3">
                                    <label for="preference" class="form-label">@lang('Preference') <span class="text-danger">*</span></label>
                                    <select name="preference" id="preference" class="form-select @error('preference') is-invalid @enderror" required>
                                        <option value="" disabled @selected(!old('preference', $user->preference))>@lang('Select your preference')</option>
                                        @foreach (['Advertising', 'Data Management', 'Donor Collection', 'Event Management', 'FB/Web Boost', 'FeceBook Page/Group Maintain', 'Fild Collection', 'Giving Advice', 'Graphics Design', 'Health Care', 'Law Support', 'Meeting Leading', 'Member Teaching', 'My Donation Only', 'New Member Collection', 'Phone Communication', 'Presentation Making', 'Project Planing', 'Web/App Development', 'Website Maintain', 'Others', 'Unknown'] as $option)
                                            <option value="{{ $option }}" @selected(old('preference', $user->preference) == $option)>{{ $option }}</option>
                                        @endforeach
                                    </select>
                                    @error('preference')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group mb-3">
                                    <label class="form-label d-block">@lang('Joining Media') <span class="text-danger">*</span></label>
                                    @php
                                        $joiningOptions = ['Existing Member', 'Website', 'Facebook Group', 'Facebook Page', 'YouTube', 'WhatsApp', 'Banner', 'Leaflet', 'Other'];
                                        $currentJoiningMedia = old('joining_media', $user->joining_media ?? '');
                                    @endphp
                                    <div class="d-flex gap-3 flex-wrap">
                                        @foreach ($joiningOptions as $option)
                                            <div class="form-check">
                                                <input class="form-check-input @error('joining_media') is-invalid @enderror" type="radio" name="joining_media" id="joining_media_{{ \Illuminate\Support\Str::slug($option) }}" value="{{ $option }}" @checked($currentJoiningMedia == $option) required>
                                                <label class="form-check-label" for="joining_media_{{ \Illuminate\Support\Str::slug($option) }}">
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
                            <div class="col-12 col-md-6">
                                <div class="form-group mb-3">
                                    <label for="reference" class="form-label">@lang('Reference Person/Phone') <span class="text-danger yeah d-none">*</span></label>
                                    <input placeholder="@lang('Reference person or phone')" type="text" id="reference" name="reference" class="form-control @error('reference') is-invalid @enderror" value="{{ old('reference', $user->reference ?? '') }}" />
                                    @error('reference')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group mb-3">
                                    <label for="political_relation" class="form-label">@lang('Political Relation') <span class="text-danger">*</span></label>
                                    <select name="political_relation" id="political_relation" class="form-select @error('political_relation') is-invalid @enderror" required>
                                        <option value="" disabled @selected(!old('political_relation', $user->political_relation))>@lang('Select Option')</option>
                                        <option @selected(old('political_relation', $user->political_relation) == 1) value="1">@lang('Yes')</option>
                                        <option @selected(old('political_relation', $user->political_relation) == 0) value="0">@lang('No')</option>
                                    </select>
                                    @error('political_relation')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group mb-3">
                                    <label for="post_and_politics" class="form-label">@lang('If Yes, Post & Politics')</label>
                                    <input type="text" id="post_and_politics" name="post_and_politics" class="form-control @error('post_and_politics') is-invalid @enderror" value="{{ old('post_and_politics', $user->post_and_politics ?? '') }}" />
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
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input @error('monthly_fee') is-invalid @enderror" type="radio" name="monthly_fee" id="monthly_fee_{{ $fee }}" value="{{ $fee }}" {{ old('monthly_fee', $user->monthly_fee) == $fee ? 'checked' : '' }} required>
                                                <label class="form-check-label" for="monthly_fee_{{ $fee }}">{{ $fee }} tk</label>
                                            </div>
                                        @endforeach
                                    </div>
                                    @error('monthly_fee')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Change Password --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">@lang('Change Password')</h6>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info" role="alert">
                           @lang('Leave the password fields blank if you do not want to change your password.')
                        </div>
                        <div class="row gy-3">
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="password" class="form-label">@lang('New Password')</label>
                                    <input 
                                        type="password" 
                                        id="password" 
                                        name="password" 
                                        class="form-control @error('password') is-invalid @enderror"
                                        placeholder="@lang('Enter a new password')" 
                                        autocomplete="new-password" 
                                    />
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="password_confirmation" class="form-label">@lang('Confirm New Password')</label>
                                    <input 
                                        type="password" 
                                        id="password_confirmation" 
                                        name="password_confirmation" 
                                        class="form-control"
                                        placeholder="@lang('Confirm the new password')" 
                                        autocomplete="new-password" 
                                    />
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column for Profile Picture and Submit --}}
            <div class="col-12 col-lg-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">@lang('Profile Picture')</h6>
                    </div>
                    <div class="card-body text-center">
                        <img src="{{ $user->image_path ? asset('storage/' . $user->image_path) : 'https://placehold.co/150' }}" alt="@lang('Profile Picture')" class="img-fluid rounded-circle mb-3" width="150" id="profile-pic-preview">
                        <div class="form-group">
                            <input type="file" class="form-control @error('image') is-invalid @enderror" id="image" name="image" accept="image/*">
                             @error('image')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <small class="d-block text-muted mt-2">@lang('JPG, GIF or PNG. Max size of 2MB')</small>
                    </div>
                </div>
                <div class="card shadow-sm">
                    <div class="card-body">
                        {{-- <div class="form-check mb-3">
                             <input type="checkbox" class="form-check-input @error('commitment') is-invalid @enderror" name="commitment" id="commitment" required @checked(old('commitment', $user->commitment ?? false))>
                            <label for="commitment" class="form-check-label">@lang('I declare that I will strive to do well for all Sanatani with SDF. I will always support & adhere to the SDF constitution. I will pay my monthly membership fee by the due date (Monthly fee for each member is at least 100 TK; for students & unemployed individuals, it is 50 TK).')</label>
                            @error('commitment')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div> --}}
                        <button type="submit" class="btn btn-primary btn-block w-100">@lang('Update Profile')</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/select2.min.css') }}">
    <style>
        .form-label {
            font-weight: 500;
        }
        .select2-container--default .select2-selection--single {
            height: calc(1.5em + .75rem + 2px);
            padding: .375rem .75rem;
            border: 1px solid #d1d3e2;
            border-radius: .35rem;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 1.5;
            padding-left: 0;
            color: #6e707e;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: calc(1.5em + .75rem);
            right: .5rem;
        }
        .is-invalid + .select2-container--default .select2-selection--single {
            border-color: #e74a3b;
        }
    </style>
@endpush

@push('scripts')
    <script src="{{ asset('assets/js/select2.min.js') }}"></script>
    <script>
        $(document).ready(function() {
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

            $('[name="joining_media"]').on('change', function() {
                if ("Existing Member" === $(this).val()) {
                    $('.yeah').removeClass('d-none');
                    $('[name="reference"]').attr('required', 'required');
                } else {
                    $('.yeah').addClass('d-none');
                    $('[name="reference"]').removeAttr('required');
                }
            }).trigger('change');

            $('#political_relation').on('change', function() {
                if ($(this).val() === 'yes') {
                    $('#post_and_politics').prop('required', true);
                    $('label[for="post_and_politics"]').append(' <span class="text-danger political-required">*</span>');
                } else {
                    $('#post_and_politics').prop('required', false);
                    $('.political-required').remove();
                    $('#post_and_politics').removeClass('is-invalid');
                }
            }).trigger('change');

            $('#image').on('change', function(event) {
                var reader = new FileReader();
                reader.onload = function(){
                    $('#profile-pic-preview').attr('src', reader.result);
                };
                reader.readAsDataURL(event.target.files[0]);
            });
        });

        // --- Address Dropdown Logic ---
        const addressData = @json(addressData());
        const $division   = $('#division');
        const $district   = $('#district');
        const $upazila    = $('#upazila');
        const $postOffice = $('#post_office');
        const $ward       = $('#ward');

        const divisions = Object.keys(addressData);
        let divHtml = '<option value="" disabled selected>@lang("Select Division")</option>';
        divHtml += divisions.map(div => `<option value="${div}">${div}</option>`).join('');
        $division.html(divHtml);

        function resetSelect($el, placeholder) {
            $el.html(`<option value="" disabled selected>@lang('${placeholder}')</option>`).trigger('change');
        }

        $division.on('change', function() {
            const division = $(this).val();
            resetSelect($district, 'Select District');
            resetSelect($upazila, 'Select Upazila/City');
            resetSelect($postOffice, 'Select Union/Pouroshova');
            resetSelect($ward, 'Select Ward');

            if (addressData[division]) {
                let districtOptions = '<option value="" disabled selected>@lang("Select District")</option>';
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

            if (addressData[division]?.[district]) {
                let upazilaOptions = '<option value="" disabled selected>@lang("Select Upazila/City")</option>';
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
                 let postOfficeOptions = '<option value="" disabled selected>@lang("Select Union/Pouroshova")</option>';
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
                 let wardOptions = '<option value="" disabled selected>@lang("Select Ward")</option>';
                $.each(wards, function(index, ward) {
                    wardOptions += `<option value="${ward}">${ward}</option>`;
                });
                $ward.html(wardOptions);
            }
        });

        // --- Pre-select address on page load ---
        (function() {
            let oldDivision = "{{ old('division', $user->division) }}";
            let oldDistrict = "{{ old('district', $user->district) }}";
            let oldUpazila = "{{ old('upazila', $user->upazila) }}";
            let oldPostOffice = "{{ old('post_office', $user->post_office) }}";
            let oldWard = "{{ old('ward', $user->ward) }}";

            if (oldDivision) {
                $division.val(oldDivision).trigger('change');
                setTimeout(() => {
                    if (oldDistrict && $district.find(`option[value="${oldDistrict}"]`).length) {
                        $district.val(oldDistrict).trigger('change');
                        setTimeout(() => {
                            if (oldUpazila && $upazila.find(`option[value="${oldUpazila}"]`).length) {
                                $upazila.val(oldUpazila).trigger('change');
                                setTimeout(() => {
                                    if (oldPostOffice && $postOffice.find(`option[value="${oldPostOffice}"]`).length) {
                                        $postOffice.val(oldPostOffice).trigger('change');
                                        setTimeout(() => {
                                            if (oldWard && $ward.find(`option[value="${oldWard}"]`).length) {
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
        })();
    </script>
@endpush