@extends('user.layouts.main')

@section('content')
<div class="dashboard-container">

    {{-- Welcome Header --}}
    <div class="dashboard-header">
        <h1>@lang('Welcome to Your Dashboard')</h1>
        <p>@lang('Here is a summary of your donation activity. Thank you for your support!')</p>
    </div>

    {{-- Stats Section --}}
    <h2 class="section-title">@lang('Your Donations')</h2>
    <div class="row mb-4 gy-4">
        {{-- Total Donation Count --}}
        <div class="col-12 col-md-6 col-lg-4">
            <div class="stat-card h-100">
                <div class="stat-card-icon icon-primary">
                    <i class="fas fa-heart"></i>
                </div>
                <div class="stat-card-body">
                    <p>@lang('Total Donation Count')</p>
                    <h3>{{ $widget['total_donate_count'] }} @lang('times')</h3>
                </div>
            </div>
        </div>
        {{-- Total Donation Amount --}}
        <div class="col-12 col-md-6 col-lg-4">
            <div class="stat-card h-100">
                <div class="stat-card-icon icon-success">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-card-body">
                    <p>@lang('Total Donation Amount')</p>
                    <h3>{{ System::amountWithCurrency($widget['total_donate_amount']) }}</h3>
                </div>
            </div>
        </div>
        {{-- Last Donation Amount --}}
        <div class="col-12 col-lg-4">
            <div class="stat-card h-100">
                <div class="stat-card-icon icon-info">
                    <i class="fas fa-gift"></i>
                </div>
                <div class="stat-card-body">
                    <p>@lang('Last Donation Amount')</p>
                    <h3>{{ System::amountWithCurrency($widget['last_donate_amount']) }}</h3>
                    <small class="text-muted">{{ $widget['last_donate_date'] ? System::getDateTime($widget['last_donate_date']) : 'N/A' }}</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Collections Section --}}
    <h2 class="section-title">@lang('Your Collections')</h2>
    <div class="row mb-5 gy-4">
        {{-- Total Collected Times --}}
        <div class="col-12 col-md-6 col-lg-4">
            <div class="stat-card h-100">
                <div class="stat-card-icon icon-warning">
                    <i class="fas fa-box-open"></i>
                </div>
                <div class="stat-card-body">
                    <p>@lang('Total Collected Times')</p>
                    <h3>{{ $widget['total_collection_count'] }} @lang('times')</h3>
                </div>
            </div>
        </div>
        {{-- Total Collection Amount --}}
        <div class="col-12 col-md-6 col-lg-4">
            <div class="stat-card h-100">
                 <div class="stat-card-icon icon-danger">
                    <i class="fas fa-coins"></i>
                </div>
                <div class="stat-card-body">
                    <p>@lang('Total Collection Amount')</p>
                    <h3>{{ System::amountWithCurrency($widget['total_collection_donate_amount']) }}</h3>
                </div>
            </div>
        </div>
        {{-- Last Collection Amount --}}
        <div class="col-12 col-lg-4">
            <div class="stat-card h-100">
                <div class="stat-card-icon icon-secondary">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="stat-card-body">
                    <p>@lang('Last Collection Amount')</p>
                    <h3>{{ System::amountWithCurrency($widget['last_collected_amount']) }}</h3>
                    <small class="text-muted">{{ $widget['last_collected_date'] ? System::getDateTime($widget['last_collected_date']) : 'N/A' }}</small>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Action Cards --}}
    <div class="action-cards">
        @auth
        {{-- Donate Now --}}
        <div class="action-card">
            <div class="action-card-icon">
                <i class="fas fa-hand-holding-heart"></i>
            </div>
            <div class="action-card-body">
                <h2>@lang('Donate Now')</h2>
                <p>@lang('Make a new contribution and support our cause.')</p>
                <a href="{{ route('user.payment.new') }}" class="btn btn-primary">@lang('Go to Donation Page')</a>
            </div>
        </div>
        @endauth

        {{-- Profile Setting --}}
        <div class="action-card">
            <div class="action-card-icon">
                <i class="fas fa-user-cog"></i>
            </div>
            <div class="action-card-body">
                <h2>@lang('Profile Setting')</h2>
                <p>@lang('Update your personal information and preferences.')</p>
                @if (auth()->check())
                    <a href="{{ route('user.setting.profile') }}" class="btn btn-primary">@lang('Go to Profile Settings')</a>
                @else
                    <a href="{{ route('user.donor.profile') }}" class="btn btn-primary">@lang('Go to Profile Settings')</a>
                @endif
            </div>
        </div>

        @auth
        {{-- Donation History --}}
        <div class="action-card">
            <div class="action-card-icon">
                <i class="fas fa-history"></i>
            </div>
            <div class="action-card-body">
                <h2>@lang('Donation History')</h2>
                <p>@lang('View your past donations and transaction records.')</p>
                <a href="{{ route('user.payment.history') }}" class="btn btn-primary">@lang('View Donation History')</a>
            </div>
        </div>
        @endauth
    </div>
</div>
@endsection

@push('styles')
{{-- Make sure you have Bootstrap and Font Awesome loaded in your main layout --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<style>
    :root {
        --primary-color: #007bff;
        --secondary-color: #6c757d;
        --success-color: #28a745;
        --danger-color: #dc3545;
        --warning-color: #ffc107;
        --info-color: #17a2b8;
        --light-color: #f8f9fa;
        --dark-color: #343a40;
        --background-color: #f4f7f6;
        --card-background-color: #ffffff;
        --text-color: #495057;
        --heading-color: #343a40;
        --border-radius: 12px;
        --box-shadow: 0 6px 12px rgba(0, 0, 0, 0.08);
        --transition-speed: 0.3s;
    }

    body {
        background-color: var(--background-color);
    }

    .dashboard-container {
        padding: 2rem;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        max-width: 1200px;
        margin: 0 auto;
    }

    .dashboard-header {
        text-align: left;
        margin-bottom: 2.5rem;
    }

    .dashboard-header h1 {
        font-size: 2.25rem;
        font-weight: 700;
        color: var(--heading-color);
        margin-bottom: 0.5rem;
    }

    .dashboard-header p {
        font-size: 1.1rem;
        color: var(--text-color);
    }
    
    .section-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--heading-color);
        margin-bottom: 1.5rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #e9ecef;
    }

    /* Stat Cards */
    .stat-card {
        background-color: var(--card-background-color);
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1.5rem;
        transition: transform var(--transition-speed), box-shadow var(--transition-speed);
        border: 1px solid transparent;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.12);
    }

    .stat-card-icon {
        font-size: 1.75rem;
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
    }
    
    .icon-primary { background-color: var(--primary-color); }
    .icon-success { background-color: var(--success-color); }
    .icon-info { background-color: var(--info-color); }
    .icon-warning { background-color: var(--warning-color); }
    .icon-danger { background-color: var(--danger-color); }
    .icon-secondary { background-color: var(--secondary-color); }

    .stat-card-body {
        line-height: 1.4;
    }

    .stat-card-body p {
        margin: 0;
        color: var(--text-color);
        font-size: 1rem;
    }

    .stat-card-body h3 {
        margin: 0;
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--heading-color);
    }
    
    .stat-card-body small {
        font-size: 0.875rem;
    }

    /* Action Cards */
    .action-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2rem;
    }

    .action-card {
        background-color: var(--card-background-color);
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        padding: 2.5rem;
        transition: transform var(--transition-speed), box-shadow var(--transition-speed);
    }

    .action-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
    }

    .action-card-icon {
        font-size: 3rem;
        color: var(--primary-color);
        margin-bottom: 1.5rem;
    }

    .action-card-body h2 {
        font-size: 1.5rem;
        color: var(--heading-color);
        margin-bottom: 1rem;
        font-weight: 600;
    }

    .action-card-body p {
        color: var(--text-color);
        line-height: 1.6;
        margin-bottom: 2rem;
        min-height: 50px; /* To align buttons */
    }

    .btn {
        display: inline-block;
        padding: 0.8rem 1.75rem;
        border-radius: 50px; /* Pill-shaped buttons */
        text-decoration: none;
        font-weight: 600;
        font-size: 1rem;
        transition: background-color var(--transition-speed), transform var(--transition-speed), box-shadow var(--transition-speed);
        border: none;
        cursor: pointer;
    }
    
    .btn-primary {
        background-color: var(--primary-color);
        color: #fff;
    }

    .btn-primary:hover {
        background-color: #0056b3;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 123, 255, 0.4);
    }

    .btn-secondary {
        background-color: #e9ecef;
        color: var(--dark-color);
    }

    .btn-secondary:hover {
        background-color: #d3d9df;
        transform: translateY(-2px);
    }


    @media (max-width: 768px) {
        .dashboard-container {
            padding: 1rem;
        }

        .dashboard-header h1 {
            font-size: 1.75rem;
        }

        .dashboard-header p {
            font-size: 1rem;
        }

        .stat-card {
            flex-direction: column;
            text-align: center;
            gap: 1rem;
        }
    }
</style>
@endpush