<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Verifies the "Case C — web server/PHP rejects the request before Laravel"
 * scenario from the upload hardening work: when a request's Content-Length
 * exceeds post_max_size, PHP's own Illuminate\Http\Middleware\ValidatePostSize
 * (part of the default global middleware stack) throws PostTooLargeException
 * before any controller or form validation runs. Without the render() added
 * in bootstrap/app.php, this used to surface as Laravel's generic untranslated
 * 413 error page; it should now come back as the app's normal translated
 * error shape (JSON {message} for AJAX, session('error') flash otherwise).
 */
class PostTooLargeExceptionTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    private function simulateOversizedContentLength(): void
    {
        // ValidatePostSize compares $_SERVER['CONTENT_LENGTH'] against
        // ini_get('post_max_size') — it doesn't need an actual oversized body,
        // just a Content-Length header the current php.ini would reject.
        $this->withServerVariables([
            'CONTENT_LENGTH' => (int) ini_get('post_max_size') > 0
                ? PHP_INT_MAX
                : 999999999999,
        ]);
    }

    public function test_oversized_upload_returns_a_translated_json_error_for_ajax_requests(): void
    {
        $admin = Admin::findOrFail(1);
        $this->actingAs($admin, 'admin');
        $this->simulateOversizedContentLength();

        $response = $this->postJson(route('admin.expense.save'), [
            'expense_category_id' => 1,
            'amount' => 10,
        ]);

        $response->assertStatus(413);
        $response->assertJson([
            'message' => __('The uploaded file is too large for the server to accept. Please upload a smaller file and try again.'),
        ]);
    }

    public function test_oversized_upload_redirects_back_with_a_translated_flash_error_for_normal_requests(): void
    {
        $admin = Admin::findOrFail(1);
        $this->actingAs($admin, 'admin');
        $this->simulateOversizedContentLength();

        $response = $this->post(route('admin.expense.save'), [
            'expense_category_id' => 1,
            'amount' => 10,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', __('The uploaded file is too large for the server to accept. Please upload a smaller file and try again.'));
    }

    public function test_oversized_upload_error_is_in_bangla_when_locale_is_bn(): void
    {
        $admin = Admin::findOrFail(1);
        $this->actingAs($admin, 'admin');
        $this->withSession(['locale' => 'bn']);
        $this->simulateOversizedContentLength();

        $response = $this->postJson(route('admin.expense.save'), [
            'expense_category_id' => 1,
            'amount' => 10,
        ]);

        $response->assertStatus(413);
        $response->assertJson([
            'message' => 'আপলোড করা ফাইলটি সার্ভারের গ্রহণযোগ্য সীমার চেয়ে বড়। অনুগ্রহ করে একটি ছোট ফাইল আপলোড করে আবার চেষ্টা করুন।',
        ]);
    }
}
