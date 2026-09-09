@php
    // laskdjflkasjdlfkjaslkdjfalsdf
    $content = \App\Models\Setting::where('key', 'section_header_links_content')->first()?->value ?? null;
@endphp

    <div class="header-top bg-warning">
        <div class="container">
            <div class="top-header-wrapper d-flex flex-wrap justify-content-between w-100 align-items-center">
                <div class="top-button d-flex flex-wrap justify-content-between align-items-center w-100">
                    <ul class="login-registration-list d-flex flex-wrap justify-content-between align-items-center">
                        <li class="login-registration-list__item">
                            <a target="_blank" href="{{ $content['facebook'] ?? '' }}" class="login-registration-list__icon">
                                <i class="fab fa-facebook"></i>
                            </a>
                        </li>
                        <li class="login-registration-list__item">
                            <a target="_blank" href="{{ $content['youtube'] ?? '' }}" class="login-registration-list__icon">
                                <i class="fab fa-youtube"></i>
                            </a>
                        </li>
                        <li class="login-registration-list__item">
                            <a href="mailto:{{ $content['email'] ?? '' }}" class="login-registration-list__icon">
                                <i class="fas fa-envelope"></i>
                            </a>
                        </li>
                        <li class="login-registration-list__item">
                            <a href="tel:{{ $content['mobile'] ?? '' }}" class="login-registration-list__icon">
                                <i class="fas fa-phone-alt"></i>
                            </a>
                        </li>
                    </ul>
                    <div class="language-box">
                        <select class="select changeLang">
                            <option value="en" @selected(app()->getLocale() == 'en')>@lang('English')</option>
                            <option value="bn" @selected(app()->getLocale() == 'bn')>@lang('Bangla')</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>