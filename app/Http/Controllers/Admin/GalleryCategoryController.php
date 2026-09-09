<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GalleryCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GalleryCategoryController extends Controller
{
    public function list()
    {
        $title = __('Gallery Categories');

        $galleryCategories = GalleryCategory::latest()->searching(['name'])->withCount('galleries')->paginate();

        return view('admin.gallery_category.list', compact('title', 'galleryCategories'));
    }

    public function create()
    {
        $title = __('Add New Gallery Category');

        return view('admin.gallery_category.form', compact('title'));
    }

    public function save(Request $request, $id = null)
    {
        $request->validate([
            'name'   => 'required|string|max:255|unique:gallery_categories,name,' . $id,
            'image'  => $id ? 'nullable|image|max:4096' : 'nullable|image|max:4096',
            'status' => 'required|in:0,1'
        ]);

        $galleryCategory              = $id ? GalleryCategory::findOrFail($id) : new GalleryCategory();
        $galleryCategory->name        = $request->name;
        $galleryCategory->status      = $request->status ?? 1;

        if ($request->hasFile('image')) {
            if ($id && $galleryCategory->image && Storage::disk('public')->exists($galleryCategory->image)) {
                Storage::disk('public')->delete($galleryCategory->image);
            }

            $path = $request->file('image')->store('projects', 'public');
            $galleryCategory->image = $path;
        }

        $galleryCategory->save();

        $message = $id ? __('Gallery category updated successfully.') : __('Gallery category created successfully.');

        if ($request->has('save_and_back')) {
            if ($request->ajax()) {
                return response()->json([
                    'redirect_url' => route('admin.gallery_category.list'),
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

            return to_route('admin.gallery_category.list')->withSuccess($message);
        }
    }

    public function edit($id)
    {
        $title = __('Edit Gallery Category');

        $galleryCategory = GalleryCategory::findOrFail($id);

        return view('admin.gallery_category.form', compact('title', 'galleryCategory'));
    }

    public function delete($id)
    {
        $galleryCategory = GalleryCategory::findOrFail($id);

        if ($galleryCategory->image && Storage::disk('public')->exists($galleryCategory->image)) {
            Storage::disk('public')->delete($galleryCategory->image);
        }

        $galleryCategory->delete();

        return back()->withSuccess(__('Gallery category deleted'));
    }
}
