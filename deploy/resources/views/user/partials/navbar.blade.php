@php
    $pages = \App\Models\Page::where('is_default', 0)->get();
@endphp

<nav class="navbar navbar-expand-lg bg-body-tertiary">
    <div class="container">
        <a class="navbar-brand" href="{{ route('home') }}">
            <img src="{{ System::logo() }}" class="logo" alt="Logo" />
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item">
                    <a class="nav-link {{ activeClass('home') }}" aria-current="page"
                        href="{{ route('home') }}">@lang('Home')</a>
                </li>

                @foreach ($pages as $page)
                    <li class="nav-item">
                        <a class="nav-link {{ activeClass('site.page', $page->slug) }}" aria-current="page"
                            href="{{ route('site.page', $page->slug) }}">
                            {{ __($page->title) }}
                        </a>
                    </li>
                @endforeach

                @auth
                    <li class="nav-item">
                        <a class="nav-link {{ activeClass('user.dashboard') }}"
                            href="{{ route('user.dashboard') }}">@lang('Dashboard')</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ activeClass('user.payment.history') }}"
                            href="{{ route('user.payment.history') }}">@lang('My Donations')</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ activeClass('user.payment.new') }}"
                            href="{{ route('user.payment.new') }}">@lang('Donate')</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ activeClass('user.setting.profile') }}"
                            href="{{ route('user.setting.profile') }}">@lang('Profile')</a>
                    </li>
                    <li class="nav-item">
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button class="btn btn--sm  ms-2 btn-danger" type="submit">
                                <x-icons.logout />
                                @lang('Logout')
                            </button>
                        </form>
                    </li>
                @endauth

                @guest
          

                    @if(auth()->guard('donor')->check())
                        <li class="nav-item">
                            <a class="nav-link {{ activeClass('login') }}" href="{{ route('user.dashboard') }}">@lang('My Profile')</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('user.donor.logout') }}">@lang('Sign Out')</a>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link {{ activeClass('login') }}" href="{{ route('login') }}">@lang('Login')</a>
                        </li>
                        @if (generalSetting('user_registration'))
                            <li class="nav-item">
                                <a class="nav-link {{ activeClass('register') }}"
                                    href="{{ route('register') }}">@lang('Register')</a>
                            </li>
                        @endif
                    @endif
                @endguest

            </ul>
        </div>
    </div>
</nav>
