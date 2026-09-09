<footer class="footer-area">
    @php
        $contactInfo = \App\Models\Setting::where('key', 'section_contact_content')->value('value') ?? [];
        $registeredAddress = $contactInfo['address'] ?? null;
        $aboutPage = \App\Models\Page::where('slug', 'about')->first();
    @endphp

    <div class="payment-banner-footer py-3 bg-white text-center border-bottom">
        <div class="container">
            <img
                src="{{ asset('payment_banner_ssl.png') }}"
                alt="Accepted Payment Methods - SSLCOMMERZ"
                class="payment-banner-img img-fluid mx-auto d-block"
                loading="lazy"
            >
        </div>
    </div>

    <div class="bottom-footer py-3 bg-primary text-white" style="font-size: 0.9rem;">
        <div class="container">
            <div class="row align-items-center justify-content-center border-bottom border-white-50 pb-2 mb-2">
                <div class="col-md-auto text-center">
                    <span>Copyright &copy; {{ date('Y') }} <a href="https://www.sdf.info.bd" class="text-white fw-bold text-decoration-none">SDF</a>.</span>
                </div>

                <div class="col-md-auto text-center">
                    <span class="mx-1 d-none d-md-inline">|</span>
                    <a href="https://app.roc.gov.bd/psp/nc_search" target="_blank" class="text-white text-decoration-none opacity-75 hover-opacity-100">
                        Govt. Reg. No.
                    </a>
                    <a href="javascript:void(0)" onclick="openCertModal()" class="badge rounded-pill bg-white text-primary ms-1 px-2 text-decoration-none fw-normal">
                        S-14442/2026
                    </a>
                </div>
            </div>

            @if ($registeredAddress)
                <div class="text-center small opacity-75 mb-2">
                    <i class="fas fa-map-marker-alt me-1" aria-hidden="true"></i>
                    <strong>{{ __('Registered Address') }}:</strong> {{ $registeredAddress }}
                </div>
            @endif

            <div class="text-center small opacity-75">
                @if ($aboutPage)
                    <a href="{{ route('site.page', $aboutPage->slug) }}" class="text-white text-decoration-none mx-1">
                        {{ __('About Us') }}
                    </a>
                    &bull;
                @endif
                <a href="{{ route('site.contact') }}" class="text-white text-decoration-none mx-1">
                    {{ __('Contact Us') }}
                </a>
                &bull;
                @foreach (\App\Models\Page::where('privacy', 1)->get() as $page)
                    <a href="{{ route('site.page', $page->slug) }}" class="text-white text-decoration-none mx-1">
                        {{ __($page->title) }}
                    </a>
                    @if(!$loop->last) &bull; @endif
                @endforeach
                <div class="mt-1 text-white-50" style="font-size: 0.75rem;">All Rights Reserved.</div>
            </div>
        </div>
    </div>

    <div id="certModal" class="cert-modal" aria-hidden="true">
        <span class="close-modal" onclick="closeCertModal()" title="Close">&times;</span>
        <div class="modal-wrapper">
             <img class="modal-content" id="img01" 
                  src="/uploads/sdf_cer/reg_sdf.jpeg" 
                  alt="Registration Certificate"
                  onerror="this.src='https://via.placeholder.com/400x550?text=Certificate+Image+Not+Found'">
             <div id="caption" class="mt-2">Sanatani Development Foundation Registration Certificate</div>
        </div>
    </div>
</footer>

<style>
    /* Footer Utility */
    .hover-opacity-100:hover { opacity: 1 !important; }
    .bottom-footer a:hover { color: #ffc107 !important; transition: all 0.2s ease; }

    /* Payment banner: full-bleed on mobile, capped and centered on larger screens */
    .payment-banner-img {
        width: 100%;
        max-width: 720px;
        height: auto;
    }

    @media (max-width: 576px) {
        .payment-banner-img { max-width: 100%; }
    }

    /* Compact Modal Styling */
    .cert-modal {
        display: none;
        position: fixed;
        z-index: 99999;
        left: 0; top: 0;
        width: 100%; height: 100%;
        background-color: rgba(0,0,0,0.85);
        backdrop-filter: blur(5px); /* Professional touch */
    }
    .modal-wrapper {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        height: 100%;
        padding: 20px;
    }
    .modal-content {
        max-width: 95%;
        max-height: 80vh;
        width: auto;
        border: 5px solid #fff;
        box-shadow: 0 0 20px rgba(0,0,0,0.5);
        border-radius: 4px;
    }
    .close-modal {
        position: absolute;
        top: 20px; right: 30px;
        color: white; font-size: 40px;
        font-weight: bold; cursor: pointer;
        z-index: 100001;
    }
    #caption { color: #eee; font-weight: 300; text-align: center; }

    @media (max-width: 768px) {
        .modal-content { max-width: 100%; }
    }
</style>

<script>
    function openCertModal() {
        const modal = document.getElementById("certModal");
        modal.style.display = "block";
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = "hidden";
    }
    function closeCertModal() {
        const modal = document.getElementById("certModal");
        modal.style.display = "none";
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = "auto";
    }
    window.onclick = function(e) {
        if (e.target.className === 'modal-wrapper' || e.target.id === 'certModal') closeCertModal();
    }
    document.addEventListener('keydown', (e) => { if(e.key === "Escape") closeCertModal(); });
</script>