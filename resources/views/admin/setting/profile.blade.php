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
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <div class="text-center">
                        <img 
                            class="img-fluid admin-image" 
                            src="{{ \App\Services\FileManager::getPublicUrl(admin()->image) }}"
                            alt="image">
                    </div>


                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <strong>@lang('Name')</strong>
                            <span>{{ $admin->name }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <strong>@lang('Username')</strong>
                            <span>{{ $admin->username }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <strong>@lang('Email')</strong>
                            <span>{{ $admin->email }}</span>
                        </li>
                    </ul>
                </div>
            </div>

        </div>
        <div class="col-lg-8">
            <form action="{{ route('admin.setting.profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="card">
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <label for="image" class="form-label">@lang('Profile image')</label>
                            <input type="file" accept="image/*" class="form-control" name="image" />
                            <small class="text-muted">{{ imageSize('adminProfile') }}</small>
                        </div>

                        <div class="form-group">
                            <label for="name" class="form-label">@lang('Name')</label>
                            <input type="text" class="form-control" value="{{ $admin->name }}"
                                placeholder="@lang('Enter your name')" name="name" />
                        </div>

                        <div class="form-group mt-3">
                            <label for="name" class="form-label">@lang('Email')</label>
                            <input type="text" class="form-control" value="{{ $admin->email }}"
                                placeholder="@lang('Enter your email')" name="email" />
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

@push('styles')
    <style>
        .admin-image {
            max-width: 100px;
            border-radius: 50%;
            margin: auto;
            margin-bottom: 10px;
        }
    </style>
@endpush


@push('scripts')
    <script>
        'use strict';
        
    </script>
@endpush