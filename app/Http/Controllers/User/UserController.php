<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\FileManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\User;

class UserController extends Controller
{
    public function committeeDetail($id) 
    {        
        $title = __('Committee Details');
      
        $committee = \App\Models\Committee::findOrFail($id); 
        
        return view('user.committee', compact('title', 'committee'));
    }
    
    public function committees(Request $request)
    {
        $title = __('Committees');
      
        $committees = \App\Models\Committee::latest()->paginate(); 
        
        return view('user.committees', compact('title', 'committees'));
    }
    

    public function notices(Request $request)
    {
        $title = __('Notices');
        $divisions = User::whereNotNull('division')->distinct()->pluck('division');
    
    
        $locations = [];
        $allUsersLocations = User::select('division', 'district', 'upazila')
                        ->whereNotNull('division')
                        ->whereNotNull('district')
                        ->whereNotNull('upazila')
                        ->distinct()
                        ->get();
    
        foreach ($allUsersLocations as $user) {
            $locations[$user->division][$user->district][] = $user->upazila;
        }
        
        foreach ($locations as $division => &$districts) {
            foreach ($districts as $district => &$upazilas) {
                $upazilas = array_values(array_unique($upazilas));
            }
        }
        
        
        
        $query = User::query();
    
        if ($request->filled('division')) {
            $query->where('division', $request->division);
        }
        if ($request->filled('district')) {
            $query->where('district', $request->district);
        }
        if ($request->filled('upazila')) {
            $query->where('upazila', $request->upazila);
        }
    
        $widget = [
            'count' => $query->count()
        ];
        
        $members = $query->latest()->paginate(20);

        $notices = \App\Models\Notice::active()->latest()->get(); 
        
        return view('user.notices', compact('title', 'notices', 'members', 'divisions', 'locations', 'widget'));
    }
    
    public function donorDonations() 
    {
        $title = __('My Donations');

        $donations = auth()->guard('donor')->user()->donations()->paginate();
        
        return view('user.donor_donations', compact('title', 'donations'));
    }
    
    public function profileComplete()
    {
        // if(auth()->user()->pc) {
        //     return to_route('user.dashboard')->withInfo(__('Your profile already completed'));
        // }
        
        $title = __('Profile Complete');

        $user = auth()->user();

        return view('user.profile_data', compact('title', 'user'));
    }

    public function saveCompleteProfile(Request $request)
    {
        // if(auth()->user()->pc) {
        //     return to_route('user.dashboard')->withInfo(__('Your profile already completed'));
        // }
        
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
            'political_relation' => ['required', Rule::in(['0', '1'])],
            'post_and_politics'  => 'nullable|string|max:191|required_if:political_relation,1',
            'division'           => 'required|string',
            'district'           => 'required|string',
            'upazila'            => 'required|string',
            'post_office'        => 'required|string|max:100',
            'ward'               => 'nullable|string|max:50',
            'image'              => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10000',
            'nid_front'          => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10000',
            'nid_back'           => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10000',
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
        // $validatedData['political_relation'] = $validatedData['political_relation'] == 'yes' ? 1 : 0;

        $user->update($validatedData);
        $user->pc = 1;
        $user->save();
        
        if($user->status != 1) {
            Auth::guard('web')->logout();
            return to_route('login')->withErrors(__('Please wait for admin approval'));
        }

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
            'father_name'  => 'nullable|string|max:255',
            'mother_name'  => 'nullable|string|max:255',
            'address'      => 'nullable|string|max:500',
        ]);

        $donor->update([
            'name'         => $request->name, 
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
        $title = __('User Dashboard');
        $user = auth()->user() ?? auth()->guard('donor')->user();
        $widget = [
            'total_collection_count'         => $user->donations()->fieldCollection()->success()->count(),
            'total_collection_donate_amount' => $user->donations()->success()->fieldCollection()->sum('amount'),
            'total_donate_count'             => $user->donations()->notFieldCollection()->success()->count(),
            'total_donate_amount'            => $user->donations()->notFieldCollection()->success()->sum('amount'),
            
            'last_donate_amount'             => $user->donations()->notFieldCollection()->success()->latest()->first()?->amount ?? 0,
            'last_donate_date'               => $user->donations()->notFieldCollection()->success()->latest()->first()?->created_at ?? null,
            'last_collected_amount'          => $user->donations()->success()->latest()->fieldCollection()->first()?->amount ?? 0,
            'last_collected_date'            => $user->donations()->success()->latest()->fieldCollection()->first()?->created_at ?? null
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

        ini_set('memory_limit','2024M');
        ini_set('post_max_size','2024M');
        ini_set('upload_max_filesize','2024M');
        ini_set('max_input_time', 36000); // 10 houres
        set_time_limit(36000); // 10 houres

        $joiningOptions = [
            'Existing Member', 'Website', 'Facebook Group', 'Facebook Page', 'YouTube',
            'WhatsApp', 'Banner', 'Leaflet', 'Other',
        ];

        $occupationOptions = [
            'Actor', 'Assistant Professor', 'Associate Professor', 'Bank Job', 'Barber',
            'College Teacher', 'Driver', 'Doctor', 'Engineer', 'Expart', 'Farmer',
            'Garments', 'Goverment Job', 'High School Teacher', 'Health Services', 'House Wife',
            'Journalists', 'Lawyer', 'Lecturer', 'Military', 'NGO Job', 'Pharmaceutical Job',
            'Primary School Teacher', 'Privet Job', 'Professor', 'Police', 'Politician',
            'Retirement', 'Self Business', 'Social worker', 'Student', 'Tailor',
            'Telecommunication Job', 'Others',
        ];



        $rules = [
            // Personal Details
            'first_name'    => 'required|string|max:100',
            'last_name'     => 'required|string|max:100',
            'father_name'   => 'nullable|string|max:191',
            'mother_name'   => 'nullable|string|max:191',
            'date_of_birth' => 'nullable|date|before_or_equal:today',
            'preference'         => 'nullable|string',
            'current_age'   => 'nullable|integer|min:0|max:150',
            'gender'        => ['nullable', Rule::in(['male', 'female', 'other'])],
            'blood_group'   => ['nullable', Rule::in(['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'])],

            // Identification & Contact
            'id_type'       => ['nullable', Rule::in(['nid', 'birth_certificate', 'passport'])],
            'id_number'     => 'nullable|string|max:50',
            'whatsapp_number'    => 'nullable|string|max:20',
            'facebook_id_link'   => 'nullable|url|max:255',
            'image'              => 'nullable|image|mimes:png,jpg,jpeg|max:10000',
            'nid_front'          => 'nullable|image|mimes:png,jpg,jpeg|max:10000',
            'nid_back'           => 'nullable|image|mimes:png,jpg,jpeg|max:10000',

            // Address Details
            'country'            => 'required|string|max:100',
            'division'           => 'required|string',
            'district'           => 'required|string',
            'upazila'            => 'required|string',
            'preference'         => 'nullable|string',
            'post_office'        => 'required|string|max:100',
            'ward'               => 'nullable|string|max:50',
            'present_address'    => 'nullable|string|max:255',

            // Professional & Marital Status
            'occupation'         => ['required', 'string', Rule::in($occupationOptions)],
            'marital_status'     => ['required', Rule::in(['Single', 'Married', 'Widowed', 'Separated'])],

            // Additional Information
            'preference'         => 'required|string|max:255',
            'joining_media'      => ['required', Rule::in($joiningOptions)],
            'reference'          => ['nullable', 'string', 'max:191', 'required_if:joining_media,Existing Member'],
            'political_relation' => ['required', Rule::in(['1', '0'])],
            'post_and_politics'  => 'nullable|string|max:191|required_if:political_relation,1',
            'monthly_fee'        => 'required|numeric|min:50',

            // Commitment & Password
            // 'commitment'         => 'accepted',
            'password'           => 'nullable|string|min:8|confirmed',
        ];

        $validatedData = $request->validate($rules);

        // Handle password update separately
        if (!empty($validatedData['password'])) {
            $user->password = Hash::make($validatedData['password']);
        }

        // Handle image upload
        if ($request->hasFile('image')) { 
            $validatedData['image_path'] = FileManager::uploadPublic(
                $request->file('image'),
                filePath('user'),
                $user->image_path,
                handleResize('user')
            );  
        }

        // Handle NID front image upload
        if ($request->hasFile('nid_front')) {
            $validatedData['nid_front'] = FileManager::uploadPublic(
                $request->file('nid_front'),
                filePath('nid'),
                $user->nid_front,
                handleResize('nid')
            );
        }

        // Handle NID back image upload
        if ($request->hasFile('nid_back')) {
            $validatedData['nid_back'] = FileManager::uploadPublic(
                $request->file('nid_back'),
                filePath('nid'),
                $user->nid_back,
                handleResize('nid')
            );
        }

        // Update user's full name
        $user->name = $validatedData['first_name'] . ' ' . $validatedData['last_name'];
        
        // Transform political relation data
        $validatedData['political_relation'] = $validatedData['political_relation'];

        // Unset data that should not be mass-assigned
        unset(
            $validatedData['password'],
            $validatedData['password_confirmation'],
            $validatedData['commitment'],
            $validatedData['image'] // Unset original image field if using 'image_path'
        );

        // Fill the user model with the rest of the validated data
        $user->fill($validatedData);
        
        // Save all changes to the user model
        $user->save();

        return back()->withSuccess(__('Profile updated successfully.'));
    }
}
