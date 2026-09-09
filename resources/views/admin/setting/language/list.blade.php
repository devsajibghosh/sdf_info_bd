@extends('admin.layouts.settings')

@section('panel')

<x-page-header 
    :page_title="__('Languages')" 
>
    <x-button class="keyworsdBtn">
        <x-icons.clipboard />
        @lang('Copy Keywords')
    </x-button>
    <x-button class="addBtn">
        <x-icons.add />
        @lang('Add New')
    </x-button>
</x-page-header>

<table class="table">
    <thead>
        <th>@lang('Name')</th>
        <th>@lang('Code')</th>
        <th>@lang('Flag')</th>
        <th class="text-end">@lang('Action')</th>
    </thead>

    @forelse ($languages as $language)
        <tr>
            <td>{{ $language->name }}</td>
            <td>{{ $language->code }}</td>
            <td>{{ $language->flag }}</td>
            <td class="text-end">
                <x-button class="editBtn" data-action="{{ route('admin.setting.language.save', $language->id) }}" data-name="{{ $language->name }}" data-code="{{ $language->code }}">
                    <x-icons.edit />
                    @lang('Edit')
                </x-button>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="100%" class="text-center text-muted">@lang('No languages found.')</td>
        </tr>
    @endforelse
</table>

<x-admin-paginate :model="$languages" />

<x-modal size="lg" id="keywordsModal" title="Language Keywords" >
    <textarea name="" readonly id="" class="form-control keywords-here"></textarea>

    <x-slot:footer>
        <x-button class="clipboardBtn">
            <x-icons.clipboard />
            @lang('Copy to Clipboard')
        </x-button>
    </x-slot:footer>
</x-modal>

<x-modal 
    id="langModal" 
    title="Add new language" 
    :form="true" 
    method="POST" 
    action="{{ route('admin.setting.language.save') }}"
>
    @csrf

    <x-form.group>
        <x-form.label>@lang('Name')</x-form.label>
        <x-form.input name="name" :placeholder="__('Enter the language name')" />
    </x-form.group>

    <x-form.group>
        <x-form.label>@lang('Code')</x-form.label>
        <x-form.input name="code" :placeholder="__('Enter the language code')" />
    </x-form.group>

    <x-slot:footer>
        <x-button type="submit">
            <x-icons.save />
            @lang('Save')
        </x-button>
    </x-slot:footer>
</x-modal>

@endsection

@push('styles')
    <style>
        .keywords-here {
            min-height: 400px !important;
        }
    </style>
@endpush

@push('scripts')
    <script>
        'use strict';
        (function() {
            $(document).ready(function() {
                $('.clipboardBtn').on('click', function() {
                    const keywords = $('.keywords-here').val();
                    window.navigator.clipboard.writeText(keywords).then(() => {
                        $.jGrowl("{{ __('Keywords copied to clipboard') }}", {
                            header: "Success",
                            theme: "jgrowl-success",
                        });
                    });
                });
                
                $('.keyworsdBtn').on('click', function() {
                    const modal = $('#keywordsModal');
                    
                    $.get("{{ route('admin.setting.language.keywords') }}", function(response) {
                        if(response?.status == 'success')
                        {
                            $('.keywords-here').val(response?.keywords?.join('\n'));
                        }
                    });
                    
                    modal.modal('show');
                });
            
                $('.addBtn').on('click', function() {
                    $('#langModal').modal('show');
                });

                $('.editBtn').on('click', function() {
                    const modal = $('#langModal');
                    modal.find('form').attr('action', $(this).attr('data-action'));
                    modal.find('[name="name"]').val($(this).attr('data-name'));
                    modal.find('[name="code"]').val($(this).attr('data-code'));
                    modal.modal('show');
                });
            });
        })(jQuery);
    </script>
@endpush