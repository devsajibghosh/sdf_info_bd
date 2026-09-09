@extends('admin.layouts.settings')

@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <form class="ajax-form" action="{{ route('admin.setting.notification.update') }}" method="POST"
                enctype="multipart/form-data">
                @csrf

                <x-card>
                    {{-- email config settings --}}
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5>@lang('Email Provider Configuration')</h5>
                        <x-button class="btn-info" id="sendTestMailBtn" type="button">@lang('Send Test Mail')</x-button>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <x-form.group for="mail_host" label="Mail Host">
                                <input type="text" name="mail_host" id="mail_host" class="form-control"
                                    value="{{ old('mail_host', $generalSetting['mail_host']) }}">
                            </x-form.group>
                        </div>

                        <div class="col-md-4">
                            <x-form.group for="mail_port" label="Mail Port">
                                <input type="text" name="mail_port" id="mail_port" class="form-control"
                                    value="{{ old('mail_port', $generalSetting['mail_port']) }}">
                            </x-form.group>
                        </div>

                        <div class="col-md-4">
                            <x-form.group for="mail_username" label="Mail Username">
                                <input type="text" name="mail_username" id="mail_username" class="form-control"
                                    value="{{ old('mail_username', $generalSetting['mail_username']) }}">
                            </x-form.group>
                        </div>

                        <div class="col-md-4">
                            <x-form.group for="mail_password" label="Mail Password">
                                <input type="password" name="mail_password" id="mail_password" class="form-control"
                                    value="{{ old('mail_password', $generalSetting['mail_password']) }}">
                            </x-form.group>
                        </div>

                        <div class="col-md-4">
                            <x-form.group for="mail_encryption" label="Mail Encryption">
                                <input type="text" name="mail_encryption" id="mail_encryption" class="form-control"
                                    value="{{ old('mail_encryption', $generalSetting['mail_encryption']) }}">
                            </x-form.group>
                        </div>

                        <div class="col-md-4">
                            <x-form.group for="mail_from_address" label="From Address">
                                <input type="text" name="mail_from_address" id="mail_from_address" class="form-control"
                                    value="{{ old('mail_from_address', $generalSetting['mail_from_address']) }}">
                            </x-form.group>
                        </div>

                        <div class="col-md-4">
                            <x-form.group for="mail_from_name" label="From Name">
                                <input type="text" name="mail_from_name" id="mail_from_name" class="form-control"
                                    value="{{ old('mail_from_name', $generalSetting['mail_from_name']) }}">
                            </x-form.group>
                        </div>

                        {{-- sms settings --}}
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5>@lang('SMS Provider Configuration')</h5>
                            <x-button class="btn-info" id="testSMSBtn" type="button">@lang('Send Test SMS')</x-button>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <x-form.group for="sms_api_key" label="API Key">
                                    <input type="text" name="sms_api_key" id="sms_api_key" class="form-control"
                                        value="{{ old('sms_api_key', $generalSetting['sms_api_key']) }}">
                                </x-form.group>
                            </div>

                            <div class="col-md-6">
                                <x-form.group for="sms_sender_id" label="Sender ID">
                                    <input type="text" name="sms_sender_id" id="sms_sender_id" class="form-control"
                                        value="{{ old('sms_sender_id', $generalSetting['sms_sender_id']) }}">
                                </x-form.group>
                            </div>

                        </div>

                        <div class="d-flex justify-content-end">
                            <x-button type="submit">
                                <x-icons.save />
                                @lang('Save')
                            </x-button>
                        </div>
                </x-card>
            </form>
        </div>
    </div>

    {{-- test mail sender modal --}}
    <div class="modal fade" id="testEmailSenderModal" tabindex="-1" aria-labelledby="testEmailSenderModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="testEmailSenderModalLabel">@lang('Send Test Email')</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form class="ajax-form-mail" action="{{ route('admin.setting.notification.test_mail') }}">
                    @csrf
                    <div class="modal-body">
                        <x-form.group for="test_email" label="Email Address">
                            <input type="text" name="test_email" id="test_email" class="form-control" />
                        </x-form.group>
                    </div>
                    <div class="modal-footer">
                        <x-button type="submit">@lang('Send')</x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    {{-- test sms sender modal --}}
    <div class="modal fade" id="testSmsModal" tabindex="-1" aria-labelledby="testSmsModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="testSmsModalLabel">@lang('Send Test SMS')</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form class="ajax-form-sms" action="{{ route('admin.setting.notification.test_sms') }}">
                    @csrf
                    <div class="modal-body">
                        <x-form.group for="phone_number" label="Phone Number">
                            <input type="text" name="phone_number" id="phone_number" placeholder="@lang('e.g. 01600000000')" class="form-control" />
                        </x-form.group>
                        <x-form.group for="message" label="Message">
                            <textarea type="text" name="message" id="message" class="form-control" placeholder="@lang('Please type your test message')"></textarea>
                        </x-form.group>
                    </div>
                    <div class="modal-footer">
                        <x-button type="submit">@lang('Send')</x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection


@push('scripts')
    <script>
        'use strict';

        $(document).ready(function() {
            SystemHelper.ajaxSubmit($('.ajax-form'));

            SystemHelper.ajaxSubmit($('.ajax-form-mail'), () => {
                $('.ajax-form-mail')[0].reset();
                $('#testEmailSenderModal').modal('hide');
            });

            SystemHelper.ajaxSubmit($('.ajax-form-sms'), () => {
                $('.ajax-form-sms')[0].reset();
                $('#testSmsModal').modal('hide');
            });
        });

        $('#sendTestMailBtn').on('click', function() {
            $('#testEmailSenderModal').modal('show');
        });

        $('#testSMSBtn').on('click', function() {
            $('#testSmsModal').modal('show');
        });
    </script>
@endpush
