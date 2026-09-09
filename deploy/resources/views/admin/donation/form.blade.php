@extends('admin.layouts.app')

@section('content')
    <x-page-header 
        :page_title="isset($donor) ? __('Edit Donation') : 'Add Donation'" 
        :back_route="route('admin.donor.list')"
    />

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.donation.manual.submit') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row gy-4">
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