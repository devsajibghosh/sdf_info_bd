<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MemberCategory;
use Illuminate\Http\Request; 

class MemberCategoryController extends Controller
{
    public function list(){
        $title = __('Member Categories');
        $categories = MemberCategory::searching(['name'])->withCount('users')->paginate();
        return view('admin.member.category.list', compact('categories'));
    }

    public function delete(Request $request, $id) 
    {
        $cat = MemberCategory::findOrFail($id);
        $cat->delete();
        return back()->withSuccess(__('Member category deleted successfully'));
    }
    
    public function store(Request $request, $id = null)
    {
        $request->validate([
            'name' => 'required|unique:member_categories,name,' . $id,
        ]);

        $category       = !$id ? new MemberCategory() : MemberCategory::findOrFail($id);
        $category->name = $request->name;
        $category->save();

        return to_route('admin.user.category.list')->withSuccess(__('Category Saved Successfully'));
    }
}
