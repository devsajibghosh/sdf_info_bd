<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Silber\Bouncer\Database\Role;
use Silber\Bouncer\BouncerFacade as Bouncer;

class ManageMemberController extends Controller
{
    public function __construct()
    {
        viewShare('admin.member.form', ['roles' => Role::get()]);
    }

    public function list()
    {
        goIfUserCan('view-members');
        
        $title = 'Manage Memebers';

        $members = Admin::searching(['name', 'email', 'username'])->paginate();

        return view('admin.members.list', compact('title', 'members'));
    }

    public function create()
    {
        goIfUserCan('save-members');
        
        $title = 'Add New Member';

        return view('admin.member.form', compact('title'));
    }

    public function store(Request $request, $id = null)
    {
        goIfUserCan('save-members');
        
        $request->validate([
            'name'         => 'required',
            'email'        => 'required|email|unique:admins,email,' . $id,
            'username'     => 'required|unique:admins,username,' . $id,
            'password'     => 'sometimes',
            'phone_number' => 'required|unique:admins,phone_number,' . $id,
        ]);

        $admin               = !$id ? new Admin() : Admin::findOrFail($id);
        $admin->name         = $request->name;
        $admin->username     = $request->username;
        $admin->email        = $request->email;
        $admin->phone_number = $request->phone_number;

        if ($request->has('password')) {
            $admin->password = Hash::make($request->password);
        }

        $admin->save();

        // manage role here 
        $role = Role::find($request->role_id);

        if ($role) {
            Bouncer::sync($admin)->roles([$role->name]);
        }

        return to_route('admin.member.list')->withSuccess(__('Member Saved Successfully'));
    }

    public function edit($id)
    {
        goIfUserCan('save-members');
        
        $title = __('Edit Member');

        $member = Admin::findOrFail($id);

        return view('admin.member.form', compact('title', 'member'));
    }

    public function delete($id)
    {
        goIfUserCan('delete-members');
        
        abort_if($id == 1, 403, __('You cannot remove this item'));
        
        $member = Admin::findOrFail($id);
        
        $member->delete();

        return to_route('admin.member.list')->withSuccess(__('Member Deleted Successfully'));
    }
}
