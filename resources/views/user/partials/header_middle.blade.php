    <div class="header-middle">
        <div class="container">
            <div class="header-middle__inner">
                <div class="header-middle__left d-flex gap-2">
                    <button class="navbar-toggler header-button d-block d-lg-none" type="button" data-bs-toggle="collapse"
                        data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                        aria-expanded="false" aria-label="Toggle navigation">
                        <span id="hiddenNav">
                            <i class="las la-bars"></i>
                        </span>
                    </button>
                    <div class="header-middle__logo">
                        <a class="navbar-brand logo" href="{{ route('home') }}">
                            <img src="{{ System::logo() }}" class="logo" alt="Logo" />
                    </div>

                </div>
                <div class="header-middle__author">
                    @if(auth()->check() || auth()->guard('donor')->check())
                        <a href="{{ route('user.dashboard') }}" class="btn btn--base btn--sm">@lang('Dashboard')</a>
                    @endif

                    @if(!auth()->check() && !auth()->guard('donor')->check())
                        <a href="{{ route('login') }}" class="btn btn--base btn--sm">@lang('Login')</a>
                    @endif
                    
                    @if(!auth()->guard('donor')->check())
                        @auth
                            <a href="{{ route('user.payment.new') }}" class="btn btn--base btn--sm">@lang('Donate')</a>
                        @else
                            <a href="{{ route('site.donate') }}" class="btn btn--base btn--sm">@lang('Donate')</a>
                        @endauth
                    @endif
                </div>
            </div>
        </div>
    </div>
