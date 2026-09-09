@extends('admin.layouts.app')

@section('content')
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h3 class="mb-3 text-xl">@lang('Add New Page')</h3>

        <x-button href="{{ route('admin.website.page.list') }}">
            <x-icons.back-v1 />
            @lang('Back')
        </x-button>
    </div>

    <div class="container-fluid page-layout-editor">
        <form method="POST" action="{{ route('admin.website.page.save') }}" id="pageLayoutForm">
            @csrf
            <x-card class="mb-4">
                <div class="form-group mb-3">
                    <label for="title" class="form-label">
                        @lang('Page Title')
                    </label>
                    <input type="text" value="{{ old('title') }}" required class="form-control" name="title"
                        id="title" />
                </div>

                <div class="text-end">
                    <x-button>
                        <x-icons.save />
                        @lang('Save')
                    </x-button>
                </div>
            </x-card>
        </form> {{-- End of form --}}
    </div>
@endsection
