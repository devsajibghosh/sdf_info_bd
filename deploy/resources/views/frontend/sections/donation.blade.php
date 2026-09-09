<div class="donation-form py-60">
    <div class="container">
        @php
            $donationCategories = \App\Models\DonationCategory::active()->get();
            $paymentGateways = \App\Models\PaymentGateway::active()->where('key', '!=', 'cash')->get();
        @endphp

        <form id="donationEntryForm" class="donation-form__card">
            @csrf
            <div class="row gy-3 align-items-end">
                <div class="col-lg-3 col-sm-6">
                    <select name="donation_category_id" class="select form--control" required>
                        <option value="">@lang('Select Fund')</option>
                        @foreach ($donationCategories as $donationCategory)
                            <option value="{{ $donationCategory->id }}">{{ $donationCategory->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-3 col-sm-6">
                    <input type="text" name="contact" placeholder="@lang('Please enter your phone number without +88')" class="form-control form--control" required>
                </div>

                <div class="col-lg-3 col-sm-6">
                    <input type="number" name="donation_amount" placeholder="@lang('Donation Amount')" class="form-control form--control" required min="1">
                </div>

                <div class="col-lg-3 col-sm-6">
                    <button type="button" class="btn btn--base w-100 h-100" data-bs-toggle="modal" data-bs-target="#gatewayModal">
                        @lang('Donate')
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Gateway Selection Modal -->
<div class="modal fade" id="gatewayModal" tabindex="-1" aria-labelledby="gatewayModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form id="gatewaySelectionForm" action="{{ route('guest.donate') }}" method="POST" class="modal-content">
            @csrf

            <div class="modal-header">
                <h5 class="modal-title" id="gatewayModalLabel">@lang('Select Payment Gateway')</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="@lang('Close')"></button>
            </div>

            <div class="modal-body">
                <!-- Hidden fields to carry over user inputs -->
                <input type="hidden" name="donation_category_id">
                <input type="hidden" name="contact">
                <input type="hidden" name="amount">

                <div class="row g-3">
                    @foreach ($paymentGateways as $gateway)
                        <div class="col-md-4">
                            <label class="payment-gateway-item w-100 h-100 text-center p-3" for="gw-{{ $gateway->id }}">
                                <img class="img-fluid mb-2" src="{{ $gateway->image_url }}" alt="{{ $gateway->name }}">
                                <div class="fw-bold">{{ $gateway->name }}</div>
                                <input type="radio" name="payment_gateway_id" id="gw-{{ $gateway->id }}" value="{{ $gateway->id }}" required>
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-primary w-100">@lang('Confirm & Proceed')</button>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
    .payment-gateway-item {
        border: 2px solid transparent;
        border-radius: 0.5rem;
        background-color: #f9f9f9;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .payment-gateway-item:hover {
        border-color: #0d6efd;
    }

    .payment-gateway-item input[type="radio"] {
        display: none;
    }

    .payment-gateway-item.selected {
        border-color: #0d6efd;
        background-color: #eaf2ff;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const donationForm = document.getElementById('donationEntryForm');
        const gatewayForm = document.getElementById('gatewaySelectionForm');

        const radioButtons = document.querySelectorAll('input[name="payment_gateway_id"]');

        // Style selected gateway
        radioButtons.forEach(radio => {
            radio.addEventListener('change', () => {
                document.querySelectorAll('.payment-gateway-item').forEach(item => item.classList.remove('selected'));
                const parentLabel = radio.closest('.payment-gateway-item');
                if (parentLabel) parentLabel.classList.add('selected');
            });
        });

        // When modal is shown, sync the inputs
        const modal = document.getElementById('gatewayModal');
        modal.addEventListener('show.bs.modal', () => {
            const formData = new FormData(donationForm);
            gatewayForm.querySelector('input[name="donation_category_id"]').value = formData.get('donation_category_id');
            gatewayForm.querySelector('input[name="contact"]').value = formData.get('contact');
            gatewayForm.querySelector('input[name="amount"]').value = formData.get('donation_amount');
        });
    });
</script>
@endpush
