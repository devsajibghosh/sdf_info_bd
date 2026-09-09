@extends('user.layouts.main')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-12">

            <div class="text-center mb-5">
                <h1 class="display-5 fw-bold text-primary">@lang('Donate Now')</h1>
                <p class="lead text-muted">Your contribution makes a difference. Thank you for your support!</p>
            </div>

            <div class="card shadow-lg border-0 donation-card">
                <div class="card-body p-4 p-md-12">
                    <form action="{{ route('user.payment.insert') }}" method="POST">
                        @csrf

                        {{-- Step 1: Donation Details --}}
                        <div class="form-step">
                            <h4 class="step-title"><span>1</span> @lang('Donation Details')</h4>
                            <div class="row">
                                <div class="col-12 col-md-6 mb-3 mb-md-0">
                                    <label for="donation_category_id" class="form-label fw-semibold">@lang('Donation Category')</label>
                                    <select class="form-select form-select-lg" name="donation_category_id" id="donation_category_id">
                                        @foreach ($donationCategories as $donationCategory)
                                            <option @selected((request()->donation_category ?? -1) == $donationCategory->id) value="{{ $donationCategory->id }}">{{ $donationCategory->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label for="field_collection" class="form-label fw-semibold">@lang('Field Collection')</label>
                                    <select class="form-select form-select-lg" name="field_collection" id="field_collection">
                                        <option value="0" @selected(old('field_collection') == 0)>@lang('No')</option>
                                        <option value="1" @selected(old('field_collection') == 1)>@lang('Yes')</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Step 2: Payment Method --}}
                        <div class="form-step">
                            <h4 class="step-title"><span>2</span> @lang('Select Payment Gateway')</h4>
                            <div class="row g-3">
                                @foreach ($paymentGateways as $gateway)
                                    <div class="col-6 col-lg-4">
                                        <label class="payment-gateway-item w-100 h-100" for="{{ $gateway->name . '-' . $loop->index }}">
                                            <img class="img-fluid mb-2 gateway-logo" src="{{ $gateway->image_url }}" alt="{{ $gateway->name }}" />
                                            <div class="fw-bold gateway-name">{{ $gateway->name }}</div>
                                            <input type="radio" name="payment_gateway_id" id="{{ $gateway->name . '-' . $loop->index }}" value="{{ $gateway->id }}">
                                            <div class="selected-checkmark">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M10 15.172l9.192-9.193 1.414 1.414L10 18l-6.364-6.364 1.414-1.414z"/></svg>
                                            </div>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Step 3: Amount --}}
                        <div class="form-step">
                            <h4 class="step-title"><span>3</span> @lang('Choose an Amount')</h4>
                            
                            {{-- Quick Amount Buttons --}}
                            <div class="mb-3">
                                <label class="form-label fw-semibold d-block">@lang('Quick Select Amount')</label>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach ([100, 200, 300, 500, 1000, 2000] as $amount)
                                        <button type="button" class="btn btn--base quick-amount" data-amount="{{ $amount }}">
                                            ৳{{ number_format($amount) }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Manual Amount Input --}}
                            <label for="donation-amount" class="form-label fw-semibold">@lang('Or Enter a Custom Amount')</label>
                            <div class="input-group input-group-lg mb-4">
                                <span class="input-group-text">৳</span>
                                <input type="number" step="0.01" min="1" id="donation-amount" name="amount"
                                    class="form-control" value="{{ old('amount') }}" placeholder="@lang('e.g., 2500')" required>
                            </div>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary btn-lg fw-bold py-3">
                                @lang('Submit Donation')
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection



@push('styles')
<style>
    :root {
        --primary-color: #0d6efd;
        --primary-light: #e7f0ff;
        --border-color: #dee2e6;
        --text-dark: #212529;
        --text-muted: #6c757d;
        --card-bg: #ffffff;
        --body-bg: #f8f9fa;
    }

    body {
        background-color: var(--body-bg);
    }

    .donation-card {
        border-radius: 1rem;
        transition: all 0.3s ease-in-out;
    }
    
    .form-step {
        margin-bottom: 2.5rem;
    }
    .form-step:last-child {
        margin-bottom: 0;
    }

    .step-title {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1.5rem;
        color: var(--text-dark);
        font-weight: 600;
    }
    
    .step-title span {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background-color: var(--primary-light);
        color: var(--primary-color);
        font-weight: bold;
    }

    .payment-gateway-item {
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        border: 2px solid var(--border-color);
        padding: 1rem;
        border-radius: 0.75rem;
        transition: all 0.2s ease-in-out;
        background-color: var(--card-bg);
        cursor: pointer;
        min-height: 120px;
        overflow: hidden;
    }

    .payment-gateway-item:hover {
        border-color: var(--primary-color);
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }

    .payment-gateway-item input[type="radio"] {
        display: none;
    }

    .payment-gateway-item.selected {
        border-color: var(--primary-color);
        background-color: var(--primary-light);
        box-shadow: 0 4px 15px rgba(13, 110, 253, 0.2);
    }
    
    .gateway-logo {
        max-height: 150px;
        width: auto;
        object-fit: contain;
    }
    
    .gateway-name {
        font-size: 0.85rem;
        color: var(--text-muted);
    }
    
    .selected-checkmark {
        position: absolute;
        top: 8px;
        right: 8px;
        width: 24px;
        height: 24px;
        background-color: var(--primary-color);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transform: scale(0.5);
        transition: all 0.2s ease-in-out;
    }
    
    .selected-checkmark svg {
        width: 16px;
        height: 16px;
    }
    
    .payment-gateway-item.selected .selected-checkmark {
        opacity: 1;
        transform: scale(1);
    }

    .quick-amount {
        padding: 0.6rem 1.25rem;
        font-weight: 600;
        border-radius: 50px;
        font-size: 0.95rem;
        transition: all 0.2s ease;
        border-width: 2px;
    }
    
    .quick-amount.active, .quick-amount:hover {
        background-color: var(--primary-color);
        color: white !important;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
</style>
@endpush




@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const amountInput = document.getElementById('donation-amount');
        const quickAmountButtons = document.querySelectorAll('.quick-amount');
        const paymentGatewayRadios = document.querySelectorAll('input[name="payment_gateway_id"]');

        // Quick Amount Button Logic
        quickAmountButtons.forEach(button => {
            button.addEventListener('click', () => {
                const amount = button.dataset.amount;
                
                // Set input value and focus
                if (amountInput) {
                    amountInput.value = amount;
                    amountInput.focus();
                }

                // Update active state for buttons
                quickAmountButtons.forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');
            });
        });
        
        // Clear active quick amount button if user types manually
        if (amountInput) {
            amountInput.addEventListener('input', () => {
                quickAmountButtons.forEach(btn => btn.classList.remove('active'));
            });
        }


        // Payment Gateway Selection Logic
        paymentGatewayRadios.forEach(radio => {
            radio.addEventListener('change', () => {
                // Remove 'selected' from all gateway items
                document.querySelectorAll('.payment-gateway-item').forEach(item => {
                    item.classList.remove('selected');
                });

                // Add 'selected' to the parent of the checked radio
                const selectedLabel = radio.closest('.payment-gateway-item');
                if (selectedLabel) {
                    selectedLabel.classList.add('selected');
                }
            });
        });

        // On page load, check if a gateway is already selected and style it
        const initiallyChecked = document.querySelector('input[name="payment_gateway_id"]:checked');
        if (initiallyChecked) {
            initiallyChecked.closest('.payment-gateway-item').classList.add('selected');
        }
    });
</script>
@endpush