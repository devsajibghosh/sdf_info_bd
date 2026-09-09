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

                    <form id="donationEntryForm" class="modern-donation-form">
                        @csrf
                        <div class="row g-3">
                            <div class="col-12 col-md-6 col-lg-3">
                                <div class="input-group-custom">
                                    <label><i class="fas fa-hand-holding-heart"></i> @lang('Select Fund')</label>
                                    <select name="donation_category_id" class="form-select custom-input" required>
                                        <option value="">@lang('Choose...')</option>
                                        @foreach ($donationCategories as $donationCategory)
                                            <option value="{{ $donationCategory->id }}">{{ $donationCategory->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-12 col-md-6 col-lg-3">
                                <div class="input-group-custom">
                                    <label><i class="fas fa-phone-alt"></i> @lang('Contact Number')</label>
                                    <input type="tel" name="contact" inputmode="numeric" autocomplete="tel" maxlength="11" pattern="01[0-9]{9}" placeholder="01XXXXXXXXX" class="form-control custom-input" required>
                                </div>
                            </div>

                            <div class="col-12 col-md-6 col-lg-3">
                                <div class="input-group-custom">
                                    <label><i class="fas fa-money-bill-wave"></i> @lang('Amount (Min 20tk)')</label>
                                    <input type="number" name="donation_amount" placeholder="500" class="form-control custom-input" required min="1">
                                </div>
                            </div>

                            <div class="col-12 col-md-6 col-lg-3 d-flex align-items-end">
                                <button type="button" class="btn-donate-now donateBtn">
                                    <span>@lang('Donate Now')</span>
                                    <div class="btn-glow"></div>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="gatewayModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <form id="gatewaySelectionForm" action="{{ route('guest.donate') }}" method="POST" class="modal-content custom-modal">
            @csrf
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">@lang('Payment Method')</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <p class="text-muted small mb-4">@lang('Please select your preferred gateway to complete the donation.')</p>
                
                <input type="hidden" name="donation_category_id">
                <input type="hidden" name="contact">
                <input type="hidden" name="amount">

                <div class="row g-3">
                    @foreach ($paymentGateways as $gateway)
                        <div class="col-6">
                            <input type="radio" name="payment_gateway_id" id="gw-{{ $gateway->id }}" value="{{ $gateway->id }}" class="btn-check" required>
                            <label class="gateway-card" for="gw-{{ $gateway->id }}">
                                <img src="{{ $gateway->image_url }}" alt="{{ $gateway->name }}">
                                <span>{{ $gateway->name }}</span>
                                <div class="check-mark"><i class="fas fa-check-circle"></i></div>
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="modal-footer border-0 p-3">
                <button type="submit" class="btn-confirm-proceed">
                    @lang('Confirm & Proceed') <i class="fas fa-chevron-right ms-2"></i>
                </button>
            </div>
        </form>
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

    /* Modal Styling */
    .custom-modal {
        border-radius: 20px;
        border: none;
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

    .btn-confirm-proceed {
        width: 100%;
        padding: 14px;
        background: #00A78E;
        color: white;
        border: none;
        border-radius: 12px;
        font-weight: 700;
        transition: 0.3s;
    }

    .btn-confirm-proceed:hover {
        background: #008f7a;
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
        const donationForm = document.getElementById('donationEntryForm');
        const gatewayForm = document.getElementById('gatewaySelectionForm');
        const modalEl = document.getElementById('gatewayModal');
        const PHONE_REGEX = /^01[0-9]{9}$/;

        const contactInput = donationForm.querySelector('input[name="contact"]');
        if (contactInput) {
            contactInput.addEventListener('input', function () {
                contactInput.value = contactInput.value.replace(/[^0-9]/g, '').slice(0, 11);
            });
        }

        $('.donateBtn').on('click', function (event) {
            event.preventDefault();

            const categorySelect = donationForm.querySelector('select[name="donation_category_id"]');
            if (!categorySelect.value) {
                $.jGrowl("@lang('This field is required.')", {
                    theme: 'jgrowl-error',
                    life: 4000,
                    position: 'top-right'
                });
                return;
            }

            const contact = (contactInput ? contactInput.value : '').trim();
            if (!PHONE_REGEX.test(contact)) {
                $.jGrowl("@lang('Please enter a valid 11-digit mobile number.')", {
                    theme: 'jgrowl-error',
                    life: 4000,
                    position: 'top-right'
                });
                if (contactInput) contactInput.focus();
                return;
            }

            const amountInput = donationForm.querySelector('input[name="donation_amount"]');
            const amount = parseFloat(amountInput.value);

            if (isNaN(amount) || amount < 20) {
                $.jGrowl("@lang('Please donate at least 20 tk')", {
                    theme: 'jgrowl-error',
                    life: 4000,
                    position: 'top-right'
                });
                return;
            }

            const modal = new bootstrap.Modal(modalEl);
            const formData = new FormData(donationForm);
            
            gatewayForm.querySelector('input[name="donation_category_id"]').value = formData.get('donation_category_id');
            gatewayForm.querySelector('input[name="contact"]').value = formData.get('contact');
            gatewayForm.querySelector('input[name="amount"]').value = formData.get('donation_amount');
            
            modal.show();
        });
    });
</script>
@endpush