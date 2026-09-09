<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Exercises the admin Expense create/edit/delete flow, in particular the
 * large-image upload + auto-optimization path (FileManager, shared with
 * BlogPostController — see BlogPostAdminTest for the same coverage there).
 *
 * Runs against the real database wrapped in a rolled-back transaction
 * (DatabaseTransactions), matching BlogPostAdminTest/DonationPageTest's
 * rationale: this repo's migration history doesn't reproduce the live
 * schema from scratch (e.g. expenses.status/approved_by/image and the
 * expense_categories table itself all exist on the live DB without a
 * matching migration), so RefreshDatabase isn't usable here.
 */
class ExpenseAdminTest extends TestCase
{
    use DatabaseTransactions;

    private Admin $admin;
    private ExpenseCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::findOrFail(1);
        $this->category = ExpenseCategory::create([
            'added_by' => $this->admin->id,
            'status' => 1,
            'name' => 'Test Category ' . uniqid(),
        ]);

        Storage::fake('public');
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    private function baseFields(array $overrides = []): array
    {
        return array_merge([
            'expense_category_id' => $this->category->id,
            'amount' => 100.50,
            'note' => 'Test expense note',
        ], $overrides);
    }

    public function test_admin_can_create_an_expense_with_an_image(): void
    {
        $this->actingAs($this->admin, 'admin');

        $response = $this->post(route('admin.expense.save'), $this->baseFields([
            'image' => UploadedFile::fake()->image('receipt.jpg', 2000, 1200),
        ]));

        $response->assertRedirect(route('admin.expense.list'));

        $expense = Expense::latest('id')->first();
        $this->assertNotNull($expense->image);
        Storage::disk('public')->assertExists($expense->image);
    }

    public function test_large_expense_image_is_resized_down_preserving_aspect_ratio(): void
    {
        $this->actingAs($this->admin, 'admin');

        $this->post(route('admin.expense.save'), $this->baseFields([
            'image' => UploadedFile::fake()->image('big.jpg', 4000, 2000),
        ]))->assertRedirect(route('admin.expense.list'));

        $expense = Expense::latest('id')->first();
        [$width, $height] = getimagesize(Storage::disk('public')->path($expense->image));

        // config/files.php 'expense' => 1080x720; source is 4000x2000 (2:1), so
        // width is the binding constraint: 1080x540, never upscaled.
        $this->assertLessThanOrEqual(1080, $width);
        $this->assertLessThanOrEqual(720, $height);
        $this->assertEqualsWithDelta(2000 / 4000, $height / $width, 0.01);
    }

    public function test_small_expense_image_is_not_upscaled(): void
    {
        $this->actingAs($this->admin, 'admin');

        $this->post(route('admin.expense.save'), $this->baseFields([
            'image' => UploadedFile::fake()->image('small.jpg', 300, 200),
        ]))->assertRedirect(route('admin.expense.list'));

        $expense = Expense::latest('id')->first();
        [$width, $height] = getimagesize(Storage::disk('public')->path($expense->image));

        $this->assertSame(300, $width);
        $this->assertSame(200, $height);
    }

    public function test_a_non_image_file_disguised_as_an_image_is_rejected(): void
    {
        $this->actingAs($this->admin, 'admin');

        $response = $this->post(route('admin.expense.save'), $this->baseFields([
            'image' => UploadedFile::fake()->create('not-an-image.jpg', 50, 'image/jpeg'),
        ]));

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['image']);
        $this->assertDatabaseMissing('expenses', ['note' => 'Test expense note', 'amount' => 100.50]);
    }

    public function test_oversized_dimensions_are_rejected_by_validation(): void
    {
        $this->actingAs($this->admin, 'admin');

        $response = $this->postJson(route('admin.expense.save'), $this->baseFields([
            'image' => UploadedFile::fake()->image('huge.jpg', 9000, 9000),
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['image']);
    }

    public function test_expense_without_an_image_still_saves(): void
    {
        $this->actingAs($this->admin, 'admin');

        $this->post(route('admin.expense.save'), $this->baseFields())
            ->assertRedirect(route('admin.expense.list'));

        $expense = Expense::latest('id')->first();
        $this->assertNull($expense->image);
        $this->assertEquals(100.50, $expense->amount);
    }

    public function test_editing_an_expense_without_a_new_image_keeps_the_existing_image(): void
    {
        $this->actingAs($this->admin, 'admin');

        $this->post(route('admin.expense.save'), $this->baseFields([
            'image' => UploadedFile::fake()->image('orig.jpg', 800, 600),
        ]));

        $expense = Expense::latest('id')->first();
        $originalImage = $expense->image;

        $this->post(route('admin.expense.save', $expense->id), $this->baseFields([
            'amount' => 250,
        ]))->assertRedirect(route('admin.expense.list'));

        $expense->refresh();
        $this->assertSame($originalImage, $expense->image);
        $this->assertEquals(250, $expense->amount);
        Storage::disk('public')->assertExists($originalImage);
    }

    public function test_replacing_the_image_on_edit_deletes_the_old_file_no_orphan(): void
    {
        $this->actingAs($this->admin, 'admin');

        $this->post(route('admin.expense.save'), $this->baseFields([
            'image' => UploadedFile::fake()->image('orig.jpg', 800, 600),
        ]));

        $expense = Expense::latest('id')->first();
        $originalImage = $expense->image;

        $this->post(route('admin.expense.save', $expense->id), $this->baseFields([
            'image' => UploadedFile::fake()->image('new.jpg', 800, 600),
        ]))->assertRedirect(route('admin.expense.list'));

        $expense->refresh();
        $this->assertNotSame($originalImage, $expense->image);
        Storage::disk('public')->assertMissing($originalImage);
        Storage::disk('public')->assertExists($expense->image);
    }

    public function test_deleting_an_expense_removes_it(): void
    {
        $this->actingAs($this->admin, 'admin');

        $this->post(route('admin.expense.save'), $this->baseFields([
            'image' => UploadedFile::fake()->image('del.jpg', 800, 600),
        ]));

        $expense = Expense::latest('id')->first();

        $this->post(route('admin.expense.delete', $expense->id))->assertRedirect();

        $this->assertDatabaseMissing('expenses', ['id' => $expense->id]);
    }

    public function test_expense_listing_page_still_works(): void
    {
        $this->actingAs($this->admin, 'admin');

        $this->post(route('admin.expense.save'), $this->baseFields());

        $this->get(route('admin.expense.list'))->assertOk();
    }

    public function test_amount_is_required(): void
    {
        $this->actingAs($this->admin, 'admin');

        $response = $this->postJson(route('admin.expense.save'), $this->baseFields(['amount' => '']));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['amount']);
    }

    public function test_validation_errors_are_translated_to_bangla_when_that_locale_is_selected(): void
    {
        $this->actingAs($this->admin, 'admin');
        $this->withSession(['locale' => 'bn']);

        $response = $this->postJson(route('admin.expense.save'), $this->baseFields(['amount' => '']));

        $response->assertStatus(422);
        $response->assertJsonFragment(['amount' => ['পরিমাণ ফিল্ডটি আবশ্যক।']]);
    }

    public function test_validation_errors_are_in_english_when_that_locale_is_selected(): void
    {
        $this->actingAs($this->admin, 'admin');
        $this->withSession(['locale' => 'en']);

        $response = $this->postJson(route('admin.expense.save'), $this->baseFields(['amount' => '']));

        $response->assertStatus(422);
        $response->assertJsonFragment(['amount' => ['The amount field is required.']]);
    }
}
