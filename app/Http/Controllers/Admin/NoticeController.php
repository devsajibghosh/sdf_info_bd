<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use Illuminate\Http\Request;

class NoticeController extends Controller
{
    public function list()
    {
        goIfUserCan('manage-notice');
        $title = __('Notices');
        $notices = Notice::latest()->searching(['title', 'description'])->paginate(15);
        return view('admin.notices.list', compact('title', 'notices'));
    }

    public function store(Request $request, $id = null)
    {
        goIfUserCan('manage-notice');
        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'status'       => 'required|boolean',
            'visible_from' => 'nullable|date',
            'visible_to'   => 'nullable|date|after_or_equal:visible_from',
        ]);

        $notice = $id ? Notice::findOrFail($id) : new Notice();
        $notice->fill($validated);
        $notice->save();

        return to_route('admin.notice.list')->withSuccess(__('Notice saved successfully.'));
    }

    public function delete($id)
    {
        goIfUserCan('manage-notice');
        $notice = Notice::findOrFail($id);
        $notice->delete();

        return to_route('admin.notice.list')->withSuccess(__('Notice deleted successfully.'));
    }
}
