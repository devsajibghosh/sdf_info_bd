@extends('admin.layouts.app')

@section('content')
    <x-page-header 
        :page_title="isset($donationCategory) ? __('Edit Donation Category') : __('Add Donation Category')" 
        :back_route="route('admin.donation_category.list')"
    />

    <form class="ajax-form" action="{{ route('admin.donation_category.save', $donationCategory->id ?? null) }}" method="POST"
        enctype="multipart/form-data">
        <div class="row">
            <div class="col-lg-9">
                @csrf

                <x-card>
                    <div class="row gy-4">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label for="name" class="form-label">@lang('Name')</label>
                                <input type="text" class="form-control" name="name"
                                    value="{{ old('title', $donationCategory->name ?? '') }}"
                                    placeholder="@lang('Enter category name')" />
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="form-group">
                                <label for="image" class="form-label">@lang('Image')</label>
                                <input type="file" class="form-control" name="image" accept="image/*" />

                                <x-file-input-help-text key="donationCategory" />

                                @if (!empty($donationCategory->image))
                                    <div class="mt-2">
                                        <img src="{{ asset('storage/' . $donationCategory->image) }}"
                                            alt="@lang('Image')" height="60">
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-group">
                                <label for="short_desc" class="form-label">@lang('Short Description')</label>
                                <textarea class="form-control" name="short_desc" id="short_desc" rows="6" placeholder="@lang('Enter a short description')">{{ old('short_desc', $donationCategory->short_desc ?? '') }}</textarea>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-group">
                                <label for="body" class="form-label">@lang('Details')</label>
                                <textarea class="form-control" name="body" id="details-editor" rows="6" placeholder="@lang('Enter a details for this category')">{{ old('body', $donationCategory->details ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>
                </x-card>

            </div>

            <div class="col-lg-3">
                <aside class="software-sidebar">
                    <x-card>
                        <select class="form-control select2" name="status">
                            <option value="1" @selected(isset($donationCategory) && $donationCategory?->status == 1 ?? false)>@lang('Active')</option>
                            <option value="0" @selected(isset($donationCategory) && $donationCategory?->status == 0 ?? false)>@lang('Inactive')</option>
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
    <script src="https://cdn.ckeditor.com/ckeditor5/12.3.1/classic/ckeditor.js"></script>

    <script>
        $(document).ready(function() {
            SystemHelper.initEditor('#details-editor');

            SystemHelper.ajaxSubmit(
                $('.ajax-form')
            );

        });
    </script>
@endpush
