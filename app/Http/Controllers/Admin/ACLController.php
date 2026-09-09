<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\AbilitiesGenerator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Silber\Bouncer\Database\Ability;
use Silber\Bouncer\Database\Role;
use Illuminate\Support\Str;

class ACLController extends Controller
{
    private $formView = 'admin.acl.role.form';

    public function __construct()
    {
        viewShare($this->formView, ['abilities' => Ability::get()]);
    }

    public function roles()
    {
        goIfUserCan('view-acl');
        
        $title = __('Roles');
        $roles = Role::paginate();

        return view('admin.acl.role.list', compact('title', 'roles'));
    }

    public function createRole()
    {
        goIfUserCan('save-acl');

        $title = __('Create Role');

        return view($this->formView, compact('title'));
    }

    public function storeRole(Request $request, $id = null)
    {
        goIfUserCan('save-acl');

        $request->validate([
            'name'        => 'required|alpha_dash|unique:roles,name,' . $id,
            'title'       => 'required|string|max:255|unique:roles,title,' . $id,
            'abilities'   => 'nullable|array',
            'abilities.*' => 'exists:abilities,id',
        ]);

        $role        = !$id ? new Role() : Role::findOrFail($id);

        if ($id && $role->name === 'super-admin') { 
            if (
                $request->input('name') !== $role->name ||
                $request->input('title') !== $role->title
            ) {
                return back()->withError(__('Sorry, you cannot modify Super Admin role name or title'));
            }
        }
        
        $role->name  = Str::slug($request->name);                  // e.g., editor, super-admin
        $role->title = $request->title;
        $role->save();

        $abilityIds = $request->input('abilities', []);
        $role->abilities()->sync($abilityIds);

        return to_route('admin.acl.role.list')->withSuccess(__('Role Saved Successfully'));
    }

    public function editRole($id)
    {
        goIfUserCan('save-acl');

        $title = __('Edit Role');
        $role  = Role::with('abilities')->findOrFail($id);

        return view($this->formView, compact('title', 'role'));
    }

    public function deleteRole(Request $request, $id)
    {
        goIfUserCan('delete-acl');

        $role = Role::findOrFail($id);

        if($role->name == 'super-admin') {
            return back()->withError(__('You cannot delete this role'));
        }
        
        $role->delete();

        return to_route('admin.acl.role.list')->withSuccess(__('Role Deleted Successfully'));
    }

    public function genreateAbilities()
    {
        goIfUserCan('save-acl');

        AbilitiesGenerator::generate();

        return back()->withSuccess(__('Abilities Generated'));
    }
}
