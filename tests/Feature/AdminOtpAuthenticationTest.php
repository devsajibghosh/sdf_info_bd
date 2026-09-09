<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AdminOtpChallenge;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Exercises the two-step admin login flow (username/password -> 4-digit SMS
 * OTP -> authenticated dashboard session).
 *
 * Runs against the real application database wrapped in a transaction that is
 * rolled back after every test (DatabaseTransactions), matching the pattern
 * already established in DonationPageTest — this repo's migration history has
 * drifted from the live schema in places unrelated to this feature, so a
 * from-scratch RefreshDatabase migration does not currently produce a working
 * schema (see that test's docblock). Testing against the real, already-
 * consistent schema with transactional rollback is safe: nothing written here
 * survives the test.
 */
class AdminOtpAuthenticationTest extends TestCase
{
    use DatabaseTransactions;

    private const PASSWORD = 'correct-password-for-tests';

    private function makeAdmin(): Admin
    {
        // Admin::$fillable only allows username/password (by design, since
        // admin creation goes through a dedicated form) — forceFill the rest
        // for this test fixture, bypassing that mass-assignment guard.
        $admin = new Admin();
        $admin->forceFill([
            'username'     => 'otp_test_admin_' . uniqid(),
            'name'         => 'OTP Test Admin',
            'email'        => 'otp_test_' . uniqid() . '@example.test',
            'phone_number' => '01700000000',
            'password'     => Hash::make(self::PASSWORD),
        ])->save();

        return $admin;
    }

    private function fakeSmsSuccess(): void
    {
        Http::fake([
            'bulksmsbd.net/*' => Http::response('SMS SUBMITTED', 200),
            'ip-api.com/*'    => Http::response(['country' => 'Bangladesh', 'city' => 'Dhaka'], 200),
        ]);
    }

    private function fakeSmsFailure(): void
    {
        Http::fake([
            'bulksmsbd.net/*' => Http::response('gateway down', 500),
            'ip-api.com/*'    => Http::response(['country' => 'Bangladesh', 'city' => 'Dhaka'], 200),
        ]);
    }

    public function test_direct_dashboard_access_without_any_login_is_blocked(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('admin.login'));
        $this->assertFalse(Auth::guard('admin')->check());
    }

    public function test_wrong_password_does_not_create_an_otp_challenge_or_authenticate(): void
    {
        $admin = $this->makeAdmin();
        $this->fakeSmsSuccess();

        $response = $this->from(route('admin.login'))->post(route('admin.login'), [
            'username' => $admin->username,
            'password' => 'definitely-wrong',
        ]);

        $response->assertRedirect(route('admin.login'));
        $response->assertSessionHasErrors('username');
        $this->assertFalse(Auth::guard('admin')->check());
        $this->assertSame(0, AdminOtpChallenge::where('admin_id', $admin->id)->count());

        Http::assertNothingSent();
    }

    public function test_wrong_username_does_not_create_an_otp_challenge_or_authenticate(): void
    {
        $this->fakeSmsSuccess();

        $response = $this->from(route('admin.login'))->post(route('admin.login'), [
            'username' => 'no-such-admin-username',
            'password' => 'whatever',
        ]);

        $response->assertRedirect(route('admin.login'));
        $response->assertSessionHasErrors('username');
        $this->assertFalse(Auth::guard('admin')->check());

        Http::assertNothingSent();
    }

    public function test_correct_credentials_issue_an_otp_challenge_and_redirect_to_otp_page_not_dashboard(): void
    {
        $admin = $this->makeAdmin();
        $this->fakeSmsSuccess();

        $response = $this->post(route('admin.login'), [
            'username' => $admin->username,
            'password' => self::PASSWORD,
        ]);

        $response->assertRedirect(route('admin.login.otp'));
        $this->assertFalse(Auth::guard('admin')->check(), 'Admin must NOT be authenticated after password step alone.');

        $challenge = AdminOtpChallenge::where('admin_id', $admin->id)->firstOrFail();
        $this->assertNull($challenge->verified_at);
        $this->assertSame(0, $challenge->attempts);
        $this->assertTrue($challenge->expires_at->isFuture());
        $this->assertTrue($challenge->expires_at->diffInSeconds(now()) <= AdminOtpChallenge::VALIDITY_SECONDS);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'bulksmsbd.net');
        });
    }

    public function test_dashboard_is_still_blocked_after_password_step_before_otp_is_verified(): void
    {
        $admin = $this->makeAdmin();
        $this->fakeSmsSuccess();

        $this->post(route('admin.login'), [
            'username' => $admin->username,
            'password' => self::PASSWORD,
        ]);

        $this->assertFalse(Auth::guard('admin')->check());

        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('admin.login'));
        $this->assertFalse(Auth::guard('admin')->check(), 'Knowing the dashboard URL must not grant access while OTP is pending.');
    }

    public function test_sms_failure_does_not_advance_to_otp_page_or_authenticate(): void
    {
        $admin = $this->makeAdmin();
        $this->fakeSmsFailure();

        $response = $this->from(route('admin.login'))->post(route('admin.login'), [
            'username' => $admin->username,
            'password' => self::PASSWORD,
        ]);

        $response->assertRedirect(route('admin.login'));
        $response->assertSessionHasErrors('username');
        $this->assertFalse(Auth::guard('admin')->check());
        $this->assertSame(0, AdminOtpChallenge::where('admin_id', $admin->id)->count(), 'A failed SMS send must not leave a usable challenge behind.');
    }

    public function test_correct_otp_authenticates_and_reaches_dashboard(): void
    {
        $admin = $this->makeAdmin();
        $this->fakeSmsSuccess();

        $challenge = AdminOtpChallenge::create([
            'admin_id'   => $admin->id,
            'otp_hash'   => Hash::make('1234'),
            'attempts'   => 0,
            'expires_at' => now()->addSeconds(AdminOtpChallenge::VALIDITY_SECONDS),
        ]);

        $this->withSession(['admin_otp_challenge_id' => $challenge->id]);

        $response = $this->post(route('admin.login.otp.verify'), ['otp_code' => '1234']);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertTrue(Auth::guard('admin')->check());
        $this->assertSame($admin->id, Auth::guard('admin')->id());

        $challenge->refresh();
        $this->assertNotNull($challenge->verified_at);

        // Dashboard must now actually be reachable (200, not a redirect).
        $dashboard = $this->get(route('admin.dashboard'));
        $dashboard->assertOk();
    }

    public function test_incorrect_otp_is_rejected_and_does_not_authenticate(): void
    {
        $admin = $this->makeAdmin();

        $challenge = AdminOtpChallenge::create([
            'admin_id'   => $admin->id,
            'otp_hash'   => Hash::make('1234'),
            'attempts'   => 0,
            'expires_at' => now()->addSeconds(AdminOtpChallenge::VALIDITY_SECONDS),
        ]);

        $this->withSession(['admin_otp_challenge_id' => $challenge->id]);

        $response = $this->post(route('admin.login.otp.verify'), ['otp_code' => '9999']);

        $response->assertSessionHasErrors('otp_code');
        $this->assertFalse(Auth::guard('admin')->check());

        $challenge->refresh();
        $this->assertSame(1, $challenge->attempts);
        $this->assertNull($challenge->verified_at);
    }

    public function test_expired_otp_is_rejected_even_if_the_code_is_correct(): void
    {
        $admin = $this->makeAdmin();

        $challenge = AdminOtpChallenge::create([
            'admin_id'   => $admin->id,
            'otp_hash'   => Hash::make('1234'),
            'attempts'   => 0,
            'expires_at' => now()->subSecond(), // already expired
        ]);

        $this->withSession(['admin_otp_challenge_id' => $challenge->id]);

        $response = $this->post(route('admin.login.otp.verify'), ['otp_code' => '1234']);

        $response->assertSessionHasErrors('otp_code');
        $this->assertFalse(Auth::guard('admin')->check(), 'A correct-but-expired OTP must never authenticate.');

        $challenge->refresh();
        $this->assertNull($challenge->verified_at);
    }

    public function test_otp_cannot_be_reused_after_successful_verification(): void
    {
        $admin = $this->makeAdmin();
        $this->fakeSmsSuccess();

        $challenge = AdminOtpChallenge::create([
            'admin_id'   => $admin->id,
            'otp_hash'   => Hash::make('1234'),
            'attempts'   => 0,
            'expires_at' => now()->addSeconds(AdminOtpChallenge::VALIDITY_SECONDS),
        ]);

        $this->withSession(['admin_otp_challenge_id' => $challenge->id]);
        $this->post(route('admin.login.otp.verify'), ['otp_code' => '1234'])
            ->assertRedirect(route('admin.dashboard'));

        Auth::guard('admin')->logout();
        $this->assertFalse(Auth::guard('admin')->check());

        // Re-present the same (now used) challenge id + same code.
        $this->withSession(['admin_otp_challenge_id' => $challenge->id]);
        $replay = $this->post(route('admin.login.otp.verify'), ['otp_code' => '1234']);

        $replay->assertRedirect(route('admin.login'));
        $this->assertFalse(Auth::guard('admin')->check(), 'A verified OTP must not be usable a second time.');
    }

    public function test_five_wrong_attempts_locks_the_challenge_even_with_the_correct_code_afterwards(): void
    {
        $admin = $this->makeAdmin();

        $challenge = AdminOtpChallenge::create([
            'admin_id'   => $admin->id,
            'otp_hash'   => Hash::make('1234'),
            'attempts'   => 0,
            'expires_at' => now()->addSeconds(AdminOtpChallenge::VALIDITY_SECONDS),
        ]);

        for ($i = 0; $i < AdminOtpChallenge::MAX_ATTEMPTS; $i++) {
            $this->withSession(['admin_otp_challenge_id' => $challenge->id]);
            $this->post(route('admin.login.otp.verify'), ['otp_code' => '0000'])
                ->assertSessionHasErrors('otp_code');
        }

        $challenge->refresh();
        $this->assertSame(AdminOtpChallenge::MAX_ATTEMPTS, $challenge->attempts);

        // Even the correct code must now be refused — the challenge is locked.
        $this->withSession(['admin_otp_challenge_id' => $challenge->id]);
        $response = $this->post(route('admin.login.otp.verify'), ['otp_code' => '1234']);

        $response->assertSessionHasErrors('otp_code');
        $this->assertFalse(Auth::guard('admin')->check());
    }

    public function test_resend_invalidates_the_old_otp_and_issues_a_new_one(): void
    {
        $admin = $this->makeAdmin();

        $challenge = AdminOtpChallenge::create([
            'admin_id'   => $admin->id,
            'otp_hash'   => Hash::make('1234'),
            'attempts'   => 0,
            'expires_at' => now()->subSecond(), // expired, so resend is allowed
        ]);

        $this->fakeSmsSuccess();
        $this->withSession(['admin_otp_challenge_id' => $challenge->id]);

        $resend = $this->post(route('admin.login.otp.resend'));
        $resend->assertRedirect(route('admin.login.otp'));

        $challenge->refresh();
        $this->assertTrue($challenge->expires_at->isFuture(), 'Resend must issue a fresh 60-second window.');
        $this->assertSame(0, $challenge->attempts, 'Resend must reset the attempt counter.');
        $this->assertFalse(Hash::check('1234', $challenge->otp_hash), 'The old OTP must stop working after resend.');

        // Old code must now fail against the (same row, new hash) challenge.
        $this->withSession(['admin_otp_challenge_id' => $challenge->id]);
        $oldCodeAttempt = $this->post(route('admin.login.otp.verify'), ['otp_code' => '1234']);
        $oldCodeAttempt->assertSessionHasErrors('otp_code');
        $this->assertFalse(Auth::guard('admin')->check());
    }

    public function test_resend_is_refused_while_the_current_otp_is_still_valid(): void
    {
        $admin = $this->makeAdmin();

        $challenge = AdminOtpChallenge::create([
            'admin_id'   => $admin->id,
            'otp_hash'   => Hash::make('1234'),
            'attempts'   => 0,
            'expires_at' => now()->addSeconds(AdminOtpChallenge::VALIDITY_SECONDS), // still valid
        ]);

        $this->fakeSmsSuccess();
        $this->withSession(['admin_otp_challenge_id' => $challenge->id]);

        $resend = $this->post(route('admin.login.otp.resend'));

        $resend->assertSessionHasErrors('otp_code');
        Http::assertNothingSent();

        $challenge->refresh();
        $this->assertTrue(Hash::check('1234', $challenge->otp_hash), 'The still-valid OTP must be untouched.');
    }

    public function test_visiting_otp_page_without_a_pending_challenge_redirects_to_login(): void
    {
        $response = $this->get(route('admin.login.otp'));

        $response->assertRedirect(route('admin.login'));
    }
}
