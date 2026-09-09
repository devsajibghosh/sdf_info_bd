@php
    $section = 'banner';
    $about = \App\Models\Setting::where('key', 'section_about_content')->first()->value ?? null;

    $about = (object) $about;
@endphp

<div class="about-section py-60">
    <div class="container">
        <div class="row align-items-center gy-4">
            <div class="col-12 col-lg-6">
                <div class="about-section__thumb reveal-scale">

<div id="zoom-in-out">
<img src="{{  asset($about->image) }}" alt="@lang('About Image')">
</div>

                </div>
            </div>
            <div class="col-12 col-lg-6">
                <div class="about-section__content reveal">
                    <h4 class="title">{{ __($about->heading)  }}</h4>

<p class="desc">
    
<div class="description-wrapper">
    {{-- The Short Text --}}
    <span class="short-text">
        {{ str(strip_tags($about->description))->words(50) }}
        <button onclick="toggleText(this)" style="color: blue;">...See More</button>
    </span>

    {{-- The Full Text (Hidden) --}}
    <span class="full-text" style="display: none;">
        {{ $about->description }}
        <button onclick="toggleText(this)" style="color: red;margin-top:3px;">Show Less</button>
    </span>
</div>

    
</p>

                    {{-- <ul class="timeline-links">
                        <li><a href="#">Education</a></li>
                        <li><a href="#">Charity</a></li>
                        <li><a href="#">Dawah</a></li>
                    </ul> --}}
                </div>

            </div>
        </div>
    </div>
</div>



@push('scripts')

<script>
function toggleText(button) {
    // Find the parent wrapper
    const wrapper = button.closest('.description-wrapper');
    const short = wrapper.querySelector('.short-text');
    const full = wrapper.querySelector('.full-text');

    // Toggle visibility
    if (short.style.display === 'none') {
        short.style.display = 'inline';
        full.style.display = 'none';
    } else {
        short.style.display = 'none';
        full.style.display = 'inline';
    }
}
</script>



<style>

#zoom-in-out {
    width: 100%;        /* Or your desired width */
    overflow: hidden;   /* Important: prevents the image from overlapping other content */
    border-radius: 5px; /* Optional: rounded corners */
}

#zoom-in-out img {
    width: 100%;
    height: auto;
    display: block;
    transform: scale(1);
    transition: transform 0.6s var(--motion-ease, ease-out);
}

/* A subtle, single zoom on hover reads as premium; a looping zoom reads as noisy. */
.about-section__thumb:hover #zoom-in-out img {
    transform: scale(1.04);
}
    
</style>


    
@endpush


