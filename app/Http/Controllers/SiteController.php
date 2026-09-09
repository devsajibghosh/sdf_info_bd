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

        $request->validate([
            'amount'               => 'required|numeric|gte:20',
            'donation_category_id' => 'required|exists:donation_categories,id',
            'payment_gateway_id'   => 'required|exists:payment_gateways,id',
            'contact'              => ['required', 'string', 'regex:/^01[0-9]{9}$/'],
        ], [
            'amount.required' => __('Minimum donation amount is 20 BDT.'),
            'amount.numeric'  => __('Minimum donation amount is 20 BDT.'),
            'amount.gte'      => __('Minimum donation amount is 20 BDT.'),
            'contact.required' => __('Please enter a valid 11-digit mobile number.'),
            'contact.regex'    => __('Please enter a valid 11-digit mobile number.'),
        ]);

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
    
    
    
    
    // chat bot controll logic 
    
    
public function sendMessage(Request $request)
{
    $request->validate(['message' => 'required|string|max:3000']);
    $userMessage = trim($request->input('message'));

    try {
        // --- ১. ডাটাবেস স্ট্যাটাস সংগ্রহ ---
        $totalBlogs = \App\Models\BlogPost::count();
        $totalMembers = \App\Models\User::count();
        $totalDonors = \App\Models\Donor::count();
        
        $donationInfo = null; 
        $greetingsTrigger = ['hi', 'hello', 'hey', 'কেমন আছেন', 'কেমন আছ', 'হাই', 'হ্যালো'];
        
        // --- ২. ডোনেশন চেক ও ইউজার সার্চ লজিক ---
        // যদি মেসেজটি সাধারণ গ্রিটিং না হয়, তবেই সার্চ লজিক চলবে
        if (strlen($userMessage) > 2 && !in_array(strtolower($userMessage), $greetingsTrigger)) {
            
            preg_match('/[0-9]{8,17}/', $userMessage, $matches);
            $extractedId = $matches[0] ?? null;

            $user = null;
            if ($extractedId) {
                $user = \App\Models\User::where('phone_number', 'LIKE', "%$extractedId%")
                            ->orWhere('id_number', 'LIKE', "%$extractedId%")
                            ->first();
            } else {
                $searchQuery = str_replace(['আমার', 'নাম', 'অনুদান', 'কত', 'দেখাও', 'জানতে চাই', 'টাকা'], '', $userMessage);
                $searchQuery = trim($searchQuery);

                if (strlen($searchQuery) > 2) {
                    $user = \App\Models\User::where(function($query) use ($searchQuery) {
                        $query->where('first_name', 'like', "%$searchQuery%")
                              ->orWhere('last_name', 'like', "%$searchQuery%")
                              ->orWhere('father_name', 'like', "%$searchQuery%")
                              ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%$searchQuery%"]);
                    })->first();
                }
            }

            if ($user && !empty($user->phone_number)) {
                $totalApprovedDonation = \App\Models\Donation::where('phone_number', $user->phone_number)
                                            ->where(function($q) {
                                                $q->where('status', 1) 
                                                  ->orWhereIn('status', ['success', 'Success', 'approved', 'Approved']);
                                            })
                                            ->sum('amount');
                
                $userName = trim($user->first_name . ' ' . $user->last_name) ?: "সম্মানিত সদস্য";
                
                $donationInfo = [
                    'found' => true,
                    'name' => $userName,
                    'father_name' => $user->father_name,
                    'amount' => (float)$totalApprovedDonation,
                    'has_donated' => $totalApprovedDonation > 0,
                    'phone' => $user->phone_number
                ];
            } else if ($extractedId) {
                $donationInfo = ['found' => false, 'error' => 'not_found'];
            }
        }

        // --- ৩. প্রফেশনাল গ্রিটিং লজিক ---
        $hour = date('H');
        if ($hour >= 5 && $hour < 12) $timeGreeting = "শুভ সকাল";
        elseif ($hour >= 12 && $hour < 16) $timeGreeting = "শুভ দুপুর";
        elseif ($hour >= 16 && $hour < 18) $timeGreeting = "শুভ বিকেল";
        elseif ($hour >= 18 && $hour < 20) $timeGreeting = "শুভ সন্ধ্যা";
        else $timeGreeting = "শুভ রাত্রি";

        $professionalGreeting = "{$timeGreeting}, প্রিয় সতীর্থ! আমি 'সারথি' (Sarathi), আপনার ডিজিটাল সহকারী।";

        $sdfFAQ = "
        সনাতনী ডেভেলপমেন্ট ফাউন্ডেশন (এসডিএফ):
        -যোগাযোগের ঠিকানা:
            ঠিকানা: গ্রাম: দাউদপুর, ডাকঘর: তালদিঘী, উপজেলা: তারাকান্দা, জেলা: ময়মনসিংহ।
            ফোন: +৮৮০ ১৮০৫-০০৩২৭৮ থেকে +৮৮০ ১৮০৫-০০৩২৮৮ (হেল্পলাইন)
            ইমেইল: contact@sdf.info.bd
            ওয়েবসাইট: www.sdf.info.bd
        - স্লোগান: 'সার্বিক কল্যাণে সম্মিলিত প্রচেষ্টা'
        - মূলনীতি: শিক্ষা, সততা, একতা ও শান্তি।
        - ভিশন: পিছিয়ে পড়া জনগোষ্ঠীর উন্নয়ন এবং কুসংস্কারমুক্ত সমাজ গঠন।
        - আর্থিক লেনদেন: 
            * ব্যাংক: জনতা ব্যাংক পিএলসি, বাড্ডা শাখা (অ্যাকাউন্ট: 0100256191461, রাউটিং: 135260344)
            * বিকাশ/রকেট (পেমেন্ট): 01805-003282
        ";

        $systemPrompt = "
        আপনার নাম 'সারথি' (Sarathi)। আপনি SDF-এর একজন অত্যন্ত পেশাদার এআই সহকারী। 
        
        উত্তর প্রদানের নিয়মাবলী:
        ১. ব্যবহারকারী যদি শুধুমাত্র অভিবাদন জানায় (যেমন: Hi, Hello, কেমন আছেন, হাই), তবেই আপনার উত্তর শুরু করবেন এভাবে: '{$professionalGreeting} আজ আমি আপনাকে কীভাবে সাহায্য করতে পারি?'  
        ২.ব্যবহারকারীকে সর্বদা 'সতীর্থ' অথবা 'সম্মানিত সদস্য' হিসেবে সম্বোধন করবেন। 
        ৩. DATA-তে তথ্য থাকলে (Donation Info) তা অত্যন্ত বিনয়ের সাথে উপস্থাপন করবেন।
        ৪. আপনার কথা বলার ধরণ হবে সংক্ষিপ্ত, মার্জিত এবং টু-দ্য-পয়েন্ট।
        ৫. SDF-এর বাইরের কোনো প্রশ্ন করলে বলবেন, 'দুঃখিত, আমি শুধুমাত্র SDF সম্পর্কিত তথ্য প্রদানে সক্ষম।'
        
        CONTEXT DATA: " . json_encode($donationInfo) . "
        KNOWLEDGE BASE: {$sdfFAQ}
        STATS: সদস্য: {$totalMembers}, দাতা: {$totalDonors}।";

        // --- ৫. Groq API Call ---
        $response = \Illuminate\Support\Facades\Http::withToken(config('services.groq.key'))
            ->timeout(60)
            ->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => config('services.groq.model') ?? 'llama-3.1-8b-instant',
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userMessage],
                ],
                'temperature' => 0.4,
                'max_tokens' => 800,
            ]);

        if ($response->successful()) {
            $reply = data_get($response->json(), 'choices.0.message.content');
            return response()->json(['reply' => trim($reply)]);
        }

        return response()->json(['reply' => 'দুঃখিত, এই মুহূর্তে সংযোগ স্থাপন করা সম্ভব হচ্ছে না।'], 200);

    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error("Sarathi API Error: " . $e->getMessage());
        return response()->json(['reply' => 'অভ্যন্তরীণ কারিগরি ত্রুটির কারণে আমি দুঃখিত।'], 500);
    }
    }
    
    
        
    
    
    
    
}
