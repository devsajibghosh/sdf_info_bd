@extends('admin.layouts.app')

@section('content')
    <x-page-header 
        :page_title="__('Manual Submissions')"
    >
        <x-button class="addBtn">
            <x-icons.add /> @lang('Add New')
        </x-button>
    </x-page-header>

    <table class="table">
        <thead>
            <th>@lang('Gateway/Method')</th>
            <th>@lang('Amount')</th>
            <th>@lang('Note')</th>
            <th>@lang('Created At')</th> 
            <th class="text-end">@lang('Action')</th>
        </thead>

        <tbody>
            @forelse ($manualSubmissions as $manualSubmission)
                <tr>
                    <td>{{ $manualSubmission?->gateway?->name }}</td>
                    <td>{{ System::amountWithCurrency($manualSubmission->amount) }}</td> 
                    <td>{{ $manualSubmission->note ?? '' }}</td>
                    <td>{{ System::getDateTime($manualSubmission->created_at) }}</td>
                    <td class="text-end">      
                        <x-button 
                            data-action="{{ route('admin.donation.manual_submissions.save', $manualSubmission->id) }}"
                            class="btn-sm btn-primary editBtn" 
                            title="@lang('Edit')"
                            data-id="{{ $manualSubmission->id }}"
                            data-note="{{ $manualSubmission->note }}"
                            data-gateway="{{ $manualSubmission->gateway_id }}"
                            data-amount="{{ $manualSubmission->amount }}"
                            data-created="{{ $manualSubmission->created_at }}"
                        >
                            <x-icons.edit />
                        </x-button>

                        <x-button confirmDelete class="btn-sm btn-danger" title="@lang('Delete')"
                            href="{{ route('admin.donation.manual_submissions.delete', $manualSubmission->id) }}">
                            <x-icons.delete-v2 />
                        </x-button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="100%" class="text-center text-muted">@lang('No data found.')</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if ($manualSubmissions->hasPages())
        <div class="mt-3">
            {!! $manualSubmissions->links() !!}
        </div>
    @endif 
    
    
    <x-modal 
        id="theModal" 
        title="Manual Submission" 
        :form="true" 
        method="POST" 
        action="{{ route('admin.donation.manual_submissions.save') }}"
    >
        @csrf  
        <input type="hidden" name="id" id="submission_id" />

        <x-form.group>
            <x-form.label>@lang('Gateway')</x-form.label>
            <select name="gateway_id" id="gateway_id" class="form-control">
                @foreach($gateways as $gate)
                    <option value="{{ $gate->id }}">{{ $gate->name }}</option>
                @endforeach
            </select>
        </x-form.group>
        
        <x-form.group>
            <x-form.label>@lang('Amount')</x-form.label>
            <x-form.input name="amount" id="amount" type="number" />
        </x-form.group>

        <x-form.group>
            <x-form.label>@lang('Date Time')</x-form.label>
            <x-form.input 
                value="{{ old('created_at', now()->format('Y-m-d\TH:i')) }}" 
                name="created_at" 
                id="created_at" 
                type="datetime-local" 
            />
        </x-form.group>
        
        <x-form.group>
            <x-form.label>@lang('Note')</x-form.label>
            <textarea name="note" class="form-control" id="note">@lang('Add a note')</textarea>
        </x-form.group>

    
        <x-slot:footer>
            <x-button type="submit">
                <x-icons.save />
                @lang('Submit')
            </x-button>
        </x-slot:footer>
    </x-modal>
@endsection

@push('scripts')
<script>
    $(function () {
        // datetime-local inputs need local wall-clock "YYYY-MM-DDTHH:mm", not UTC.
        // toISOString() converts to UTC first, which silently shifts the value by
        // the browser's UTC offset (6 hours for Bangladesh) every time this ran.
        function toLocalDateTimeInputValue(date) {
            const pad = (n) => String(n).padStart(2, '0');
            return date.getFullYear() + '-' + pad(date.getMonth() + 1) + '-' + pad(date.getDate())
                + 'T' + pad(date.getHours()) + ':' + pad(date.getMinutes());
        }

        // Add new
        $('.addBtn').on('click', function() {
            $('#theModal').find('form').trigger('reset');
            $('#submission_id').val('');
            $('#gateway_id').val('').trigger('change');
            $('#amount').val('');
            $('#created_at').val(toLocalDateTimeInputValue(new Date()));
            $('#theModal').modal('show');
        });


        $('.editBtn').on('click', function() {
            let id = $(this).data('id');
            let gateway = $(this).data('gateway');
            let amount = $(this).data('amount');
            let note = $(this).data('note');
            let created = $(this).data('created');

            $('#submission_id').val(id);
            $('#gateway_id').val(gateway).trigger('change');
            $('#amount').val(amount);
            $('#note').val(note);

            // `created` is already "Y-m-d H:i:s" local wall-clock time from the
            // server; reformat as text instead of round-tripping through Date/UTC.
            $('#created_at').val(String(created).replace(' ', 'T').slice(0, 16));
            $('#theModal').find('form').attr('action', $(this).data('action'));
            $('#theModal').modal('show');
        });
    });
</script>
@endpush