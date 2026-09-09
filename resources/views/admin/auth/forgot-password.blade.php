@extends('admin.layouts.auth')

@section('content')
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <x-card class="auth-card">
                    <div class="admin-card card-header text-center mb-4">
                        <h4>@lang('Admin Password Reset')</h4>
                    </div>

                    <form method="POST" action="{{ route('admin.password.email') }}">
                        @csrf

                        <x-form.group label="Email" for="email">
                            <input 
                                type="email"
                                name="email" 
                                class="form-control" 
                                id="email"
                                placeholder="@lang('Please enter your email address')" 
                                required 
                                autofocus
                            />
                        </x-form.group>


                        {{-- {!! NoCaptcha::renderJs() !!}
                        {!! NoCaptcha::display() !!} --}}


                        <div class="d-flex mb-3 justify-content-between">
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
