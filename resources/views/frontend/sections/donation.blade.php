<div class="donation-section py-10" style="background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);">
    <div class="container">
        @php
            $donationCategories = \App\Models\DonationCategory::active()->get();
            $paymentGateways = \App\Models\PaymentGateway::active()->hidden()->get();
        @endphp

        <div class="row justify-content-center">
            <div class="col-12 col-xl-10">
                <div class="donation-form-wrapper hero-enter">
                    <div class="text-center mb-4">
                        <h3 class="fw-bold text-primary ff-heading">@lang('Contribution')</h3>

                    </div>

                    <form
                        id="donationEntryForm"
                        method="POST"
                        action="{{ route('guest.donate') }}"
                        class="modern-donation-form"
                        novalidate
                    >
                        @csrf
                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <div class="input-group-custom">
                                    <label for="home_donation_category_id"><i class="fas fa-hand-holding-heart"></i> @lang('Select Fund')</label>
                                    <select
                                        id="home_donation_category_id"
                                        name="donation_category_id"
                                        class="form-select custom-input"
                                        required
                                        aria-describedby="homeCategoryError"
                                    >
                                        <option value="">@lang('Choose...')</option>
                                        @foreach ($donationCategories as $donationCategory)
                                            <option value="{{ $donationCategory->id }}" @selected(old('donation_category_id') == $donationCategory->id)>
                                                {{ $donationCategory->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('donation_category_id', 'donation')
                                        <div class="text-danger small mt-1" role="alert">{{ $message }}</div>
                                    @enderror
                                    <div class="text-danger small mt-1 d-none" id="homeCategoryError" role="alert"></div>
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <div class="input-group-custom">
                                    <label for="home_contact"><i class="fas fa-phone-alt"></i> @lang('Contact Number')</label>
                                    <input
                                        type="tel"
                                        id="home_contact"
                                        name="contact"
                                        inputmode="numeric"
                                        autocomplete="tel"
                                        maxlength="11"
                                        pattern="01[0-9]{9}"
                                        placeholder="01XXXXXXXXX"
                                        value="{{ old('contact') }}"
                                        class="form-control custom-input"
                                        required
                                        aria-describedby="homeContactError"
                                    >
                                    @error('contact', 'donation')
                                        <div class="text-danger small mt-1" role="alert">{{ $message }}</div>
                                    @enderror
                                    <div class="text-danger small mt-1 d-none" id="homeContactError" role="alert"></div>
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <div class="input-group-custom">
                                    <label for="home_amount"><i class="fas fa-money-bill-wave"></i> @lang('Amount (Min 20tk)')</label>
                                    <input
                                        type="number"
                                        id="home_amount"
                                        name="amount"
                                        inputmode="numeric"
                                        min="20"
                                        step="1"
                                        placeholder="{{ __('Enter amount') }}"
                                        value="{{ old('amount') }}"
                                        class="form-control custom-input"
                                        required
                                        aria-describedby="homeAmountError"
                                    >
                                    @error('amount', 'donation')
                                        <div class="text-danger small mt-1" role="alert">{{ $message }}</div>
                                    @enderror
                                    <div class="text-danger small mt-1 d-none" id="homeAmountError" role="alert"></div>
                                </div>
                            </div>
                        </div>

                        {{-- Only ever rendered if more than one automatic gateway is active; today
                             SSLCommerz is the sole automatic gateway, so donations go straight
                             through without an extra "choose a gateway" step. --}}
                        @if ($paymentGateways->count() > 1)
                            <div class="mt-3">
                                <label class="d-block fw-semibold small mb-2 text-muted">@lang('Payment Method')</label>
                                <div class="row g-2">
                                    @foreach ($paymentGateways as $gateway)
                                        <div class="col-6 col-md-3">
                                            <input type="radio" name="payment_gateway_id" id="home-gw-{{ $gateway->id }}" value="{{ $gateway->id }}" class="btn-check" @checked($loop->first) required>
                                            <label class="gateway-card" for="home-gw-{{ $gateway->id }}">
                                                <img src="{{ $gateway->image_url }}" alt="{{ $gateway->name }}">
                                                <span>{{ $gateway->name }}</span>
                                                <div class="check-mark"><i class="fas fa-check-circle"></i></div>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                @error('payment_gateway_id', 'donation')
                                    <div class="text-danger small mt-1" role="alert">{{ $message }}</div>
                                @enderror
                            </div>
                        @elseif ($paymentGateways->count() === 1)
                            <input type="hidden" name="payment_gateway_id" value="{{ $paymentGateways->first()->id }}">
                            @error('payment_gateway_id', 'donation')
                                <div class="text-danger small mt-1" role="alert">{{ $message }}</div>
                            @enderror
                        @else
                            <div class="alert alert-danger mt-3 mb-0" role="alert">
                                {{ __('Unable to start the payment. Please try again in a moment.') }}
                            </div>
                        @endif

                        {{-- Mandatory policy acknowledgement (SSLCommerz compliance requirement) --}}
                        <div class="form-check mt-3">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                id="home_agree_terms"
                                name="agree_terms"
                                value="1"
                                required
                                aria-describedby="homeAgreeTermsError"
                            >
                            <label class="form-check-label small" for="home_agree_terms">
                                আমি
                                <a href="{{ route('site.page', 'terms-and-conditions') }}" target="_blank" rel="noopener" class="fw-semibold text-decoration-underline">Terms &amp; Conditions</a>,
                                <a href="{{ route('site.page', 'privacy-policy') }}" target="_blank" rel="noopener" class="fw-semibold text-decoration-underline">Privacy Policy</a> এবং
                                <a href="{{ route('site.page', 'return-and-refund-policy') }}" target="_blank" rel="noopener" class="fw-semibold text-decoration-underline">Refund &amp; Return Policy</a>
                                মেনে নিচ্ছি।
                            </label>
                            @error('agree_terms', 'donation')
                                <div class="text-danger small mt-1" role="alert">{{ $message }}</div>
                            @enderror
                            <div class="text-danger small mt-1 d-none" id="homeAgreeTermsError" role="alert"></div>
                        </div>

                        <div class="mt-3">
                            <button type="submit" id="homeDonateSubmitBtn" class="btn-donate-now donateBtn" @disabled($paymentGateways->isEmpty())>
                                <span>@lang('Donate Now')</span>
                                <div class="btn-glow"></div>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .py-80 { padding: 80px 0; }

    .donation-form-wrapper {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        padding: 40px;
        border-radius: 20px;
        box-shadow: 0 15px 35px rgba(0,0,0,0.1);
    }

    .input-group-custom label {
        display: block;
        font-weight: 600;
        font-size: 13px;
        margin-bottom: 8px;
        color: #444;
    }

    .custom-input {
        border: 2px solid #eee !important;
        border-radius: 12px !important;
        padding: 12px 15px !important;
        transition: 0.3s !important;
        height: 50px !important;
    }

    .custom-input:focus {
        border-color: #00A78E !important;
        box-shadow: 0 0 0 4px rgba(0, 167, 142, 0.1) !important;
    }

    /* Professional Donate Button */
    .btn-donate-now {
        width: 100%;
        height: 50px;
        border: none;
        background: #111;
        color: #fff;
        border-radius: 12px;
        font-weight: 700;
        position: relative;
        overflow: hidden;
        transition: 0.3s;
        z-index: 1;
    }

    .btn-donate-now:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.2);
    }

    .btn-donate-now:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    .btn-donate-now::before {
        content: '';
        position: absolute;
        top: 0; left: -100%;
        width: 100%; height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: 0.5s;
    }

    .btn-donate-now:hover::before {
        left: 100%;
    }

    .gateway-card {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 20px;
        border: 2px solid #f1f1f1;
        border-radius: 15px;
        cursor: pointer;
        transition: all 0.3s;
        position: relative;
    }

    .gateway-card img {
        height: 40px;
        margin-bottom: 10px;
        object-fit: contain;
    }

    .gateway-card span {
        font-size: 14px;
        font-weight: 600;
        color: #555;
    }

    .btn-check:checked + .gateway-card {
        border-color: #00A78E;
        background: rgba(0, 167, 142, 0.05);
    }

    .check-mark {
        position: absolute;
        top: 5px; right: 5px;
        color: #00A78E;
        opacity: 0;
        transition: 0.3s;
    }

    .btn-check:checked + .gateway-card .check-mark {
        opacity: 1;
    }

    /* Mandatory terms checkbox: comfortably tappable on mobile, links wrap cleanly */
    #home_agree_terms {
        width: 1.15em;
        height: 1.15em;
        margin-top: 0.2em;
        cursor: pointer;
    }

    .form-check-label[for="home_agree_terms"] {
        cursor: pointer;
        line-height: 1.5;
    }

    /*font-fmaily*/

    .ff-heading {
    font-family: 'Poppins', sans-serif; /* আপনার পছন্দের ফন্ট এখানে দিন */
}

</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('donationEntryForm');
    if (!form) return;

    const categorySelect = document.getElementById('home_donation_category_id');
    const contactInput = document.getElementById('home_contact');
    const amountInput = document.getElementById('home_amount');
    const agreeTermsInput = document.getElementById('home_agree_terms');
    const submitBtn = document.getElementById('homeDonateSubmitBtn');

    const categoryError = document.getElementById('homeCategoryError');
    const contactError = document.getElementById('homeContactError');
    const amountError = document.getElementById('homeAmountError');
    const agreeTermsError = document.getElementById('homeAgreeTermsError');

    const PHONE_REGEX = /^01[0-9]{9}$/;
    const MSG_REQUIRED = @json(__('This field is required.'));
    const MSG_PHONE = @json(__('Please enter a valid 11-digit mobile number.'));
    const MSG_AMOUNT = @json(__('Please donate at least 20 tk'));
    const MSG_AGREE_TERMS = @json(__('Please accept the Terms & Conditions, Privacy Policy, and Refund & Return Policy before proceeding with payment.'));
    const MSG_PREPARING = @json(__('Preparing payment...'));

    const submitBtnLabel = submitBtn ? submitBtn.querySelector('span') : null;
    const submitBtnDefaultText = submitBtnLabel ? submitBtnLabel.textContent.trim() : '';

    if (contactInput) {
        contactInput.addEventListener('input', function () {
            contactInput.value = contactInput.value.replace(/[^0-9]/g, '').slice(0, 11);
        });
    }

    function showError(el, msg) {
        if (!el) return;
        el.textContent = msg;
        el.classList.remove('d-none');
    }

    function hideError(el) {
        if (!el) return;
        el.classList.add('d-none');
    }

    function validate() {
        let valid = true;

        if (!categorySelect.value) {
            showError(categoryError, MSG_REQUIRED);
            valid = false;
        } else {
            hideError(categoryError);
        }

        const contact = contactInput.value.trim();
        if (!PHONE_REGEX.test(contact)) {
            showError(contactError, MSG_PHONE);
            valid = false;
        } else {
            hideError(contactError);
        }

        const amount = parseFloat(amountInput.value);
        if (isNaN(amount) || amount < 20) {
            showError(amountError, MSG_AMOUNT);
            valid = false;
        } else {
            hideError(amountError);
        }

        if (agreeTermsInput && !agreeTermsInput.checked) {
            showError(agreeTermsError, MSG_AGREE_TERMS);
            valid = false;
        } else {
            hideError(agreeTermsError);
        }

        return valid;
    }

    form.addEventListener('submit', function (e) {
        // Guards against a double-click firing two payment initializations:
        // once disabled, any further submit attempt is ignored outright.
        if (submitBtn && submitBtn.disabled) {
            e.preventDefault();
            return;
        }

        if (!validate()) {
            e.preventDefault();
            return;
        }

        // Valid: let the native form submission proceed (full page POST to
        // guest.donate, which redirects to the SSLCommerz gateway). We only
        // disable the button afterwards to block a duplicate double-click;
        // this does not cancel the in-flight submission.
        if (submitBtn) {
            submitBtn.disabled = true;
            if (submitBtnLabel) submitBtnLabel.textContent = MSG_PREPARING;
        }
    });

    // Restore the button if the page is restored from the back/forward cache
    // (e.g. user hits "back" from the gateway) so the form isn't stuck disabled.
    window.addEventListener('pageshow', function (event) {
        if (event.persisted && submitBtn) {
            submitBtn.disabled = false;
            if (submitBtnLabel) submitBtnLabel.textContent = submitBtnDefaultText;
        }
    });
});
</script>
@endpush
