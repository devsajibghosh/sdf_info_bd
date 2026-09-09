@extends('user.layouts.main')

@section('content')
<div class="dashboard-container"> 
    <div class="row mb-4 gy-4">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h2>@lang('Total Donation Count')</h2>
                    <h4>{{ $widget['total_donate_count'] }} @lang('times')</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h2>@lang('Total Donation Amount')</h2>
                    <h4>{{ System::amountWithCurrency($widget['total_donate_amount']) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body h-100">
                    <h2>@lang('Last Donation Amount')</h2>
                    <h4>{{ System::amountWithCurrency(
                        $widget['last_donate_amount']
                    ) }}</h4>
                    <p>{{ $widget['last_donate_date'] ? System::getDateTime($widget['last_donate_date']) : 'N/A' }}</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="dashboard-cards">
        @auth
        <div class="card">
            <div class="card-icon">
                <i class="fas fa-hand-holding-heart"></i>
            </div>
            <div class="card-body">
                <h2>@lang('Donate Now')</h2>
                <p>@lang('Make a new contribution and support our cause.')</p>
                <a href="{{ route('user.payment.new') }}" class="btn">@lang('Go to Donation Page')</a>
            </div>
        </div>
        @endauth

        {{-- Profile Setting --}}
        <div class="card">
            <div class="card-icon">
                <i class="fas fa-user-cog"></i>
            </div>
            <div class="card-body">
                <h2>@lang('Profile Setting')</h2>
                <p>@lang('Update your personal information and preferences.')</p>

                @if (auth()->check())
                    <a href="{{ route('user.setting.profile') }}" class="btn">@lang('Go to Profile Settings')</a>
                @else
                    <a href="{{ route('user.donor.profile') }}" class="btn">@lang('Go to Profile Settings')</a>
                @endif
            </div>
        </div>

        @auth
        <div class="card">
            <div class="card-icon">
                <i class="fas fa-history"></i>
            </div>
            <div class="card-body">
                <h2>@lang('Donation History')</h2>
                <p>@lang('View your past donations and transaction records').</p>
                <a href="{{ route('user.payment.history') }}" class="btn">@lang('View Donation History')</a>
            </div>
        </div>
        @endauth
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<style>
    .dashboard-container {
        padding: 2rem;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        min-height: 100vh;
    }

    .dashboard-header {
        text-align: center;
        margin-bottom: 3rem;
    }

    .dashboard-header h1 {
        font-size: 2.5rem;
        color: #343a40;
        margin-bottom: 0.5rem;
    }

    .dashboard-header p {
        font-size: 1.1rem;
        color: #6c757d;
    }

    .dashboard-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2rem;
        margin: 0 auto;
    }

    .card {
        background-color: #ffffff;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        padding: 2rem;
        transition: transform 0.3s, box-shadow 0.3s;
    }

    .card:hover {
        transform: translateY(-10px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
    }

    .card-icon {
        font-size: 3rem;
        color: #007bff;
        margin-bottom: 1.5rem;
    }

    .card-body h2 {
        font-size: 1.5rem;
        color: #343a40;
        margin-bottom: 1rem;
    }

    .card-body p {
        color: #6c757d;
        line-height: 1.6;
        margin-bottom: 1.5rem;
    }

    .btn {
        display: inline-block;
        background-color: #007bff;
        padding: 0.75rem 1.5rem;
        border-radius: 5px;
        text-decoration: none;
        font-weight: bold;
        transition: background-color 0.3s;
    }

    .btn:active,
    .btn:focus {
        background-color: #0056b3 !important;
    }

    .btn:hover {
        background-color: #0056b3;
    }

    @media (max-width: 768px) {
        .dashboard-container {
            padding: 1rem;
        }

        .dashboard-header h1 {
            font-size: 2rem;
        }

        .dashboard-header p {
            font-size: 1rem;
        }
    }
</style>
@endpush