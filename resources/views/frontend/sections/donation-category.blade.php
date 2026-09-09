@php
    $donationCategories = \App\Models\DonationCategory::active()->get();
    $donationCategorySection = \App\Models\Setting::where('key', 'section_donation_category_content')->first()?->value;
@endphp

<div class="donation-category py-5" style="background-color: #fcfcfc;">
    <div class="container">
        <div class="row justify-content-center mb-5">
            <div class="col-12 col-lg-7 text-center reveal">
                <h2 class="fw-bold mb-3" style="color: #222;">{{ __($donationCategorySection['heading'] ?? 'Our Donation Categories') }}</h2>
                <div style="width: 60px; height: 3px; background: #ff5e00; margin: 0 auto;"></div>
                <p class="text-muted mt-3">
                    {{ __($donationCategorySection['subheading'] ?? 'Choose a cause to support and help us make a positive impact.') }}
                </p>
            </div>
        </div>

        <div class="row gy-4 reveal-stagger">
            @foreach ($donationCategories as $donationCategory)
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="modern-cause-card reveal">
                        <div class="cause-img">
                            {{-- ইমেজের পাথে সমস্যা হলে asset('storage/' . $donationCategory->image) চেক করুন --}}
                            <img src="{{ asset('storage/' . $donationCategory->image) }}" alt="img" class="img-fluid w-100">
                            <div class="cause-tag">Category</div>
                        </div>
                        <div class="cause-body p-4 text-center">
                            <h4 class="fw-bold mb-3">
                                <a href="{{ route('site.donate') }}" style="text-decoration: none; color: #333; transition: 0.3s;">{{ __($donationCategory->name) }}</a>
                            </h4>
                            <p class="text-muted small mb-4">
                                {{ \Illuminate\Support\Str::limit(__($donationCategory->short_desc), 80) }}
                            </p>
                            <a href="{{ route('site.donate') }}" class="btn-donate-modern">
                                <span>@lang('Donate Now')</span>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="col-12 text-center mt-5">
                <a href="{{ route('site.donate') }}" class="btn-outline-custom">
                    @lang('Explore More') <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Card Design */
    .modern-cause-card {
        background: #fff;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        border: 1px solid #f1f1f1;
        transition: all 0.4s ease;
        height: 100%;
    }

    .modern-cause-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.1);
    }

    .cause-img {
        position: relative;
        height: 200px;
        overflow: hidden;
    }

    .cause-img img {
        height: 100%;
        object-fit: cover;
    }

    .cause-tag {
        position: absolute;
        top: 15px;
        left: 15px;
        background: #ff5e00;
        color: #fff;
        padding: 4px 12px;
        border-radius: 5px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
    }

    /* Button Style */
    .btn-donate-modern {
        display: inline-block;
        width: 100%;
        padding: 12px;
        background: #222;
        color: #fff;
        text-decoration: none;
        border-radius: 8px;
        font-weight: 600;
        transition: 0.3s;
    }

    .btn-donate-modern:hover {
        background: #ff5e00;
        color: #fff;
    }

    .btn-outline-custom {
        display: inline-block;
        padding: 10px 30px;
        border: 2px solid #222;
        color: #222;
        text-decoration: none;
        border-radius: 50px;
        font-weight: 700;
        transition: 0.3s;
    }

    .btn-outline-custom:hover {
        background: #222;
        color: #fff;
    }
</style>
@endpush