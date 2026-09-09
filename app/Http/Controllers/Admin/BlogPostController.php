<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Services\FileManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BlogPostController extends Controller
{
    public function blogPostList()
    {
        goIfUserCan('view-blog-posts');

        $title = 'Blog Posts';

        $blogPosts = BlogPost::latest()->searching(['title', 'body'])->paginate();

        return view('admin.blog_post.list', compact('title', 'blogPosts'));
    }

    public function published()
    {
        goIfUserCan('view-blog-posts');

        $title = 'Published Posts';

        $blogPosts = BlogPost::published()->latest()->paginate();

        return view('admin.blog_post.list', compact('title', 'blogPosts'));
    }
    

    public function create()
    {
        goIfUserCan('save-blog-posts');

        $title = __('Add New Blog Post');

        return view('admin.blog_post.form', compact('title'));
    }

    public function save(Request $request, $id = null)
    {
        goIfUserCan('save-blog-posts');

        // Large-image decode/resize/encode can take longer than the default 30s.
        set_time_limit(120);

        $request->validate([
            'title'  => 'required|string|max:255|unique:blog_posts,title,' . $id,
            'image'  => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:30720', // 30MB raw upload; FileManager auto-optimizes it down server-side
                'dimensions:max_width=8000,max_height=8000',
            ],
            'body'   => 'required|string',
            'status' => 'required|in:0,1',
        ]);

        $post         = $id ? BlogPost::findOrFail($id) : new BlogPost();
        $post->title  = $request->title;
        $post->slug   = BlogPost::uniqueSlug($request->title, $post->id);
        $post->body   = $request->body;
        $post->status = $request->status;

        if (!$post->exists) {
            // SEO (title/description/OG/image/alt) is derived automatically from
            // the title, body and featured image — see BlogPost::seoTitle() etc.
            $post->seo_content = (object) [];
        }

        if ($request->hasFile('image')) {
            $post->image = FileManager::uploadPublic(
                $request->file('image'),
                filePath('blog'),
                $post->image ?? null,
                handleResize('blog')
            );
        }

        $post->admin_id = admin()->id;
        $post->save();

        $message = $id ? __('Post updated successfully.') : __('Post created successfully.');

        if ($request->has('save_and_back')) {
            if ($request->ajax()) {
                return response()->json([
                    'redirect_url' => route('admin.blog_post.list'),
                    'message'      => $message,
                    'success'      => true
                ]);
            };

            return back()->withSuccess($message);
        } else {
            if ($request->ajax()) {
                return response()->json([
                    'reset_form' => true,
                    'message'    => $message,
                    'success'    => true
                ]);
            };

            return to_route('admin.blog_post.list')->withSuccess($message);
        }
    }

    public function edit($id)
    {
        goIfUserCan('save-blog-posts');

        $title = __('Edit Blog Post');

        $blogPost = BlogPost::findOrFail($id);

        return view('admin.blog_post.form', compact('title', 'blogPost'));
    }

    public function delete($id)
    {
        goIfUserCan('delete-blog-posts');

        $post = BlogPost::findOrFail($id);

        if ($post->image && Storage::disk('public')->exists($post->image)) {
            Storage::disk('public')->delete($post->image);
        }

        $post->delete();

        return back()->withSuccess(__('Blog Post Deleted'));
    }
}
