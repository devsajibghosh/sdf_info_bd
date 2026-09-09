@extends('frontend.layouts.main')

@section('content')
    <x-breadcrumb title="Registration" />

    <div class="container-fluid my-5">
        <div class="row">
            <div class="col-12 col-md-4 offset-md-4">
                <x-card>
                    <form method="POST" action="{{ route('register.store') }}">
                        @csrf
                        <div class="form-group mb-3">
                            <label for="first_name" class="form-label">@lang('First Name')</label>
                            <input type="text" id="first_name" placeholder="@lang('Please enter your first name')" value="{{ old('first_name') }}" name="first_name" class="form--control" required />
                        </div>

                        <div class="form-group mb-3">
                            <label for="last_name" class="form-label">@lang('Last Name')</label>
                            <input type="text" id="last_name" name="last_name" value="{{ old('last_name') }}" class="form--control" placeholder="@lang('Please enter your last name')" required />
                        </div>

                        <div class="form-group mb-3">
                            <label for="phone_number" class="form-label">@lang('Phone Number')</label>
                            <input type="text" id="phone_number" name="phone_number" value="{{ old('phone_number') }}" class="form--control" placeholder="@lang('Please enter your phone number')" required />
                        </div>

                        {{-- Password Input with Toggle --}}
                        <div class="form-group position-relative mb-3">
                            <label for="password" class="form-label">@lang('Password')</label>
                            <input type="password" name="password" class="form--control" placeholder="@lang('Please enter a password')" id="password" required />
                             <span id="togglePassword" class="password-toggle-icon">
                                <i class="bi bi-eye"></i>
                            </span>
                        </div>
                        
                        {{-- Confirm Password Input with Toggle --}}
                        <div class="form-group position-relative mb-3">
                            <label for="password_confirmation" class="form-label">@lang('Confirm Password')</label>
                            <input type="password" name="password_confirmation" class="form--control" placeholder="@lang('Please confirm your password')" id="password_confirmation" required />
                             <span id="toggleConfirmPassword" class="password-toggle-icon">
                                <i class="bi bi-eye"></i>
                            </span>
                        </div>


                        @if (System::googleCaptchaEnabled())
                            <div class="mt-3">
                                {!! NoCaptcha::renderJs() !!}
                                {!! NoCaptcha::display() !!}
                            </div>
                        @endif
                        
                        <div class="col-sm-12 form-group">
                            <button type="submit" class="btn btn--base w-100 mt-3">@lang('Sign Up')</button>
                        </div>

                        <div class="col-sm-12">
                            <div class="have-account text-center">
                                <p class="have-account__text">@lang('Already Have An Account')?
                                    <a href="{{ route('login') }}"
                                        class="have-account__link text--base">@lang('Login Now')</a>
                                </p>
                            </div>
                        </div>
                    </form>

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

        function togglePasswordVisibility(toggleBtnId, passwordInputId) {
            const passwordInput = $('#' + passwordInputId);
            const icon = $('#' + toggleBtnId).find('i');
            const type = passwordInput.attr('type') === 'password' ? 'text' : 'password';
            
            passwordInput.attr('type', type);
            
            // Change the icon
            if (type === 'text') {
                icon.removeClass('bi-eye').addClass('bi-eye-slash');
            } else {
                icon.removeClass('bi-eye-slash').addClass('bi-eye');
            }
        }

        $('#togglePassword').on('click', function() {
            togglePasswordVisibility('togglePassword', 'password');
        });

        $('#toggleConfirmPassword').on('click', function() {
            togglePasswordVisibility('toggleConfirmPassword', 'password_confirmation');
        });

    });
</script>
@endpush