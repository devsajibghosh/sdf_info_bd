@extends('frontend.layouts.main')

@section('content')
    <x-breadcrumb title="Login" />

    <div class="container-fluid my-5">
        <div class="row">
            <div class="col-12 col-md-4 offset-md-4">
                <x-card>

                    <ul class="nav nav-tabs mb-4" id="loginTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="login-tab" data-bs-toggle="tab" data-bs-target="#login-form" type="button" role="tab">
                                @lang('Member Login')
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="otp-tab" data-bs-toggle="tab" data-bs-target="#otp-form" type="button" role="tab">
                                @lang('Donor Login')
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="loginTabContent">
                        <div class="tab-pane fade show active" id="login-form" role="tabpanel">
                            <form method="POST" action="{{ route('login.store') }}" id="memberLoginForm" novalidate>
                                @csrf
                                <div class="col-sm-12 form-group">
                                    <label for="phone_number" class="form--label">@lang('Phone Number')</label>
                                    <input type="tel" id="phone_number" name="phone_number" inputmode="numeric" autocomplete="tel" maxlength="11" pattern="01[0-9]{9}" placeholder="@lang('Please enter your phone number without +88')" value="{{ old('phone_number') }}" class="form--control" required>
                                    <small class="text-danger d-none" id="memberPhoneError"></small>
                                </div>

                                {{-- Password Input with Toggle --}}
                                <div class="form-group position-relative">
                                    <label for="password" class="form-label">@lang('Password')</label>
                                    <input type="password" name="password" placeholder="@lang('Please enter your password')" class="form--control" id="password" required />
                                    <span id="togglePassword" class="password-toggle-icon">
                                        <i class="bi bi-eye"></i>
                                    </span>
                                </div>
                                {{-- End of Password Input with Toggle --}}


                                @if (System::googleCaptchaEnabled())
                                    <div class="mt-3">
                                        {!! NoCaptcha::renderJs() !!}
                                        {!! NoCaptcha::display() !!}
                                    </div>
                                @endif

                                <div class="col-sm-12 form-group">
                                    <div class="d-flex flex-wrap justify-content-between">
                                        <div class="form--check">
                                            <input class="form-check-input" type="checkbox" id="remember">
                                            <label class="form-check-label" for="remember">@lang('Remember me')</label>
                                        </div>
                                        <a href="{{ route('password.request') }}"
                                           class="forgot-password text--base">@lang('Forgot Your Password?')</a>
                                    </div>
                                </div>

                                <div class="col-sm-12 form-group">
                                    <button type="submit" class="btn btn--base w-100">@lang('Sign In')</button>
                                </div>

                                <div class="col-sm-12">
                                    <div class="have-account text-center">
                                        <p class="have-account__text">@lang("Don't Have An Account?")
                                            <a href="{{ route('register') }}" class="have-account__link text--base">@lang('Register Here')</a>
                                        </p>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="tab-pane fade" id="otp-form" role="tabpanel">
                            <form method="POST" action="{{ route('login.otp.store') }}" id="otp-request-form">
                                @csrf
                                <div class="col-sm-12 form-group">
                                    <label for="phone_number_otp" class="form--label">@lang('Phone Number')</label>
                                    <input type="tel" id="phone_number_otp" otp-phone name="phone_number" inputmode="numeric" autocomplete="tel" maxlength="11" pattern="01[0-9]{9}" class="form--control" placeholder="@lang('Please enter your phone number')" required>
                                    <small class="text-danger d-none" id="otpPhoneError"></small>
                                </div>

                                @if (System::googleCaptchaEnabled())
                                    <div class="mt-3">
                                        {!! NoCaptcha::renderJs() !!}
                                        {!! NoCaptcha::display() !!}
                                    </div>
                                @endif

                                <div class="col-sm-12 form-group">
                                    <button type="submit" class="btn btn--base w-100" id="request-otp-btn">@lang('Send OTP')</button>
                                </div>
                            </form>

                            <form method="POST" action="{{ route('login.otp.validate') }}" id="otp-verify-form" style="display: none;">
                                @csrf
                                <input type="hidden" name="phone_number" id="verify-phone-number">

                                <div class="col-sm-12 form-group">
                                    <label for="otp_code" class="form--label">@lang('Enter OTP')</label>
                                    <input type="text" id="otp_code" name="otp_code" class="form--control" maxlength="6" required>
                                </div>

                                <div class="col-sm-12 form-group">
                                    <button type="submit" class="btn btn--base w-100">@lang('Verify & Login')</button>
                                </div>
                            </form>

                            <div id="otp-message" class="text-success mt-2" style="display: none;"></div>
                        </div>
                    </div>

                </x-card>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    {{-- Add Bootstrap Icons for the eye/eye-slash toggle --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .password-toggle-icon {
            position: absolute;
            top: 70%; /* Adjusts vertical position */
            right: 15px;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d; /* A neutral color */
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {

            var PHONE_REGEX = /^01[0-9]{9}$/;
            var MSG_PHONE = @json(__('Please enter a valid 11-digit mobile number.'));

            // Digits-only input restriction (11 digit BD phone numbers)
            $('#phone_number, #phone_number_otp').on('input', function () {
                this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11);
            });

            // Member login phone validation
            $('#memberLoginForm').on('submit', function (e) {
                var phone = $('#phone_number').val().trim();
                if (!PHONE_REGEX.test(phone)) {
                    e.preventDefault();
                    $('#memberPhoneError').text(MSG_PHONE).removeClass('d-none');
                    $('#phone_number').trigger('focus');
                } else {
                    $('#memberPhoneError').addClass('d-none');
                }
            });

            // OTP Form Logic
            $('#otp-request-form').on('submit', function (e) {
                e.preventDefault();

                let phone = $('[otp-phone]').val().trim();
                if (!PHONE_REGEX.test(phone)) {
                    $('#otpPhoneError').text(MSG_PHONE).removeClass('d-none');
                    $('[otp-phone]').trigger('focus');
                    return;
                }
                $('#otpPhoneError').addClass('d-none');

                let $btn = $('#request-otp-btn');
                $btn.prop('disabled', true).text('Sending...');

                $.ajax({
                    method: 'POST',
                    url: $(this).attr('action'),
                    data: $(this).serialize(),
                    success: function (res) {
                        if (res.success) {
                            $('#otp-message').text(res.message).show();
                            $('#otp-request-form').hide();
                            $('#otp-verify-form').show();
                            $('#verify-phone-number').val(phone);
                        } else {
                            $.jGrowl(res.message || 'Failed to send OTP', {
                                header: 'Error',
                                theme: 'jgrowl-error'
                            });
                        }
                    },
                    error: function (xhr) {
                         $.jGrowl(xhr.responseJSON?.message || 'An unknown error occurred', {
                            header: 'Error',
                            theme: 'jgrowl-error'
                        });
                    },
                    complete: function () {
                        $btn.prop('disabled', false).text('Send OTP');
                    }
                });
            });

            // Password Toggle Logic
            $('#togglePassword').on('click', function() {
                const passwordInput = $('#password');
                const icon = $(this).find('i');
                const type = passwordInput.attr('type') === 'password' ? 'text' : 'password';
                
                passwordInput.attr('type', type);
                
                // Change the icon
                if (type === 'text') {
                    icon.removeClass('bi-eye').addClass('bi-eye-slash');
                } else {
                    icon.removeClass('bi-eye-slash').addClass('bi-e eye');
                }
            });
        });
    </script>
@endpush