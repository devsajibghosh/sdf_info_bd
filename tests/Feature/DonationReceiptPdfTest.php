<?php

namespace Tests\Feature;

use App\Models\Donation;
use App\Models\DonationCategory;
use App\Models\Donor;
use App\Models\GeneralSetting;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Services\ReceiptPdfService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Exercises the single-donation receipt PDF download (SiteController::
 * downloadPdf, route `download.receipt`), which previously rendered any
 * Bengali text (the site's own Bangla foundation name, a Bangla donor name,
 * etc.) as literal "?" characters via dompdf: dompdf's bundled default font
 * (DejaVu Sans) has no Bengali glyphs at all, and even with a Bengali font
 * registered dompdf has no complex-script shaping engine, so conjuncts and
 * vowel signs land in the wrong position. Generation now goes through
 * ReceiptPdfService (mPDF, which does shape Bengali correctly).
 *
 * Runs against the real application database wrapped in a transaction that
 * is rolled back after every test (DatabaseTransactions), matching this
 * repo's established pattern (see DonationPageTest) for the same reason:
 * migration history has drifted from the live schema in places unrelated to
 * this feature, so a from-scratch migration cannot reliably produce a
 * working schema.
 */
class DonationReceiptPdfTest extends TestCase
{
    use DatabaseTransactions;

    private DonationCategory $category;
    private PaymentGateway $gateway;

    protected function setUp(): void
    {
        parent::setUp();

        if (!GeneralSetting::query()->exists()) {
            GeneralSetting::create(['site_title' => 'Test SDF', 'currency' => 'BDT']);
        }

        $this->category = DonationCategory::create([
            'name' => 'Test Donation Category ' . uniqid(),
            'status' => 1,
        ]);

        $this->gateway = PaymentGateway::where('key', 'sslcommerz')->where('status', 1)->first()
            ?? PaymentGateway::forceCreate([
                'name' => 'SSLCommerz',
                'key' => 'sslcommerz',
                'status' => 1,
                'manual' => 0,
                'for_admin' => 0,
            ]);
    }

    /**
     * Builds the donation via a Donor record (not User), matching how the
     * real public donation flow (SiteController::guestDonate) actually
     * stores a donor-supplied name: User::name is always overwritten to
     * "first_name last_name" by User::booted()'s saving hook (see
     * app/Models/User.php), so a User-based fixture couldn't exercise a
     * `name` distinct from that derivation. Donor::name has no such hook.
     */
    private function makeDonation(array $donorAttributes, float $amount = 100): Donation
    {
        $donor = Donor::create(array_merge([
            'phone_number' => '0171' . random_int(1000000, 9999999),
        ], $donorAttributes));

        $payment = Payment::create([
            'donor_id' => $donor->id,
            'status' => 'success',
            'amount' => $amount,
            'currency' => 'BDT',
            'transaction_no' => 'TXN-RECEIPT-' . uniqid(),
            'payment_gateway_id' => $this->gateway->id,
            'method' => 'sslcommerz',
        ]);

        return Donation::create([
            'amount' => $amount,
            'donor_id' => $donor->id,
            'payment_id' => $payment->id,
            'email' => $donor->phone_number,
            'phone_number' => $donor->phone_number,
            'donation_category_id' => $this->category->id,
            'field_collection' => 0,
            'status' => 1,
        ]);
    }

    public function test_receipt_downloads_as_a_pdf_with_expected_headers(): void
    {
        $donation = $this->makeDonation(['name' => 'Sajib Ghosh']);

        $response = $this->get(route('download.receipt', encrypt($donation->id)));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $response->assertHeader('Content-Disposition', 'attachment; filename=donation-receipt-' . $donation->id . '.pdf');
        $this->assertStringStartsWith('%PDF-', $response->streamedContent());
    }

    public function test_ascii_name_is_used_directly_without_an_english_name_field(): void
    {
        $donation = $this->makeDonation(['name' => 'Sajib Ghosh']);

        $this->assertSame('Sajib Ghosh', $donation->receiptDonorName());
    }

    public function test_explicit_english_name_takes_priority_over_the_stored_name(): void
    {
        $donation = $this->makeDonation(['name' => 'সজীব ঘোষ', 'name_en' => 'Sajib Ghosh']);

        $this->assertSame('Sajib Ghosh', $donation->receiptDonorName());
    }

    public function test_non_english_name_is_kept_as_a_last_resort_rather_than_dropped_or_guessed(): void
    {
        $donation = $this->makeDonation(['name' => 'সজীব ঘোষ']);

        // No name_en, and the stored name isn't ASCII: the original value is
        // returned as-is (mPDF renders it correctly) rather than fabricating
        // a transliteration or silently omitting the donor's name.
        $this->assertSame('সজীব ঘোষ', $donation->receiptDonorName());
    }

    public function test_receipt_generates_successfully_for_a_bengali_donor_name(): void
    {
        // Regression guard for the reported bug: this previously either threw
        // (dompdf can't embed/shape Bengali out of the box) or silently
        // produced a PDF full of "?" glyphs. A PDF is a binary/compressed
        // container, so glyph-shaping correctness itself is verified
        // visually (rendered to an image and inspected), not asserted here
        // — this just guards that the request succeeds and returns a real
        // PDF rather than erroring or returning an empty/broken file.
        $donation = $this->makeDonation(['name' => 'সজীব ঘোষ']);

        $response = $this->get(route('download.receipt', encrypt($donation->id)));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $response->streamedContent());
    }

    public function test_generation_failure_redirects_back_with_a_translated_flash_error_instead_of_crashing(): void
    {
        $donation = $this->makeDonation(['name' => 'Sajib Ghosh']);

        $this->app->bind(ReceiptPdfService::class, function () {
            return new class extends ReceiptPdfService {
                public function download(string $view, array $data, string $filename): \Symfony\Component\HttpFoundation\StreamedResponse
                {
                    throw new \RuntimeException('simulated PDF generation failure');
                }
            };
        });

        $response = $this->get(route('download.receipt', encrypt($donation->id)));

        $response->assertRedirect();
        $response->assertSessionHas('error', __('Unable to generate the donation receipt. Please try again later.'));
    }
}
