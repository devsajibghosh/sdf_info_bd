<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\FileManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function profileComplete()
    {
        $title = __('Profile Complete');

        $user = auth()->user();

        return view('user.profile_data', compact('title', 'user'));
    }

    public function saveCompleteProfile(Request $request)
    {
        $user = auth()->user();

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

        $rules = [
            'first_name'    => 'required|string|max:100',
            'last_name'     => 'required|string|max:100',
            'father_name'   => 'required|string|max:191',
            'mother_name'   => 'required|string|max:191',
            'date_of_birth' => 'required|date|before_or_equal:today',
            'current_age'   => 'required|integer|min:0|max:150',
            'id_type'       => ['required', Rule::in(['nid', 'birth_certificate', 'passport'])],
            'id_number'     => 'required|string|max:50',
            'gender'        => ['required', Rule::in(['male', 'female', 'other'])],
            'blood_group'   => ['nullable', Rule::in(['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'])],
            'present_address' => 'nullable',
            'occupation'    => ['required', 'string', Rule::in([
                'Actor',
                'Assistant Professor',
                'Associate Professor',
                'Bank Job',
                'Barber',
                'College Teacher',
                'Driver',
                'Doctor',
                'Engineer',
                'Expart',
                'Farmer',
                'Garments',
                'Goverment Job',
                'High School Teacher',
                'Health Services',
                'House Wife',
                'Journalists',
                'Lawyer',
                'Lecturer',
                'Military',
                'NGO Job',
                'Pharmaceutical Job',
                'Primary School Teacher',
                'Privet Job',
                'Professor',
                'Police',
                'Politician',
                'Retirement',
                'Self Business',
                'Social worker',
                'Student',
                'Tailor',
                'Telecommunication Job',
                'Others',
            ])],
            'marital_status'     => ['required', Rule::in(['Single', 'Married', 'Widowed', 'Separated'])],
            'country'            => 'required|string|max:2',
            // 'phone_number'       => 'required|string|max:20',
            'whatsapp_number'    => 'nullable|string|max:20',
            'facebook_id_link'   => 'nullable|url|max:255',
            'joining_media'      => ['required', Rule::in($joiningOptions)],
            'reference'     => [
                'nullable', // default
                'string',
                'max:191',
                'required_if:joining_media,Existing Member',
            ],
            'political_relation' => ['required', Rule::in(['yes', 'no'])],
            'post_and_politics'  => 'nullable|string|max:191|required_if:political_relation,yes',
            'division'           => 'required|string',
            'district'           => 'required|string',
            'upazila'            => 'required|string',
            'post_office'        => 'required|string|max:100',
            'ward'               => 'nullable|string|max:50',
            'image'              => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'nid_front'          => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'nid_back'           => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'commitment'         => 'accepted',
            'monthly_fee'        => 'required|numeric|min:50',
            'preference'         => 'nullable|max:255'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $validatedData = $validator->validated();

        // Handle image upload
        if ($request->hasFile('image')) {
            $validatedData['image']  = FileManager::uploadPublic(
                $request->file('image'),
                filePath('user'),
                $user->image ?? null,
                handleResize('user')
            );
        } else {

            unset($validatedData['commitment']);
        }

        if ($request->hasFile('nid_front')) {
            $validatedData['nid_front']  = FileManager::uploadPublic(
                $request->file('nid_front'),
                filePath('nid'),
                $user->nid_front ?? null,
                handleResize('nid')
            );
        }

        if ($request->hasFile('nid_back')) {
            $validatedData['nid_back']  = FileManager::uploadPublic(
                $request->file('nid_back'),
                filePath('nid'),
                $user->nid_back ?? null,
                handleResize('nid')
            );
        }

        // Remove fields not in users table
        // dd($validatedData);
        // unset($validatedData['image']);
        $validatedData['political_relation'] = $validatedData['political_relation'] == 'yes' ? 1 : 0;

        $user->update($validatedData);

        $user->pc = 1;
        $user->save();

        return to_route('user.dashboard')->withSuccess(__('Profile completed successfully.'));
    }

    public function logoutDonorUser() {
        Auth::guard('donor')->logout();
        return back()->withSuccess(__('You are logged out'));
    }
    
    public function donorProfile()
    {
        $title = 'Donor Profile';
        $donor = auth()->guard('donor')->user();
        return view('user.donor_profile', compact('title', 'donor'));
    }

    public function donorProfileSubmit(Request $request)
    {
        $donor = auth()->guard('donor')->user();

        $request->validate([
            'name'         => 'required|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'father_name'  => 'nullable|string|max:255',
            'mother_name'  => 'nullable|string|max:255',
            'address'      => 'nullable|string|max:500',
        ]);

        $donor->update([
            'name'         => $request->name,
            'phone_number' => $request->phone_number,
            'father_name'  => $request->father_name,
            'mother_name'  => $request->mother_name,
            'address'      => $request->address,
        ]);

        return redirect()
            ->back()
            ->with('success', __('Profile updated successfully.'));
    }


    public function dashboard()
    {
        $title = 'User Dashboard';
        $user = auth()->user() ?? auth()->guard('donor')->user();
        $widget = [
            'total_donate_count'  => $user->donations()->count(),
            'total_donate_amount' => $user->donations()->sum('amount'),
            'last_donate_amount'  => $user->donations()->latest()->first()?->amount ?? 0,
            'last_donate_date'    => $user->donations()->latest()->first()->created_at ?? null
        ];
        return view('user.dashboard', compact('title', 'widget'));
    }

    public function profile()
    {
        $title = 'Profile';

        $user = auth()->user();

        return view('user.setting.profile', compact('title', 'user'));
    }

    public function saveProfile(Request $request)
    {
        $user = auth()->user();
        $isDonator = $user->donator;

        $validated = $request->validate([
            'phone_number'  => ['required', 'max:255', Rule::unique('users')->ignore($user->id)],
            'first_name'    => 'required|string|max:120',
            'last_name'     => 'required|string|max:120',
            'father_name'   => 'required|string|max:255',
            'mother_name'   => 'required|string|max:255',
            'date_of_birth' => 'required|date|before_or_equal:today',
            'current_age'   => 'required|integer|min:0',
            'gender'        => 'required|string|in:male,female,other',
            'blood_group'   => 'nullable|string|max:10',

            // Identification & Contact
            'id_type'        => 'required|string|in:nid,birth_certificate,passport',
            'id_number'      => 'required|string|max:50',
            'whatsapp_number' => 'nullable|string|max:25',
            'facebook_id_link' => 'nullable|url|max:255',

            // Address Details
            'country'        => 'required|string|max:100',
            'division'       => 'required|string|max:100',
            'district'       => 'required|string|max:100',
            'upazila'        => 'required|string|max:100',
            'post_office'    => 'required|string|max:100',
            'ward'           => 'required|string|max:100',

            // Professional & Marital Status
            'occupation'     => 'required|string|max:100',
            'marital_status' => 'required|string|in:Single,Married,Widowed,Separated',

            // Password
            'password'       => 'nullable|string|min:8|confirmed',

            // Profile Picture
            'image'          => 'nullable|image|mimes:jpeg,png,jpg,gif|max:800', // max 800KB
        ]);

        // Update user's full name
        $user->name = $validated['first_name'] . ' ' . $validated['last_name'];

        // Handle password update
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        unset($validated['password']);

        if ($request->hasFile('image')) {
            $validated['image_path'] = FileManager::uploadPublic(
                $request->file('image'),
                filePath('user'),
                $user->image_path,
                handleResize('user') // Assumes a helper for resize dimensions
            );
        }

        $user->fill($validated);
        $user->save();

        return back()->withSuccess(__('Profile updated successfully.'));
    }
}
