@extends('admin.layouts.app')

@section('content')
    @php
        if(!@$donor) $donor = new \App\Models\Donor();
    @endphp

    <x-page-header 
        :page_title="__('Manual Donation Form')" 
        :back_route="route('admin.donor.list')"
    >
        <x-button class="uploadBtn btn-info">@lang('Upload CSV')</x-button>
    </x-page-header>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.donation.manual.submit') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row gy-4">
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label>@lang('Date')</label>
                            <input 
                                type="date" 
                                class="form-control" 
                                name="date"
                                value="{{ old('date', optional($donor->created_at)->format('Y-m-d')) }}"  
                            />
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="form-group">
                            <label>@lang('Donator Name')</label>
                            <input 
                                type="text" 
                                class="form-control" 
                                name="name"
                                value="{{ old('name', @$donor->name ?? '') }}"  
                                placeholder="@lang('Plese enter the donator name')"
                            />
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="form-group">
                            <label>@lang('Phone Number')</label>
                            <input 
                                type="text" class="form-control" 
                                name="phone_number"
                                value="{{ old('phone_number', $donor->phone_number ?? '') }}"  
                                placeholder="@lang('Plese enter the phone number')"
                                required
                            />
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="form-group">
                            <label>@lang('Amount')</label>
                            <input 
                                type="text" class="form-control" 
                                name="amount"
                                value="{{ old('amount', $donor->amount ?? '') }}"  
                                required
                                placeholder="@lang('Please enter the donate amount')"
                            />
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="form-group">
                            <label>@lang('Donation Category')</label>
                            <select name="donation_category_id" required id="donation_category_id" class="form-control select2">
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">
                                        {{ __($category->name) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="form-group">
                            <label>@lang('Payment Via')</label>
                            <select name="payment_gateway_id" required id="payment_gateway_id" class="form-control select2">
                                @foreach ($paymentGateways as $gateway)
                                    <option value="{{ $gateway->id }}">
                                        {{ __($gateway->name) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div> 

                    <div class="col-lg-4">
                        <div class="form-group">
                            <label>@lang('Field Collection')</label>
                            <select name="field_collection" required id="field_collection" class="form-control select2">
                                <option value="0">@lang('No')</option>
                                <option value="1">@lang('Yes')</option>
                            </select>
                        </div>
                    </div> 
                </div>

                <div class="d-flex justify-content-end mt-4">
                
                <x-button id="saveandback" type="submit" class="btn-danger">
                        <x-icons.save />
                        @lang('Save & Exit')
                </x-button>
                
                </div>
            </form>
        </div>
    </div>

    <x-modal 
        id="uploadModal" 
        title="Upload Donation Data" 
        :form="true" 
        method="POST" 
        action="{{ route('admin.donation.upload') }}"
    >
        @csrf
    
        <div class="alert alert-info">
            @lang('Please download the sample file by ') <a href="{{ asset('sample.csv') }}" download="">@lang('clicking here')</a>
        </div>
        
        <x-form.group>
            <x-form.label>@lang('Upload The File')</x-form.label>
            <x-form.input name="file" type="file" />
        </x-form.group>
    
        <x-slot:footer>
            <x-button type="submit">
                <x-icons.save />
                @lang('Upload')
            </x-button>
        </x-slot:footer>
    </x-modal>
@endsection 


@push('scripts')
    <script>
        $('.uploadBtn').on('click', function() {
            $('#uploadModal').modal('show');
        });
        
        document.getElementById('saveandback').addEventListener('click', function (e) {
    const btn = this;
    const form = btn.closest('form');

    // Prevent multiple clicks if form is already submitting
    if (btn.disabled) return;

    btn.disabled = true;
    btn.innerHTML = 'Saving...';
    
    form.submit(); 
});


        
        
    </script>
@endpush