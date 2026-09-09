@php
    $faq = \App\Models\Setting::where('key', 'section_faq_content')->first()->value ?? null;
    $faq = (object) $faq;
    $faqItems = collect($faq->items ?? [])->map(function($item) {
        return literal(...$item);
    });
@endphp

@if($faqItems->isNotEmpty())
    <div class="container my-5">
        <div class="text-center">
            <h2 class="mb-2">{{ __($faq->section_title ?? 'Frequently Asked Questions') }}</h2>
            <p class="mb-5 lead text-muted">{{ __($faq->section_description ?? '') }}</p>
        </div>
        
        <!-- সম্পূর্ণ স্বতন্ত্র কাস্টম অ্যাকর্ডিয়ন -->
        <div class="my-custom-faq-wrapper">
            @foreach($faqItems as $index => $item)
                <div class="my-faq-item {{ $index === 0 ? 'active' : '' }}">
                    <div class="my-faq-header">
                        <button type="button" class="my-faq-btn">
                            {{ $item->question }}
                        </button>
                    </div>
                    <div class="my-faq-body-wrap" style="{{ $index === 0 ? 'display: block;' : 'display: none;' }}">
                        <div class="my-faq-body">
                            {!! $item->answer !!}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif

@push('styles')
    <style>
        .my-custom-faq-wrapper {
            width: 100%;
        }

        .my-faq-item {
            margin-bottom: 1.5rem;
            border-radius: 0.75rem !important;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.05);
            border: 1px solid #dee2e6;
            overflow: hidden;
            background-color: #fff;
        }

        .my-faq-header {
            margin: 0;
        }

        .my-faq-btn {
            width: 100%;
            text-align: left;
            font-size: 1.1rem;
            font-weight: 600;
            color: #212529 !important;
            background-color: #ffffff !important;
            padding: 1.25rem 1.5rem;
            border: none;
            outline: none;
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        /* যখন অ্যাক্টিভ থাকবে তখন টেক্সট ও ব্যাকগ্রাউন্ড স্পষ্ট রাখার জন্য ফিক্সড কালার */
        .my-faq-item.active .my-faq-btn {
            color: #ffffff !important;
            background-color: #0d6efd !important; /* এখানে আপনার পছন্দমতো কালার বা প্রাইমারি কালার কোড দিতে পারেন */
            background-image: none !important;
        }

        /* প্লাস এবং মাইনাস আইকন */
        .my-faq-btn::after {
            content: "\002B";
            font-size: 1.5rem;
            font-weight: bold;
            color: #212529;
            transition: transform 0.3s ease;
            flex-shrink: 0;
            margin-left: 1rem;
        }

        .my-faq-item.active .my-faq-btn::after {
            content: "\2212";
            color: #ffffff !important;
        }

        .my-faq-body-wrap {
            transition: all 0.3s ease-in-out;
        }

        .my-faq-body {
            padding: 1.5rem;
            background-color: #f8f9fa;
            line-height: 1.6;
            color: #495057;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const faqButtons = document.querySelectorAll('.my-faq-btn');

            faqButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const currentItem = this.closest('.my-faq-item');
                    const bodyWrap = currentItem.querySelector('.my-faq-body-wrap');
                    const isActive = currentItem.classList.contains('active');

                    if (isActive) {
                        currentItem.classList.remove('active');
                        bodyWrap.style.display = 'none';
                    } else {
                        currentItem.classList.add('active');
                        bodyWrap.style.display = 'block';
                    }
                });
            });
        });
    </script>
@endpush