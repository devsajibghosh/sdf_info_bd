@extends('admin.layouts.app')


@section('content')
<div class="content-wrapper bg-light">
    <!-- Header Section -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row align-items-center mb-4">
                <div class="col-sm-6">
                    <h1 class="m-0 font-weight-bold text-dark"><i class="fas fa-chart-line mr-2 text-primary"></i>Donor Analytics</h1>
                    <p class="text-muted small mb-0">Monitor donation frequency and lifetime value of each donor.</p>
                </div>
                <div class="col-sm-6 text-sm-right mt-3 mt-sm-0">
                    <a href="{{ route('donor.live.report.download') }}" class="btn btn-success px-4 shadow-sm btn-responsive">
                        <i class="fas fa-file-csv mr-2"></i>Export CSV
                    </a>
                </div>
            </div>

            <!-- Stats Overview (Optional but recommended for Production) -->
            <div class="row">
                <div class="col-md-3 col-6">
                    <div class="card report-card shadow-sm border-left border-primary">
                        <div class="card-body p-3 text-center">
                            <span class="text-muted small">Active Donors</span>
                            <h4 class="font-weight-bold mb-0">{{ $donorsData->total() }}</h4>
                        </div>
                    </div>
                </div>
                <!-- Add more KPI cards here if needed -->
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <section class="content">
        <div class="container-fluid">
            
            <!-- Search & Filter Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <form action="{{ url()->current() }}" method="GET">
                        <div class="row align-items-end">
                            <div class="col-lg-8 col-md-7">
                                <label class="small font-weight-bold text-muted">Search Donor</label>
                                <div class="input-group">
                                    <input type="text" name="phone_number" class="form-control border-left-0" 
                                           placeholder="Enter phone number..." value="{{ request('phone_number') }}">
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-5 mt-3 mt-md-0">
                                <button type="submit" class="btn btn-primary px-4 btn-responsive font-weight-bold">
                                    <i class="fas fa-filter mr-1"></i> Filter
                                </button>
                                <a href="{{ url()->current() }}" class="btn btn-light px-4 ml-md-2 btn-responsive font-weight-bold">
                                    <i class="fas fa-sync-alt mr-1"></i> Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Table Card -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h3 class="card-title font-weight-bold text-dark">Donor List</h3>
                    <div class="card-tools px-2">
                        <span class="badge badge-primary text-primary p-2 border">Showing {{ $donorsData->firstItem() ?? 0 }}-{{ $donorsData->lastItem() ?? 0 }} of {{ $donorsData->total() }}</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="px-4">Donor Phone</th>
                                    <th class="text-center">Frequency</th>
                                    <th class="text-right">Total Donations</th>
                                    <th class="text-right">Last Donation</th>
                                    <th class="text-center">Last Donation Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($donorsData as $data)
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm mr-3 bg-soft-primary text-primary text-center rounded-circle" style="width: 35px; height:35px; line-height:35px; background: #e7f1ff;">
                                                <i class="fas fa-user small"></i>
                                            </div>
                                            <div>
                                                <span class="d-block font-weight-bold donor-phone text-dark">{{ $data->phone_number }}</span>
                                                {{-- <small class="text-muted">Donor ID: #{{ $data->id }}</small> --}}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-pill badge-light border text-primary px-3 py-2 font-weight-bold">
                                            {{ $data->total_count }} Times
                                        </span>
                                    </td>
                                    <td class="text-right font-weight-bold text-dark">
                                        ৳{{ number_format($data->total_amount, 0) }}
                                    </td>
                                    <td class="text-right">
                                        <span class="text-success font-weight-bold">৳{{ number_format($data->last_amount ?? 0, 0) }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if($data->last_date)
                                            <div class="d-flex flex-column">
                                                <span class="text-dark small font-weight-bold">{{ \Carbon\Carbon::parse($data->last_date)->format('d M, Y') }}</span>
                                                <span class="text-muted" style="font-size: 10px;">{{ \Carbon\Carbon::parse($data->last_date)->diffForHumans() }}</span>
                                            </div>
                                        @else
                                            <span class="text-muted italic small">No history</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 bg-white">
                                        <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" width="80" class="mb-3 opacity-50" alt="No Data">
                                        <h5 class="text-muted">No donor records found</h5>
                                        <p class="small text-muted">Try adjusting your search criteria.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Pagination Footer -->
                @if($donorsData->hasPages())
                <div class="card-footer bg-white d-flex justify-content-between align-items-center">
                    <!--<div class="small text-muted d-none d-md-block">-->
                    <!--    Showing data from {{ $donorsData->firstItem() }} to {{ $donorsData->lastItem() }}-->
                    <!--</div>-->
                    <div>
                        {{ $donorsData->links() }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </section>
</div>
@endsection


@section('styles')
<style>
    /* Custom refinements */
    .report-card { transition: transform 0.2s; border: none; }
    .report-card:hover { transform: translateY(-5px); }
    .table thead th { 
        position: sticky; 
        top: 0; 
        background: #f8f9fa; 
        z-index: 10; 
        border-bottom: 2px solid #dee2e6;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
    }
    .donor-phone { font-family: 'Courier New', Courier, monospace; letter-spacing: 0.5px; }
    @media (max-width: 768px) {
        .btn-responsive { width: 100%; margin-bottom: 10px; }
        .card-header .btn { float: none !important; display: block; margin-top: 10px; }
    }
</style>
@endsection