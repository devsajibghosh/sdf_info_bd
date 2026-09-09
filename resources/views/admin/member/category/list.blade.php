@extends('admin.layouts.app')

@section('content')

<x-page-header 
    :page_title="__('Member Categories')" 
    :search="true"
>
    <x-button class="addBtn">
        <x-icons.add />
        @lang('Add New')
    </x-button>
</x-page-header>

<table class="table">
    <thead>
        <th>@lang('Name')</th>
        <th>@lang('Members Count')</th>
        <th class="text-end">@lang('Action')</th>
    </thead>

    <tbody>
        @forelse ($categories as $category)
            <tr>
                <td>{{ $category->name }}</td>
                <td>{{ $category->users_count }}</td>
                <td class="text-end">
                    <x-button href="{{ route('admin.user.list') }}?category_id={{ $category->id }}">
                        <x-icons.eye />
                        @lang('Members')
                    </x-button>

                    <x-button class="editBtn" data-action="{{ route('admin.user.category.store', $category->id) }}" data-name="{{ $category->name }}">
                        <x-icons.edit />
                        @lang('Edit')
                    </x-button>
                    <x-button confirmDelete class="btn-danger" href="{{ route('admin.user.category.delete', $category->id) }}">
                        <x-icons.delete-v2 />
                        @lang('Delete')
                    </x-button>
                </td>
            </tr>
        @empty
            <x-admin-empty-table />
        @endforelse
    </tbody>
</table>

<x-admin-paginate :model="$categories" />

<x-modal 
    id="categoryModal" 
    title="Add member category" 
    :form="true" 
    method="POST" 
    action="{{ route('admin.user.category.store') }}"
>
    @csrf

    <x-form.group>
        <x-form.label>@lang('Name')</x-form.label>
        <x-form.input name="name" :placeholder="__('Enter the category name')" />
    </x-form.group> 

    <x-slot:footer>
        <x-button type="submit">
            <x-icons.save />
            @lang('Save')
        </x-button>
    </x-slot:footer>
</x-modal>

@endsection

@push('scripts')
    <script>
        $('.editBtn').on('click', function() {
            const modal = $('#categoryModal');
            modal.find('form').attr('action', $(this).attr('data-action'));
            modal.find('[name="name"]').val($(this).attr('data-name'));
            modal.modal('show');
        });
        
        $('.addBtn').on('click', function() {
            $('#categoryModal').modal('show');
        });
    </script>
@endpush