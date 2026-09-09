@extends('admin.layouts.auth')

@section('content')
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <x-card class="auth-card">
                    <div class="card-header admin-card text-center mb-4">
                        <h4>@lang('Admin Login')</h4>
                    </div>

                    <form method="POST" action="{{ route('admin.login') }}">
                        @csrf

                        <x-form.group label="Username" for="username">
                            <input type="text" name="username" class="form-control" id="username"
                                placeholder="@lang('Please enter your username')" required autofocus />
                        </x-form.group>

                        <x-form.group label="Password" for="password">
                            {{-- Added a wrapper for positioning the icon --}}
                            <div class="position-relative">
                                <input type="password" name="password" class="form-control" id="password"
                                    placeholder="@lang('Please enter your password')" required />
                                {{-- The eye icon toggler --}}
                                <span id="togglePassword" class="position-absolute top-50 end-0 translate-middle-y me-3" style="cursor: pointer;">
                                    {{-- SVG icon for visibility (eye) --}}
                                    <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye-fill" viewBox="0 0 16 16">
                                        <path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0z"/>
                                        <path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8zm8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z"/>
                                    </svg>
                                    {{-- SVG icon for hidden (slashed eye) --}}
                                    <svg id="eye-slash-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye-slash-fill d-none" viewBox="0 0 16 16">
                                        <path d="m10.79 12.912-1.614-1.615a3.5 3.5 0 0 1-4.474-4.474l-2.06-2.06C.938 6.278 0 8 0 8s3 5.5 8 5.5a7.029 7.029 0 0 0 2.79-.588zM5.21 3.088A7.028 7.028 0 0 1 8 2.5c5 0 8 5.5 8 5.5s-.939 1.721-2.641 3.238l-2.062-2.062a3.5 3.5 0 0 0-4.474-4.474L5.21 3.089z"/>
                                        <path d="M5.525 7.646a2.5 2.5 0 0 0 2.829 2.829l-2.83-2.829zm4.95.708-2.829-2.83a2.5 2.5 0 0 1 2.829 2.829zm3.171 6-12-12 .708-.708 12 12-.708.708z"/>
                                    </svg>
                                </span>
                            </div>
                        </x-form.group>


                        @if(System::googleCaptchaEnabled())
                            @php echo NoCaptcha::renderJs() @endphp
                            @php echo NoCaptcha::display() @endphp
                        @endif


                        <div class="d-flex justify-content-between">
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" name="remember" id="remember">
                                <label class="form-check-label" for="remember">@lang('Remember Me')</label>
                            </div>

                            <!--<a href="{{ route('admin.password.forgot') }}" class="text-white">@lang('Forgot your password ?')</a>-->
                        </div>

                        <x-button class="w-100 d-flex gap-2 justify-content-center align-items-center" type="submit">
                            <x-icons.login />
                            @lang('Login')
                        </x-button>
                    </form>
                </x-card>
            </div>
        </div>
    </div>
@endsection

{{-- Add this script section to your blade file --}}
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const passwordInput = document.getElementById('password');
        const togglePassword = document.getElementById('togglePassword');
        const eyeIcon = document.getElementById('eye-icon');
        const eyeSlashIcon = document.getElementById('eye-slash-icon');

        if (passwordInput && togglePassword && eyeIcon && eyeSlashIcon) {
            togglePassword.addEventListener('click', function () {
                // Toggle the type attribute
                const isPassword = passwordInput.getAttribute('type') === 'password';
                passwordInput.setAttribute('type', isPassword ? 'text' : 'password');

                // Toggle the icon visibility
                eyeIcon.classList.toggle('d-none', isPassword);
                eyeSlashIcon.classList.toggle('d-none', !isPassword);
            });
        }
    });
</script>
@endpush