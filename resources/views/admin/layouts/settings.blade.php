@extends('admin.layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h4>@lang('Settings & Configurations')</h4>
    </div>

    <div class="row">
        <div class="col-lg-3">
            <x-card class="settings-sidebar">
                <ul>
                    <li>
                        <a href="{{ route('admin.setting.general') }}" class="{{ activeClass('admin.setting.general') }}" wire:navigate> 
                            <x-icons.setting />
                            @lang('General Setting')
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.setting.configuration') }}" class="{{ activeClass('admin.setting.configuration') }}" wire:navigate>
                            <x-icons.cog />
                            @lang('System Configuration')
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.setting.server.information') }}" class="{{ activeClass('admin.setting.server.information') }}" wire:navigate>
                            <x-icons.server />
                            @lang('Server Information')
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('admin.setting.notification') }}" class="{{ activeClass('admin.setting.notification') }}">
                            <x-icons.bell />
                            @lang('Notifications')
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('admin.setting.sms') }}" class="{{ activeClass('admin.setting.sms') }}">
                            <x-icons.bell />
                            @lang('SMS Settings')
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('admin.setting.language.list') }}" class="{{ activeClass('admin.setting.language.list') }}">
                            <x-icons.list />
                            @lang('Languages')
                        </a>
                    </li>
                </ul>
            </x-card>
        </div>

        <div class="col-lg-9">
            @yield('panel')
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .settings-sidebar ul {
            padding: 0;
            display: flex;
            flex-direction: column;
            list-style: none;
        }

        .settings-sidebar a {
            text-decoration: none;
            border-radius: 4px;
            padding: 10px;
            display: inline-block;
            width: 100%;
        }

        .settings-sidebar a.active {
            background-color: #f0f0f0;
        }
    </style>
@endpush
