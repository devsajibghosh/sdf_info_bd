@extends('admin.layouts.app')

@section('content')
    <x-page-header 
        :page_title="isset($donor) ? __('Donor Details') : 'Add Donor'" 
        :back_route="route('admin.donor.list')"
    />

    <div class="row">
        <div class="col-lg-3">
            <x-widgets.one 
                link="{{ route('admin.donation.list', ['search' => $donor->phone_number]) }}"
                icon="hash" 
                :value="System::amountWithCurrency($widget['total_donation_amount'])"
                :title="__('Total Donation Amount')"
                class="h-100"
            />
        </div>

        <div class="col-lg-3">
            <x-widgets.one 
                link="{{ route('admin.donation.list', ['search' => $donor->phone_number]) }}"
                icon="hash" 
                :value="number_format($widget['number_of_donations'])"
                :title="__('Total Number Of Donations')"
                class="h-100"
                />
        </div>

        <div class="col-lg-3">
            <x-widgets.one 
                icon="hand-coins" 
                :value="System::amountWithCurrency(amount($widget['last_donation_amount']))"
                class="h-100"
                :title="__('Last Donation Amount')"
                subtitle="{{ $widget['last_donation_date'] ? System::getDateTime($widget['last_donation_date']) : 'N/A' }}"
            />
        </div>

        <div class="col-lg-3">
            <x-widgets.one 
                icon="hand-coins" 
                :value="System::amountWithCurrency(amount($widget['maximum_donation_amount']))"
                :title="__('Maximum Donation Amount')"
                subtitle="{{ $widget['max_donation_date'] ? System::getDateTime($widget['max_donation_date']) : 'N/A' }}"
            />
        </div>
        
        <div class="col-lg-3">
            <x-widgets.one
                icon="hash" 
                :value="System::amountWithCurrency($widget['total_collected_amount'])"
                :title="__('Total Collection Amount')"
                class="h-100"
            />
        </div>
        
        <div class="col-lg-3">
            <x-widgets.one  
                icon="hash" 
                :value="number_format($widget['number_of_field_donations'])"
                :title="__('Total Number Of Collection')"
                class="h-100"
                />
        </div>

    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.donor.save', @$donor->id) }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row gy-4">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label>@lang('Name')</label>
                            <input 
                                type="text" class="form-control" name="name"
                                value="{{ old('name', $donor->name ?? '') }}"  
                            />
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="form-group">
                            <label>@lang('Phone Number')</label>
                            <input 
                                type="text" 
                                class="form-control" 
                                name="phone_number"
                                value="{{ old('phone_number', $donor->phone_number ?? '') }}"  
                            />
                        </div>
                    </div>

                    <div class="col-lg-6">
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

                    <div class="col-lg-6">
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

                    <div class="col-lg-12">
                        <div class="form-group">
                            <label>@lang('Address')</label>
                            <textarea 
                                type="text" 
                                class="form-control" 
                                name="address"
                            >{{old('address', $donor->address ?? '')}}</textarea>
                        </div>
                    </div> 
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <x-button type="submit">
                        <x-icons.save />
                        @lang('Save')
                    </x-button>
                </div>
            </form>
        </div>
    </div>
@endsection 