<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\BlogPost;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Exercises the admin Blog Post create/edit/delete flow and the public blog
 * details page end-to-end: image auto-optimization (FileManager) and the
 * automatically generated SEO/OG/structured-data (BlogPost::seoTitle() etc,
 * SiteController::blogDetails()).
 *
 * Runs against the real database wrapped in a rolled-back transaction
 * (DatabaseTransactions), matching DonationPageTest's rationale: this repo's
 * migration history doesn't reproduce the live schema from scratch, so
 * RefreshDatabase isn't usable here. Storage::fake() isolates all file writes
 * from the real storage/app/public directory.
 */
class BlogPostAdminTest extends TestCase
{
    use DatabaseTransactions;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Admin id=1 bypasses ability checks entirely (see goIfUserCan()), so
        // reuse it rather than wiring up Bouncer abilities for a fresh admin.
        $this->admin = Admin::findOrFail(1);

        Storage::fake('public');

        // Feature tests post raw data, not a rendered form with a real @csrf
        // token; browsers always send that token, so this only affects the
        // test harness, not production requests.
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    public function test_admin_can_create_a_blog_post_with_an_image(): void
    {
        $this->actingAs($this->admin, 'admin');

        $response = $this->post(route('admin.blog_post.save'), [
            'title'  => 'A Fresh Test Post ' . uniqid(),
            'body'   => '<p>Some interesting body content for the test post.</p>',
            'status' => 1,
            'image'  => UploadedFile::fake()->image('photo.jpg', 2000, 1200),
        ]);

        $response->assertRedirect(route('admin.blog_post.list'));

        $post = BlogPost::latest('id')->first();
        $this->assertNotNull($post->image);
        Storage::disk('public')->assertExists($post->image);
        $this->assertNotEmpty($post->slug);
    }

    public function test_large_image_is_resized_down_to_the_configured_box_preserving_aspect_ratio(): void
    {
        $this->actingAs($this->admin, 'admin');

        $this->post(route('admin.blog_post.save'), [
            'title'  => 'Large Image Post ' . uniqid(),
            'body'   => '<p>Body</p>',
            'status' => 1,
            'image'  => UploadedFile::fake()->image('big.jpg', 4000, 2000),
        ])->assertRedirect(route('admin.blog_post.list'));

        $post = BlogPost::latest('id')->first();
        [$width, $height] = getimagesize(Storage::disk('public')->path($post->image));

        // config/files.php 'blog' => 1300x800; source is 4000x2000 (2:1), so
        // width is the binding constraint: 1300x650, never upscaled.
        $this->assertLessThanOrEqual(1300, $width);
        $this->assertLessThanOrEqual(800, $height);
        $this->assertEqualsWithDelta(2000 / 4000, $height / $width, 0.01);
    }

    public function test_small_image_is_not_upscaled(): void
    {
        $this->actingAs($this->admin, 'admin');

        $this->post(route('admin.blog_post.save'), [
            'title'  => 'Small Image Post ' . uniqid(),
            'body'   => '<p>Body</p>',
            'status' => 1,
            'image'  => UploadedFile::fake()->image('small.jpg', 300, 200),
        ])->assertRedirect(route('admin.blog_post.list'));

        $post = BlogPost::latest('id')->first();
        [$width, $height] = getimagesize(Storage::disk('public')->path($post->image));

        $this->assertSame(300, $width);
        $this->assertSame(200, $height);
    }

    public function test_a_non_image_file_disguised_as_an_image_is_rejected(): void
    {
        $this->actingAs($this->admin, 'admin');

        $response = $this->post(route('admin.blog_post.save'), [
            'title'  => 'Bad Upload Post ' . uniqid(),
            'body'   => '<p>Body</p>',
            'status' => 1,
            'image'  => UploadedFile::fake()->create('not-an-image.jpg', 50, 'image/jpeg'),
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['image']);
        $this->assertDatabaseMissing('blog_posts', ['title' => 'Bad Upload Post']);
    }

    public function test_oversized_dimensions_are_rejected_by_validation(): void
    {
        $this->actingAs($this->admin, 'admin');

        $response = $this->postJson(route('admin.blog_post.save'), [
            'title'  => 'Huge Dimensions Post ' . uniqid(),
            'body'   => '<p>Body</p>',
            'status' => 1,
            'image'  => UploadedFile::fake()->image('huge.jpg', 9000, 9000),
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['image']);
    }

    public function test_editing_a_post_without_a_new_image_keeps_the_existing_image(): void
    {
        $this->actingAs($this->admin, 'admin');

        $this->post(route('admin.blog_post.save'), [
            'title'  => 'Editable Post ' . uniqid(),
            'body'   => '<p>Body</p>',
            'status' => 1,
            'image'  => UploadedFile::fake()->image('orig.jpg', 800, 600),
        ]);

        $post = BlogPost::latest('id')->first();
        $originalImage = $post->image;

        $this->post(route('admin.blog_post.save', $post->id), [
            'title'  => $post->title,
            'body'   => '<p>Updated body</p>',
            'status' => 1,
        ])->assertRedirect(route('admin.blog_post.list'));

        $post->refresh();
        $this->assertSame($originalImage, $post->image);
        $this->assertSame('<p>Updated body</p>', $post->body);
        Storage::disk('public')->assertExists($originalImage);
    }

    public function test_replacing_the_image_on_edit_deletes_the_old_file_no_orphan(): void
    {
        $this->actingAs($this->admin, 'admin');

        $this->post(route('admin.blog_post.save'), [
            'title'  => 'Replaceable Image Post ' . uniqid(),
            'body'   => '<p>Body</p>',
            'status' => 1,
            'image'  => UploadedFile::fake()->image('orig.jpg', 800, 600),
        ]);

        $post = BlogPost::latest('id')->first();
        $originalImage = $post->image;

        $this->post(route('admin.blog_post.save', $post->id), [
            'title'  => $post->title,
            'body'   => $post->body,
            'status' => 1,
            'image'  => UploadedFile::fake()->image('new.jpg', 800, 600),
        ])->assertRedirect(route('admin.blog_post.list'));

        $post->refresh();
        $this->assertNotSame($originalImage, $post->image);
        Storage::disk('public')->assertMissing($originalImage);
        Storage::disk('public')->assertExists($post->image);
    }

    public function test_two_titles_colliding_to_the_same_slug_get_a_unique_suffix(): void
    {
        $this->actingAs($this->admin, 'admin');

        $unique = uniqid();

        $this->post(route('admin.blog_post.save'), [
            'title'  => "Hello World {$unique}!",
            'body'   => '<p>Body</p>',
            'status' => 1,
        ])->assertRedirect(route('admin.blog_post.list'));

        $this->post(route('admin.blog_post.save'), [
            'title'  => "Hello World {$unique}?",
            'body'   => '<p>Body</p>',
            'status' => 1,
        ])->assertRedirect(route('admin.blog_post.list'));

        $posts = BlogPost::where('title', 'like', "%{$unique}%")->orderBy('id')->pluck('slug');

        $this->assertCount(2, $posts);
        $this->assertNotSame($posts[0], $posts[1]);
    }

    public function test_deleting_a_post_removes_its_image_file(): void
    {
        $this->actingAs($this->admin, 'admin');

        $this->post(route('admin.blog_post.save'), [
            'title'  => 'Deletable Post ' . uniqid(),
            'body'   => '<p>Body</p>',
            'status' => 1,
            'image'  => UploadedFile::fake()->image('del.jpg', 800, 600),
        ]);

        $post = BlogPost::latest('id')->first();
        $image = $post->image;

        $this->post(route('admin.blog_post.delete', $post->id))->assertRedirect();

        Storage::disk('public')->assertMissing($image);
        $this->assertDatabaseMissing('blog_posts', ['id' => $post->id]);
    }

    public function test_blog_details_page_renders_automatic_seo_and_structured_data(): void
    {
        $this->actingAs($this->admin, 'admin');

        $longBody = '<p>' . str_repeat('Interesting sentence about our community programs. ', 10) . '</p>';

        $this->post(route('admin.blog_post.save'), [
            'title'  => 'SEO Showcase Post ' . uniqid(),
            'body'   => $longBody,
            'status' => 1,
            'image'  => UploadedFile::fake()->image('seo.jpg', 1600, 900),
        ]);

        $post = BlogPost::latest('id')->first();

        $response = $this->get(route('site.blog.details', $post->slug));

        $response->assertOk();
        $response->assertSee('<title>' . $post->title . '</title>', false);
        $response->assertSee('property="og:type" content="article"', false);
        $response->assertSee('property="og:title" content="' . $post->title . '"', false);
        $response->assertSee('property="og:image" content="' . asset('storage/' . $post->image) . '"', false);
        $response->assertSee('name="twitter:card" content="summary_large_image"', false);
        $response->assertSee('"@type":"Article"', false);
        $response->assertSee('"headline":"' . $post->title . '"', false);
    }

    public function test_a_very_long_article_body_is_saved_and_rendered_in_full(): void
    {
        $this->actingAs($this->admin, 'admin');

        // ~20,000 words of HTML. blog_posts.body is LONGTEXT (see the
        // 2026_09_08_004459 migration) precisely so this doesn't get
        // truncated the way MySQL's 65,535-byte TEXT cap would.
        $paragraph = '<p>' . str_repeat('word ', 200) . '</p>';
        $longBody = str_repeat($paragraph, 105);
        $this->assertGreaterThan(20000, substr_count($longBody, 'word'));

        $this->post(route('admin.blog_post.save'), [
            'title'  => 'Very Long Article ' . uniqid(),
            'body'   => $longBody,
            'status' => 1,
        ])->assertRedirect(route('admin.blog_post.list'));

        $post = BlogPost::latest('id')->first();
        $this->assertSame($longBody, $post->body);
        $this->assertSame(strlen($longBody), strlen($post->fresh()->body));

        $this->get(route('site.blog.details', $post->slug))
            ->assertOk()
            ->assertSee(strip_tags($paragraph), false);
    }

    public function test_blog_listing_page_still_works(): void
    {
        $this->actingAs($this->admin, 'admin');

        $this->post(route('admin.blog_post.save'), [
            'title'  => 'Listed Post ' . uniqid(),
            'body'   => '<p>Body</p>',
            'status' => 1,
        ]);

        $this->get(route('site.blogs'))->assertOk();
    }
}
