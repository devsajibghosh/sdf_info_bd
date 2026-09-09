@extends('admin.layouts.app')

@section('content')
    <x-page-header :page_title="__('Expense Categories')" search="true">
        <x-button class="addBtn">
            <x-icons.add />
            @lang('Add New')
        </x-button>
    </x-page-header>

    <table class="table">
        <thead>
            <th>@lang('Name')</th>
            <th>@lang('Image')</th>
            <th>@lang('Status')</th>
            <th>@lang('Added By')</th>
            <th>@lang('Total Expense')</th>
            <th class="text-end">@lang('Action')</th>
        </thead>

        @forelse ($expenseCategories as $expenseCategory)
            <tr>
                <td>{{ $expenseCategory->name }}</td>
                <td>
                    <img src="{{ imageSrc($expenseCategory->image) }}" alt="@lang('Img')" class="img" width="50" />
                </td>
                <td>@php echo $expenseCategory->statusBadge; @endphp</td>
                <td>{{ $expenseCategory->admin->name ?? 'N/A' }}</td>
                <td>{{ System::amountWithCurrency($expenseCategory->expenses_sum_amount) }}</td>
                <td class="text-end">
                    <x-button class="btn-sm btn-info editBtn" data-status="{{ $expenseCategory->status }}" data-name="{{ $expenseCategory->name }}" data-action="{{ route('admin.expense_category.save', $expenseCategory->id) }}">
                        <x-icons.edit />
                        @lang('Edit')
                    </x-button>

                    <x-button confirmDelete class="btn-sm btn-danger"
                        href="{{ route('admin.expense_category.delete', $expenseCategory->id) }}">
                        <x-icons.delete-v2 />
                        @lang('Delete')
                    </x-button>
                </td>
            </tr>
        @empty
            <x-admin-empty-table />
        @endforelse
    </table>

    <x-admin-paginate :model="$expenseCategories" />

    <x-modal 
        id="crudModal" 
        title="Add New Expense Category" 
        :form="true" 
        method="POST" 
        action="{{ route('admin.expense_category.save') }}"
    >
        @csrf

        <x-form.group>
            <x-form.label>@lang('Name')</x-form.label>
            <x-form.input name="name" :placeholder="__('Enter the name')" />
        </x-form.group>

        <x-form.group>
            <x-form.label>@lang('Status')</x-form.label>
            <select name="status" id="status" class="form-control">
                <option value="1">@lang('Active')</option>
                <option value="0">@lang('Inactive')</option>
            </select>
        </x-form.group>

        <x-form.group>
            <x-form.label>@lang('Image')</x-form.label>
            <x-form.input name="image" type="file" />
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
        'use strict';
        (function($) {
            $('.addBtn').on('click', function() {
                const modal = $('#crudModal');
                modal.find('.modal-title').text("{{ __('Add New Expense Category') }}");
                modal.modal('show');
            });
            
            $('.editBtn').on('click', function() {
                const data =  $(this).data();

                const modal = $('#crudModal');
                modal.find('.modal-title').text("{{ __('Edit Expense Category') }}");
                modal.find('form').attr('action', data.action);
                modal.find('[name="name"]').val(data.name);
                modal.find('[name="status"]').val(data.status);
                modal.modal('show');

                console.log(data);
            });
        })(jQuery);
    </script>
@endpush