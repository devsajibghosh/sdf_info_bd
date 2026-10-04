<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\UserDataTable;
use App\Helpers\BulkSmsHelper;
use App\Http\Controllers\Controller;
use App\Models\MemberCategory;
use App\Models\SMSLog;
use App\Models\User;
use App\Models\Contact;
use App\Services\FileManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function sendContactSMS(Request $request, $contactId)
    {
        goIfUserCan('manage-users');

        $request->validate([
            'message' => 'required|string|max:500',
        ]);

        $contact = Contact::findOrFail($contactId);


        $result = (new BulkSmsHelper())->send($contact->phone_number, $request->message);

        SMSLog::create([
            'contact_id' => $contact->id,
            'sms' => $request->message
        ]);

        if (empty($result['success'])) {
            Log::error('Contact SMS send failed', ['contact_id' => $contact->id, 'status' => $result['status'] ?? null, 'body' => $result['body'] ?? null]);

            return back()->withErrors(__('Failed to send SMS. Please check SMS configuration.'));
        }

        return back()->withSuccess(__('SMS successfully sent.'));
    }

    
    public function sendSMS(Request $request, $userId)
    {
        goIfUserCan('manage-users');

        $request->validate([
            'message' => 'required|string|max:500',
        ]);

        $user = User::findOrFail($userId);


        $result = (new BulkSmsHelper())->send($user->phone_number, $request->message);

        SMSLog::create([
            'user_id' => $userId,
            'sms' => $request->message
        ]);

        if (empty($result['success'])) {
            Log::error('User SMS send failed', ['user_id' => $userId, 'status' => $result['status'] ?? null, 'body' => $result['body'] ?? null]);

            return back()->withErrors(__('Failed to send SMS. Please check SMS configuration.'));
        }

        return back()->withSuccess(__('SMS successfully sent.'));
    }

    public function changeStatus(Request $request, $id)
    {
        goIfUserCan('save-users');

        $request->validate([
            'status' => ['required', Rule::in([0, 1, 2])],
        ]);

        $user = User::findOrFail($id);
        $user->status = $request->status;
        $user->save();

        $statusText = match ($request->status) {
            1 => __('User activated successfully.'),
            0 => __('User deactivated successfully.'),
            2 => __('User blocked successfully.'),
            default => __('Status updated.'),
        };

        return back()->withSuccess($statusText);
    }


    public function delete($id)
    {
        goIfUserCan('delete-users');

        $user = User::findOrFail($id);

        $user->delete();

        return back()->withSuccess(__('User deleted successfully'));
    }

    public function loginUser($id)
    {
        goIfUserCan('manage-users');

        Auth::loginUsingId($id);
        return to_route('user.dashboard');
    }
    
    



private function data($title = 'Users', $scope = null)
{
    goIfUserCan('view-users');

    $search = trim(request('search', ''));

    // Check if search text is masked number like: 0131xxxx053
    if ($search && preg_match('/^(\d{4})[xX\*]+(\d{3})$/', $search, $matches)) {

        $firstPart = $matches[1]; // 0131
        $lastPart  = $matches[2]; // 053

        $users = User::where('donator', 0)
            ->where('phone_number', 'like', $firstPart . '%')
            ->where('phone_number', 'like', '%' . $lastPart);

    } else {

        // Existing search logic
        $users = User::searching([
            'name',
            'first_name',
            'last_name',
            'email',
            'city',
            'father_name',
            'mother_name',
            'id_number',
            'ward',
            'phone_number',
            'district',
            'upazila',
            'division'
        ])->where('donator', 0);
    }

    // Apply scope if exists
    if ($scope) {
        $users->$scope();
    }

    // Category filter
    if (request()->filled('category_id')) {
        $users->where('category_id', request('category_id'));
    }

    $users = $users->latest()->paginate();

    return view('admin.users.list', compact('title', 'users'));
}





    
    

    public function list(UserDataTable $dataTable)
    {
        return $this->data();
    }
 
    public function inactive(){
        return $this->data('Inactive Members', 'inActive');
    }


    public function emailUnverified(UserDataTable $dataTable)
    {
        return $this->data('Email Unverified Users', 'emailUnverified');
    }

    public function mobileUnverified(UserDataTable $dataTable)
    {
        return $this->data('Phone Number Unverified Users', 'phoneNumberUnverified');
    }


    public function newUsers(UserDataTable $dataTable)
    {
        return $this->data('New Users', 'new');
    }

    public function new()
    {
        goIfUserCan('save-users');

        $title = 'Add New User';

        $categories = MemberCategory::get();

        return view('admin.users.form', compact('title', 'categories'));
    }


    public function edit($id)
    {
        goIfUserCan('save-users');

        $title = __('Edit User');

        $user = User::findOrFail($id);

        $categories = MemberCategory::get();
        
        return view('admin.users.form', compact('title', 'user', 'categories'));
    }

    public function details($id)
    {
        goIfUserCan('view-users');

        $title = 'User Details';

        $user = User::findOrFail($id);

        $widget = [
            'total_collection_amount' => $user->donations()->fieldCollection()->success()->sum('amount'),
            'total_collection_count' => $user->donations()->fieldCollection()->success()->count(),
            'last_collection_date'      => $user->donations()->fieldCollection()->success()->latest()->first()?->created_at ?? null,
            'last_collection_amount'    => $user->donations()->fieldCollection()->success()->latest()->first()?->amount ?? 0,
            'max_collection_date'       => $user->donations()->fieldCollection()->success()->orderBy('amount', 'desc')->first()?->created_at ?? null,
            'maximum_collection_amount' => $user->donations()->fieldCollection()->success()->orderBy('amount', 'desc')->first()?->amount ?? 0,
            
            'number_of_donations'     => $user->donations()->notFieldCollection()->success()->count(),
            'total_donation_amount'   => $user->donations()->notFieldCollection()->success()->sum('amount'),
            'last_donation_amount'    => $user->donations()->notFieldCollection()->success()->latest()->first()?->amount ?? 0,
            'maximum_donation_amount' => $user->donations()->notFieldCollection()->success()->orderBy('amount', 'desc')->first()?->amount ?? 0,
            'max_donation_date'       => $user->donations()->notFieldCollection()->success()->orderBy('amount', 'desc')->first()?->created_at ?? null,
            'last_donation_date'      => $user->donations()->notFieldCollection()->success()->latest()->first()?->created_at ?? null,
            // 
        ];

        return view('admin.users.details', compact('title', 'widget', 'user'));
    }

    public function save(Request $request, $id = null)
    {
        goIfUserCan('save-users');

        $joiningOptions = [
            'Existing Member',
            'Website',
            'Facebook Group',
            'Facebook Page',
            'YouTube',
            'WhatsApp',
            'Banner',
            'Leaflet',
            'Other',
        ];

        // --- 1. Define Validation Rules ---
        $rules = [
            // Account Details
            'username'           => ['nullable', 'string', 'max:100', Rule::unique('users')->ignore($id)],
            'email'              => ['nullable', 'email', 'max:191', Rule::unique('users')->ignore($id)],
            'password'           => [$id ? 'nullable' : 'required', 'confirmed', 'min:6'],
            'first_name'         => 'required|string|max:100',
            'last_name'          => 'required|string|max:100',
            'father_name'        => 'nullable|string|max:191',
            'mother_name'        => 'nullable|string|max:191',
            'date_of_birth'      => 'nullable|date|before_or_equal:today',
            'current_age'        => 'nullable|integer|min:0|max:150',
            'gender'             => ['nullable', Rule::in(['male', 'female', 'other'])],
            'blood_group'        => ['nullable', Rule::in(['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'])],
            'id_type'            => ['nullable', Rule::in(['nid', 'birth_certificate', 'passport'])],
            'id_number'          => 'nullable|string|max:50',
            'phone_number'       => ['nullable', 'string', 'max:20', Rule::unique('users')->ignore($id)],
            'whatsapp_number'    => 'nullable|string|max:20',
            'country'            => 'nullable|string|max:100', // Changed max to 100 to store full name
            'facebook_id_link'   => 'nullable|url|max:255',
            'image'              => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'occupation'         => ['nullable', 'string', Rule::in([
                'Teacher',
                'Doctor',
                'Engineer',
                'Farmer',
                'Businessman',
                'Student',
                'Government Employee',
                'Private Service',
                'Housewife',
                'Unemployed',
                'Other'
            ])],
            'marital_status'     => ['nullable', Rule::in(['Single', 'Married', 'Widowed', 'Separated'])],
            'division'           => 'nullable|string',
            'district'           => 'nullable|string',
            'upazila'            => 'nullable|string',
            'post_office'        => 'nullable|string|max:100',
            'ward'               => 'nullable|string|max:50',
            'joining_media'      => ['nullable', Rule::in($joiningOptions)],
            'reference'          => 'nullable|string|max:191|required_if:joining_media,Existing Member',
            'political_relation' => ['nullable', Rule::in([0, 1])],
            'post_and_politics'  => 'nullable|string|max:191|required_if:political_relation,yes',
            'monthly_fee'        => 'nullable|numeric|min:50',
        ];

        // --- 2. Validate the Request ---
        $validatedData = $request->validate($rules);

        // --- 3. Find or Create User Instance ---
        $user = $id ? User::findOrFail($id) : new User();

        // --- 4. Handle File Upload ---
        if ($request->hasFile('image')) {
            $validatedData['image_path'] = FileManager::uploadPublic(
                $request->file('image'),
                filePath('user'),
                $user->image_path ?? null, // Pass existing image path to delete it
                handleResize('user')
            );
        }

        // --- 5. Prepare Data for Saving ---

        // Hash password if it's provided
        if (!empty($validatedData['password'])) {
            $validatedData['password'] = Hash::make($validatedData['password']);
        } else {
            unset($validatedData['password']); // Avoid overwriting existing password with null
        }

        // Convert 'yes'/'no' to boolean/integer
        $validatedData['political_relation'] = $request->political_relation;

        $validatedData['pc'] = 1;

        // Unset fields that are not in the database table
        unset($validatedData['image']);
        unset($validatedData['commitment']);
        unset($validatedData['password_confirmation']);

        $user->admin_id = auth('admin')->id();
        $user->name = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
        $user->category_id = $request->category_id;
        $validatedData['name'] = $validatedData['first_name'] . ' ' . $validatedData['last_name'];
        $user->fill($validatedData);
        $user->save();

        $message = $id ? __('User updated successfully') : __('User created successfully');
        return redirect()->route('admin.user.list')->withSuccess($message);
    }
}
