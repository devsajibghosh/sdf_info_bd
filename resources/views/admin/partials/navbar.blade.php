<div class="header py-2 px-3 border-bottom bg-warning shadow-sm">
    <div class="row align-items-center">
        <div class="col-lg-6 col-md-6 col-6">
            {{-- Left side (optional logo or menu) --}}
        </div>
        <div class="col-lg-6 col-md-6 col-6">
            <div class="d-flex justify-content-end align-items-center gap-3">

                @php
                    $unreadNotifications = \App\Models\AdminNotification::unRead()->count();
                @endphp
                
                <!--<div class="position-relative">-->
                <!--    <a href="{{ route('home') }}" class="text-dark" title="@lang('Website')" target="_blank">-->
                <!--        <x-icons.globe />-->
                <!--    </a>-->
                <!--</div>-->
                
                
                
                
<div class="session-timer-wrapper">
    <span id="session-timer" class="badge bg-warning">
        <span id="timer-countdown">30:00</span>
    </span>
</div>
                
                

                <div class="position-relative">
                    <a href="{{ route('admin.report.notifications') }}" class="text-dark">
                        <x-icons.bell />
                        @if ($unreadNotifications > 0)
                            <span
                                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                {{ $unreadNotifications }}
                            </span>
                        @endif
                    </a>
                </div>

                {{-- Language Switcher --}}
                <div class="dropdown">
                    <button class="btn border-0 bg-transparent d-flex align-items-center" type="button"
                        id="langDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa fa-globe me-1 text-primary"></i>
                        <span class="fw-semibold text-dark">@lang('Language')</span>
                        <i class="fa fa-caret-down ms-2 text-muted"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="langDropdown">
                        <li><a class="dropdown-item" href="{{ route('lang.switch', 'en') }}">US @lang('English')</a></li>
                        <li><a class="dropdown-item" href="{{ route('lang.switch', 'bn') }}">BD @lang('Bengali')</a></li>
                        <li><a class="dropdown-item" href="{{ route('lang.switch', 'hi') }}">IN @lang('Hindi')</a></li>
                    </ul>
                </div>

                <div class="dropdown">
                    <button class="btn p-0 border-0 bg-transparent d-flex align-items-center" type="button"
                        id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="{{ \App\Services\FileManager::getPublicUrl(admin()->image) }}" class="profile-pic rounded-circle me-2" width="32" height="32" alt="Avatar">
                        <span class="fw-semibold text-dark d-none d-md-inline">{{ admin()->name }}</span>
                        <i class="fa fa-caret-down ms-2 text-muted"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li>
                            <a class="dropdown-item" href="{{ route('admin.setting.profile') }}">
                                <i class="fa fa-user me-2">
                                </i>@lang('Profile')
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('admin.setting.password') }}">
                                <i class="fa fa-wrench me-2"></i>@lang('Settings')
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item text-danger" href="{{ route('admin.logout') }}"><i
                                    class="fa fa-power-off me-2"></i>@lang('Logout')</a></li>
                    </ul>
                </div>

            </div>
        </div>
    </div>
</div>



@push('scripts')
    
<script>
(function () {
    const SESSION_LIFETIME_MINUTES = {{ config('session.lifetime') }};
    const IDLE_DURATION = SESSION_LIFETIME_MINUTES * 60 * 1000;
    const STORAGE_KEY = 'logoutDeadline';

    let logoutDeadline = localStorage.getItem(STORAGE_KEY);

    // ✅ Only create deadline if it does NOT exist
    if (!logoutDeadline) {
        logoutDeadline = Date.now() + IDLE_DURATION;
        localStorage.setItem(STORAGE_KEY, logoutDeadline);
    } else {
        logoutDeadline = parseInt(logoutDeadline);
    }

    function updateDisplay() {
        const diff = logoutDeadline - Date.now();

        if (diff <= 0) {
            localStorage.removeItem(STORAGE_KEY);
            document.getElementById('timer-countdown').innerText = "00:00";
            window.location.href = "{{ route('admin.logout') }}";
            return;
        }

        const minutes = Math.floor(diff / 60000);
        const seconds = Math.floor((diff % 60000) / 1000);

        document.getElementById('timer-countdown').innerText =
            `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
    }

    function resetTimer() {
        logoutDeadline = Date.now() + IDLE_DURATION;
        localStorage.setItem(STORAGE_KEY, logoutDeadline);

        // Optional: keep Laravel session alive
        // fetch('/keep-alive');
    }

    setInterval(updateDisplay, 1000);

    // User activity resets timer
    ['mousedown', 'keydown', 'scroll', 'click'].forEach(event => {
        window.addEventListener(event, resetTimer);
    });

    updateDisplay();
})();


// Inside your updateDisplay function:
const displaySpan = document.getElementById('session-timer');
if (minutes < 10) {
    displaySpan.classList.replace('bg-warning', 'bg-danger');
} else {
    displaySpan.classList.replace('bg-danger', 'bg-warning');
}


</script>




@endpush



