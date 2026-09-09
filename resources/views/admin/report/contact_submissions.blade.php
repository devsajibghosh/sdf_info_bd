@extends('admin.layouts.app')

@section('content')
    <x-page-header 
        :page_title="__('Contact Submissions')" 
    >
        <x-admin-search />
    </x-page-header>

    <table class="table">
        <thead>
            <th>@lang('Name')</th>
            <th>@lang('Phone Number')</th>
            <th>@lang('Subject')</th>
            <th>@lang('Message')</th>
            <th class="text-end">@lang('Time')</th>
            <th>@lang('Action')</th>
        </thead>

        <tbody>
            @forelse ($contacts as $contact)
                <tr>
                    <td>{{ $contact->name }}</td>
                    <td>{{ $contact->phone_number }}</td>
                    <td>{{ $contact->subject }}</td>
                    <td>{{ $contact->message }}</td>
                    <td class="text-end">
                        {{ System::getDateTime($contact->created_at) }}
                        <br>
                        <strong>{{ $contact->created_at->diffForHumans() }}</strong>
                    </td>
                    <td class="text-end">
                        <div class="d-flex gap-2">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-primary dropdown-toggle" type="button"
                                    id="actionMenu{{ $contact->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                    @lang('Actions')
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm"
                                    aria-labelledby="actionMenu{{ $contact->id }}">
                                    
                                    <li>
                                        <a class="dropdown-item" href="https://wa.me/+88{{ $contact->phone_number }}" target="_blank">
                                            <x-icons.whatsapp /> 
                                            @lang('Chat on WhatsApp')
                                        </a>
                                    </li>
        
                                    {{-- Send SMS --}}
                                    <li>
                                        <button type="button"
                                            class="dropdown-item d-flex align-items-center gap-1 text-info btn-send-sms"
                                            data-contact-id="{{ $contact->id }}"
                                            data-user-name="{{ $contact->name }}"
                                            data-bs-toggle="modal"
                                            data-bs-target="#sendSmsModal">
                                            <x-icons.send class="me-1" />
                                            {{ __('Send SMS') }}
                                        </button>
                                    </li>
                                </ul>
                                    <x-button confirmDelete class="btn-danger" href="{{ route('admin.report.contact_submission.delete', $contact->id) }}">
                                       <x-icons.delete-v2 />
                                       @lang('Delete')
                                     </x-button> 
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <x-admin-empty-table />
            @endforelse
        </tbody>
    </table>
    
    <div class="modal fade" id="sendSmsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-3 shadow">
                <div class="modal-header">
                    <h5 class="modal-title">
                        {{ __('Send SMS') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="@lang('Close')"></button>
                </div>
                <form id="sendSmsForm" method="POST" action="#">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">@lang('Message')</label>
                            <textarea name="message" class="form-control" rows="4" placeholder="@lang('Write your message here...')"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">@lang('Close')</button>
                        <button type="submit" class="btn btn-primary">@lang('Send')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <x-admin-paginate :model="$contacts" />
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const smsButtons = document.querySelectorAll(".btn-send-sms");
        const form = document.getElementById("sendSmsForm");
        const modalTitle = document.querySelector("#sendSmsModal .modal-title");

        smsButtons.forEach(button => {
            button.addEventListener("click", function () {
                let contactId = this.getAttribute("data-contact-id");
                let userName = this.getAttribute("data-user-name");

                // Update modal title
                modalTitle.textContent = " " + (userName ?? '');

                const url = "{{ route('admin.user.contact_sms', ':id') }}".replace(':id', contactId);
                form.setAttribute("action", url);
            });
        });
    });
</script>
@endpush

@push('styles')
<style>
    .dropdown-menu .dropdown-item svg {
        width: 14px;
        height: 14px;
    }

    .dropdown-menu .dropdown-item:hover {
        background-color: #f1f1f1;
    }
</style>
@endpush