@extends('frontend.layouts.main')

@section('content')
    <section class="bg-light-gray py-100 py-5">
        <div class="container">
            <div class="text-center mb-5">
                <div class="d-inline-flex align-items-center justify-content-center bg-warning bg-opacity-10 text-warning rounded-circle mb-3"
                    style="width: 80px; height: 80px;">
                    <i class="fas fa-exclamation fa-2x"></i>
                </div>
                <h2 class="fw-bold mb-2" style="font-size: 2rem;">
                    @lang('Payment Cancelled')
                </h2>
                <p class="text-muted fs-5">
                    @lang("You cancelled the payment before it was completed. No amount has been deducted. You can restart your donation any time.")
                </p>
            </div>

            <div class="row justify-content-center">
                <div class="col-12 col-xl-7 col-lg-8">
                    <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                        <div class="card-header bg-warning text-white text-center py-3">
                            <h5 class="mb-0">@lang('Transaction Details')</h5>
                        </div>

                        <div class="card-body bg-white p-4">
                            <table class="table table-borderless mb-0">
                                <tbody>
                                    <tr>
                                        <td class="text-muted">@lang('Transaction No')</td>
                                        <td class="fw-semibold text-end">{{ $donation->payment->transaction_no ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">@lang('Date')</td>
                                        <td class="fw-semibold text-end">{{ System::getDateTime($donation->created_at) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">@lang('Amount')</td>
                                        <td class="fw-semibold text-warning text-end">{{ number_format($donation->amount, 2) }} ৳</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">@lang('Status')</td>
                                        <td class="text-end">{!! $donation->status_badge !!}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="card-footer bg-light text-center py-3">
                            <div class="d-flex flex-column flex-md-row justify-content-center gap-3">
                                <a href="{{ route('home') }}#donate" class="btn btn--base btn-lg px-4">
                                    <i class="fas fa-redo me-2"></i>@lang('Donate Again')
                                </a>
                                <a href="{{ route('home') }}" class="btn btn-outline-secondary px-4">
                                    <i class="fas fa-home me-2"></i>@lang('Go to Home')
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('styles')
    <style>
        .bg-light-gray {
            background-color: #f9fafb;
        }

        .card {
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.05);
        }

        .table td {
            padding: 0.75rem 0.5rem;
            vertical-align: middle;
        }

        @media (max-width: 576px) {
            .card-body table td {
                text-align: center !important;
            }
        }
    </style>
@endpush
