<?php

namespace App\Http\Controllers;

use App\Facades\System;
use App\Models\AdminNotification;
use App\Models\BlogPost;
use App\Models\Contact;
use App\Models\Donation;
use App\Models\Donor;
use App\Models\Page;
use App\Models\PageView;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Models\User;
use App\Services\GatewayFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

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


    public function gallery()
    {
        $title = __('Gallery');

        count_page_view('/gallery');

        return view('gallery', compact('title'));
    }

    public function downloadPdf($id)
    {
        $di = decrypt($id);

        $donation = Donation::findOrFail($di);

        $pdf = Pdf::loadView('user.payment.receipt', compact('donation'));

        return $pdf->download('donation-receipt-' . $donation->id . '.pdf');
    }


    public function guestDonate(Request $request)
    {
        $request->validate([
            'amount'               => 'required|numeric|gt:0',
            'donation_category_id' => 'required|exists:donation_categories,id',
            'payment_gateway_id'   => 'required|exists:payment_gateways,id',
            'contact'              => 'required|string|max:255',
        ]);
        
        $contact = $request->contact;

        $donor = Donor::where('phone_number', $request->contact)->first() ?? new Donor();
        $donor->phone_number = $contact;
        $donor->save();

        $paymentGateway = PaymentGateway::where('status', 1)->findOrFail($request->payment_gateway_id);
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

        $gateway = GatewayFactory::make($gatewayCode);
        return $gateway->create($payment);
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
            'phone_number' => $validated['email'],
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

        $blogPosts = BlogPost::published()->paginate();

        return view('blogs', compact('title', 'blogPosts'));
    }

    public function blogDetails($slug)
    {
        $post = BlogPost::published()->where('slug', $slug)->firstOrFail();

        $title = $post->title;

        $seoContent = get_seo_content($slug, true);

        return view('blog.details', compact('title', 'post', 'seoContent'));
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
