@extends('admin.layouts.app')

@section('content')

    <x-page-header :page_title="__('Members')" :add_route="route('admin.user.new')" search="true" />

    <table class="table">
        <thead>
            <th>@lang('Name')</th>
            <th>@lang('Email')</th>
            <th>@lang('Phone Number')</th>
            <th>@lang('Father name')</th>
            <th>@lang('Address')</th>
            <th>@lang('District')</th>
            <th>@lang('Upazilla')</th>
            <th>@lang('Joined At')</th>
            <th>@lang('Status')</th>
            <th class="text-end">@lang('Action')</th>
        </thead>

        @forelse ($users as $user)
            <tr>
                <td>{{ $user->name ?? '-' }}</td>
                <td>{{ $user->email ?? 'N/A' }}</td>
                <td>
                    @if ($user->phone_number)
                        <a href="tel:{{ $user->phone_number }}">{{ $user->phone_number }}</a>
                    @else
                        {{ __('N/A') }}
                    @endif
                </td>
                <td>{{ $user->father_name ?? 'N/A' }}</td>
                <td>{{ $user->address ?? 'N/A' }}</td>
                <td>{{ $user->district ?? '-' }}</td>
                <td>{{ $user->upazila ?? '-' }}</td>
                <td>
                    <span>{{ System::getDateTime($user->created_at) }}</span>
                    <br />
                    <strong>{{ diffForHumans($user->created_at) }}</strong>
                </td>
                <td>@php echo $user->statusBadge; @endphp</td>

                <td class="text-end">
                    <div class="dropdown">
                        <button class="btn btn-sm btn-primary dropdown-toggle" type="button"
                            id="actionMenu{{ $user->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                            @lang('Actions')
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm"
                            aria-labelledby="actionMenu{{ $user->id }}">
                            {{-- Edit --}}
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-1"
                                    href="{{ route('admin.user.edit', $user->id) }}">
                                    <x-icons.edit class="me-1" />
                                    @lang('Edit')
                                </a>
                            </li>

                            {{-- Details --}}
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-1"
                                    href="{{ route('admin.user.details', ['id' => $user->id, 'page' => request()->get('page', 1)]) }}">
                                    <x-icons.eye class="me-1" />
                                    @lang('Details')
                                </a>
                            </li>

                            {{-- Delete --}}
                            <li>
                                <form method="POST" action="{{ route('admin.user.delete', $user->id) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger d-flex align-items-center gap-1"
                                        onclick="return confirm('Are you sure you want to delete this user?')">
                                        <x-icons.delete-v2 class="me-1" />
                                        @lang('Delete')
                                    </button>
                                </form>
                            </li>

                            <li><hr class="dropdown-divider"></li>

                            {{-- Dynamic Status Actions --}}
                            @php
                                $statusActions = [];

                                if ($user->status == 1) {
                                    $statusActions[] = ['label' => 'Deactivate','value' => 0,'class' => 'text-danger','icon' => 'x-circle','confirm' => 'Deactivate this user?'];
                                    $statusActions[] = ['label' => 'Block','value' => 2,'class' => 'text-warning','icon' => 'ban','confirm' => 'Block this user?'];
                                } elseif ($user->status == 0) {
                                    $statusActions[] = ['label' => 'Activate','value' => 1,'class' => 'text-success','icon' => 'check-circle','confirm' => 'Activate this user?'];
                                } elseif ($user->status == 2) {
                                    $statusActions[] = ['label' => 'Unblock','value' => 1,'class' => 'text-success','icon' => 'unlock','confirm' => 'Unblock this user?'];
                                }
                            @endphp

                            @foreach ($statusActions as $action)
                                <li>
                                    <form method="POST" action="{{ route('admin.user.status.change', $user->id) }}" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="status" value="{{ $action['value'] }}">
                                        <button type="submit"
                                            class="dropdown-item d-flex align-items-center gap-1 {{ $action['class'] }}"
                                            onclick="return confirm('{{ $action['confirm'] }}')">
                                            <x-icons.{{ $action['icon'] }} class="me-1" />
                                            {{ __($action['label']) }}
                                        </button>
                                    </form>
                                </li>
                            @endforeach

                            <li>
                                <a class="dropdown-item" href="{{ route('admin.user.login', $user->id) }}" target="_blank">
                                    <x-icons.login />
                                    @lang('Login As')
                                </a>
                            </li>
                            
                            <li>
                                <a class="dropdown-item" href="https://wa.me/+88{{ $user->phone_number }}" target="_blank">
                                    <x-icons.whatsapp /> 
                                    @lang('Chat on WhatsApp')
                                </a>
                            </li>

                            {{-- Send SMS --}}
                            <li>
                                <button type="button"
                                    class="dropdown-item d-flex align-items-center gap-1 text-info btn-send-sms"
                                    data-user-id="{{ $user->id }}"
                                    data-user-name="{{ $user->name }}"
                                    data-bs-toggle="modal"
                                    data-bs-target="#sendSmsModal">
                                    <x-icons.send class="me-1" />
                                    {{ __('Send SMS') }}
                                </button>
                            </li>
                        </ul>
                    </div>
                </td>
            </tr>
        @empty
            <x-admin-empty-table />
        @endforelse
    </table>

    <x-admin-paginate :model="$users" />

    {{-- Global SMS Modal --}}
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
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const smsButtons = document.querySelectorAll(".btn-send-sms");
        const form = document.getElementById("sendSmsForm");
        const modalTitle = document.querySelector("#sendSmsModal .modal-title");

        smsButtons.forEach(button => {
            button.addEventListener("click", function () {
                let userId = this.getAttribute("data-user-id");
                let userName = this.getAttribute("data-user-name");

                // Update modal title
                modalTitle.textContent = "Send SMS to " + userName;

                const url = "{{ route('admin.user.send_sms', ':id') }}".replace(':id', userId);
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
