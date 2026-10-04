<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Facades\System;
use App\Models\AdminNotification;
use App\Models\BlogPost;
use App\Models\Contact;
use App\Models\Donation;
use App\Models\DonationCategory;
use App\Models\Donor;
use App\Models\Page;
use App\Models\PageView;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Models\User;
use App\Services\GatewayFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SiteController extends Controller
{
    public function donationSuccess($donationId, $isManualGateway = false)
    {
        $title = __('Donation Success');

        try {
            $donationId = decrypt($donationId);

            $donation = Donation::findOrFail($donationId);

            if($isManualGateway) {
                return view('donation_pending', compact('title', 'donation'));
            }
            
            return view('donation_success', compact('title', 'donation'));
        } catch (\Exception $e) {
            abort(404);
        }
    }

    public function donationFailed($donationId)
    {
        $title = __('Donation Failed');

        try {
            $donation = Donation::findOrFail(decrypt($donationId));

            return view('donation_failed', compact('title', 'donation'));
        } catch (\Exception $e) {
            abort(404);
        }
    }

    public function donationCancelled($donationId)
    {
        $title = __('Donation Cancelled');

        try {
            $donation = Donation::findOrFail(decrypt($donationId));

            return view('donation_cancelled', compact('title', 'donation'));
        } catch (\Exception $e) {
            abort(404);
        }
    }


    public function donate()
    {
        $title = __('Make a Donation');

        count_page_view('/donate');

        $donationCategories = DonationCategory::active()->get(['id', 'name']);

        $paymentGateways = PaymentGateway::active()->automatic()->get(['id', 'key', 'name']);

        $bannerPath = public_path('donation_banner.png');
        $bannerExists = is_file($bannerPath);

        if ($bannerExists) {
            $metaImage = asset('donation_banner.png');
            $dimensions = @getimagesize($bannerPath);
            $metaImageWidth = $dimensions[0] ?? 1200;
            $metaImageHeight = $dimensions[1] ?? 630;
        } else {
            // Required social/share asset (public/donation_banner.png) is not
            // present on disk. Falling back to the site's default share image
            // so the page still renders valid Open Graph/Twitter tags instead
            // of a broken/404 image; this must be replaced once the real
            // banner file is added (see docs/DONATION_PAGE_IMPLEMENTATION.md).
            $metaImage = asset('sdf_bn.jpeg');
            $metaImageWidth = 1200;
            $metaImageHeight = 630;
        }

        $orgName = generalSetting('site_title') ?: config('app.name');
        $currency = generalSetting('currency') ?: 'BDT';

        $metaTitle = __('Make a Donation') . ' | ' . $orgName;
        $metaDescription = __('Donation Page Meta Description');
        $metaCanonical = route('site.donate');

        return view('donate', compact(
            'title',
            'donationCategories',
            'paymentGateways',
            'bannerExists',
            'metaImage',
            'metaImageWidth',
            'metaImageHeight',
            'metaTitle',
            'metaDescription',
            'metaCanonical',
            'orgName',
            'currency'
        ));
    }

    public function gallery()
    {
        $title = __('Gallery');

        count_page_view('/gallery');

        return view('gallery', compact('title'));
    }

    public function downloadPdf($id, \App\Services\ReceiptPdfService $receiptPdf)
    {
        $di = decrypt($id);

        $donation = Donation::active()->findOrFail($di);

        try {
            return $receiptPdf->download(
                'user.payment.receipt',
                compact('donation'),
                'donation-receipt-' . $donation->id . '.pdf'
            );
        } catch (\Throwable $e) {
            Log::error('Donation receipt PDF generation failed.', [
                'donation_id' => $donation->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', __('Unable to generate the donation receipt. Please try again later.'));
        }
    }


    public function guestDonate(Request $request)
    {
        $request->merge(['contact' => trim((string) $request->input('contact'))]);

        // Named "donation" error bag: this endpoint is submitted from more than one
        // page (the homepage quick-donate widget and the dedicated /donate page).
        // Keeping its errors in their own bag stops them from also being picked up
        // by the site-wide global alert partial (admin.partials.alerts loops over
        // the *default* error bag only) — validation errors must only ever appear
        // inline, next to the field/checkbox on whichever form was submitted.
        $validator = Validator::make($request->all(), [
            'amount'               => 'required|numeric|gte:20',
            // Row must exist AND still be active — a stale/tampered category id
            // (e.g. one the admin has since disabled) must not silently succeed.
            'donation_category_id' => ['required', Rule::exists('donation_categories', 'id')->where('status', 1)],
            // Row must exist AND be an active, automatic gateway — matches the
            // constraint enforced again below before the gateway is charged, so a
            // manipulated request fails here with a clean validation message
            // instead of a raw 404 from findOrFail().
            'payment_gateway_id'   => ['required', Rule::exists('payment_gateways', 'id')->where('status', 1)->where('manual', 0)],
            'contact'              => ['required', 'string', 'regex:/^01[0-9]{9}$/'],
            'agree_terms'          => 'accepted',
        ], [
            'amount.required' => __('Minimum donation amount is 20 BDT.'),
            'amount.numeric'  => __('Minimum donation amount is 20 BDT.'),
            'amount.gte'      => __('Minimum donation amount is 20 BDT.'),
            'donation_category_id.required' => __('This field is required.'),
            'donation_category_id.exists'   => __('The selected donation category is not available. Please choose another one.'),
            'payment_gateway_id.exists'     => __('Unable to start the payment. Please try again in a moment.'),
            'contact.required' => __('Please enter a valid 11-digit mobile number.'),
            'contact.regex'    => __('Please enter a valid 11-digit mobile number.'),
            'agree_terms.accepted' => __('Please accept the Terms & Conditions, Privacy Policy, and Refund & Return Policy before proceeding with payment.'),
        ]);

        $validator->validateWithBag('donation');

        $contact = $request->contact;

        $donor = Donor::where('phone_number', $request->contact)->first() ?? new Donor();
        $donor->phone_number = $contact;
        $donor->save();

        // Automatic gateways only: manual gateway ids can never be submitted here,
        // even via a crafted direct POST — they must go through the admin's
        // manual donation entry flow instead.
        $paymentGateway = PaymentGateway::where('status', 1)->where('manual', 0)->findOrFail($request->payment_gateway_id);
        $gatewayCode    = $paymentGateway->key;

        // Split contact (try email or phone fallback)
        // $email   = filter_var($contact, FILTER_VALIDATE_EMAIL) ? $contact : null;
        // $phone   = !$email ? $contact : null;

        // $user = null;

        // if ($email) $user = User::where('email', $email)->first();
        // else if ($phone) $user = User::where('phone_number', $phone)->first();

        // if (!$user) {
        //     $user = new User();

        //     if ($email) {
        //         $user->email = $email;
        //     } else {
        //         $user->phone_number = $phone;
        //     }

        //     $user->donator = 1;
        //     $user->save();
        // }

        // Store payment log
        $payment                     = new Payment();
        $payment->status             = 'pending';
        $payment->amount             = $request->amount;
        $payment->currency           = generalSetting('currency');
        $payment->transaction_no     = generateTransactionId();
        $payment->payment_gateway_id = $request->payment_gateway_id;
        $payment->method             = $gatewayCode;
        $payment->donor_id           = $donor->id;
        $payment->save();

        $donation                       = new Donation();
        $donation->amount               = $request->amount;
        $donation->donor_id             = $donor->id;
        $donation->payment_id           = $payment->id;
        $donation->email                = $contact;
        $donation->phone_number         = $contact;
        $donation->donation_category_id = $request->donation_category_id;
        $donation->status               = 0;
        $donation->save();

        $adminNotification          = new AdminNotification();
        $adminNotification->user_id = 0;
        $adminNotification->donor_id= $donor->id;
        $adminNotification->link    = route('admin.donation.list') . '?search='.$payment->transaction_no;
        $adminNotification->details = __('New donation received');
        $adminNotification->save();

        try {
            $gateway = GatewayFactory::make($gatewayCode);
            return $gateway->create($payment);
        } catch (\Throwable $e) {
            Log::error('Payment gateway initiation failed.', [
                'payment_id' => $payment->id,
                'gateway' => $gatewayCode,
                'error' => $e->getMessage(),
            ]);

            $payment->status = 'failed';
            $payment->save();

            $donation->status = 2;
            $donation->save();

            return back()->withError(__('Unable to start the payment. Please try again in a moment.'));
        }
    }


    public function contactSubmit(Request $request)
    {
        $rules = [
            'name'         => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:255'],
            'subject'      => ['required', 'string', 'max:255'],
            'message'      => ['required', 'string', 'min:5'],
        ];
        
        if (System::googleCaptchaEnabled()) {
            $rules['g-recaptcha-response'] = ['required', 'captcha'];
        }

        $validated = $request->validate($rules);

        Contact::create([
            'name'         => $validated['name'],
            'phone_number' => $validated['phone_number'],
            'subject'      => $validated['subject'],
            'message'      => $validated['message'],
        ]);

        return back()->with('success', __('Your message has been submitted.'));
    }

    public function contact()
    {
        $title = __('Contact Us');

        count_page_view('/contact');

        return view('contact', compact('title'));
    }

    public function projects()
    {
        $title = __('Projects');

        count_page_view('/projects');

        return view('projects', compact('title'));
    }

    public function videos()
    {
        $title = __('Videos');

        count_page_view('/videos');

        return view('videos', compact('title'));
    }

    public function blogs()
    {
        $title = __('Blog Posts');

        count_page_view('/blog');

        $blogPosts = BlogPost::select(['id', 'title', 'slug', 'image', 'created_at'])
            ->latest()
            ->published()
            ->paginate();

        return view('blogs', compact('title', 'blogPosts'));
    }

    public function blogDetails($slug)
    {
        $post = BlogPost::published()->with('admin')->where('slug', $slug)->firstOrFail();

        $title = $post->title;

        // Auto-generated SEO/Open Graph data, fed into the shared meta tags in
        // frontend.layouts.main (see BlogPost::seoTitle()/seoDescription() etc.).
        $metaTitle       = $post->seoTitle();
        $metaDescription = $post->seoDescription();
        $metaImage       = $post->seoImageUrl();
        $metaImageAlt    = $post->seoImageAlt();
        $metaCanonical   = $post->canonicalUrl();
        $metaOgType      = 'article';

        return view('blog.details', compact(
            'title', 'post',
            'metaTitle', 'metaDescription', 'metaImage', 'metaImageAlt', 'metaCanonical', 'metaOgType'
        ));
    }

    public function changeLang($lang)
    {
        session(['locale' => $lang]);

        return back()->withSuccess(__('Language changed successfully'));
    }

    public function home()
    {
        $title = 'Home';

        count_page_view();

        $seoContent = get_seo_content('home');

        return view('home', compact('title', 'seoContent'));
    }

    public function renderPageBySlug($pageSlug)
    {

        $page = Page::where('slug', $pageSlug)->firstOrFail();

        count_page_view($pageSlug);

        $title = $page->title;

        $seoContent = get_seo_content($pageSlug);

        $sections = $page->sections;

        return view('page', compact('title', 'sections', 'page', 'seoContent'));
    }
    
    
        
    
    
    
    
}
