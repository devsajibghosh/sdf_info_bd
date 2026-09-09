@php
    $projects = \App\Models\Project::latest()->limit(3)->get();
    $value = \App\Models\Setting::where('key', 'section_project_content')->first()?->value;
@endphp

<section class="project-section py-80 bg-light-gray">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-8">
                <div class="section-heading text-center mb-5 reveal">
                    <h2 class="section-heading__title">{{ __($value['heading'] ?? 'Our Featured Projects') }}</h2>
                    <div class="heading-divider mx-auto"></div>
                    <p class="section-heading__desc mt-3 text-muted">
                        {{ __($value['subheading'] ?? 'Empowering lives through charity, education, and faith—together we build a more compassionate world.') }}
                    </p>
                </div>
            </div>
        </div>

        <div class="row gy-4 justify-content-center mt-2 reveal-stagger">
            @foreach ($projects as $project)
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="custom-project-card reveal">
                        <div class="project-card__img">
                            <img src="{{ imageSrc($project->image) }}" alt="{{ $project->title }}">
                            <div class="project-card__overlay">
                                <a href="{{ route('site.projects') }}" class="icon-link"><i class="fas fa-link"></i></a>
                            </div>
                        </div>
                        <div class="project-card__body">
                            <h4 class="project-card__title">
                                <a href="{{ route('site.projects') }}">{{ __($project->title) }}</a>
                            </h4>
                            <div class="project-card__text">
                                @php echo str(nl2br(__($project->details)))->words(18) @endphp
                            </div>
                            <div class="project-card__footer">
                                <a href="{{ route('site.projects') }}" class="read-more-btn">
                                    <span>@lang('Explore Details')</span>
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="row mt-5">
            <div class="col-12 text-center">
                <a href="{{ route('site.projects') }}" class="view-all-btn">
                    @lang('View All Projects')
                </a>
            </div>
        </div>
    </div>
</section>

@push('styles')
<style>
    .py-80 { padding: 80px 0; }
    .bg-light-gray { background-color: #f9fbff; }

    /* Section Heading */
    .section-heading__title {
        font-size: 2.5rem;
        font-weight: 800;
        color: #1a1a1a;
        margin-bottom: 0;
    }
    .heading-divider {
        width: 70px;
        height: 4px;
        background: linear-gradient(90deg, #9C135A, #00A78E);
        margin-top: 15px;
        border-radius: 5px;
    }
    .section-heading__desc {
        font-size: 1.1rem;
        max-width: 700px;
        margin-inline: auto;
    }

    /* Project Card */
    .custom-project-card {
        background: #ffffff;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        height: 100%;
        display: flex;
        flex-direction: column;
        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        border: 1px solid #f0f0f0;
    }

    .custom-project-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12);
    }

    .project-card__img {
        position: relative;
        height: 240px;
        overflow: hidden;
    }

    .project-card__img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.6s ease;
    }

    .custom-project-card:hover .project-card__img img {
        transform: scale(1.05);
    }

    .project-card__overlay {
        position: absolute;
        inset: 0;
        background: rgba(156, 19, 90, 0.7); /* Your Theme Color with opacity */
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: 0.3s ease;
    }

    .custom-project-card:hover .project-card__overlay {
        opacity: 1;
    }

    .icon-link {
        width: 50px;
        height: 50px;
        background: #fff;
        color: #9C135A;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        font-size: 1.2rem;
    }

    .project-card__body {
        padding: 30px;
        display: flex;
        flex-direction: column;
        flex-grow: 1;
    }

    .project-card__title {
        font-size: 1.4rem;
        font-weight: 700;
        margin-bottom: 15px;
    }

    .project-card__title a {
        color: #1a1a1a;
        text-decoration: none;
        transition: 0.3s;
    }

    .project-card__title a:hover {
        color: #00A78E;
    }

    .project-card__text {
        color: #666;
        line-height: 1.7;
        margin-bottom: 25px;
        flex-grow: 1;
    }

    /* Buttons */
    .read-more-btn {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        color: #9C135A;
        font-weight: 700;
        text-decoration: none;
        font-size: 0.95rem;
        transition: 0.3s;
    }

    .read-more-btn:hover {
        gap: 15px;
        color: #00A78E;
    }

    .view-all-btn {
        background: #1a1a1a;
        color: #fff;
        padding: 14px 40px;
        border-radius: 50px;
        text-decoration: none;
        font-weight: 600;
        display: inline-block;
        transition: all 0.3s;
        border: 2px solid #1a1a1a;
    }

    .view-all-btn:hover {
        background: transparent;
        color: #1a1a1a;
    }

    @media (max-width: 768px) {
        .section-heading__title { font-size: 2rem; }
    }
</style>
@endpush