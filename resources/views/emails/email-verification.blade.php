@extends('emails.layout')

@section('content')
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background: #ffffff; border-radius: 8px; padding: 30px;">
    <tr>
        <td align="center" style="font-family: Arial, sans-serif;">
            <h2 style="color: #333333;">Verify Your Email Address</h2>
            <p style="font-size: 16px; color: #555555;">
                Hello {{ $user->name }},
            </p>
            <p style="font-size: 15px; color: #555555;">
                Thank you for registering with {{ config('app.name') }}. Please click the button below to verify your email address.
            </p>

            <p style="margin: 30px 0;">
                <a href="{{ $verificationUrl }}"
                   style="background-color: #1a73e8; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-size: 16px;">
                    Verify Email
                </a>
            </p>

            <p style="font-size: 14px; color: #999999;">
                If you didn’t create an account, no further action is required.
            </p>

            <p style="font-size: 15px; color: #555555;">
                Regards,<br>
                The {{ config('app.name') }} Team
            </p>
        </td>
    </tr>
</table>
@endsection
