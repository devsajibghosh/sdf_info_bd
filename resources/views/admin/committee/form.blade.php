@extends('admin.layouts.app')

@section('content')
    <x-page-header 
        :page_title="isset($committee) ? __('Edit Committe') : __('Add Committe')" 
        :back_route="route('admin.committee.list')"
    />

    <form class="ajax-form" action="{{ route('admin.committee.save', $committee->id ?? null) }}" method="POST" enctype="multipart/form-data">
        <div class="row">
            <div class="col-lg-9">
                @csrf

                <x-card>
                    <div class="row gy-4">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label>@lang('Name')</label>
                                <input type="text" class="form-control" name="name" value="{{ old('name', $committee->name ?? '') }}" placeholder="@lang('Enter name')" />
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label>@lang('Title')</label>
                                <input type="text" class="form-control" name="title" value="{{ old('title', $committee->title ?? '') }}" placeholder="@lang('Enter title')" />
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="form-group">
                                <label>@lang('Image')</label>
                                <input type="file" class="form-control" name="image" accept="image/*" />
                                <small class="text-muted">{{ imageSize('committe') }}</small>
                                @if (!empty($committe->image))
                                    <div class="mt-2">
                                        <img src="{{ asset('storage/' . $committee->image) }}" alt="Image"
                                            height="60">
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-group">
                                <label class="form-label">@lang('Descriptoin')</label>
                                <textarea class="form-control" name="description" id="post-editor" rows="6" placeholder="@lang('Enter descriptoin')">{{ old('description', $committee->description ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>
                </x-card>
            </div>

            <div class="col-lg-3">
                <aside class="software-sidebar">
                    <x-card> 
                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <x-button type="submit">
                                <x-icons.save />
                                @lang('Save')
                            </x-button>
                            <x-button id="save-and-back" type="submit" class="btn-danger">
                                <x-icons.save />
                                @lang('Save & Exit')
                            </x-button>
                        </div>
                    </x-card>
                </aside>
            </div>
        </div>
    </form>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/shared/css/tagify.css') }}">
@endpush

@push('scripts')
    <script src="https://cdn.ckeditor.com/ckeditor5/12.3.1/classic/ckeditor.js"></script>
    <script src="{{ asset('assets/shared/js/tagify.js') }}"></script>

    <script>
        $(document).ready(function() {
            SystemHelper.initEditor('#post-editor');
            SystemHelper.ajaxSubmit(
                $('.ajax-form')
            );

        });
    </script>
@endpush
