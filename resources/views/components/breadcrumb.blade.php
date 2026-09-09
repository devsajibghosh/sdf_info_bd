@props([
    'title' => '',
])

<section class="breadcrumb py-120">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-8">
                <div class="breadcrumb__wrapper">
                    <h4 class="breadcrumb__title">{{ __($title) }}</h4>

                    <ul class="breadcrumb__list">
                        <li class="breadcrumb__item">
                            <a href="{{ route('home') }}" class="breadcrumb__link">
                                <i class="las la-home me-1"></i>@lang('Home')
                            </a>
                        </li>
                        <li class="breadcrumb__item"><i class="fas fa-arrow-right"></i></li>
                        <li class="breadcrumb__item">
                            <span class="breadcrumb__item-text">{{ __($title) }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>
