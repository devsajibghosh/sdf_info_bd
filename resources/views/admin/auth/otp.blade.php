@extends('admin.layouts.auth')

@section('content')
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-12 col-md-5">
                <x-card class="auth-card">
                    <div class="card-header admin-card text-center mb-4">
                        <h4>@lang('Verify OTP')</h4>
                        <p class="mb-0 mt-2" style="opacity:.85;font-size:.95rem;">
                            @lang('OTP sent to'):
                            <strong>{{ $maskedPhone }}</strong>
                        </p>
                    </div>

                    @if (session('status'))
                        <div class="alert alert-success py-2 text-center">{{ session('status') }}</div>
                    @endif

                    <form method="POST" action="{{ route('admin.login.otp.verify') }}" id="otpForm" novalidate>
                        @csrf

                        <input type="hidden" name="otp_code" id="otp_code" />

                        <div class="mb-3">
                            <div class="d-flex justify-content-center gap-2" id="otpBoxes" role="group" aria-label="@lang('4 digit OTP')">
                                @for ($i = 0; $i < 4; $i++)
                                    <input
                                        type="text"
                                        inputmode="numeric"
                                        autocomplete="{{ $i === 0 ? 'one-time-code' : 'off' }}"
                                        pattern="[0-9]*"
                                        maxlength="1"
                                        class="form-control text-center otp-box"
                                        style="width:3rem;height:3.25rem;font-size:1.5rem;"
                                        aria-label="@lang('OTP digit') {{ $i + 1 }}"
                                        {{ $i === 0 ? 'autofocus' : '' }}
                                    />
                                @endfor
                            </div>

                            @error('otp_code')
                                <small class="text-danger d-block mt-2 text-center">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="text-center mb-3" id="countdownWrap">
                            <span id="countdownActive" class="{{ $isExpired ? 'd-none' : '' }}">
                                @lang('OTP expires in'):
                                <strong><span id="countdownTimer">01:00</span></strong>
                            </span>
                            <span id="countdownExpired" class="{{ $isExpired ? '' : 'd-none' }}">
                                @lang('OTP expired.')
                            </span>
                        </div>

                        <x-button class="w-100 d-flex gap-2 justify-content-center align-items-center" type="submit" id="verifyBtn">
                            @lang('Verify OTP')
                        </x-button>
                    </form>

                    <form method="POST" action="{{ route('admin.login.otp.resend') }}" id="resendForm" class="mt-3 text-center">
                        @csrf
                        <p class="mb-2" style="opacity:.85;">@lang("Didn't receive OTP?")</p>
                        <button type="submit" class="btn btn-outline-info v2-btn" id="resendBtn" disabled>
                            @lang('Resend OTP')
                        </button>
                    </form>

                    <div class="text-center mt-3">
                        <a href="{{ route('admin.login') }}" style="color:#fff;opacity:.85;font-size:.9rem;">
                            @lang('Back to login')
                        </a>
                    </div>
                </x-card>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // ---- 4-box OTP input: auto-advance, backspace, numeric-only, paste ----
    var boxes = Array.prototype.slice.call(document.querySelectorAll('.otp-box'));
    var hidden = document.getElementById('otp_code');
    var form = document.getElementById('otpForm');
    var verifyBtn = document.getElementById('verifyBtn');

    function syncHidden() {
        hidden.value = boxes.map(function (b) { return b.value; }).join('');
    }

    boxes.forEach(function (box, index) {
        box.addEventListener('input', function () {
            box.value = box.value.replace(/[^0-9]/g, '').slice(0, 1);
            if (box.value && index < boxes.length - 1) {
                boxes[index + 1].focus();
            }
            syncHidden();
        });

        box.addEventListener('keydown', function (e) {
            if (e.key === 'Backspace' && !box.value && index > 0) {
                boxes[index - 1].focus();
            }
        });

        box.addEventListener('paste', function (e) {
            e.preventDefault();
            var pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/[^0-9]/g, '');
            if (!pasted) return;

            pasted.slice(0, boxes.length).split('').forEach(function (digit, i) {
                if (boxes[i]) boxes[i].value = digit;
            });

            var next = Math.min(pasted.length, boxes.length) - 1;
            if (boxes[next]) boxes[next].focus();

            syncHidden();
        });
    });

    form.addEventListener('submit', function (e) {
        syncHidden();

        if (hidden.value.length !== 4) {
            e.preventDefault();
            boxes[0].focus();
            return;
        }

        // Prevent double submission; the backend remains authoritative either way.
        verifyBtn.disabled = true;
        verifyBtn.textContent = @json(__('Verifying...'));
    });

    // ---- Countdown, driven by SERVER timestamps so a page refresh or clock
    // drift can never extend/reset the real (backend-enforced) expiration. ----
    var expiresAt   = {{ (int) $expiresAt }};   // server epoch seconds
    var serverNow    = {{ (int) $serverNow }};  // server epoch seconds at render time
    var remainingAtLoad = Math.max(0, expiresAt - serverNow);
    var endClientTime = Date.now() + remainingAtLoad * 1000;

    var timerEl = document.getElementById('countdownTimer');
    var activeEl = document.getElementById('countdownActive');
    var expiredEl = document.getElementById('countdownExpired');
    var resendBtn = document.getElementById('resendBtn');

    function render(remainingSeconds) {
        var m = Math.floor(remainingSeconds / 60);
        var s = remainingSeconds % 60;
        timerEl.textContent = (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
    }

    function showExpired() {
        activeEl.classList.add('d-none');
        expiredEl.classList.remove('d-none');
        resendBtn.disabled = false;
    }

    if (remainingAtLoad <= 0) {
        showExpired();
    } else {
        render(remainingAtLoad);

        var interval = setInterval(function () {
            var remaining = Math.max(0, Math.round((endClientTime - Date.now()) / 1000));
            render(remaining);

            if (remaining <= 0) {
                clearInterval(interval);
                showExpired();
            }
        }, 250);
    }

    document.getElementById('resendForm').addEventListener('submit', function () {
        resendBtn.disabled = true;
        resendBtn.textContent = @json(__('Sending...'));
    });
});
</script>
@endpush
