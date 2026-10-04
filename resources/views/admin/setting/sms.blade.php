@extends('admin.layouts.settings')

@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <form action="{{ route('admin.setting.sms.update') }}" class="ajax-form" method="POST">
                @csrf

                <x-card>
                    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                        <h4 class="mb-0">@lang('SMS Settings')</h4>
                        <a href="{{ route('admin.setting.notification') }}" class="btn btn-sm btn-outline-info">
                            @lang('SMS Provider (API Key / Sender ID) & Test SMS')
                        </a>
                    </div>

                    <div class="form-group flex-wrap d-flex justify-content-between align-items-center">
                        <div class="d-flex flex-column">
                            <label for="sms-enabled-toggle" class="mb-0 cursor-pointer user-select-none fw-bold">
                                @lang('Donation SMS (master switch)')
                            </label>
                            <small class="text-muted">@lang('Turn off to stop every donation SMS (online, manual and approval). OTP messages are not affected.')</small>
                        </div>
                        <input type="checkbox" id="sms-enabled-toggle" class="js-switch" name="sms_enabled"
                            @checked($generalSetting->sms_enabled)>
                    </div>

                    <div class="form-group flex-wrap d-flex justify-content-between align-items-center">
                        <div class="d-flex flex-column">
                            <label for="admin-otp-toggle" class="mb-0 cursor-pointer user-select-none fw-bold">
                                @lang('Admin Login OTP Verification')
                            </label>
                            <small class="text-muted">@lang('When off, admins log in to the dashboard with username and password only (no OTP SMS).')</small>
                        </div>
                        <input type="checkbox" id="admin-otp-toggle" class="js-switch" name="admin_login_otp"
                            @checked($generalSetting->admin_login_otp)>
                    </div>
                </x-card>

                @foreach ([false => __('Donation SMS (Donor / Member)'), true => __('OTP / Verification SMS')] as $isOtp => $heading)
                    <x-card class="mt-3">
                        <h5 class="mb-1">{{ $heading }}</h5>
                        @if ($isOtp)
                            <small class="text-muted d-block mb-2">@lang('These are always sent (members need them to log in or reset password). The message must contain {code}.')</small>
                        @endif

                        @foreach ($events as $key => $event)
                            @continue($event['otp'] != $isOtp)
                            @php $template = $templates[$key] ?? null; @endphp

                            <div class="form-group">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-1">
                                    <div class="d-flex flex-column">
                                        <label for="tpl-{{ $key }}" class="mb-0 fw-bold">@lang($event['label'])</label>
                                        <small class="text-muted">@lang($event['help'])</small>
                                    </div>
                                    @unless ($event['otp'])
                                        <input type="checkbox" class="js-switch" name="templates[{{ $key }}][status]"
                                            @checked($template?->status ?? true)>
                                    @endunless
                                </div>

                                <textarea name="templates[{{ $key }}][message]" id="tpl-{{ $key }}" rows="3"
                                    class="form-control sms-message" maxlength="1000">{{ $template?->message }}</textarea>

                                <div class="d-flex justify-content-between flex-wrap gap-2 mt-1">
                                    <small class="text-muted">
                                        @lang('Placeholders'):
                                        @foreach ($event['placeholders'] as $placeholder)
                                            <code class="sms-placeholder" role="button" data-target="tpl-{{ $key }}"
                                                title="@lang('Click to insert')">{{ $placeholder }}</code>
                                        @endforeach
                                    </small>
                                    <small class="text-muted sms-counter" data-for="tpl-{{ $key }}"></small>
                                </div>
                            </div>
                        @endforeach
                    </x-card>
                @endforeach

                <x-card class="mt-3">
                    <small class="text-muted d-block">
                        <code>{name}</code> @lang('donor / member name'),
                        <code>{amount}</code> @lang('donation amount'),
                        <code>{trx}</code> @lang('transaction number'),
                        <code>{date}</code> @lang('today\'s date'),
                        <code>{code}</code> @lang('OTP code').
                    </small>
                    <div class="text-end mt-3">
                        <x-button type="submit" class="clicking"><x-icons.save /> @lang('Save')</x-button>
                    </div>
                </x-card>
            </form>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/switchery/0.8.2/switchery.css">
    <style>
        .form-group {
            padding: 12px 0;
            border-bottom: 1px solid #eaeaea;
        }

        .form-group:last-child {
            border-bottom: 0;
        }

        .cursor-pointer {
            cursor: pointer;
        }

        .user-select-none {
            user-select: none;
        }

        .sms-placeholder {
            cursor: pointer;
            margin-right: 4px;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/switchery/0.8.2/switchery.min.js"></script>
    <script>
        $('.js-switch').each(function(index, elem) {
            new Switchery(elem, {
                size: 'small',
                color: '#0d6efd'
            });
        });

        // Length / SMS-part counter: GSM text is 160 chars per SMS (153 when split),
        // Bangla (Unicode) is 70 per SMS (67 when split).
        function updateCounter(textarea) {
            const text = textarea.value;
            const unicode = /[^\x00-\x7F]/.test(text);
            const single = unicode ? 70 : 160;
            const multi = unicode ? 67 : 153;
            const parts = text.length <= single ? 1 : Math.ceil(text.length / multi);
            $('.sms-counter[data-for="' + textarea.id + '"]').text(
                text.length + ' {{ __('chars') }} · ' + parts + ' SMS' + (unicode ? ' (Unicode)' : '')
            );
        }

        $('.sms-message').each(function() {
            updateCounter(this);
        }).on('input', function() {
            updateCounter(this);
        });

        $('.sms-placeholder').on('click', function() {
            const textarea = document.getElementById($(this).data('target'));
            const value = $(this).text();
            const start = textarea.selectionStart ?? textarea.value.length;
            textarea.value = textarea.value.slice(0, start) + value + textarea.value.slice(textarea.selectionEnd ?? start);
            textarea.focus();
            textarea.selectionStart = textarea.selectionEnd = start + value.length;
            updateCounter(textarea);
        });

        SystemHelper.ajaxSubmit($('.ajax-form'));
    </script>
@endpush
