@extends('admin.layouts.app')

@section('content')
    <x-base.page-header>
        <x-slot name="title">
            {{ __('Notifications') }}
        </x-slot>

        @if(\App\Models\AdminNotification::unRead()->count() > 0)
            <x-slot name="right">
                <x-admin-search />
                <x-button href="{{ route('admin.report.notifications.read.all') }}">@lang('Mark all as read')</x-button>
            </x-slot>
        @endif 
        
        
        @if(\App\Models\AdminNotification::count() > 0)
            <x-slot name="right">
              <x-button confirmDelete class="btn-danger" href="{{ route('admin.report.notifications.delete.all') }}">
                  <x-icons.delete-v2 />
                  @lang('Delete All')</x-button>
            </x-slot>
        @endif
    </x-base.page-header>

    <div class="notification-list">
        @forelse ($notifications as $notification)
            <a href="{{ route('admin.report.notifications.read', $notification->id) }}"
                class="notif-link {{ $notification->is_read ? 'read' : 'unread' }}">
                <div class="notif-content">
                    <span class="notif-icon">🔔</span>
                    <div class="notif-text">
                        <span class="notif-details">{{ $notification->details }}</span>
                        <small class="notif-date">{{ $notification->created_at->diffForHumans() }}</small>
                    </div>

                    <x-button href="{{ route('admin.report.notifications.delete', $notification->id) }}" confirmDelete class="btn-danger btn-sm">
                        <x-icons.delete-v2 />
                    </x-button>
                </div>
            </a>
        @empty
            <div class="no-notifications">
                {{ __('No notifications found.') }}
            </div>
        @endforelse
    </div>

    @if ($notifications->hasPages())
        <div class="mt-4">
            {!! $notifications->links() !!}
        </div>
    @endif
@endsection

@push('styles')
    <style>
        .notification-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .notif-link {
            text-decoration: none;
            padding: 12px 16px;
            background-color: #ffffff;
            color: #1a1a1a;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
            transition: background-color 0.2s, box-shadow 0.2s;
            display: flex;
            align-items: center;
        }

        .notif-link:hover {
            background-color: #f9f9f9;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.06);
        }

        .notif-link.unread {
            border-left: 4px solid var(--primary-color);
            background-color: #eef5ff;
        }

        .notif-content {
            display: flex;
            align-items: center;
            gap: 12px;
            width: 100%;
            position: relative;
        }

        .notif-content .btn {
            position: absolute;
            top: 10px;
            right: 10px;
        }

        .notif-icon {
            font-size: 1.5rem;
        }

        .notif-text {
            display: flex;
            flex-direction: column;
        }

        .notif-details {
            font-size: 1rem;
            font-weight: 500;
            margin-bottom: 4px;
        }

        .notif-date {
            font-size: 0.85rem;
            color: #777;
        }

        .no-notifications {
            padding: 20px;
            text-align: center;
            color: #888;
            font-style: italic;
        }
    </style>
@endpush
