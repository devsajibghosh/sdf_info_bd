@extends('admin.layouts.app')

@section('content')
    <x-page-header 
        :page_title="isset($member) ? __('Edit Admin') : 'Add Admin'" 
        :back_route="route('admin.member.list')"
    />

    <div class="card">
        <div class="card-body">

            <form method="POST" action="{{ route('admin.member.store', @$member->id) }}">
                @csrf

                <div class="row">
                    <div class="col-lg-6">
                        <x-form.group>
                            <label>@lang('Name')</label>
                            <x-form.input
                                name="name"
                                :placeholder="__('Enter full name')"
                                value="{{ old('name', @$member->name) }}"
                            />
                        </x-form.group>
                    </div>
                    <div class="col-lg-6">
                        <x-form.group>
                            <x-form.label>@lang('Email')</x-form.label>
                            <x-form.input 
                                type="email" 
                                name="email" 
                                :placeholder="__('Enter email address')" 
                                value="{{ old('email', @$member->email) }}"
                            />
                        </x-form.group>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-6">
                        <x-form.group>
                            <x-form.label>@lang('Username')</x-form.label>
                            <x-form.input
                                name="username"
                                :placeholder="__('Enter an username')"
                                value="{{ old('username', @$member->username) }}"
                            />
                        </x-form.group>
                    </div>

                    <div class="col-lg-6">
                        <x-form.group>
                            <x-form.label>@lang('Phone Number')</x-form.label>
                            <x-form.input
                                name="phone_number"
                                :placeholder="__('Enter a phone number')"
                                value="{{ old('phone_number', @$member->phone_number) }}"
                            />
                        </x-form.group>
                    </div>

                    @if (!@$member)
                        <div class="col-lg-6">
                            <x-form.group>
                                <x-form.label>@lang('Password')</x-form.label>
                                <x-form.input
                                    type="password"
                                    name="password"
                                    :placeholder="__('Enter password')"
                                />
                            </x-form.group>
                        </div>
                    @endif

                    <div class="col-lg-6">
                        <x-form.group>
                            <x-form.label>@lang('Role')</x-form.label>
                            <select name="role_id" class="form-control select2">
                                @foreach ($roles as $role)
                                    <option @isset($member) @selected(@$member->roles->contains('id', $role->id) ?? []) @endisset 
                                    value="{{ $role->id }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </x-form.group>
                    </div>
                </div>

                <div class="text-end">
                    <x-button type="submit">
                        <x-icons.save />
                        @lang('Submit')
                    </x-button>
                </div>
            </form>

        </div>
    </div>
@endsection
