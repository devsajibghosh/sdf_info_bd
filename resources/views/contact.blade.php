@extends('frontend.layouts.main')

@php
    $value = \App\Models\Setting::where('key', 'section_contact_content')->first()?->value;

    $value = literal(...$value);
@endphp

@section('content')
    <x-breadcrumb title="Contact" />

    <div class="contact-page-container">

        <div class="container">
            <div class="row">

                <div class="col-12 col-lg-7">
                    <div class="contact-card">
                        <h2 class="card-title">{{ __($value->heading1) }}</h2>
                        <form action="{{ route('site.contact.submit') }}" method="POST" class="contact-form" id="contactForm" novalidate>
                            @csrf
                            <div class="form-row">
                                <label for="name"><span>*</span> @lang('Your Name'):</label>
                                <div class="input-wrapper">
                                    <input type="text" id="name" name="name" value="{{ old('name') }}"
                                        class="form--control" required>
                                </div>
                            </div>
                            <div class="form-row">
                                <label for="phone_number"><span>*</span> @lang('Phone Number'):</label>
                                <div class="input-wrapper">
                                    <input type="tel" id="phone_number" name="phone_number" class="form--control"
                                        inputmode="numeric" autocomplete="tel" maxlength="11" pattern="01[0-9]{9}"
                                        placeholder="01XXXXXXXXX"
                                        value="{{ old('phone_number') }}" required>
                                    <small class="text-danger d-none" id="phoneNumberError"></small>
                                </div>
                            </div>
                            <div class="form-row">
                                <label for="subject"><span>*</span> @lang('Subject'):</label>
                                <div class="input-wrapper">
                                    <input type="text" id="subject" name="subject" class="form--control"
                                        value="{{ old('subject') }}" required>
                                </div>
                            </div>
                            <div class="form-row">
                                <label for="message"><span>*</span> @lang('Message'):</label>
                                <div class="input-wrapper">
                                    <textarea id="message" name="message" rows="8" class="form--control" required>{{ old('message') }}</textarea>
                                </div>
                            </div>

                            <div class="form-row">
                                <label for=""></label>
                                <div>
                                    @if (System::googleCaptchaEnabled())
                                        @php echo NoCaptcha::renderJs() @endphp
                                        @php echo NoCaptcha::display() @endphp
                                    @endif
                                </div>
                            </div>
                            <div class="form-row">
                                <label></label>
                                <div class="input-wrapper">
                                    <button type="submit" class="btn btn--base w-100">@lang('Submit')</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Right Column: Map & Details --}}
                <div class="col-12 col-lg-5">
                    <div class="contact-card mb-4">
                        <h2 class="card-title">{{ __($value->heading2) }}</h2>
                        <div class="map-container">
                            <iframe src="{{ $value->map_link }}" width="100%" height="250" style="border:0;"
                                allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                        </div>
                    </div>

                    <div class="contact-card">
                        <h2 class="card-title">{{ $value->heading3 }}</h2>
                        <div class="details-item">
                            <h3>@lang('Address')</h3>
                            <p>{{ __($value->address) }}</p>
                        </div>
                        <div class="details-item">
                            <h3>@lang('Phone')</h3>
                            <p>{{ $value->phone }}</p>
                        </div>
                        <div class="details-item">
                            <h3>@lang('Email')</h3>
                            <p>{{ $value->email }}</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .contact-page-container {
            background-color: #f7f7f7;
            padding: 50px 0;
        }

        .contact-card {
            background: #fff;
            padding: 30px;
            border: 1px solid #e9e9e9;
        }

        .card-title {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 30px;
            color: #333;
        }

        .contact-form .form-row {
            display: flex;
            margin-bottom: 20px;
            align-items: center;
        }

        .contact-form .form-row:nth-child(4) {
            /* Target message row */
            align-items: flex-start;
        }

        .contact-form label {
            flex: 0 0 120px;
            text-align: right;
            padding-right: 15px;
            color: #555;
            font-weight: 500;
        }

        .contact-form label span {
            color: red;
        }

        .contact-form .input-wrapper {
            flex: 1;
        }

        .map-container {
            width: 100%;
            height: 250px;
        }

        .details-item {
            margin-bottom: 20px;
        }

        .details-item:last-child {
            margin-bottom: 0;
        }

        .details-item h3 {
            font-size: 16px;
            font-weight: bold;
            color: #444;
            margin-bottom: 5px;
        }

        .details-item p {
            margin: 0;
            color: #666;
            line-height: 1.6;
        }

        /* Responsive Adjustments */
        @media (max-width: 767.98px) {
            .contact-form .form-row {
                flex-direction: column;
                align-items: flex-start;
            }

            .contact-form label {
                text-align: left;
                padding-right: 0;
                margin-bottom: 5px;
                flex-basis: auto;
            }

            .contact-form .form-row:last-child label {
                display: none;
                /* Hide empty label on mobile */
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var form = document.getElementById('contactForm');
            var phoneInput = document.getElementById('phone_number');
            var phoneError = document.getElementById('phoneNumberError');
            if (!form || !phoneInput) return;

            var PHONE_REGEX = /^01[0-9]{9}$/;
            var MSG_PHONE = @json(__('Please enter a valid 11-digit mobile number.'));

            phoneInput.addEventListener('input', function () {
                phoneInput.value = phoneInput.value.replace(/[^0-9]/g, '').slice(0, 11);
            });

            form.addEventListener('submit', function (e) {
                var phone = phoneInput.value.trim();
                if (!PHONE_REGEX.test(phone)) {
                    e.preventDefault();
                    phoneError.textContent = MSG_PHONE;
                    phoneError.classList.remove('d-none');
                    phoneInput.focus();
                } else {
                    phoneError.classList.add('d-none');
                }
            });
        });
    </script>
@endpush
