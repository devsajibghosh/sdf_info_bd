@extends('admin.layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-3">
            {{ isset($gallery) ? __('Edit Gallery Item') : __('Add Gallery Item') }}
        </h3>

        <x-button href="{{ route('admin.gallery.list') }}">
            <x-icons.back-v1 />
            @lang('Back')
        </x-button>
    </div>

    <form class="ajax-form" action="{{ route('admin.gallery.save', $gallery->id ?? null) }}" method="POST"
        enctype="multipart/form-data">
        <div class="row">
            <div class="col-lg-9">
                @csrf

                <x-card>
                    <div class="row gy-4">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label>@lang('Title')</label>
                                <input type="text" class="form-control" name="title"
                                    value="{{ old('title', $gallery->title ?? '') }}" placeholder="@lang('Enter gallery title or leave empty')" />
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="form-group">
                                <label for="image" class="form-label">@lang('Image')</label>
                                <input type="file" class="form-control" name="image" accept="image/*" />
                                <x-file-input-help-text key="gallery" />
                                @if (!empty($gallery->image))
                                    <div class="mt-2">
                                        <img src="{{ asset('storage/' . $gallery->image) }}" alt="@lang('Image')"
                                            height="60">
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="form-group">
                                <label>@lang('Gallery Category')</label>
                                <select name="gallery_category_id" class="form-control select2">
                                    <option value="">@lang('Select Gallery Category')</option>
                                    @foreach ($galleryCategories as $galleryCategory)
                                        <option value="{{ $galleryCategory->id }}">{{ $galleryCategory->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </x-card>

            </div>

            <div class="col-lg-3">
                <aside class="software-sidebar">
                    <x-card>
                        <select class="form-control select2" name="status">
                            <option value="1" @selected(isset($gallery) && $gallery?->status == 1 ?? false)>@lang('Active')</option>
                            <option value="0" @selected(isset($gallery) && $gallery?->status == 0 ?? false)>@lang('Inactive')</option>
                        </select>

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

@push('scripts')
    <script>
        $(document).ready(function() {
            SystemHelper.ajaxSubmit(
                $('.ajax-form')
            );

        });
    </script>
@endpush
