@extends('admin.layouts.settings')

@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <form action="{{ route('admin.setting.configuration.update') }}" class="ajax-form" method="POST"
                enctype="multipart/form-data">
                @csrf

                <x-card>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h4>@lang('Configuration Setting')</h4>
                    </div>

                    <div class="configuration-item form-group flex-wrap d-flex justify-content-between align-items-center">
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <img src="{{ asset('assets/images/configurations/google_recaptcha.png') }}" alt="">
                            <div class="d-flex flex-column">
                                <label for="google-recaptcha-toggle" class="mb-0 cursor-pointer user-select-none">
                                    @lang('Enable Google reCAPTCHA')
                                </label>
                                <small class="text-muted">@lang('You can enable google re-captcha on various forms throughout the system')</small>
                            </div>
                        </div>

                        <div class="mt-3 md-mt-0">
                            <input type="checkbox" id="google-recaptcha-toggle" class="js-switch"
                                name="google_recaptcha_enabled" @checked($generalSetting->google_recaptcha_enabled)>
                        </div>
                    </div>

                    <div class="configuration-item form-group flex-wrap d-flex justify-content-between align-items-center">
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <img src="{{ asset('assets/images/configurations/user_register.png') }}" alt="">
                            <div class="d-flex flex-column">
                                <label for="user-registration-toggle" class="mb-0 cursor-pointer user-select-none">
                                    @lang('User Registration')
                                </label>
                                <small class="text-muted">@lang('If you turn this off, new user in this system cannot register')</small>
                            </div>
                        </div>

                        <div class="mt-3 md-mt-0">
                            <input type="checkbox" id="user-registration-toggle" class="js-switch" name="user_registration"
                                @checked($generalSetting->user_registration) />
                        </div>
                    </div>

                    <div class="configuration-item form-group flex-wrap d-flex justify-content-between align-items-center">
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <img src="{{ asset('assets/images/configurations/kyc.jpg') }}" alt="">
                            <div class="d-flex flex-column">
                                <label for="kyc-toggle" class="mb-0 cursor-pointer user-select-none">
                                    @lang('KYC(Know your customer)')
                                </label>
                                <small class="text-muted">@lang('Toggle this for new user with KYC is reuqired or not')</small>
                            </div>
                        </div>

                        <div class="mt-3 md-mt-0">
                            <input type="checkbox" id="kyc-toggle" class="js-switch" name="kyc"
                                @checked($generalSetting->kyc) />
                        </div>
                    </div>

                    <div class="configuration-item form-group flex-wrap d-flex justify-content-between align-items-center">
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <img src="{{ asset('assets/images/configurations/maintenance.png') }}" alt="">
                            <div class="d-flex flex-column">
                                <label for="maintenance_mode-toggle" class="mb-0 cursor-pointer user-select-none">
                                    @lang('Maintenance Mode')
                                </label>
                                <small class="text-muted">@lang('Enable this for maintenace mode for the website')</small>
                            </div>
                        </div>

                        <div class="mt-3 md-mt-0">
                            <input type="checkbox" id="maintenance_mode-toggle" class="js-switch" name="maintenance_mode"
                                @checked($generalSetting->maintenance_mode) />
                        </div>
                    </div>

                    <div class="text-end mt-4">
                        <x-button type="submit"><x-icons.save /> @lang('Save')</x-button>
                    </div>
                </x-card>
            </form>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/switchery/0.8.2/switchery.css">
    <style>
        .configuration-item img {
            max-width: 100px;
            max-height: 100px;
        }

        .form-group {
            padding: 10px 0;
            border-bottom: 1px solid #eaeaea;
        }

        .cursor-pointer {
            cursor: pointer;
        }

        .user-select-none {
            user-select: none;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/switchery/0.8.2/switchery.min.js"></script>
    <script>
        console.log($);
        const elem = $('.js-switch');

        $('.js-switch').each(function(index, elem) {
            new Switchery(elem, {
                size: 'small',
                color: '#0d6efd'
            });
        });

        SystemHelper.ajaxSubmit(
            $('.ajax-form')
        );
    </script>
@endpush
