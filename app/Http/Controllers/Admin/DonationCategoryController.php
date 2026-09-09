<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DonationCategory;
use App\Services\FileManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DonationCategoryController extends Controller
{
    public function list()
    {
        goIfUserCan('view-donation');
        
        $title = __('Donation Categories');

        $donationCategories = DonationCategory::searching(['name', 'details'])->latest()->withSum('donations', 'amount')->paginate(10);

        return view('admin.donation_category.list', compact('title', 'donationCategories'));
    }

    public function create()
    {
        goIfUserCan('save-donation');
        
        $title = __('Add New Donation Category');

        return view('admin.donation_category.form', compact('title'));
    }

    public function save(Request $request, $id = null)
    {
        goIfUserCan('save-donation');
        
        $request->validate([
            'name'       => 'required|string|max:255|unique:donation_categories,name,' . $id,
            'image'      => $id ? 'nullable|image|max:4096' : 'nullable|image|max:4096',
            'short_desc' => 'nullable|max:255',
            'details'    => 'nullable|string',
            'status'     => 'required|in:0,1'
        ]);

        $donationCategory             = $id ? DonationCategory::findOrFail($id) : new DonationCategory();
        $donationCategory->name       = $request->name;
        $donationCategory->details    = $request->details;
        $donationCategory->short_desc = $request->short_desc;
        $donationCategory->status     = $request->status ?? 1;

        if ($request->hasFile('image')) {
            $resize = null;

            if (fileSizes('donationCategory')) {
                $sizeStr          = uploadImageSize('donationCategory');
                [$width, $height] = explode('x', $sizeStr);
                $resize           = ['width' => (int)$width, 'height' => (int)$height];
            }

            $donationCategory->image = FileManager::uploadPublic(
                $request->file('image'),
                filePath('donationCategory'),
                $donationCategory->image,
                $resize
            );
        }

        $donationCategory->admin_id = admin()->id;
        $donationCategory->save();

        $message = $id ? __('Donation category updated successfully.') : __('Donation category created successfully.');

        if ($request->has('save_and_back')) {
            if ($request->ajax()) {
                return response()->json([
                    'redirect_url' => route('admin.donation_category.list'),
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

            return to_route('admin.donation_category.list')->withSuccess($message);
        }
    }

    public function edit($id)
    {
        goIfUserCan('save-donation');
        
        $title = __('Edit Donation Category');

        $donationCategory = DonationCategory::findOrFail($id);

        return view('admin.donation_category.form', compact('title', 'donationCategory'));
    }

    public function delete($id)
    {
        goIfUserCan('delete-donation');
        
        $donationCategory = DonationCategory::findOrFail($id);

        if ($donationCategory->image && Storage::disk('public')->exists($donationCategory->image)) {
            Storage::disk('public')->delete($donationCategory->image);
        }

        $donationCategory->delete();

        return back()->withSuccess(__('Donation category deleted'));
    }
}
