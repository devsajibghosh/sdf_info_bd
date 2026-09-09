<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class ReturnRefundPolicySeeder extends Seeder
{
    /**
     * Creates the "Return and Refund Policy" page required for SSLCommerz
     * merchant compliance (must state a 7-10 working days refund timeline).
     * Safe to re-run: updates the existing page instead of duplicating it.
     */
    public function run(): void
    {
        $content = <<<'HTML'
<p>This Return and Refund Policy explains how Sanatani Development Foundation (SDF) handles refund requests for donations and payments made through www.sdf.info.bd.</p>
<p><strong>Donations:</strong></p>
<p>Contributions made through this website are voluntary donations toward SDF's projects and are, as a general rule, non-refundable once a donation has been successfully processed.</p>
<p><strong>Erroneous or Duplicate Transactions:</strong></p>
<p>If you were charged in error, charged more than once for the same donation, or a technical fault caused a payment failure while the amount was still debited from your account/card, please contact us within 7 (seven) days of the transaction with your transaction ID and payment details.</p>
<p><strong>Refund Timeline:</strong></p>
<p>Once a refund request is verified and approved, the refund will be processed and the amount returned to the original payment method within <strong>7 to 10 working days</strong>. The exact time for the amount to reflect in your account may vary slightly depending on your bank or card issuer's processing time.</p>
<p><strong>How to Request a Refund:</strong></p>
<p>Email us at <a href="mailto:contact@sdf.info.bd">contact@sdf.info.bd</a> or reach us via the <a href="{{contact_us_url}}">Contact Us</a> page with your name, mobile number, transaction ID, and the reason for the refund request.</p>
<p><strong>Cancellation:</strong></p>
<p>Since donations are processed instantly upon successful payment, cancellation is only possible before the payment is completed. Once a payment is confirmed by the payment gateway, it is treated as a completed donation subject to the refund terms above.</p>
<p>&nbsp;</p>
<p><strong>রিটার্ন ও রিফান্ড নীতি (বাংলা)</strong></p>
<p>এই রিটার্ন ও রিফান্ড নীতি ব্যাখ্যা করে যে সানাতানি ডেভেলপমেন্ট ফাউন্ডেশন (এসডিএফ) কীভাবে www.sdf.info.bd ওয়েবসাইটের মাধ্যমে করা দান/পেমেন্টের ক্ষেত্রে রিফান্ড অনুরোধ পরিচালনা করে।</p>
<p><strong>দান:</strong> এই ওয়েবসাইটের মাধ্যমে প্রদত্ত দান সম্পূর্ণ স্বেচ্ছাপ্রণোদিত এবং একবার সফলভাবে সম্পন্ন হলে, সাধারণত তা ফেরতযোগ্য নয়।</p>
<p><strong>ভুল বা দ্বৈত লেনদেন:</strong> যদি ভুলবশত একাধিকবার টাকা কেটে নেওয়া হয় বা প্রযুক্তিগত ত্রুটির কারণে পেমেন্ট ব্যর্থ হওয়া সত্ত্বেও অ্যাকাউন্ট/কার্ড থেকে টাকা কেটে নেওয়া হয়, তাহলে লেনদেনের ৭ (সাত) দিনের মধ্যে ট্রানজেকশন আইডিসহ আমাদের সাথে যোগাযোগ করুন।</p>
<p><strong>রিফান্ডের সময়সীমা:</strong> রিফান্ড অনুরোধ যাচাই ও অনুমোদনের পর, টাকা <strong>৭ থেকে ১০ কার্যদিবসের</strong> মধ্যে মূল পেমেন্ট মাধ্যমে ফেরত দেওয়া হবে।</p>
<p><strong>রিফান্ডের জন্য যোগাযোগ:</strong> ই-মেইল করুন contact@sdf.info.bd -এ অথবা কন্টাক্ট আস পেজের মাধ্যমে আপনার নাম, মোবাইল নম্বর, ট্রানজেকশন আইডি এবং কারণসহ যোগাযোগ করুন।</p>
HTML;

        // Contact-us URL isn't known at seed time in a portable way, so keep the
        // link relative instead of resolving it via route() inside the seeder.
        $content = str_replace('{{contact_us_url}}', '/contact-us', $content);

        Page::updateOrCreate(
            ['slug' => 'return-and-refund-policy'],
            [
                'title'      => 'Return and Refund Policy',
                'privacy'    => 1,
                // Matches Terms and Conditions / Privacy Policy: is_default pages
                // are excluded from the main nav loop in navbar2.blade.php, so
                // this stays footer-only instead of also showing as a menu item.
                'is_default' => 1,
                'content'    => $content,
            ]
        );
    }
}
