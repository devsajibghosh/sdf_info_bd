@extends('frontend.layouts.main')

@section('content')
    <div class="container card my-5 p-4 shadow-lg bg-warning">
        <h3 class="m-0 text-center fw-bold">@lang('Complete Your Profile')</h3>
        <p class="mb-4 text-center text-muted">@lang('Please complete your profile by submitting this form.')</p>

        <form method="POST" action="{{ route('user.save_profile_data') }}" enctype="multipart/form-data">
            @csrf

            {{-- Personal Details Section --}}
            <fieldset class="mb-4 p-3 border rounded">
                <legend class="w-auto h5">@lang('Personal Details')</legend>
                <div class="row">
                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="form-group mb-3">
                            <label for="first_name" class="form-label">@lang('First Name') <span
                                    class="text-danger">*</span></label>
                            <input type="text" id="first_name" name="first_name"
                                class="form--control @error('first_name') is-invalid @enderror"
                                value="{{ old('first_name', $user->first_name ?? ($user->first_name ?? '')) }}" required />
                            @error('first_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="form-group mb-3">
                            <label for="last_name" class="form-label">@lang('Last Name') <span
                                    class="text-danger">*</span></label>
                            <input type="text" id="last_name" name="last_name"
                                class="form--control @error('last_name') is-invalid @enderror"
                                value="{{ old('last_name', $user->last_name ?? ($user->last_name ?? '')) }}" required />
                            @error('last_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="form-group mb-3">
                            <label for="father_name" class="form-label">@lang('Father\'s Name') <span
                                    class="text-danger">*</span></label>
                            <input type="text" id="father_name" name="father_name"
                                class="form--control @error('father_name') is-invalid @enderror"
                                value="{{ old('father_name', $user->father_name ?? '') }}" required />
                            @error('father_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="form-group mb-3">
                            <label for="mother_name" class="form-label">@lang('Mother\'s Name') <span
                                    class="text-danger">*</span></label>
                            <input type="text" id="mother_name" name="mother_name"
                                class="form--control @error('mother_name') is-invalid @enderror"
                                value="{{ old('mother_name', $user->mother_name ?? '') }}" required />
                            @error('mother_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="form-group mb-3">
                            <label for="date_of_birth" class="form-label">@lang('Date of Birth') <span
                                    class="text-danger">*</span></label>
                            <input type="date" id="date_of_birth" name="date_of_birth"
                                class="form--control @error('date_of_birth') is-invalid @enderror"
                                value="{{ old('date_of_birth', $user->date_of_birth ? \Carbon\Carbon::parse($user->date_of_birth)->format('Y-m-d') : '') }}"
                                required />
                            @error('date_of_birth')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="form-group mb-3">
                            <label for="current_age" class="form-label">@lang('Current Age') <span
                                    class="text-danger">*</span></label>
                            <input type="number" id="current_age" name="current_age"
                                class="form--control @error('current_age') is-invalid @enderror"
                                value="{{ old('current_age', $user->current_age ?? '') }}" required readonly />
                            @error('current_age')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="form-group mb-3">
                            <label for="gender" class="form-label">@lang('Gender') <span
                                    class="text-danger">*</span></label>
                            <select name="gender" id="gender" class="form-select @error('gender') is-invalid @enderror"
                                required>
                                <option value="" disabled @selected(!old('gender', $usergender ?? ''))>@lang('Select Gender')</option>
                                <option @selected(old('gender', $user->gender ?? '') == 'male') value="male">@lang('Male')</option>
                                <option @selected(old('gender', $user->gender ?? '') == 'female') value="female">@lang('Female')</option>
                                <option @selected(old('gender', $user->gender ?? '') == 'other') value="other">@lang('Other')</option>
                            </select>
                            @error('gender')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="form-group mb-3">
                            <label for="blood_group" class="form-label">@lang('Blood Group')</label>
                            <select name="blood_group" id="blood_group"
                                class="form-select @error('blood_group') is-invalid @enderror">
                                <option value="" disabled @selected(!old('blood_group', $user->blood_group ?? ''))>@lang('Select Blood Group')</option>
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
                <div class="row">
                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="form-group mb-3">
                            <label for="id_type" class="form-label">@lang('ID Type') <span
                                    class="text-danger">*</span></label>
                            <select name="id_type" id="id_type"
                                class="form-select @error('id_type') is-invalid @enderror" required>
                                <option value="" disabled @selected(!old('id_type', $user->id_type ?? ''))>@lang('Select ID Type')</option>
                                <option @selected(old('id_type', $user->id_type ?? '') == 'nid') value="nid">@lang('NID')</option>
                                <option @selected(old('id_type', $user->id_type ?? '') == 'birth_certificate') value="birth_certificate">@lang('Birth Certificate')</option>
                                <option @selected(old('id_type', $user->id_type ?? '') == 'passport') value="passport">@lang('Passport')</option>
                            </select>
                            @error('id_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="form-group mb-3">
                            <label for="id_number" class="form-label">@lang('ID Number') <span
                                    class="text-danger">*</span></label>
                            <input type="text" id="id_number" name="id_number"
                                class="form--control @error('id_number') is-invalid @enderror"
                                value="{{ old('id_number', $user->id_number ?? '') }}" required />
                            @error('id_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="form-group mb-3">
                            <label for="phone_number" class="form-label">@lang('Phone Number') <span
                                    class="text-danger">*</span></label>
                            <input type="text" id="phone_number" name="phone_number" disabled
                                class="form--control @error('phone_number') is-invalid @enderror"
                                value="{{ old('phone_number', $user->phone_number ?? ($user->phone_number ?? '')) }}"
                                required placeholder="@lang('Your phone number(01XXXXXXXX)')" title="you can't change" />
                            @error('phone_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="form-group mb-3">
                            <label for="whatsapp_number" class="form-label">@lang('Whatsapp Number')</label>
                            <input type="text" id="whatsapp_number" name="whatsapp_number"
                                class="form--control @error('whatsapp_number') is-invalid @enderror"
                                value="{{ old('whatsapp_number', $user->whatsapp_number ?? '') }}" />
                            @error('whatsapp_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="form-group mb-3">
                            <label for="country" class="form-label">@lang('Present Country') <span
                                    class="text-danger">*</span></label>
                            <select name="country" id="country"
                                class="form-select @error('country') is-invalid @enderror" required>
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
                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="form-group mb-3">
                            <label for="facebook_id_link" class="form-label">@lang('Facebook ID link')</label>
                            <input type="url" id="facebook_id_link" name="facebook_id_link"
                                class="form--control @error('facebook_id_link') is-invalid @enderror"
                                value="{{ old('facebook_id_link', $user->facebook_id_link ?? '') }}" />
                            @error('facebook_id_link')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="form-group mb-3">
                            <label for="image" class="form-label">@lang('Profile Image')</label>
                            <input type="file" id="image" name="image"
                                class="form--control @error('image') is-invalid @enderror" accept="image/*" />
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if (isset($user->image_path) && $user->image_path)
                                <img src="{{ asset('storage/' . $user->image_path) }}" alt="Profile Image"
                                    class="img-thumbnail mt-2" width="100">
                            @endif
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="form-group mb-3">
                            <label for="nid_front" class="form-label">@lang('NID Front')</label>
                            <input type="file" id="nid_front" name="nid_front"
                                class="form--control @error('nid_front') is-invalid @enderror" accept="image/*" />
                            @error('nid_front')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="form-group mb-3">
                            <label for="nid_back" class="form-label">@lang('NID Back')</label>
                            <input type="file" id="nid_back" name="nid_back"
                                class="form--control @error('nid_back') is-invalid @enderror" accept="image/*" />
                            @error('nid_back')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </fieldset>

            {{-- Professional & Marital Status --}}
            <fieldset class="mb-4 p-3 border rounded">
                <legend class="w-auto h5">@lang('Professional & Marital Status')</legend>
                <div class="row">
                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="form-group mb-3">
                            <label for="occupation" class="form-label">@lang('Occupation')<span
                                    class="text-danger">*</span></label>
                            <select name="occupation" id="occupation" requried
                                class="form-select @error('occupation') is-invalid @enderror">
                                <option value="" disabled @selected(!old('occupation', $user->occupation ?? ''))>@lang('Select Occupation')</option>
                                <option @selected(old('occupation', $user->occupation ?? '') == 'Actor') value="Actor">@lang('Actor')</option>
                                <option @selected(old('occupation', $user->occupation ?? '') == 'Assistant Professor') value="Assistant Professor">@lang('Assistant Professor')
                                </option>
                                <option @selected(old('occupation', $user->occupation ?? '') == 'Associate Professor') value="Associate Professor">@lang('Associate Professor')
                                </option>
                                <option @selected(old('occupation', $user->occupation ?? '') == 'Bank Job') value="Bank Job">@lang('Bank Job')</option>
                                <option @selected(old('occupation', $user->occupation ?? '') == 'Barber') value="Barber">@lang('Barber')</option>
                                <option @selected(old('occupation', $user->occupation ?? '') == 'College Teacher') value="College Teacher">@lang('College Teacher')</option>
                                <option @selected(old('occupation', $user->occupation ?? '') == 'Driver') value="Driver">@lang('Driver')</option>
                                <option @selected(old('occupation', $user->occupation ?? '') == 'Doctor') value="Doctor">@lang('Doctor')</option>
                                <option @selected(old('occupation', $user->occupation ?? '') == 'Engineer') value="Engineer">@lang('Engineer')</option>
                                <option @selected(old('occupation', $user->occupation ?? '') == 'Expart') value="Expart">@lang('Expart')</option>
                                <option @selected(old('occupation', $user->occupation ?? '') == 'Farmer') value="Farmer">@lang('Farmer')</option>
                                <option @selected(old('occupation', $user->occupation ?? '') == 'Garments') value="Garments">@lang('Garments')</option>
                                <option @selected(old('occupation', $user->occupation ?? '') == 'Goverment Job') value="Goverment Job">@lang('Goverment Job')</option>
                                <option @selected(old('occupation', $user->occupation ?? '') == 'High School Teacher') value="High School Teacher">@lang('High School Teacher')
                                </option>
                                <option @selected(old('occupation', $user->occupation ?? '') == 'Health Services') value="Health Services">@lang('Health Services')</option>
                                <option @selected(old('occupation', $user->occupation ?? '') == 'House Wife') value="House Wife">@lang('House Wife')</option>
                                <option @selected(old('occupation', $user->occupation ?? '') == 'Journalists') value="Journalists">@lang('Journalists')</option>
                                <option @selected(old('occupation', $user->occupation ?? '') == 'Lawyer') value="Lawyer">@lang('Lawyer')</option>
                                <option @selected(old('occupation', $user->occupation ?? '') == 'Lecturer') value="Lecturer">@lang('Lecturer')</option>
                                <option @selected(old('occupation', $user->occupation ?? '') == 'Military') value="Military">@lang('Military')</option>
                                <option @selected(old('occupation', $user->occupation ?? '') == 'NGO Job') value="NGO Job">@lang('NGO Job')</option>
                                <option @selected(old('occupation', $user->occupation ?? '') == 'Pharmaceutical Job') value="Pharmaceutical Job">@lang('Pharmaceutical Job')
                                </option>
                                <option @selected(old('occupation', $user->occupation ?? '') == 'Primary School Teacher') value="Primary School Teacher">@lang('Primary School Teacher')
                                </option>
                                <option @selected(old('occupation', $user->occupation ?? '') == 'Privet Job') value="Privet Job">@lang('Privet Job')</option>
                                <option @selected(old('occupation', $user->occupation ?? '') == 'Professor') value="Professor">@lang('Professor')</option>
                                <option @selected(old('occupation', $user->occupation ?? '') == 'Police') value="Police">@lang('Police')</option>
                                <option @selected(old('occupation', $user->occupation ?? '') == 'Politician') value="Politician">@lang('Politician')</option>
                                <option @selected(old('occupation', $user->occupation ?? '') == 'Retirement') value="Retirement">@lang('Retirement')</option>
                                <option @selected(old('occupation', $user->occupation ?? '') == 'Self Business') value="Self Business">@lang('Self Business')</option>
                                <option @selected(old('occupation', $user->occupation ?? '') == 'Social worker') value="Social worker">@lang('Social worker')</option>
                                <option @selected(old('occupation', $user->occupation ?? '') == 'Student') value="Student">@lang('Student')</option>
                                <option @selected(old('occupation', $user->occupation ?? '') == 'Tailor') value="Tailor">@lang('Tailor')</option>
                                <option @selected(old('occupation', $user->occupation ?? '') == 'Telecommunication Job') value="Telecommunication Job">@lang('Telecommunication Job')
                                </option>
                                <option @selected(old('occupation', $user->occupation ?? '') == 'Others') value="Others">@lang('Others')</option>
                            </select>
                            @error('occupation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="form-group mb-3">
                            <label for="marital_status" class="form-label">@lang('Marital Status') <span
                                    class="text-danger">*</span></label>
                            <select name="marital_status" id="marital_status"
                                class="form-select @error('marital_status') is-invalid @enderror" required>
                                <option value="" disabled @selected(!old('marital_status', $user->marital_status ?? ''))>@lang('Select Status')</option>
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
                <legend class="w-auto h5">@lang('Address Information')</legend>
                <p>@lang('Permanent Address')</p>
                <div class="row">
                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="form-group mb-3">
                            <label for="division" class="form-label">@lang('Division') <span
                                    class="text-danger">*</span></label>
                            <select name="division" id="division"
                                class="form-select @error('division') is-invalid @enderror" required>
                                <option value="" disabled selected>@lang('Select Division')</option>
                            </select>
                            @error('division')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="form-group mb-3">
                            <label for="district" class="form-label">@lang('District') <span
                                    class="text-danger">*</span></label>
                            <select name="district" id="district"
                                class="form-select @error('district') is-invalid @enderror" required>
                                <option value="" disabled selected>@lang('Select District')</option>
                            </select>
                            @error('district')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="form-group mb-3">
                            <label for="upazila" class="form-label">@lang('Upazila/City Corporation') <span
                                    class="text-danger">*</span></label>
                            <select name="upazila" id="upazila"
                                class="form-select @error('upazila') is-invalid @enderror" required>
                                <option value="" disabled selected>@lang('Select Upazila/City')</option>
                            </select>
                            @error('upazila')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="form-group mb-3">
                            <label for="post_office" class="form-label">@lang('Union/Pouroshova') <span
                                    class="text-danger">*</span></label>
                            <select name="post_office" id="post_office"
                                class="form-select @error('post_office') is-invalid @enderror" required>
                                <option value="" disabled selected>@lang('Select Union/Pouroshova')</option>
                            </select>
                            @error('post_office')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="form-group mb-3">
                            <label for="ward" class="form-label">@lang('Ward No')</label>
                            <select name="ward" id="ward"
                                class="form-select @error('ward') is-invalid @enderror">
                                <option value="" disabled selected>@lang('Select Union/Pouroshova')</option>
                            </select>
                            @error('ward')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 col-md-6">
                        <p class="mt-2 mb-1">@lang('Present Address')</p>
                        <input type="text name" name="present_address" placeholder="@lang('House, Road, Village, Post, Thana, District')"
                            value="{{ old('present_address') }}" class="form-control form--control">
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="form-group mb-3">
                            <label for="preference" class="form-label">@lang('Preference') <span
                                    class="text-danger">*</span></label>
                            <select name="preference" id="preference"
                                class="form-select @error('preference') is-invalid @enderror" required>
                                <option value="" disabled @selected(!old('preference'))>@lang('কোন কাজে সহায়তা করতে চান, তা সিলেক্ট করুন')</option>
                                @foreach (['Advertising', 'Data Management', 'Donor Collection', 'Event Management', 'FB/Web Boost', 'FeceBook Page/Group Maintain', 'Fild Collection', 'Giving Advice', 'Graphics Design', 'Health Care', 'Law Support', 'Meeting Leading', 'Member Teaching', 'My Donation Only', 'New Member Collection', 'Phone Communication', 'Presentation Making', 'Project Planing', 'Web/App Development', 'Website Maintain', 'Others', 'Unknown'] as $option)
                                    <option value="{{ $option }}" @selected(old('preference') == $option)>{{ $option }}
                                    </option>
                                @endforeach
                            </select>
                            @error('preference')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </fieldset>

            {{-- Additional Information Section --}}
            <fieldset class="mb-4 p-3 border rounded">
                <legend class="w-auto h5">@lang('Additional Information')</legend>
                <div class="row">
                    <div class="col-12">
                        <div class="form-group mb-3">
                            <label class="form-label d-block">@lang('Joining Media') <span
                                    class="text-danger">*</span></label>
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
                                            value="{{ $option }}" @checked($currentJoiningMedia == $option) required>
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

                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="form-group mb-3">
                            <label for="reference" class="form-label">@lang('Reference Person/Phone') <span
                                    class="text-danger yeah d-none">*</span></label>
                            <input placeholder="@lang('Reference person or phone')" type="text" id="reference" name="reference"
                                class="form--control yeah-input @error('reference') is-invalid @enderror"
                                value="{{ old('reference', $user->reference ?? '') }}" />
                            @error('reference')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

<!-- start polictical relation data -->


 {{-- Political Relation --}}
    <div class="col-12 col-md-6 col-lg-4">
        <div class="form-group mb-3">
            <label for="political_relation" class="form-label">
                @lang('Political Relation') <span class="text-danger">*</span>
            </label>

            <select name="political_relation" id="political_relation"
                class="form-select @error('political_relation') is-invalid @enderror" required>

                <option value="" disabled
                    @selected(!old('political_relation', $user->political_relation ?? ''))>
                    @lang('Select Option')
                </option>

                <option value="1"
                    @selected(old('political_relation', $user->political_relation ?? '') == 1)>
                    @lang('Yes')
                </option>

                <option value="0"
                    @selected(old('political_relation', $user->political_relation ?? '') == 0)>
                    @lang('No')
                </option>

            </select>

            @error('political_relation')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    {{-- Post & Politics (Hidden by default) --}}
    <div class="col-12 col-lg-4" id="postPoliticsWrapper">
        <div class="form-group mb-3">
            <label for="post_and_politics" class="form-label">
                @lang('If Yes, Post & Politics')
            </label>

            <input type="text" id="post_and_politics" name="post_and_politics"
                class="form--control @error('post_and_politics') is-invalid @enderror"
                value="{{ old('post_and_politics', $user->post_and_politics ?? '') }}">

            @error('post_and_politics')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>


                    
                    
<!-- political relation end data -->

                    <div class="col-12">
                        <div class="form-group mb-3">
                            <label class="form-label">@lang('Monthly Fee') <span class="text-danger">*</span></label>
                            <div>
                                @foreach ([50, 100, 200, 500, 1000, 2000, 3000, 5000] as $fee)
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input @error('monthly_fee') is-invalid @enderror"
                                            type="radio" name="monthly_fee" id="monthly_fee_{{ $fee }}"
                                            value="{{ $fee }}" {{ old('monthly_fee') == $fee ? 'checked' : '' }}
                                            required>
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

            <div class="col-12 d-flex mb-4 gap-2 align-items-start">
                <input type="checkbox"
                    class="form-check-input mt-1 commitment-checkbox @error('commitment') is-invalid @enderror"
                    name="commitment"
                    id="commitment"
                    required
                    @checked(old('commitment', $user->commitment ?? false))>
                <label for="commitment" class="form-check-label">@lang('I declare that I will strive to do well for all Sanatani with SDF. I will always support & adhere to the SDF constitution. I will pay my monthly membership fee by the due date (Monthly fee for each member is at least 100 TK; for students & unemployed individuals, it is 50 TK).')</label>
                @error('commitment')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

<button type="submit"
    class="w-100 btn btn-primary btn-lg submit-btn">
    @lang('Submit Profile')
</button>

        </form>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/select2.min.css') }}">
    <style>
    


/* ===== Disabled Input Visibility Fix ===== */
input:disabled,
.form--control:disabled {
    background-color: #f9fafb !important;
    border: 2px solid #9ca3af !important; /* Visible border */
    color: #374151 !important;
    opacity: 1 !important; /* Prevent fade-out */
    cursor: not-allowed;
}

/* Optional: lock icon feel */
input:disabled::placeholder {
    color: #6b7280;
}

input{
    background-color: #fff !important;
    color: #fff !important; /* Dark text */
    border: 2px solid #fff;
    
}
input::placeholder {
    color: #fff;
}

input:checked {
  border: none;
  background-color: #1F53FF !important;
  outline: 2px solid deeppink;
}

input:focus {
    border-color: #fff;
    font-weight: bold;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
}



/*button load view*/
    
.submit-btn {
    position: relative;
    font-weight: 600;
    border-radius: 999px;
    transition: all 0.25s ease;
    box-shadow: 0 10px 25px rgba(255, 193, 7, 0.35);
}

/* Hover */
.submit-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 14px 30px rgba(255, 193, 7, 0.45);
}

/* Active (click effect) */
.submit-btn:active {
    transform: scale(0.98);
    box-shadow: 0 6px 14px rgba(255, 193, 7, 0.35);
}

/* Disabled state */
.submit-btn:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

/* Loading spinner */
.submit-btn.loading {
    pointer-events: none;
    background-color: #e0a800 !important;
}

.submit-btn.loading::after {
    content: '';
    width: 22px;
    height: 22px;
    border: 3px solid rgba(255, 255, 255, 0.4);
    border-top-color: #fff;
    border-radius: 50%;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    animation: spin 0.8s linear infinite;
}

.submit-btn.loading span {
    visibility: hidden;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

    
    
/*check box style*/
        
    .commitment-checkbox {
    width: 20px;
    height: 20px;
    border: 3px solid #000 !important;
    border-radius: 2px;
    background-color: #ffffff;
    cursor: pointer;
    appearance: none;
    -webkit-appearance: none;
    outline: none;
}

/* Checked state */
.commitment-checkbox:checked {
    background-color: #2563eb !important; /* Blue */
    border-color: #000 !important;
}

/* Optional check mark */
.commitment-checkbox:checked::after {
    content: "✔";
    color: #ffffff;
    font-size: 14px;
    position: absolute;
    top: -1px;
    left: 3px;
}

/* Focus state */
.commitment-checkbox:focus {
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.25);
}

/* Invalid state */
.commitment-checkbox.is-invalid {
    border-color: #000 !important;
}


    
        .select2-container--default .select2-selection--single {
            height: calc(1.5em + .75rem + 2px);
            padding: .375rem .75rem;
            border: 1px solid #ced4da;
            border-radius: .25rem;
            min-height: 53px;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 1.5em;
            padding-left: 0;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: calc(1.5em + .75rem);
            right: .5rem;
        }

        .select2-container--default.select2-container--open .select2-selection--single {
            border-color: #86b7fe;
            box-shadow: 0 0 0 .25rem rgba(13, 110, 253, .25);
        }

        /* For invalid state */
        select.is-invalid+.select2-container--default .select2-selection--single {
            border-color: #dc3545;
        }

        select.is-invalid+.select2-container--default.select2-container--open .select2-selection--single {
            box-shadow: 0 0 0 .25rem rgba(220, 53, 69, .25);
        }

        .form-label {
            font-weight: 500;
        }

        fieldset legend {
            font-weight: 600;
        }
        
    /*warpe political relatiion data*/
        
    #postPoliticsWrapper {
        display: none;
    }
        
    </style>
@endpush

@push('scripts')
    <script src="{{ asset('assets/js/select2.min.js') }}"></script>
    <script>
        
    // button load view script
    
    $('form').on('submit', function () {
        const btn = $('.submit-btn');
        btn.addClass('loading');
        btn.prop('disabled', true);
    });
    
    
    
     $(document).ready(function () {

        function togglePostPolitics() {
            let value = $('#political_relation').val();

            if (value === '1') {
                $('#postPoliticsWrapper').slideDown();
            } else {
                $('#postPoliticsWrapper').slideUp();
            }
        }

        // On page load (edit mode)
        togglePostPolitics();

        // On select change
        $('#political_relation').on('change', function () {
            togglePostPolitics();
        });

    });
        

    // political relaton end area
    
        $(document).ready(function() {
            $('[name="joining_media"]').on('change', function() {
                const jM = $(this).val();

                if ("Existing Member" == jM) {
                    $('.yeah').removeClass('d-none');
                    $('[name="reference"').attr('required', 'required');
                } else {
                    $('.yeah').addClass('d-none');
                    $('[name="reference"').removeAttr('required');
                }

            });

            $('select:not(#division, #district, #upazila, #post_office)')
                .select2({ // Exclude address dropdowns initially if they are dynamically populated
                    width: '100%',
                    theme: 'default'
                });

            $('#division, #district, #upazila, #post_office').select2({
                width: '100%',
                theme: 'default'
            });


            var divisions = @json(divisions());
            var districts = @json(districts());
            var upazilas = @json(upazilas());
            var postcodes = @json(postcodes());

            var oldDivision = "{{ old('division', $user->division_id ?? '') }}";
            var oldDistrict = "{{ old('district', $user->district_id ?? '') }}";
            var oldUpazila = "{{ old('upazila', $user->upazila_id ?? '') }}";
            var oldPostOffice = "{{ old('post_office', $user->post_office ?? '') }}";

            // function populateDistricts(divisionId, selectedDistrict = null) {
            //     const filteredDistricts = districts.filter(d => d.fields.division == divisionId);
            //     $('#district').html('<option value="" disabled selected>@lang('Select District')</option>');
            //     $('#upazila').html('<option value="" disabled selected>@lang('Select Upazila/City')</option>');
            //     $('#post_office').html('<option value="" disabled selected>@lang('Select Union/Pouroshova')</option>');

            //     filteredDistricts.forEach(d => {
            //         $('#district').append(
            //             `<option value="${d.pk}" ${selectedDistrict == d.pk ? 'selected' : ''}>${d.fields.name}</option>`
            //         );
            //     });
            //     $('#district').trigger('change.select2'); // Notify Select2
            //     if (selectedDistrict) {
            //         $('#district').trigger('change'); // Trigger change to load upazilas
            //     }
            // }

            // function populateUpazilas(districtId, selectedUpazila = null) {
            //     const filteredUpazilas = upazilas.filter(u => u.fields.district == districtId);
            //     $('#upazila').html('<option value="" disabled selected>@lang('Select Upazila/City')</option>');
            //     $('#post_office').html('<option value="" disabled selected>@lang('Select Union/Pouroshova')</option>');

            //     filteredUpazilas.forEach(u => {
            //         $('#upazila').append(
            //             `<option value="${u.pk}" data-name="${u.fields.name}" ${selectedUpazila == u.pk ? 'selected' : ''}>${u.fields.name}</option>`
            //         );
            //     });
            //     $('#upazila').trigger('change.select2');
            //     if (selectedUpazila) {
            //         $('#upazila').trigger('change'); // Trigger change to load post offices
            //     }
            // }

            // function populatePostOffices(upazilaId, selectedPostOffice = null) {
            //     const filteredPostOffices = postcodes.filter(p => p.fields.sub_district ==
            //         upazilaId); // Assuming sub_district is upazila PK
            //     $('#post_office').html('<option value="" disabled selected>@lang('Select Union/Pouroshova')</option>');

            //     filteredPostOffices.forEach(p => {
            //         // Assuming post_office name is the value to be stored
            //         $('#post_office').append(
            //             `<option value="${p.fields.name}" ${selectedPostOffice == p.fields.name ? 'selected' : ''}>${p.fields.name}</option>`
            //         );
            //     });
            //     $('#post_office').trigger('change.select2');
            // }


            // $('#division').on('change', function() {
            //     const divisionId = $(this).val();
            //     populateDistricts(divisionId);
            // });

            // $('#district').on('change', function() {
            //     const districtId = $(this).val();
            //     populateUpazilas(districtId);
            // });

            // $('#upazila').on('change', function() {
            //     const upazilaId = $(this).val();
            //     populatePostOffices(upazilaId);
            // });

            // --- Handle old values for address dropdowns on page load ---
            // if (oldDivision) {
            //     $('#division').val(oldDivision).trigger('change.select2'); // Set and notify Select2

            //     setTimeout(function() {
            //         populateDistricts(oldDivision, oldDistrict);
            //         if (oldDistrict) {
            //             setTimeout(function() {
            //                 populateUpazilas(oldDistrict, oldUpazila);
            //                 if (oldUpazila) {
            //                     setTimeout(function() {
            //                         populatePostOffices(oldUpazila, oldPostOffice);
            //                     }, 200); // Adjust timing if needed
            //                 }
            //             }, 200); // Adjust timing if needed
            //         }
            //     }, 100); // Adjust timing if needed
            // }


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
            }).trigger('blur'); // Trigger on load to calculate age if DOB is pre-filled

            // Make post_and_politics required if political_relation is 'yes'
            $('#political_relation').on('change', function() {
                if ($(this).val() === 'yes') {
                    $('#post_and_politics').prop('required', true);
                    $('label[for="post_and_politics"]').append(
                        ' <span class="text-danger political-required">*</span>');
                } else {
                    $('#post_and_politics').prop('required', false);
                    $('.political-required').remove();
                    $('#post_and_politics').removeClass('is-invalid'); // Clear validation state if any
                    $('#post_and_politics').next('.invalid-feedback').remove(); // Clear error message
                }
            }).trigger('change'); // Trigger on load

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

        function reset($el, placeholder) {
            $el.empty().append(`<option value="" disabled selected>${placeholder}</option>`);
        }

        $division.on('change', function() {
            const division = $(this).val();
            reset($district, 'Select District');
            reset($upazila, 'Select Upazila/City');
            reset($postOffice, 'Select Union/Pouroshova');
            $ward.val('');

            if (addressData[division]) {
                $.each(addressData[division], function(district) {
                    $district.append(`<option value="${district}">${district}</option>`);
                });
            }
        });

        $district.on('change', function() {
            const division = $division.val();
            const district = $(this).val();
            reset($upazila, 'Select Upazila/City');
            reset($postOffice, 'Select Union/Pouroshova');
            $ward.val('');

            if (addressData[division] && addressData[division][district]) {
                $.each(addressData[division][district], function(upazila) {
                    $upazila.append(`<option value="${upazila}">${upazila}</option>`);
                });
            }
        });

        $upazila.on('change', function() {
            const division = $division.val();
            const district = $district.val();
            const upazila = $(this).val();
            reset($postOffice, 'Select Union/Pouroshova');
            $ward.val('');

            if (addressData[division] && addressData[division][district] && addressData[division][district][
                    upazila
                ]) {
                $.each(addressData[division][district][upazila], function(postOffice) {
                    $postOffice.append(`<option value="${postOffice}">${postOffice}</option>`);
                });
            }
        });

        $postOffice.on('change', function() {
            const division = $division.val();
            const district = $district.val();
            const upazila = $upazila.val();
            const postOffice = $(this).val();

            const wards = addressData[division]?.[district]?.[upazila]?.[postOffice] || [];

            // Clear and reset ward select
            $ward.empty().append('<option value="" disabled selected>Select Ward</option>');

            if (wards.length > 0) {
                $.each(wards, function(index, ward) {
                    $ward.append(`<option value="${ward}">${ward}</option>`);
                });
            }
        });
    </script>
@endpush
