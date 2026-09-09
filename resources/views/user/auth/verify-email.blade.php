@extends('user.layouts.main')

@section('content')
<div style="max-width: 500px; margin: auto; padding: 20px; text-align: center;">
    <h2>@lang('Email Verification Required')</h2>

    <p>
        @lang('Thanks for signing up! Please verify your email address by clicking the link we sent to your inbox.')
    </p>

    @if (session('status') === 'verification-link-sent')
        <div style="color: green; margin-bottom: 10px;">
            @lang('A new verification link has been sent to your email address.')
        </div>
    @endif

    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit" style="padding: 10px 20px; background: #1a73e8; color: #fff; border: none; border-radius: 4px;">
            @lang('Resend Verification Email')
        </button>
    </form>
</div>
@endsection
