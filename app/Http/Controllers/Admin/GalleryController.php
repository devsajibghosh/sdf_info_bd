<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use App\Models\GalleryCategory;
use App\Services\FileManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GalleryController extends Controller
{
    public function list()
    {
        goIfUserCan('view-gallery');
        
        $title = __('Galleries');

        $galleries = Gallery::latest()->searching(['title'])->with('galleryCategory')->paginate();

        return view('admin.gallery.list', compact('title', 'galleries'));
    }

    public function create()
    {
        goIfUserCan('save-gallery');

        $title = __('Add Gallery Item');

        $galleryCategories = GalleryCategory::active()->get();

        return view('admin.gallery.form', compact('title', 'galleryCategories'));
    }

    public function save(Request $request, $id = null)
    {
        goIfUserCan('save-gallery');

        $request->validate([
            'title'               => 'nullable',
            'image'               => $id ? 'nullable' : 'required|image|max:4096',
            'gallery_category_id' => 'nullable',
            'status'              => 'required|in:0,1'
        ]);

        $gallery                      = $id ? Gallery::findOrFail($id) : new Gallery();
        $gallery->title               = $request->title;
        $gallery->gallery_category_id = $request->gallery_category_id ?? 0;
        $gallery->status              = $request->status ?? 1;

        if ($request->hasFile('image')) {
            $resize = null;

            if (fileSizes('gallery')) {
                $sizeStr          = uploadImageSize('gallery');
                [$width, $height] = explode('x', $sizeStr);
                $resize           = ['width' => (int)$width, 'height' => (int)$height];
            }

            $gallery->image = FileManager::uploadPublic(
                $request->file('image'),
                filePath('gallery'),
                $gallery->image,
                $resize
            );
        }

        // if ($request->hasFile('image')) {
        //     if ($id && $gallery->image && Storage::disk('public')->exists($gallery->image)) {
        //         Storage::disk('public')->delete($gallery->image);
        //     }

        //     $path = $request->file('image')->store('projects', 'public');
        //     $gallery->image = $path;
        // }

        $gallery->save();

        $message = $id ? __('Gallery updated successfully.') : __('Gallery created successfully.');

        if ($request->has('save_and_back')) {
            if ($request->ajax()) {
                return response()->json([
                    'redirect_url' => route('admin.gallery.list'),
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

            return to_route('admin.gallery.list')->withSuccess($message);
        }
    }

    public function edit($id)
    {
        goIfUserCan('save-gallery');

        $title = __('Edit Gallery');

        $gallery = Gallery::findOrFail($id);

        $galleryCategories = GalleryCategory::active()->get();

        return view('admin.gallery.form', compact('title', 'gallery', 'galleryCategories'));
    }

    public function delete($id)
    {
        goIfUserCan('delete-gallery');

        $gallery = Gallery::findOrFail($id);

        if ($gallery->image && Storage::disk('public')->exists($gallery->image)) {
            Storage::disk('public')->delete($gallery->image);
        }

        $gallery->delete();

        return back()->withSuccess(__('Gallery deleted'));
    }
}
