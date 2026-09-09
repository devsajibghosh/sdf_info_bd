@extends('user.layouts.main')

@section('content')
<div style="max-width: 500px; margin: auto; padding: 20px; text-align: center;">
    <h2>@lang('Phone Number Verification Required')</h2>

    <p>
        @lang('Thanks for signing up! Please verify your phone number by entering the OTP we sent to your mobile.')
    </p>

    @if (session('status') === 'otp-sent')
        <div style="color: green; margin-bottom: 10px;">
            @lang('A new OTP has been sent to your phone number.')
        </div>
    @endif

    @if (session('error'))
        <div style="color: red; margin-bottom: 10px;">
            {{ session('error') }}
        </div>
    @endif

    <!-- OTP Submission Form -->
    <form method="POST" action="{{ route('user.phone.verify.submit') }}" style="margin-bottom: 20px;">
        @csrf
        <input type="text" name="otp" placeholder="@lang('Enter OTP')" required
               style="padding: 10px; width: 80%; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 4px;">

        <button type="submit"
                style="padding: 10px 20px; background: #28a745; color: #fff; border: none; border-radius: 4px;">
            @lang('Verify Phone Number')
        </button>
    </form>

    <!-- Resend OTP Form -->
    <form method="POST" action="{{ route('user.phone.verify.resend') }}">
        @csrf
        <button type="submit"
                style="padding: 10px 20px; background: #1a73e8; color: #fff; border: none; border-radius: 4px;">
            @lang('Resend OTP')
        </button>
    </form>
</div>
@endsection
