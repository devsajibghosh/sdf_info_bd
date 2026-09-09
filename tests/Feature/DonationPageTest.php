<?php

namespace Tests\Feature;

use App\Models\Donation;
use App\Models\DonationCategory;
use App\Models\Donor;
use App\Models\GeneralSetting;
use App\Models\Payment;
use App\Models\PaymentGateway;
use PHPUnit\Framework\Attributes\DataProvider;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Exercises the public donation page (SiteController::donate/guestDonate) and
 * the existing SslCommerzGateway callback flow end-to-end.
 *
 * Runs against the real application database wrapped in a transaction that is
 * rolled back after every test (DatabaseTransactions), rather than a fresh
 * RefreshDatabase migration. This repo's migration history has drifted from
 * the live schema in multiple places unrelated to this feature (e.g.
 * payments.user_id, payment_gateways.manual/for_admin, donations.donor_id/
 * status, users.phone_number are all columns that exist on the real database
 * but have no corresponding migration file — see
 * docs/DONATION_PAGE_IMPLEMENTATION.md "Known schema drift"), so a from-scratch
 * migration cannot currently produce a working schema. Testing against the
 * real, already-consistent schema with transactional rollback is safe (no
 * data is permanently written) and reflects actual behavior faithfully.
 */
class DonationPageTest extends TestCase
{
    use DatabaseTransactions;

    private DonationCategory $category;
    private PaymentGateway $gateway;

    protected function setUp(): void
    {
        parent::setUp();

        // generalSetting('currency') (used by guestDonate()) is cached forever
        // for the life of the process (see app/helpers.php), so this only
        // needs to exist by the time the first test in the run actually calls
        // it — but it's cheap to guarantee every time.
        if (!GeneralSetting::query()->exists()) {
            GeneralSetting::create(['site_title' => 'Test SDF', 'currency' => 'BDT']);
        }

        $this->category = DonationCategory::create([
            'name' => 'Test Donation Category ' . uniqid(),
            'status' => 1,
        ]);

        // Reuse the real, already-configured SSLCommerz gateway row when one
        // exists (payment_gateways.key is unique, so never create a second
        // one); otherwise seed a minimal active one with sandbox config.
        $this->gateway = PaymentGateway::where('key', 'sslcommerz')->where('status', 1)->first()
            ?? PaymentGateway::forceCreate([
                'name' => 'SSLCommerz',
                'key' => 'sslcommerz',
                'status' => 1,
                'manual' => 0,
                'for_admin' => 0,
                // PaymentGateway casts `config` as `object`, which JSON-encodes
                // the attribute on save — pass a plain array, not a pre-encoded
                // JSON string, or the config ends up double-encoded.
                'config' => [
                    'store_id' => 'test_store',
                    'store_passwd' => 'test_pass',
                    'sandbox_mode' => 'yes',
                ],
            ]);
    }

    private function fakeSslCommerzCreate(): void
    {
        Http::fake([
            '*/gwprocess/v4/api.php' => Http::response([
                'status' => 'SUCCESS',
                'sessionkey' => 'sess123',
                'GatewayPageURL' => 'https://sandbox.sslcommerz.com/EasyCheckOut/testabc',
            ]),
        ]);
    }

    // --- 1. Public donation page ---------------------------------------

    public function test_donate_page_is_publicly_accessible_without_login(): void
    {
        $response = $this->get(route('site.donate'));

        $response->assertOk();
        $response->assertSee($this->category->name);
        $response->assertSee('name="donation_category_id"', false);
        $response->assertSee('name="contact"', false);
        $response->assertSee('name="amount"', false);
    }

    public function test_donate_page_renders_seo_and_social_meta_tags(): void
    {
        $response = $this->get(route('site.donate'));

        $response->assertOk();
        $response->assertSee('<title>', false);
        $response->assertSee('name="description"', false);
        $response->assertSee('rel="canonical"', false);
        $response->assertSee('property="og:title"', false);
        $response->assertSee('property="og:description"', false);
        $response->assertSee('property="og:image"', false);
        $response->assertSee('property="og:url"', false);
        $response->assertSee('property="og:type"', false);
        $response->assertSee('property="og:site_name"', false);
        $response->assertSee('property="og:locale"', false);
        $response->assertSee('property="og:image:width"', false);
        $response->assertSee('property="og:image:height"', false);
        $response->assertSee('property="og:image:alt"', false);
        $response->assertSee('name="twitter:card"', false);
        $response->assertSee('name="twitter:title"', false);
        $response->assertSee('name="twitter:description"', false);
        $response->assertSee('name="twitter:image"', false);
    }

    // --- Category validation --------------------------------------------

    public function test_empty_donation_category_is_rejected(): void
    {
        $response = $this->postJson(route('guest.donate'), [
            'contact' => '01712345678',
            'amount' => 100,
            'payment_gateway_id' => $this->gateway->id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['donation_category_id']);
    }

    public function test_invalid_donation_category_is_rejected(): void
    {
        $response = $this->postJson(route('guest.donate'), [
            'donation_category_id' => 999999999,
            'contact' => '01712345678',
            'amount' => 100,
            'payment_gateway_id' => $this->gateway->id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['donation_category_id']);
    }

    // --- Phone validation --------------------------------------------------

    public function test_empty_phone_is_rejected(): void
    {
        $response = $this->postJson(route('guest.donate'), [
            'donation_category_id' => $this->category->id,
            'contact' => '',
            'amount' => 100,
            'payment_gateway_id' => $this->gateway->id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['contact']);
    }

    #[DataProvider('invalidPhoneProvider')]
    public function test_invalid_phone_numbers_are_rejected(string $phone): void
    {
        $response = $this->postJson(route('guest.donate'), [
            'donation_category_id' => $this->category->id,
            'contact' => $phone,
            'amount' => 100,
            'payment_gateway_id' => $this->gateway->id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['contact']);
    }

    public static function invalidPhoneProvider(): array
    {
        return [
            '10 digits' => ['0171234567'],
            '12 digits' => ['017123456789'],
            'alphabetic' => ['01abcdefghi'],
            'valid length but not starting with 01' => ['91712345678'],
            'contains spaces/dashes' => ['017-1234567'],
        ];
    }

    public function test_valid_11_digit_phone_is_accepted(): void
    {
        $this->fakeSslCommerzCreate();

        $response = $this->post(route('guest.donate'), [
            'donation_category_id' => $this->category->id,
            'contact' => '01711000001',
            'amount' => 100,
            'payment_gateway_id' => $this->gateway->id,
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('donations', ['phone_number' => '01711000001']);
    }

    public function test_phone_number_is_trimmed_before_validation(): void
    {
        $this->fakeSslCommerzCreate();

        $response = $this->post(route('guest.donate'), [
            'donation_category_id' => $this->category->id,
            'contact' => '  01711000002  ',
            'amount' => 100,
            'payment_gateway_id' => $this->gateway->id,
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('donations', ['phone_number' => '01711000002']);
    }

    // --- Amount validation ---------------------------------------------

    public function test_empty_amount_is_rejected(): void
    {
        $response = $this->postJson(route('guest.donate'), [
            'donation_category_id' => $this->category->id,
            'contact' => '01711000003',
            'amount' => '',
            'payment_gateway_id' => $this->gateway->id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['amount']);
    }

    #[DataProvider('invalidAmountProvider')]
    public function test_amounts_below_minimum_or_invalid_are_rejected(mixed $amount): void
    {
        $response = $this->postJson(route('guest.donate'), [
            'donation_category_id' => $this->category->id,
            'contact' => '01711000004',
            'amount' => $amount,
            'payment_gateway_id' => $this->gateway->id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['amount']);
    }

    public static function invalidAmountProvider(): array
    {
        return [
            'zero' => [0],
            'one' => [1],
            'five' => [5],
            'ten' => [10],
            'nineteen' => [19],
            'nineteen point nine nine' => [19.99],
            'negative' => [-50],
            'invalid string' => ['abc'],
        ];
    }

    #[DataProvider('validAmountProvider')]
    public function test_amounts_at_or_above_minimum_are_accepted(mixed $amount): void
    {
        $this->fakeSslCommerzCreate();

        $response = $this->post(route('guest.donate'), [
            'donation_category_id' => $this->category->id,
            'contact' => '01711000005',
            'amount' => $amount,
            'payment_gateway_id' => $this->gateway->id,
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('https://sandbox.sslcommerz.com/EasyCheckOut/testabc');
    }

    public static function validAmountProvider(): array
    {
        return [
            'exactly 20' => [20],
            'above minimum' => [100],
        ];
    }

    // --- Valid donation + SSLCommerz initiation ---------------------------

    public function test_valid_donation_creates_pending_payment_and_donation_and_redirects_to_gateway(): void
    {
        $this->fakeSslCommerzCreate();

        $response = $this->post(route('guest.donate'), [
            'donation_category_id' => $this->category->id,
            'contact' => '01711000006',
            'amount' => 250,
            'payment_gateway_id' => $this->gateway->id,
        ]);

        $response->assertRedirect('https://sandbox.sslcommerz.com/EasyCheckOut/testabc');

        $this->assertDatabaseHas('payments', [
            'amount' => 250,
            'status' => 'pending',
            'method' => 'sslcommerz',
        ]);

        $this->assertDatabaseHas('donations', [
            'amount' => 250,
            'phone_number' => '01711000006',
            'donation_category_id' => $this->category->id,
            'status' => 0,
        ]);
    }

    public function test_manual_gateway_cannot_be_used_for_guest_donation(): void
    {
        $manualGateway = PaymentGateway::forceCreate([
            'name' => 'Test Manual Gateway ' . uniqid(),
            'key' => 'test-manual-' . uniqid(),
            'status' => 1,
            'manual' => 1,
            'for_admin' => 1,
        ]);

        $response = $this->postJson(route('guest.donate'), [
            'donation_category_id' => $this->category->id,
            'contact' => '01711000007',
            'amount' => 100,
            'payment_gateway_id' => $manualGateway->id,
        ]);

        $response->assertStatus(404);
    }

    // --- Fixture helper for callback tests ---------------------------------

    private function createPendingPayment(float $amount = 100): Payment
    {
        $donor = Donor::create(['phone_number' => '01711999' . random_int(100, 999)]);

        $payment = Payment::create([
            'status' => 'pending',
            'amount' => $amount,
            'currency' => 'BDT',
            'transaction_no' => 'TXN-TEST-' . uniqid(),
            'payment_gateway_id' => $this->gateway->id,
            'method' => 'sslcommerz',
            'donor_id' => $donor->id,
        ]);

        Donation::create([
            'amount' => $amount,
            'payment_id' => $payment->id,
            'donor_id' => $donor->id,
            'phone_number' => $donor->phone_number,
            'donation_category_id' => $this->category->id,
            'status' => 0,
        ]);

        return $payment;
    }

    private function notifyUrl(Payment $payment, string $status, ?string $valId = 'val_abc123'): string
    {
        $url = route('payment.notify', 'sslcommerz') . '?status=' . $status . '&value_a=' . urlencode(encrypt($payment->id));

        if ($valId !== null) {
            $url .= '&val_id=' . $valId;
        }

        return $url;
    }

    // --- Callback / verification -------------------------------------------

    public function test_successful_callback_marks_payment_success_and_donation_approved(): void
    {
        $payment = $this->createPendingPayment(100);

        Http::fake([
            '*/validator/api/validationserverAPI.php*' => Http::response([
                'status' => 'VALID',
                'tran_id' => $payment->transaction_no,
                'amount' => '100.00',
                'currency' => 'BDT',
            ]),
        ]);

        $this->get($this->notifyUrl($payment, 'success'))->assertRedirect();

        $payment->refresh();
        $this->assertSame('success', $payment->status);
        $this->assertSame(1, $payment->donation->fresh()->status);
        $this->assertTrue((bool) ($payment->meta['balance_credited'] ?? false));
    }

    public function test_duplicate_successful_callback_is_idempotent(): void
    {
        $payment = $this->createPendingPayment(100);

        Http::fake([
            '*/validator/api/validationserverAPI.php*' => Http::response([
                'status' => 'VALID',
                'tran_id' => $payment->transaction_no,
                'amount' => '100.00',
                'currency' => 'BDT',
            ]),
        ]);

        $url = $this->notifyUrl($payment, 'success');

        $this->get($url)->assertRedirect();
        $requestsAfterFirstCall = count(Http::recorded());

        $this->get($url)->assertRedirect(); // duplicate callback

        // The first call legitimately makes two HTTP requests: the SSLCommerz
        // validation API call, then one confirmation SMS send (guarded by
        // GatewayHelper::addBalanceToUser()'s "balance_credited" flag so it
        // only ever fires once). The real idempotency guarantee is that the
        // duplicate callback — which short-circuits on payment->status ===
        // 'success' — makes zero further requests of either kind.
        $this->assertSame($requestsAfterFirstCall, count(Http::recorded()));

        $payment->refresh();
        $this->assertSame('success', $payment->status);
        $this->assertSame(1, $payment->donation->fresh()->status);
    }

    public function test_tampered_amount_in_callback_is_rejected(): void
    {
        $payment = $this->createPendingPayment(100);

        Http::fake([
            '*/validator/api/validationserverAPI.php*' => Http::response([
                'status' => 'VALID',
                'tran_id' => $payment->transaction_no,
                'amount' => '5.00', // tampered: far below the real 100 BDT payment
                'currency' => 'BDT',
            ]),
        ]);

        $this->get($this->notifyUrl($payment, 'success'))->assertRedirect();

        $payment->refresh();
        $this->assertSame('failed', $payment->status);
        $this->assertSame(2, $payment->donation->fresh()->status);
    }

    public function test_invalid_callback_missing_val_id_is_rejected(): void
    {
        $payment = $this->createPendingPayment(100);

        $this->get($this->notifyUrl($payment, 'success', valId: null))->assertRedirect();

        $payment->refresh();
        $this->assertSame('failed', $payment->status);
    }

    // --- Failed / cancelled callback -----------------------------------

    public function test_failed_callback_marks_payment_failed(): void
    {
        $payment = $this->createPendingPayment();

        $this->get($this->notifyUrl($payment, 'fail', valId: null))->assertRedirect();

        $payment->refresh();
        $this->assertSame('failed', $payment->status);
        $this->assertSame(2, $payment->donation->fresh()->status);
    }

    public function test_cancelled_callback_marks_payment_cancelled(): void
    {
        $payment = $this->createPendingPayment();

        $this->get($this->notifyUrl($payment, 'cancel', valId: null))->assertRedirect();

        $payment->refresh();
        $this->assertSame('cancelled', $payment->status);
        $this->assertSame(2, $payment->donation->fresh()->status);
    }
}
