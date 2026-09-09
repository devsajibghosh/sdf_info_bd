@extends('emails.layout')

@section('content')
<p>Dear {{ $user->name }},</p>

<p>We received a request to reset your password. Click the button below to choose a new one:</p>

<div class="text-center">
    <a href="{{ $resetUrl }}" class="btn-primary">Reset Password</a>
</div>

<p>If you didn’t request this, you can safely ignore this email.</p>

<p>Thank you,<br>{{ config('app.name') }} Team</p>
@endsection
