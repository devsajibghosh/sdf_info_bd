@extends('frontend.layouts.main')

@section('content')
    <section class="breadcrumb py-120">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-lg-8">
                    <div class="breadcrumb__wrapper">
                        <h4 class="breadcrumb__title">@lang('Forgotten Password')</h4>
                        <ul class="breadcrumb__list">
                            <li class="breadcrumb__item">
                                <a href="{{ route('home') }}" class="breadcrumb__link">
                                    <i class="las la-home"></i>@lang('Home')
                                </a>
                            </li>
                            <li class="breadcrumb__item"><i class="fas fa-arrow-right"></i></li>
                            <li class="breadcrumb__item">
                                <span class="breadcrumb__item-text">@lang('Forgotten Password')</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="account-form mt-5">
        <div class="account-form__content mb-4">
            <h3 class="account-form__title mb-2">@lang('Password Forget')</h3>
            <p class="account-form__desc">@lang('Please enter the form to reset your password')</p>
        </div>

        <form method="POST" action="{{ route('password.sendOtp') }}" id="forgotPasswordForm" novalidate>
            @csrf

            <div class="row">
                <div class="col-sm-12 form-group">
                    <div class="form--group">
                        <label for="phone_number" class="form--label">@lang('Phone Number')</label>
                        <input type="tel" name="phone_number" class="form--control" id="phone_number"
                            inputmode="numeric" autocomplete="tel" maxlength="11" pattern="01[0-9]{9}"
                            placeholder="01XXXXXXXXX" required />
                        <small class="text-danger d-none" id="phoneNumberError"></small>
                    </div>
                </div>

                <div class="col-sm-12 form-group">
                    <button type="submit" class="btn btn--base w-100">@lang('Send OTP')</button>
                </div>

                <div class="col-sm-12">
                    <div class="have-account text-center">
                        <p class="have-account__text">@lang('Remember the password')?
                            <a href="{{ route('login') }}" class="have-account__link text--base">@lang('Login Here')</a>
                        </p>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var form = document.getElementById('forgotPasswordForm');
            var phoneInput = document.getElementById('phone_number');
            var phoneError = document.getElementById('phoneNumberError');
            if (!form || !phoneInput) return;

            var PHONE_REGEX = /^01[0-9]{9}$/;
            var MSG_PHONE = @json(__('Please enter a valid 11-digit mobile number.'));

            phoneInput.addEventListener('input', function () {
                phoneInput.value = phoneInput.value.replace(/[^0-9]/g, '').slice(0, 11);
            });

            form.addEventListener('submit', function (e) {
                var phone = phoneInput.value.trim();
                if (!PHONE_REGEX.test(phone)) {
                    e.preventDefault();
                    phoneError.textContent = MSG_PHONE;
                    phoneError.classList.remove('d-none');
                    phoneInput.focus();
                } else {
                    phoneError.classList.add('d-none');
                }
            });
        });
    </script>
@endpush
