@extends('frontend.layouts.main')

@php
    $content = \App\Models\Setting::where('key', 'section_payment_pending_content')->first()?->value ?? null;
@endphp

@section('content')
    <section class="bg-light-gray py-100 py-5">
        <div class="container">
            <div class="text-center mb-5">
                <div class="d-inline-flex align-items-center justify-content-center bg-success bg-opacity-10 text-info rounded-circle mb-3"
                    style="width: 80px; height: 80px;">
                    <i class="fas fa-hourglass fa-2x"></i>
                </div>
                <h2 class="fw-bold mb-2" style="font-size: 2rem;">
                    {{ __($content['text']) }}
                </h2>
                <p class="text-muted fs-5">
                    {{ __($content['description']) }}
                </p>
            </div>

            {{-- <div class="row justify-content-center">
                <div class="col-12 col-xl-7 col-lg-8">
                    <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                        <div class="card-header bg-info text-white text-center py-3">
                            <h5 class="mb-0">@lang('Donation Receipt')</h5>
                        </div>

                        <div class="card-body bg-white p-4">
                            <table class="table table-borderless mb-0">
                                <tbody>
                                    <tr>
                                        <td class="text-muted">@lang('Transaction No')</td>
                                        <td class="fw-semibold text-end">{{ $donation->payment->transaction_no }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">@lang('Date')</td>
                                        <td class="fw-semibold text-end">{{ System::getDateTime($donation->created_at) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">@lang('Amount')</td>
                                        <td class="fw-semibold text-success text-end">{{ number_format($donation->amount, 2) }} ৳</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">@lang('Donation Category')</td>
                                        <td class="fw-semibold text-end">{{ $donation->category->name ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">@lang('Email')</td>
                                        <td class="fw-semibold text-end">{{ $donation->email ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">@lang('Phone Number')</td>
                                        <td class="fw-semibold text-end">{{ $donation->phone_number ?? 'N/A' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="card-footer bg-light text-center py-3">
                            <div class="d-flex flex-column flex-md-row justify-content-center gap-3">
                                <a href="{{ route('home') }}" class="btn btn-success btn-lg px-4">
                                    <i class="fas fa-home me-2"></i>@lang('Go to Home')
                                </a>
                                <a href="{{ route('download.receipt', encrypt($donation->id)) }}" class="btn btn--base px-4">
                                    <i class="fas fa-download me-2"></i>@lang('Download Receipt')
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div> --}}
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
