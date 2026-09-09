<header class="header text-white" id="header">
    <div class="container">
        <nav class="navbar navbar-expand-lg navbar-light">
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav nav-menu align-items-lg-center">
                    <li class="nav-item d-block d-lg-none">
                        <div class="top-button d-flex flex-wrap justify-content-between align-items-center">
                            <div class="language-box">
                                <select class="select changeLang">
                                    <option value="en" @selected(app()->getLocale() == 'en')>@lang('English')</option>
                                    <option value="bn" @selected(app()->getLocale() == 'bn')>@lang('Bangla')</option>
                                </select>
                            </div>
                        </div>
                    </li>
                    <li class="nav-item {{ activeClass('home') }}">
                        <a class="nav-link" aria-current="page" href="{{ route('home') }}">@lang('Home')</a>
                    </li>
                    <li class="nav-item {{ activeClass('site.page', 'about') }}">
                        <a class="nav-link" aria-current="page"
                            href="{{ route('site.page', 'about') }}">@lang('About')</a>
                    </li>
                    <li class="nav-item {{ activeClass('site.projects') }}">
                        <a class="nav-link" href="{{ route('site.projects') }}">@lang('Projects')</a>
                    </li>
                    <li class="nav-item {{ activeClass('site.videos') }}">
                        <a class="nav-link" href="{{ route('site.videos') }}">@lang('Videos')</a>
                    </li>
                    <li class="nav-item {{ activeClass('site.blogs') }}">
                        <a class="nav-link" href="{{ route('site.blogs') }}">@lang('News Feed')</a>
                    </li>
                    <li class="nav-item {{ activeClass('site.gallery') }}">
                        <a class="nav-link" href="{{ route('site.gallery') }}">@lang('Gallery')</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('site.contact') }}">@lang('Contact')</a>
                    </li>
                    <li class="nav-item {{ activeClass('site.donate') }}">
                        <a class="nav-link fw-bold" href="{{ route('site.donate') }}">@lang('Donate Now')</a>
                    </li>

                    <!--temple list view -->
                    
                    <li class="nav-item">
                        <a class="nav-link" href="https://temple.sdf.info.bd/">@lang('Temple List')</a>
                    </li>
                    
                    <!-- temple list view end-->
                    
                    <!--workflow url start-->
                        
                        
                    <li class="nav-item">
                        <a class="nav-link" href="https://workflow-sdf.blogspot.com/p/login.html">@lang('Workflow')</a>
                    </li>
                    
                    <!--workflow url end-->
                    @foreach(\App\Models\Page::where('is_default', 0)->get() as $thePage)
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('site.page', $thePage->slug) }}">
                                {{ $thePage->title }}
                            </a>
                        </li>                    
                    @endforeach
                    @guest
                        @if(!auth()->guard('donor')->check())
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('login') }}">@lang('Login/Register')</a>
                            </li>
                        @endif
                    @endauth
                </ul>
            </div>
        </nav>
    </div>
</header>

