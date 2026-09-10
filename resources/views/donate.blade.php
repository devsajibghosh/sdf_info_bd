@extends('frontend.layouts.main')

@section('content')
<div class="bg-slate-50 py-10 md:py-16">
    <div class="max-w-3xl mx-auto px-4 sm:px-6">

        <div class="text-center mb-8">
            <span class="inline-block bg-emerald-100 text-emerald-700 text-xs font-semibold tracking-wide px-3 py-1 rounded-full mb-3">
                {{ $orgName }}
            </span>
            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900">@lang('Make a Donation')</h1>
            <p class="text-slate-500 mt-2 text-sm md:text-base">@lang('Donation Page Meta Description')</p>
        </div>

        @if (!$bannerExists && auth('admin')->check())
            {{-- Internal ops notice, visible only to logged-in admins; guests never see this. --}}
            <div class="mb-6 rounded-lg border border-amber-300 bg-amber-50 text-amber-800 text-sm px-4 py-3">
                Admin notice: public/donation_banner.png not found on disk — the donation page's social share image is falling back to the site default. Upload the file to enable the dedicated donation banner.
            </div>
        @endif

        <form
            id="donationForm"
            method="POST"
            action="{{ route('guest.donate') }}"
            class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-200 p-5 sm:p-8 space-y-6"
            novalidate
        >
            @csrf

            {{-- Step 1: Donation purpose --}}
            <div>
                <label for="donation_category_id" class="block text-sm font-semibold text-slate-700 mb-1.5">
                    @lang('Donation Purpose') <span class="text-red-500">*</span>
                </label>
                <select
                    id="donation_category_id"
                    name="donation_category_id"
                    required
                    class="w-full rounded-lg border border-slate-300 px-3.5 py-3 text-base text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                >
                    <option value="">@lang('Choose...')</option>
                    @foreach ($donationCategories as $category)
                        <option value="{{ $category->id }}" @selected(old('donation_category_id') == $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                @error('donation_category_id', 'donation')
                    <p class="mt-1.5 text-sm text-red-600" role="alert">{{ $message }}</p>
                @enderror
                <p class="mt-1.5 text-sm text-red-600 hidden" id="categoryError" role="alert"></p>
            </div>

            {{-- Step 2: Mobile number --}}
            <div>
                <label for="contact" class="block text-sm font-semibold text-slate-700 mb-1.5">
                    @lang('Mobile Number') <span class="text-red-500">*</span>
                </label>
                <input
                    type="tel"
                    id="contact"
                    name="contact"
                    inputmode="numeric"
                    autocomplete="tel"
                    maxlength="11"
                    placeholder="01XXXXXXXXX"
                    value="{{ old('contact') }}"
                    required
                    aria-describedby="contactError"
                    class="w-full rounded-lg border border-slate-300 px-3.5 py-3 text-base text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                >
                @error('contact', 'donation')
                    <p class="mt-1.5 text-sm text-red-600" role="alert">{{ $message }}</p>
                @enderror
                <p class="mt-1.5 text-sm text-red-600 hidden" id="contactError" role="alert"></p>
            </div>

            {{-- Step 3: Amount --}}
            <div>
                <label for="donation_amount" class="block text-sm font-semibold text-slate-700 mb-1.5">
                    @lang('Donation Amount') <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-500 font-semibold">৳</span>
                    <input
                        type="number"
                        id="donation_amount"
                        name="amount"
                        inputmode="numeric"
                        min="20"
                        step="1"
                        placeholder="{{ __('Enter amount') }}"
                        value="{{ old('amount') }}"
                        required
                        aria-describedby="amountError"
                        class="w-full rounded-lg border border-slate-300 pl-8 pr-3.5 py-3 text-base text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                    >
                </div>
                <div class="mt-2.5 flex flex-wrap gap-2">
                    @foreach ([100, 200, 500, 1000, 2000, 5000] as $quickAmount)
                        <button
                            type="button"
                            class="quick-amount-btn rounded-full border border-slate-300 px-3.5 py-1.5 text-sm font-semibold text-slate-700 hover:border-emerald-500 hover:text-emerald-700 transition"
                            data-amount="{{ $quickAmount }}"
                        >৳{{ number_format($quickAmount) }}</button>
                    @endforeach
                </div>
                @error('amount', 'donation')
                    <p class="mt-1.5 text-sm text-red-600" role="alert">{{ $message }}</p>
                @enderror
                <p class="mt-1.5 text-sm text-red-600 hidden" id="amountError" role="alert"></p>
            </div>

            @if ($paymentGateways->count() > 1)
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">@lang('Payment Method')</label>
                    <div class="grid grid-cols-2 gap-3">
                        @foreach ($paymentGateways as $gateway)
                            <label class="flex items-center gap-2 rounded-lg border border-slate-300 px-3.5 py-2.5 cursor-pointer has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50">
                                <input type="radio" name="payment_gateway_id" value="{{ $gateway->id }}" @checked($loop->first) required>
                                <span class="text-sm font-medium text-slate-700">{{ $gateway->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('payment_gateway_id', 'donation')
                        <p class="mt-1.5 text-sm text-red-600" role="alert">{{ $message }}</p>
                    @enderror
                </div>
            @elseif ($paymentGateways->count() === 1)
                <input type="hidden" name="payment_gateway_id" value="{{ $paymentGateways->first()->id }}">
                @error('payment_gateway_id', 'donation')
                    <p class="mt-1.5 text-sm text-red-600" role="alert">{{ $message }}</p>
                @enderror
            @else
                <div class="rounded-lg border border-red-300 bg-red-50 text-red-700 text-sm px-4 py-3" role="alert">
                    {{ __('Unable to start the payment. Please try again in a moment.') }}
                </div>
            @endif

            {{-- Donation summary --}}
            <div class="rounded-xl bg-slate-50 border border-slate-200 p-4 sm:p-5" id="donationSummary">
                <h2 class="text-sm font-bold text-slate-700 mb-3">@lang('Donation Summary')</h2>
                <dl class="space-y-1.5 text-sm">
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">@lang('Donation Purpose')</dt>
                        <dd class="font-semibold text-slate-800 text-right" id="summaryCategory">—</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">@lang('Mobile Number')</dt>
                        <dd class="font-semibold text-slate-800 text-right" id="summaryContact">—</dd>
                    </div>
                    <div class="flex justify-between gap-3 pt-1.5 border-t border-slate-200">
                        <dt class="text-slate-600 font-semibold">@lang('Total')</dt>
                        <dd class="font-extrabold text-emerald-700 text-base text-right" id="summaryAmount">৳0 {{ $currency }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Mandatory policy acknowledgement (SSLCommerz compliance requirement) --}}
            <div>
                <label for="agree_terms" class="flex items-start gap-2.5 text-sm text-slate-600 cursor-pointer">
                    <input
                        type="checkbox"
                        id="agree_terms"
                        name="agree_terms"
                        value="1"
                        required
                        aria-describedby="agreeTermsError"
                        class="mt-0.5 h-4 w-4 shrink-0 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                    >
                    <span>
                        আমি
                        <a href="{{ route('site.page', 'terms-and-conditions') }}" target="_blank" rel="noopener" class="text-emerald-700 font-semibold underline hover:text-emerald-800">Terms &amp; Conditions</a>,
                        <a href="{{ route('site.page', 'privacy-policy') }}" target="_blank" rel="noopener" class="text-emerald-700 font-semibold underline hover:text-emerald-800">Privacy Policy</a> এবং
                        <a href="{{ route('site.page', 'return-and-refund-policy') }}" target="_blank" rel="noopener" class="text-emerald-700 font-semibold underline hover:text-emerald-800">Refund &amp; Return Policy</a>
                        মেনে নিচ্ছি।
                    </span>
                </label>
                @error('agree_terms', 'donation')
                    <p class="mt-1.5 text-sm text-red-600" role="alert">{{ $message }}</p>
                @enderror
                <p class="mt-1.5 text-sm text-red-600 hidden" id="agreeTermsError" role="alert"></p>
            </div>

            <button
                type="submit"
                id="donateSubmitBtn"
                @disabled($paymentGateways->isEmpty())
                class="w-full rounded-xl bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold text-base py-3.5 transition"
            >
                @lang('Continue to Payment')
            </button>

            <p class="flex items-center justify-center gap-1.5 text-xs text-slate-500">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4 text-emerald-600" aria-hidden="true">
                    <path fill-rule="evenodd" d="M12 1.5a5.25 5.25 0 00-5.25 5.25v3a3 3 0 00-3 3v6.75a3 3 0 003 3h10.5a3 3 0 003-3v-6.75a3 3 0 00-3-3v-3c0-2.9-2.35-5.25-5.25-5.25zm3.75 8.25v-3a3.75 3.75 0 10-7.5 0v3h7.5z" clip-rule="evenodd" />
                </svg>
                @lang('Payment is Secure')
            </p>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('donationForm');
    if (!form) return;

    const categorySelect = document.getElementById('donation_category_id');
    const contactInput = document.getElementById('contact');
    const amountInput = document.getElementById('donation_amount');
    const agreeTermsInput = document.getElementById('agree_terms');
    const submitBtn = document.getElementById('donateSubmitBtn');

    const categoryError = document.getElementById('categoryError');
    const contactError = document.getElementById('contactError');
    const amountError = document.getElementById('amountError');
    const agreeTermsError = document.getElementById('agreeTermsError');

    const summaryCategory = document.getElementById('summaryCategory');
    const summaryContact = document.getElementById('summaryContact');
    const summaryAmount = document.getElementById('summaryAmount');

    const PHONE_REGEX = /^01[0-9]{9}$/;
    const CURRENCY = @json($currency);
    const MSG_PHONE = @json(__('Please enter a valid 11-digit mobile number.'));
    const MSG_AMOUNT = @json(__('Minimum donation amount is 20 BDT.'));
    const MSG_AGREE_TERMS = @json(__('Please accept the Terms & Conditions, Privacy Policy, and Refund & Return Policy before proceeding with payment.'));
    const MSG_PREPARING = @json(__('Preparing payment...'));
    const submitBtnDefaultText = submitBtn ? submitBtn.textContent.trim() : '';

    function updateSummary() {
        const selectedOption = categorySelect.options[categorySelect.selectedIndex];
        summaryCategory.textContent = (selectedOption && selectedOption.value) ? selectedOption.text : '—';

        summaryContact.textContent = contactInput.value.trim() || '—';

        const amountValue = parseFloat(amountInput.value);
        const formatted = (!isNaN(amountValue) && amountValue > 0)
            ? new Intl.NumberFormat('en-US').format(amountValue)
            : '0';
        summaryAmount.textContent = '৳' + formatted + ' ' + CURRENCY;
    }

    // Quick amount chips
    document.querySelectorAll('.quick-amount-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            amountInput.value = btn.dataset.amount;
            document.querySelectorAll('.quick-amount-btn').forEach(function (b) {
                b.classList.remove('border-emerald-500', 'text-emerald-700', 'bg-emerald-50');
            });
            btn.classList.add('border-emerald-500', 'text-emerald-700', 'bg-emerald-50');
            updateSummary();
        });
    });

    [categorySelect, contactInput, amountInput].forEach(function (el) {
        el.addEventListener('input', updateSummary);
        el.addEventListener('change', updateSummary);
    });

    updateSummary();

    function validate() {
        let valid = true;

        if (!categorySelect.value) {
            categoryError.textContent = @json(__('This field is required.'));
            categoryError.classList.remove('hidden');
            valid = false;
        } else {
            categoryError.classList.add('hidden');
        }

        const contact = contactInput.value.trim();
        if (!PHONE_REGEX.test(contact)) {
            contactError.textContent = MSG_PHONE;
            contactError.classList.remove('hidden');
            valid = false;
        } else {
            contactError.classList.add('hidden');
        }

        const amount = parseFloat(amountInput.value);
        if (isNaN(amount) || amount < 20) {
            amountError.textContent = MSG_AMOUNT;
            amountError.classList.remove('hidden');
            valid = false;
        } else {
            amountError.classList.add('hidden');
        }

        if (agreeTermsInput && !agreeTermsInput.checked) {
            agreeTermsError.textContent = MSG_AGREE_TERMS;
            agreeTermsError.classList.remove('hidden');
            valid = false;
        } else if (agreeTermsError) {
            agreeTermsError.classList.add('hidden');
        }

        return valid;
    }

    form.addEventListener('submit', function (e) {
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
            submitBtn.textContent = MSG_PREPARING;
        }
    });

    // Restore the button if the page is restored from the back/forward cache
    // (e.g. user hits "back" from the gateway) so the form isn't stuck disabled.
    window.addEventListener('pageshow', function (event) {
        if (event.persisted && submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = submitBtnDefaultText;
        }
    });
});
</script>
@endpush
