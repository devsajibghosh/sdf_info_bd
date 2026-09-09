@extends('admin.layouts.settings')

@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <form class="ajax-form" action="{{ route('admin.setting.general.update') }}" method="POST"
                enctype="multipart/form-data">
                @csrf

                <x-card>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h4>@lang('General Setting')</h4>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <x-form.group for="site_title" label="Site Title">
                                <input type="text" name="site_title" id="site_title" class="form-control"
                                    value="{{ old('site_title', $generalSetting['site_title']) }}">
                            </x-form.group>
                        </div>

                        <div class="col-md-4">
                            <x-form.group for="site_email" label="Site Email">
                                <input type="email" name="site_email" id="site_email" class="form-control"
                                    value="{{ old('site_email', $generalSetting['site_email']) }}">
                            </x-form.group>
                        </div>

                        <div class="col-md-4">
                            <x-form.group for="verification_method" label="Verification Method">
                                <select class="form-control" name="verification_method">
                                    <option value="email" @selected($generalSetting['verification_method'] == 'email')>@lang('Email')</option>
                                    <option value="phone" @selected($generalSetting['verification_method'] == 'phone')>@lang('Phone')</option>
                                </select>
                            </x-form.group>
                        </div>

                        <div class="col-md-4">
                            <x-form.group for="otp_time" label="OTP Verification Time(Seconds)">
                                <input type="number" name="otp_time" id="otp_time" class="form-control"
                                    value="{{ old('otp_time', $generalSetting['otp_time']) }}">
                            </x-form.group>
                        </div>

                        <div class="col-md-4">
                            <x-form.group for="site_phone" label="Site Phone">
                                <input type="text" name="site_phone" id="site_phone" class="form-control"
                                    value="{{ old('site_phone', $generalSetting['site_phone']) }}">
                            </x-form.group>
                        </div>

                        <div class="col-md-4">
                            <x-form.group for="app_url" label="App Url">
                                <input 
                                    type="text" 
                                    name="app_url" 
                                    id="app_url" 
                                    class="form-control"
                                    value="{{ old('app_url', $generalSetting['app_url']) }}">
                            </x-form.group>
                        </div>

                        <div class="col-md-4">
                            <x-form.group for="base_color" label="Base Color">
                                <input type="color" class="form-control color-picker" name="base_color" value="{{ old('base_color', $generalSetting['base_color']) }}" />
                            </x-form.group>
                        </div>

                        <div class="col-md-4">
                            <x-form.group for="software_version" label="System Currency">
                                <select name="currency" class="form-control select2" data-placeholder="@lang('Select A Currency')"
                                    id="currency">
                                    @foreach (System::currencies() as $currency)
                                        <option @selected($generalSetting['currency'] == $currency['code']) value="{{ $currency['code'] }}">
                                            {{ $currency['code'] }} - {{ $currency['name'] }} -
                                            {{ html_entity_decode($currency['symbol']) }}</option>
                                    @endforeach
                                </select>
                            </x-form.group>
                        </div>

                        <div class="col-md-4">
                            <x-form.group for="timezone" label="Timezone">
                                <select name="timezone" class="form-control select2" data-placeholder="@lang('Select A Timezone')"
                                    id="timezone">
                                    @foreach (DateTimeZone::listIdentifiers() as $tz)
                                        <option @selected($generalSetting['timezone'] == $tz) value="{{ $tz }}">
                                            {{ $tz }}
                                        </option>
                                    @endforeach
                                </select>
                            </x-form.group>
                        </div>

                        <div class="col-md-4">
                            <x-form.group for="debug_mode" label="Application Debug Mode">
                                <select class="form-control" name="debug_mode" id="debug_mode">
                                    <option value="1" @selected($generalSetting['debug_mode'] == 1)>@lang('Yes')</option>
                                    <option value="0" @selected($generalSetting['debug_mode'] == 0)>@lang('No')</option>
                                </select>
                            </x-form.group>
                        </div>

                        <div class="col-md-12">
                            <x-form.group for="site_description" label="Site Description">
                                <textarea name="site_description" id="site_description" rows="4" class="form-control">{{ old('site_description', $generalSetting['site_description']) }}</textarea>
                            </x-form.group>
                        </div>

                        <div class="col-md-6">
                            <x-form.group for="site_logo" label="Site Logo">
                                <input type="file" name="site_logo" accept="image/*" class="form-control">
                                @if ($generalSetting['site_logo'])
                                    <img src="{{ System::logo() }}" alt="Logo"
                                        class="img-thumbnail mt-2" style="height: 50px;">
                                @endif
                            </x-form.group>
                        </div>

                        <div class="col-md-6">
                            <x-form.group for="site_favicon" label="Site Favicon">
                                <input type="file" name="site_favicon" accept="image/*" class="form-control">
                                @if ($generalSetting['site_favicon'])
                                    <img src="{{ System::favicon() }}" alt="Favicon"
                                        class="img-thumbnail mt-2" style="height: 32px;">
                                @endif
                            </x-form.group>
                        </div>

                        <div class="d-flex justify-content-end">
                            <x-button type="submit">
                                <x-icons.save />
                                @lang('Save')
                            </x-button>
                        </div>
                    </div>
                </x-card>
            </form>
        </div>
    </div>
@endsection


@push('scripts')
    <script>
        'use strict';

        $('.color-picker').minicolors({
            control: 'hue',
            format: 'hex',
            theme: 'bootstrap'
        });

        SystemHelper.ajaxSubmit(
            $('.ajax-form')
        );
    </script>
@endpush
