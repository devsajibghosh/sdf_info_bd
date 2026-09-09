@extends('user.layouts.main')

@section('content')
    <section class="breadcrumb py-120">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-lg-8">
                    <div class="breadcrumb__wrapper text-center">
                        <h4 class="breadcrumb__title">@lang('Reset Password')</h4>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="account-form mt-5">
        <div class="account-form__content mb-4 text-center">
            <h3 class="account-form__title mb-2">@lang('Enter Your New Password')</h3>
        </div>

        <form method="POST" action="{{ route('password.update') }}">
            @csrf

            <div class="row">
                <div class="col-sm-12 form-group">
                    <div class="form--group">
                        <label for="password" class="form--label">@lang('New Password')</label>
                        <input type="password" name="password" class="form--control" placeholder="@lang('Enter new password')" required />
                    </div>
                </div>

                <div class="col-sm-12 form-group">
                    <div class="form--group">
                        <label for="password_confirmation" class="form--label">@lang('Confirm Password')</label>
                        <input type="password" name="password_confirmation" class="form--control" placeholder="@lang('Confirm new password')" required />
                    </div>
                </div>

                <div class="col-sm-12 form-group">
                    <button type="submit" class="btn btn--base w-100">@lang('Reset Password')</button>
                </div>
            </div>
        </form>
    </div>
@endsection