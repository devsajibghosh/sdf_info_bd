@extends('admin.layouts.app')

@section('content')

    <x-modal 
        id="uploadModal" 
        title="Upload Expense Data" 
        :form="true" 
        method="POST" 
        action="{{ route('admin.expense.upload') }}"
    >
        @csrf
    
        <div class="alert alert-info">
            @lang('Please download the sample file by ') <a target="_blank" href="{{ asset('expense-test.csv') }}" download="">@lang('clicking here')</a>
        </div>
        
        <x-form.group>
            <x-form.label>@lang('Upload The File')</x-form.label>
            <x-form.input name="file" type="file" />
        </x-form.group>
    
        <x-slot:footer>
            <x-button type="submit">
                <x-icons.save />
                @lang('Upload')
            </x-button>
        </x-slot:footer>
    </x-modal>






    <x-page-header :page_title="__('Expenses')" search="true">
    <div class="d-flex align-items-center gap-2">
        @if(userCan('download-expense'))
            <!-- PDF Download -->
            <x-button href="{{ route('admin.expense.list', array_merge(request()->all(), ['download' => true])) }}">
                <x-icons.print />
                @lang('PDF')
            </x-button>
            <x-button href="{{ route('admin.expense.list', array_merge(request()->all(), ['export_excel' => true])) }}">
                <x-icons.print />
                @lang('Excel')
            </x-button>
        @endif
            
        <x-button class="uploadBtn btn-info">@lang('Upload CSV')</x-button>

        <x-button class="addBtn">
            <x-icons.add />
            @lang('Add New')
        </x-button>
    </div>
</x-page-header>
    
    

    <div class="table-responsive">
    <table class="table">
        <thead>
            <tr>
                <th>@lang('Image')</th>
                <th>@lang('Category')</th>
                <th>@lang('Amount')</th>
                <th>@lang('Note')</th>
                <th>@lang('Added By')</th> 
                <th>@lang('Approved By')</th> 
                <th>@lang('Created At')</th>
                <th>@lang('Status')</th>
                <th class="text-end">@lang('Action')</th>
            </tr>
        </thead>

        @forelse ($expenses as $expense)
            <tr>
                
                <td>
                    @if($expense->image)
                        <a href="{{ imageSrc($expense->image) }}" targe="_blank">
                            <img src="{{ imageSrc($expense->image) }}" alt="img" class="img-thumbnail" style="max-width: 60px;" width="60" height="60" loading="lazy">
                        </a>
                    @else
                        {{ __('N/A') }}
                    @endif
                </td>
                
                
                <td>{{ $expense->category->name ?? 'N/A' }}</td>
                <td>{{ System::amountWithCurrency($expense->amount) }}</td>
                <td>{{ $expense->note ? str()->limit($expense->note, 50) : __('N/a') }}</td>
                <td>{{ $expense->expenseBy->name ?? __('N/A') }}</td> 
                <td>{{ $expense->approvedBy->name ?? __('N/A') }}</td> 
                <td>{{ System::getDatetime($expense->created_at) }}</td>
                <td>@php echo $expense->statusBadge; @endphp</td>
                <td class="text-end">
                    <x-button class="btn-sm btn-info editBtn"
                        data-id="{{ $expense->id }}"
                        data-amount="{{ $expense->amount }}"
                        data-note="{{ $expense->note }}"
                        data-created_at="{{ $expense->created_at }}"
                        data-category="{{ $expense->expense_category_id }}"
                        data-action="{{ route('admin.expense.save', $expense->id) }}">
                        <x-icons.edit />
                        @lang('Edit')
                    </x-button>


                    @if($expense->status == 0) 
                        <form action="{{ route('admin.expense.status.approve', $expense->id) }}" method="POST" class="d-inline-block">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Are you sure?')">
                                <x-icons.check-circle />
                                @lang('Approve')
                            </button>
                        </form> 
                    @elseif($expense->status == 1) 
                        <form action="{{ route('admin.expense.status.unapprove', $expense->id) }}" method="POST" class="d-inline-block">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                <x-icons.ban />
                                @lang('Unapprove')
                            </button>
                        </form> 
                    @endif
                    
                    <form action="{{ route('admin.expense.delete', $expense->id) }}" method="POST" class="d-inline-block">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                            <x-icons.delete-v2 />
                            @lang('Delete')
                        </button>
                    </form>
                </td>
            </tr>
        @empty
            <x-admin-empty-table />
        @endforelse
    </table>
    </div>

    <x-admin-paginate :model="$expenses" />

    {{-- Add/Edit Modal --}}
    <x-modal 
        id="crudModal" 
        title="Add New Expense" 
        :form="true" 
        method="POST" 
        enctype="multipart/form-data"
        action="{{ route('admin.expense.save') }}"
    >
        @csrf
        <x-form.group>
            <x-form.label>@lang('Category')</x-form.label>
            <select name="expense_category_id" class="form-control">
                @foreach ($expenseCategories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
        </x-form.group>

        <x-form.group>
            <x-form.label>@lang('Amount')</x-form.label>
            <x-form.input name="amount" type="number" step="0.01" :placeholder="__('Enter amount')" />
        </x-form.group>

        <x-form.group>
            <x-form.label>@lang('Created At')</x-form.label>
            <x-form.input 
                name="created_at" 
                type="date" 
                :placeholder="__('Optional note')" 
            />
        </x-form.group>

        <x-form.group>
            <x-form.label>@lang('Note')</x-form.label>
            <textarea class="form-control" name="note" rows="3" :placeholder="__('Optional note')"></textarea>
        </x-form.group>

        <x-form.group>
            <x-form.label>@lang('Image')</x-form.label>
            <input type="file" name="image" class="form-control" accept="image/jpeg,image/jpg,image/png,image/webp">
            <small class="text-muted">
                {{ imageSize('expense') }} &mdash; @lang('Large images are automatically optimized/compressed.')
            </small>
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
        $('.uploadBtn').on('click', function() {
            $('#uploadModal').modal('show');
        }); 
        
        $('.addBtn').on('click', function() {
            const modal = $('#crudModal');
            modal.find('.modal-title').text("{{ __('Add New Expense') }}");
            modal.find('form').trigger('reset');
            modal.find('form').attr('action', "{{ route('admin.expense.save') }}");
            modal.modal('show');
        });

        $('.editBtn').on('click', function() {
            const data = $(this).data();
            const modal = $('#crudModal');

            modal.find('.modal-title').text("{{ __('Edit Expense') }}");
            modal.find('form').attr('action', data.action);

            modal.find('[name="created_at"]').val(data.created_at?.split(' ')[0]);
            modal.find('[name="amount"]').val(Number(data.amount).toFixed(2));
            modal.find('[name="note"]').val(data.note);
            modal.find('[name="expense_category_id"]').val(data.category);

            modal.modal('show');
        });
    })(jQuery);
</script>
@endpush
