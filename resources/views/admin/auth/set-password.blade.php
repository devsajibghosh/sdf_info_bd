@extends('admin.layouts.auth')

@section('content')
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <x-card class="auth-card">
                    <div class="card-header admin-card text-center mb-4">
                        <h4>@lang('Set New Password')</h4>
                    </div>

                    <form method="POST" action="{{ route('admin.password.update') }}">
                        @csrf

                        <input type="hidden" name="token" value="{{ request('token') }}">
                        <input type="hidden" name="email" value="{{ request('email') }}">

                        <x-form.group label="Password" for="password">
                            <input type="password" name="password" class="form-control" id="password"
                                placeholder="@lang('Please enter a new password')" required />
                        </x-form.group>

                        <x-form.group label="Password Confirmation" for="password_confirmation">
                            <input type="password" name="password_confirmation" class="form-control"
                                id="password_confirmation" placeholder="@lang('Please re-enter the password')" required />
                        </x-form.group>

                        {{-- {!! NoCaptcha::renderJs() !!}
                        {!! NoCaptcha::display() !!} --}}

                        <div class="d-flex justify-content-end mb-3">
                            <a href="{{ route('admin.login') }}" class="text-white">@lang('Back to login')</a>
                        </div>

                        <x-button class="w-100 d-flex gap-2 justify-content-center align-items-center" type="submit">
                            @lang('Submit')
                        </x-button>
                    </form>
                </x-card>
            </div>
        </div>
    </div>
@endsection
