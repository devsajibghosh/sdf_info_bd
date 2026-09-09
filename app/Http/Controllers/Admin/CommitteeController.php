<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Committee;
use App\Services\FileManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CommitteeController extends Controller
{
    public function list()
    {
        goIfUserCan('manage-committee');

        $title = __('Committees');

        $committees = Committee::latest()->searching(['name', 'body'])->paginate();

        return view('admin.committee.list', compact('title', 'committees'));
    }

    public function create()
    {
        goIfUserCan('manage-committee');

        $title = __('Add New Committee');

        return view('admin.committee.form', compact('title'));
    }

    public function save(Request $request, $id = null)
    {
        goIfUserCan('manage-committee');
        
        $request->validate([
           'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('committees', 'name')->ignore($id),
            ],
            'title' => 'nullable',
            'image'          => $id ? 'nullable|image|max:10000' : 'nullable|image|max:10000',
            'description'    => 'required|string',
        ]);

        $committe              = $id ? Committee::findOrFail($id) : new Committee();
        $committe->name        = $request->name; 
        $committe->title        = $request->title; 
        $committe->description = $request->description;
        
        if ($request->hasFile('image')) {
            $committe->image = FileManager::uploadPublic(
                $request->file('image'),
                filePath('committee'),
                $committe->image ?? null,
                handleResize('committee')
            );
        }

        $committe->save();

        $message = $id ? __('Committe updated successfully.') : __('Committe created successfully.');

        if ($request->has('save_and_back')) {
            if ($request->ajax()) {
                return response()->json([
                    'redirect_url' => route('admin.committee.list'),
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

            return to_route('admin.committee.list')->withSuccess($message);
        }
    }

    public function edit($id)
    {
        goIfUserCan('manage-committee');

        $title = __('Edit committe');

        $committee = Committee::findOrFail($id);

        return view('admin.committee.form', compact('title', 'committee'));
    }

    public function delete($id)
    {
        goIfUserCan('manage-committee');

        $committe = Committee::findOrFail($id);

        if ($committe->image && Storage::disk('public')->exists($committe->image)) {
            Storage::disk('public')->delete($committe->image);
        }

        $committe->delete();

        return back()->withSuccess(__('Committe Deleted'));
    }
}
