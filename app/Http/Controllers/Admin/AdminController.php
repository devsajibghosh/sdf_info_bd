<?php

namespace App\Http\Controllers\Admin;

use App\Facades\System;
use App\Helpers\BulkSmsHelper;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\BlogPost;
use App\Models\CallLog;
use App\Models\Contact;
use App\Models\Donation;
use App\Models\DonationCategory;
use App\Models\Donor;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Gallery;
use App\Models\PageView;
use App\Models\Payment;
use App\Models\Project;
use App\Models\SiteVisit;
use App\Models\User;
use App\Services\FileManager;
use App\Services\FileService;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use PDO;

class AdminController extends Controller
{

    public function dashboard()
    {
        $title                               = __('Dashboard');
        $widget['total_expense_category']    = ExpenseCategory::count();
        $widget['total_expense']             = Expense::approved()->sum('amount');
        $widget['total_users']               = User::count();
        $widget['total_payment']             = Donation::success()->fieldCollection()->sum('amount');
        $widget['new_users']                 = User::new()->count();
        $widget['email_unverified_users']    = User::emailUnverified()->count();
        $widget['mobile_unverified_users']   = User::phoneNumberUnverified()->count();
        $widget['total_blog_posts']          = BlogPost::count();
        $widget['published_blog_posts']      = BlogPost::published()->count();
        $widget['total_projects']            = Project::count();
        $widget['total_donations']           = Donation::success()->sum('amount');
        $widget['pending_donations']         = Donation::pending()->sum('amount');
        $widget['total_donation_categories'] = DonationCategory::count();
        $widget['total_gallery_items']       = Gallery::active()->count();
        $widget['total_site_visits']         = PageView::sum('views');
        $widget['total_members']             = Admin::count();
        $widget['total_call_logs']           = CallLog::count();
        $widget['contact_submissions']       = Contact::count();
        $widget['total_donors']              = Donor::count();

        $pageViewDonut = PageView::orderBy('views', 'desc')
            ->take(5)
            ->get(['slug', 'views'])
            ->map(function ($item) {
                return [
                    'name' => $item->slug,
                    'value' => $item->views
                ];
            });
            
        $visitsByRegion = SiteVisit::select('region', 'country', DB::raw('COUNT(*) as value'))
            ->whereNotNull('region')
            ->groupBy('country', 'region')
            ->orderByDesc('value')
            ->get()
            ->map(function ($row) {
                return [
                    'name'  => $row->country . ' - ' . $row->region,
                    'value' => $row->value,
                ];
            });

        $pageViewChart = PageView::orderBy('views', 'desc')
            ->take(10)
            ->get(['slug', 'views']);

        $donationTotalsByCategory = DB::table('donations')
            ->select('donation_category_id', DB::raw('SUM(amount) as total'))
            ->groupBy('donation_category_id')
            ->get();

        $donationCategoryNames = DonationCategory::whereIn('id', $donationTotalsByCategory->pluck('donation_category_id'))
            ->pluck('name', 'id');

        $donationChart = $donationTotalsByCategory->map(function ($row) use ($donationCategoryNames) {
            return [
                'label' => $donationCategoryNames[$row->donation_category_id] ?? 'Category ' . $row->donation_category_id,
                'total' => $row->total,
            ];
        });

        $siteVisit = [
            'today'      => SiteVisit::whereDate('visit_date', Carbon::today())->count(),
            'this_week'  => SiteVisit::whereBetween('visit_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count(),
            'this_month' => SiteVisit::whereMonth('visit_date', Carbon::now()->month)
                                    ->whereYear('visit_date', Carbon::now()->year)->count(),
            'this_year'  => SiteVisit::whereYear('visit_date', Carbon::now()->year)->count(),
            'total'      => SiteVisit::count(),
        ];

            
        return view('admin.dashboard', compact('title', 'visitsByRegion', 'siteVisit', 'donationChart', 'pageViewChart', 'widget', 'pageViewDonut'));
    }

    public function serverInformation()
    {
        $title = __('Server Information');
        
        $phpInfo = [
            'Version'             => phpversion(),
            'Memory Limit'        => ini_get('memory_limit'),
            'Max Execution Time'  => ini_get('max_execution_time') . 's',
            'Upload Max Filesize' => ini_get('upload_max_filesize'),
            'Post Max Size'       => ini_get('post_max_size'),
            'Disabled Functions'  => ini_get('disable_functions') ?: 'None',
            'Timezone'            => date_default_timezone_get(),
            'GD Library'          => extension_loaded('gd') ? 'Enabled' : 'Disabled',
            'cURL'                => extension_loaded('curl') ? 'Enabled' : 'Disabled',
            'Mbstring'            => extension_loaded('mbstring') ? 'Enabled' : 'Disabled',
        ];

        $laravelInfo = [
            'Version'          => app()->version(),
            'Environment'      => config('app.env'),
            'Debug Mode'       => config('app.debug') ? 'Enabled' : 'Disabled',
            'App URL'          => config('app.url'),
            'Timezone'         => config('app.timezone'),
            'Cache Driver'     => config('cache.default'),
            'Session Driver'   => config('session.driver'),
            'Queue Connection' => config('queue.default'),
            'Maintenance Mode' => app()->isDownForMaintenance() ? 'Enabled' : 'Disabled',
        ];

        $dbInfo = [];
        try {
            $pdo = DB::connection()->getPdo();
            $dbDriver = DB::connection()->getDriverName();
            $dbInfo = [
                'Driver'            => $dbDriver,
                'Version'           => $pdo->getAttribute(PDO::ATTR_SERVER_VERSION),
                'Host'              => DB::connection()->getConfig('host'),
                'Port'              => DB::connection()->getConfig('port'),
                'Database Name'     => DB::connection()->getDatabaseName(),
                'Username'          => DB::connection()->getConfig('username'),
                'Connection Status' => 'Connected',
            ];
        } catch (\Exception $e) {
            $dbInfo['Error']  = 'Could not connect to database: ' . $e->getMessage();
            $dbInfo['Driver'] = config('database.default');
            $dbInfo['Host']   = config('database.connections.' . config('database.default') . '.host');
        }

        $serverEnvInfo = [
            'Server Software'   => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
            'Server OS'         => php_uname('s') . ' ' . php_uname('r'),
            'Server IP Address' => $_SERVER['SERVER_ADDR'] ?? gethostbyname(gethostname()),
            'Server Hostname'   => $_SERVER['SERVER_NAME'] ?? gethostname(),
            'Document Root'     => $_SERVER['DOCUMENT_ROOT'] ?? 'N/A',
            'PHP SAPI'          => php_sapi_name(),
            'OpenSSL Version'   => defined('OPENSSL_VERSION_TEXT') ? OPENSSL_VERSION_TEXT : (extension_loaded('openssl') ? 'Enabled (version not readable)' : 'Disabled'),
        ];

        if (function_exists('disk_free_space') && function_exists('disk_total_space')) {
            // $serverEnvInfo['Disk Free Space']  = bytesToHumanReadable(disk_free_space('/'));
            // $serverEnvInfo['Disk Total Space'] = bytesToHumanReadable(disk_total_space('/'));
        }


        $composerJsonPath = base_path('composer.json');
        $requiredPhpVersion = 'N/A';

        if (File::exists($composerJsonPath)) {
            $composerConfig = json_decode(File::get($composerJsonPath), true);
            $requiredPhpVersion = $composerConfig['require']['php'] ?? 'N/A';
        }

        $projectInfo = [
            'Application Name'                     => config('app.name'),
            'Project Root'                         => base_path(),
            'PHP Version Required (composer.json)' => $requiredPhpVersion,
            '.env File Exists'                     => File::exists(base_path('.env')) ? 'Yes' : 'No',
            'Storage Directory Writable'           => is_writable(storage_path()) ? 'Yes' : 'No',

            // 'Cache Directory Writable' => is_writable(bootstrap_path('cache')) ? 'Yes' : 'No',
            'Log File' => storage_path('logs/laravel.log') . (File::exists(storage_path('logs/laravel.log')) ? ' (Exists)' : ' (Not Found)'),
        ];


        $serverInformation = [
            'PHP Configuration'   => $phpInfo,
            'Laravel Application' => $laravelInfo,
            'Database'            => $dbInfo,
            'Server Environment'  => $serverEnvInfo,
            'Project Details'     => $projectInfo,
        ];

        return view('admin.setting.server_information', compact('title', 'serverInformation'));
    }

    public function profile()
    {
        $title = 'Profile';

        $admin = admin();

        return view('admin.setting.profile', compact('title', 'admin'));
    }

    public function password()
    {
        $title = 'Password';

        $admin = admin();

        return view('admin.setting.password', compact('title', 'admin'));
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'password'     => 'required|confirmed'
        ]);

        if (!Hash::check($request->old_password, admin()->password)) {
            return back()->withError(__('Old password does not match'));
        }

        $admin           = admin();
        $admin->password = Hash::make($request->password);
        $admin->save();

        return back()->withSuccess(__('Password updated successfully'));
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'name'  => 'required|max:255',
            'email' => 'required|email|max:255|unique:admins,email,' . auth('admin')->id(),
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        $admin = auth('admin')->user();
        $admin->name = $request->name;
        $admin->email = $request->email;

        if ($request->hasFile('image')) {
            $admin->image = FileManager::uploadPublic(
                $request->file('image'),
                filePath('adminProfile'),
                $admin->image ?? null,
                handleResize('adminProfile')
            );
        }

        $admin->save();

        return back()->withSuccess(__('Profile updated successfully'));
    }

    public function generalSetting()
    {
        goIfUserCan('settings');
        $title = 'General Setting';

        $generalSetting = generalSetting();

        return view('admin.setting.general', compact('title', 'generalSetting'));
    }

    public function notificationSetting()
    {
        goIfUserCan('settings');
        $title = __('Notifications Settings');

        $generalSetting = generalSetting();

        return view('admin.setting.notification', compact('title', 'generalSetting'));
    }

    public function configurationSetting()
    {
        goIfUserCan('settings');
        $title = 'Configuration Settting';

        $generalSetting = generalSetting();

        return view('admin.setting.configuration', compact('title', 'generalSetting'));
    }

    public function updateConfigurationSetting(Request $request)
    {
        goIfUserCan('settings');
        try {
            $request->validate([
                'google_recaptcha_enabled' => 'sometimes',
                'user_registration'        => 'sometimes',
                'kyc'                      => 'sometimes',
                'maintenance_mode'         => 'sometimes',
            ]);
        } catch (ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'message' => 'Validation error',
                    'errors'  => $e->errors(),
                    'success' => false
                ], 422);
            }

            throw $e;
        }

        $generalSetting                           = generalSetting();
        $generalSetting->google_recaptcha_enabled = $request->boolean('google_recaptcha_enabled');
        $generalSetting->kyc                      = $request->boolean('kyc');
        $generalSetting->user_registration        = $request->boolean('user_registration');
        $generalSetting->maintenance_mode         = $request->boolean('maintenance_mode');
        $generalSetting->save();

        System::clearCache();

        if ($request->ajax()) {
            return response()->json([
                'message' => __('Configuration saved successfully'),
                'success' => true
            ]);
        }

        return back()->withSuccess(__('Confiugration saved successfully'));
    }

    public function updateGeneralSetting(Request $request, FileService $fileService)
    {
        goIfUserCan('settings');
        try {
            $validated = $request->validate([
                'site_title'       => 'required|string|max:255',
                'site_description' => 'nullable|string|max:1000',
                'site_email'       => 'nullable|email|max:255',
                'site_phone'       => 'nullable|string|max:50',
                'base_color'       => 'required',
                'app_url'          => 'required|string|max:255',
                'site_logo'        => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'site_favicon'     => 'nullable|image|mimes:jpeg,png,jpg,ico|max:512',
                'debug_mode'       => 'required|in:0,1',
                'otp_time'         => 'required|numeric|min:30',
                'currency'         => 'required|in:' . collect(System::currencies())->pluck('code')->implode(','),
                'timezone'         => 'required|in:' . implode(',', DateTimeZone::listIdentifiers())
            ]);
        } catch (ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'errors' => $e->errors(),
                    'message' => 'Validation failed',
                    'success' => false,
                ], 422);
            }

            throw $e;
        }

        $setting = \App\Models\GeneralSetting::first();

        if (!$setting) {
            $setting = new \App\Models\GeneralSetting();
        }

        foreach ($validated as $key => $value) {
            $setting->$key = $value;
        }

        if ($request->hasFile('site_logo')) {
            $setting->site_logo = FileManager::uploadPublic(
                $request->file('site_logo'),
                '/logo-icon',
                generalSetting('site_logo')
            );
        }

        if ($request->hasFile('site_favicon')) {
            $setting->site_favicon = FileManager::uploadPublic(
                $request->file('site_favicon'),
                '/logo-icon',
                generalSetting('site_favicon')
            );
        }

        $setting->save();

        System::clearCache();

        if ($request->ajax()) {
            return response()->json([
                'message' => __('Settings saved successfully'),
                'success' => true
            ]);
        }

        return back()->withSuccess(__('General settings updated successfully.'));
    }

    public function updateNotificationSetting(Request $request, FileService $fileService)
    {
        goIfUserCan('settings');
        try {
            $validated = $request->validate([
                'sms_sender_id'     => 'nullable|string|max:255',
                'sms_api_key'       => 'nullable|string|max:255',
                'mail_host'         => 'nullable|string|max:255',
                'mail_port'         => 'nullable|string|max:255',
                'mail_username'     => 'nullable|string|max:255',
                'mail_password'     => 'nullable|string|max:255',
                'mail_encryption'   => 'nullable|string|max:255',
                'mail_from_address' => 'required|string|max:255',
                'mail_from_name'    => 'required|string|max:255',
            ]);
        } catch (ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'errors' => $e->errors(),
                    'message' => 'Validation failed',
                    'success' => false,
                ], 422);
            }

            throw $e;
        }

        $setting = \App\Models\GeneralSetting::first();

        if (!$setting) {
            $setting = new \App\Models\GeneralSetting();
        }

        foreach ($validated as $key => $value) {
            $setting->$key = $value;
        }

        $setting->save();

        System::clearCache();

        if ($request->ajax()) {
            return response()->json([
                'message' => __('Notification saved successfully'),
                'success' => true
            ]);
        }

        return back()->withSuccess(__('Notification settings updated successfully.'));
    }

    public function sendTestMail(Request $request)
    {
        goIfUserCan('settings');
        try {
            $validated = $request->validate([
                'test_email'     => 'required|email|max:255',
            ]);
        } catch (ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'errors' => $e->errors(),
                    'message' => 'Validation failed',
                    'success' => false,
                ], 422);
            }

            throw $e;
        }

        try {
            Mail::raw('This is a test mail from your system.', function ($message) use ($validated) {
                $message->to($validated['test_email'])
                    ->subject('Test Email from ' . config('app.name'));
            });

            if ($request->ajax()) {
                return response()->json([
                    'message' => 'Test email sent successfully!',
                    'success' => true
                ]);
            }

            return back()->withSuccess('Test email sent successfully!');
        } catch (\Exception $e) {
            Log::error('Test mail failed: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json([
                    'message' => 'Failed to send test email. Please check mail configuration.',
                    'success' => false,
                ], 500);
            }

            return back()->withErrors('Failed to send test email: ' . $e->getMessage());
        }
    }

    public function sendTestSMS(Request $request)
    {
        goIfUserCan('settings');
        try {
            $request->validate([
                'phone_number' => 'required|string|max:255',
                'message'      => 'required|string|max:255',
            ]);
        } catch (ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'errors' => $e->errors(),
                    'message' => 'Validation failed',
                    'success' => false,
                ], 422);
            }

            throw $e;
        }

        try {
            (new BulkSmsHelper())->send($request->phone_number, $request->message);

            if ($request->ajax()) {
                return response()->json([
                    'message' => 'Test SMS sent successfully!',
                    'success' => true
                ]);
            }

            return back()->withSuccess('Test SMS sent successfully!');
        } catch (\Exception $e) {
            Log::error('Test SMS failed: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json([
                    'message' => 'Failed to send test SMS. Please check SMS configuration.',
                    'success' => false,
                ], 500);
            }

            return back()->withErrors('Failed to send test SMS: ' . $e->getMessage());
        }
    }
}
