@extends('admin.layouts.app')

@section('content')
    <x-base.page-header>
        <x-slot name="title">
            {{ __('Media Manager') }}
        </x-slot>

        <x-slot name="right">
            <x-button class="file-uploader-btn">
                @lang('Upload File')
            </x-button>
        </x-slot>
    </x-base.page-header>

    <x-card>
        @if ($media->isEmpty())
            <p>@lang('No media files uploaded yet.')</p>
        @else
            <div class="row">
                @foreach ($media as $file)
                    <div class="col-md-2 mb-4 d-flex flex-direction-column">
                        <div class="file-item w-100">
                            @if (strpos($file->file_type, 'image') !== false)
                                <img src="{{ route('admin.media.show', $file->id) }}" alt="{{ $file->file_name }}"
                                    class="img-fluid mb-3" style="max-height: 150px;">
                            @else
                                <x-icons.file />
                                <p class="text-muted">{{ $file->file_name }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $media->links() }}
            </div>
        @endif
    </x-card>

    <!-- Media Uploader Modal -->
    <div class="modal fade" id="mediaUploaderModal" tabindex="-1" aria-labelledby="mediaUploaderModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data" action="{{ route('admin.media.upload') }}">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="mediaUploaderModalLabel">@lang('Media file uploader')</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        @csrf
                        <x-form.group label="File" for="file-upload-input">
                            <input class="form-control" id="file-upload-input" type="file" name="file" />
                        </x-form.group>
                    </div>
                    <div class="modal-footer">
                        <x-button type="submit">@lang('Submit')</x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        'use strict';
        (function() {
            $('.file-uploader-btn').on('click', function() {
                const $mediaUploaderModal = $('#mediaUploaderModal');
                $mediaUploaderModal.modal('show');
            });
        })();
    </script>
@endpush

@push('styles')
    <style>
        .file-item {
            text-align: center;
            background: #f0f0f0;
            border-radius: 6px;
            color: #000;
            display: flex;
            align-items: center;
            flex-direction: column;
            justify-content: center;
        }
    </style>
@endpush
