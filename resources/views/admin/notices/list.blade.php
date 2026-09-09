@extends('admin.layouts.app')

@section('content')
    <x-page-header :page_title="__('Notices')" search="true">
        <x-button class="addBtn">
            <x-icons.add />
            @lang('Add New')
        </x-button>
    </x-page-header>

    <table class="table">
        <thead>
            <tr>
                <th>@lang('Title')</th>
                <th>@lang('Visible From')</th>
                <th>@lang('Visible To')</th>
                <th>@lang('Status')</th>
                <th class="text-end">@lang('Action')</th>
            </tr>
        </thead>
        <tbody>
            @forelse($notices as $notice)
                <tr>
                    <td>{{ $notice->title }}</td>
                    <td>{{ $notice->visible_from ?? 'N/A' }}</td>
                    <td>{{ $notice->visible_to ?? 'N/A' }}</td>
                    <td>@php echo $notice->statusBadge; @endphp</td>
                    <td class="text-end">
                        <x-button class="btn-sm btn-info editBtn"
                            data-id="{{ $notice->id }}"
                            data-title="{{ $notice->title }}"
                            data-description="{{ $notice->description }}"
                            data-status="{{ $notice->status }}"
                            data-visible_from="{{ $notice->visible_from }}"
                            data-visible_to="{{ $notice->visible_to }}"
                            data-action="{{ route('admin.notice.save', $notice->id) }}">
                            <x-icons.edit /> @lang('Edit')
                        </x-button>

                        <form action="{{ route('admin.notice.delete', $notice->id) }}" method="POST" class="d-inline-block">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                <x-icons.delete-v2 /> @lang('Delete')
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <x-admin-empty-table />
            @endforelse
        </tbody>
    </table>

    <x-admin-paginate :model="$notices" />

    {{-- Modal --}}
    <x-modal 
        id="crudModal" 
        title="Add New Notice" 
        :form="true" 
        method="POST" 
        action="{{ route('admin.notice.save') }}"
    >
        @csrf
        <x-form.group>
            <x-form.label>@lang('Title')</x-form.label>
            <x-form.input name="title" required />
        </x-form.group>

        <x-form.group>
            <x-form.label>@lang('Description')</x-form.label>
            <textarea id="post-editor" class="form-control" name="description" rows="3"></textarea>
        </x-form.group>

        <x-form.group>
            <x-form.label>@lang('Visible From')</x-form.label>
            <x-form.input name="visible_from" type="date" />
        </x-form.group>

        <x-form.group>
            <x-form.label>@lang('Visible To')</x-form.label>
            <x-form.input name="visible_to" type="date" />
        </x-form.group>

        <x-form.group>
            <x-form.label>@lang('Status')</x-form.label>
            <select name="status" class="form-control">
                <option value="1">@lang('Active')</option>
                <option value="0">@lang('Inactive')</option>
            </select>
        </x-form.group>

        <x-slot:footer>
            <x-button type="submit">
                <x-icons.save /> @lang('Save')
            </x-button>
        </x-slot:footer>
    </x-modal>
@endsection

@push('scripts')
<script>
    (function ($) {
        'use strict';
        $('.addBtn').on('click', function () {
            const modal = $('#crudModal');
            modal.find('.modal-title').text("{{ __('Add New Notice') }}");
            modal.find('form').trigger('reset');
            modal.find('form').attr('action', "{{ route('admin.notice.save') }}");
            modal.modal('show');
        });

        $('.editBtn').on('click', function () {
            const data = $(this).data();
            const modal = $('#crudModal');

            modal.find('.modal-title').text("{{ __('Edit Notice') }}");
            modal.find('form').attr('action', data.action);

            modal.find('[name="title"]').val(data.title);
            modal.find('[name="description"]').val(data.description);
            modal.find('[name="status"]').val(data.status);
            modal.find('[name="visible_from"]').val(data.visible_from?.split(' ')[0]);
            modal.find('[name="visible_to"]').val(data.visible_to?.split(' ')[0]);
            
            if (window.editors && window.editors['post-editor']) {
                window.editors['post-editor'].setData(data.description || '');
            }

            modal.modal('show');
        });
    })(jQuery);
</script>
@endpush

@push('scripts')
    <script src="https://cdn.ckeditor.com/ckeditor5/12.3.1/classic/ckeditor.js"></script> 

    <script>
        $(document).ready(function() {
            ClassicEditor.create(document.querySelector('#post-editor'))
                .then(editor => {
                    if (!window.editors) window.editors = {};
                    window.editors['post-editor'] = editor;
                })
                .catch(error => {
                    console.error(error);
                });
        });
    </script>
@endpush