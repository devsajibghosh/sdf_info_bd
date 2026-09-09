@extends('admin.layouts.app')

@section('content')
    <x-page-header 
        :page_title="isset($project) ? __('Edit Project') : 'Add Project'" 
        :back_route="route('admin.project.list')"
    />

    <div class="card">
        <div class="card-body">
            <form action="{{ isset($project) ? route('admin.project.update', $project->id) : route('admin.project.save') }}"
                method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row gy-4">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label>@lang('Title')</label>
                            <input type="text" class="form-control" name="title"
                                value="{{ old('title', $project->title ?? '') }}" placeholder="@lang('Enter project title')" />
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label>@lang('Date')</label>
                            <input 
                                type="date" 
                                class="form-control" 
                                name="date"
                                value="{{ old('date', isset($project->created_at) ? $project->created_at->format('Y-m-d') : now()->format('Y-m-d')) }}" 
                                placeholder="@lang('Select date')" 
                            />
                        </div>
                    </div>



                    <div class="col-lg-6">
                        <div class="form-group">
                            <label>@lang('Project Image')</label>
                            <input type="file" class="form-control" name="image" accept="image/*" />
                            <small class="text-">{{ imageSize('project') }}</small>
                            @if (!empty($project->image))
                                <div class="mt-2">
                                    <img src="{{ asset('storage/' . $project->image) }}" alt="Project Image" height="60">
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="form-group">
                            <label class="form-label">@lang('Details')</label>
                            <textarea class="form-control" name="details" id="details-editor" rows="6" placeholder="@lang('Enter project details')">{{ old('details', $project->details ?? '') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <x-button type="submit">
                        <x-icons.save />
                        @lang('Save')
                    </x-button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.ckeditor.com/ckeditor5/12.3.1/classic/ckeditor.js"></script>

    <script>
        SystemHelper.initEditor('#details-editor');
    </script>
@endpush
