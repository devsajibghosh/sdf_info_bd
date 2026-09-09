@inject('blog', 'App\Models\BlogPost')
@php
    $blogPosts = $blog->select(['id', 'title', 'slug', 'image', 'created_at'])->published()->latest()->paginate(9);
@endphp

<div class="container py-5">
    <div class="marquee-container mb-5">
        <div class="marquee-text">
            <span>ওঁ | সং গচ্ছধ্বং সং বদধ্বং সং বো মনাংসি জানতাম্... | ন হি জ্ঞানেন সদৃশং পবিত্ৰমিহ বিদ্যতে | আমাদের ওয়েবসাইট এ আপনাকে স্বাগতম 🙏</span>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-lg-8">
            <div class="row g-4 reveal-stagger">
                @foreach ($blogPosts as $post)
                    <div class="col-12 col-md-6">
                        <div class="blog-card reveal">
                            <a href="{{ route('site.blog.details', $post->slug) }}" class="blog-image-wrapper">
                                <img src="{{ $post->image ? asset('storage/' . $post->image) : asset('no-image.png') }}" alt="{{ $post->title }}" loading="lazy" width="400" height="180" onerror="this.onerror=null;this.src='{{ asset('no-image.png') }}';">
                                <div class="blog-overlay"></div>
                            </a>
                            <div class="blog-content">
                                <span class="blog-date"><i class="fas fa-calendar-alt"></i> {{ System::getDateTime($post->created_at) }}</span>
                                <a href="{{ route('site.blog.details', $post->slug) }}">
                                    <h5 class="blog-title">{{ $post->title }}</h5>
                                </a>
                                <a href="{{ route('site.blog.details', $post->slug) }}" class="read-more">পড়ুন বিস্তারিত <i class="fas fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <div class="mt-4">
                {{ $blogPosts->links() }}
            </div>
        </div>
        
        


<div class="col-12 col-lg-4">
    <div class="sidebar-card p-3 shadow-sm bg-white rounded border">
        <h5 class="mb-3 fw-bold" style="border-left: 4px solid #ff5e00; padding-left: 10px;">
            আমাদের ফেসবুক পেজ
        </h5>
        
        <div class="fb-widget-container text-center">
            <div id="fb-root"></div>

            <!-- Load SDK ONLY once in your layout if possible -->
            <script async defer crossorigin="anonymous"
                src="https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v18.0">
            </script>
            
            <div class="fb-page" 
                data-href="https://www.facebook.com/pg.sdfbd/"
                data-tabs="timeline"
                data-adapt-container-width="true"
                data-hide-cover="false"
                data-show-facepile="true">
                
                <blockquote cite="https://www.facebook.com/pg.sdfbd/" class="fb-xfbml-parse-ignore">
                    <a href="https://www.facebook.com/pg.sdfbd/">Santani Development Foundation</a>
                </blockquote>

            </div>
        </div>

        <div class="d-grid mt-3">
            <a href="https://www.facebook.com/pg.sdfbd/" target="_blank"
               class="btn btn-primary btn-sm rounded-pill shadow-sm">
                <i class="fab fa-facebook-f me-2"></i> ফেসবুকে আমাদের অনুসরণ করুন
            </a>
        </div>
    </div>
</div>
        
        
        
        
    </div>
</div>

@push('styles')
<style>
    /* Marquee Styling */
    .marquee-container {
        background: #fff;
        padding: 15px;
        border-radius: 8px;
        overflow: hidden;
        border-left: 5px solid #ff5e00;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    }
    .marquee-text {
        white-space: nowrap;
        animation: marquee 25s linear infinite;
        color: #333;
        font-weight: 600;
        display: inline-block;
    }

    /* Blog Card Design */
    .blog-card {
        background: #fff;
        border-radius: 12px;
        overflow: hidden;
        transition: 0.4s;
        border: 1px solid #eee;
        height: 100%;
    }
    .blog-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    .blog-image-wrapper {
        display: block;
        height: 180px;
        overflow: hidden;
        position: relative;
    }
    .blog-image-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: 0.5s;
    }
    .blog-card:hover img {
        transform: scale(1.05);
    }
    .blog-content {
        padding: 15px;
    }
    .blog-date {
        font-size: 13px;
        color: #888;
        display: block;
        margin-bottom: 8px;
    }
    .blog-title {
        font-size: 16px;
        font-weight: 700;
        color: #222;
        margin-bottom: 12px;
        line-height: 1.4;
    }
    .read-more {
        color: #ff5e00;
        font-weight: 600;
        text-decoration: none;
        font-size: 14px;
    }
    .read-more i { margin-left: 5px; }

    .sidebar-card {
        position: sticky;
        top: 20px;
    }

    @keyframes marquee {
        from { transform: translateX(100%); }
        to { transform: translateX(-100%); }
    }
</style>
@endpush