@extends('admin.layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>@lang('Edit Profile')</h3>
        <x-button href="{{ route('admin.user.list') }}">
            <x-icons.back-v1 />
            @lang('Back')
        </x-button>
    </div>

    <div class="row">
        <div class="col-lg-8 offset-md-2">
            <form action="{{ route('admin.setting.password.update') }}" method="POST">
                @csrf
                <div class="card">
                    <div class="card-body">
                        <div class="form-group">
                            <label>@lang('Old Password')</label>
                            <input type="password" class="form-control" placeholder="@lang('Enter your old password')" name="old_password" />
                        </div>

                        <div class="form-group mt-3">
                            <label>@lang('New Password')</label>
                            <input type="password" class="form-control" placeholder="@lang('Enter a new password')" name="password" />
                        </div>

                        <div class="form-group mt-3">
                            <label>@lang('Password Confirmation')</label>
                            <input type="password" class="form-control" placeholder="@lang('Confirm New Password')" name="password_confirmation" />
                        </div>

                        <div class="d-flex mt-3 justify-content-end">
                            <x-button type="submit">
                                <x-icons.save />
                                @lang('Save')
                            </x-button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
