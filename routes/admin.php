<?php

use Illuminate\Support\Facades\Route;
use App\Helpers\RoutesHelper;

Route::middleware(['admin.guest'])->controller('Auth\LoginController')->group(function () {
    Route::get('/login', 'showLoginForm')->name('login');
    Route::post('/login', 'login');

    Route::get('/forgot-password', 'showForgotPasswordForm')->name('password.forgot');
    Route::post('/forgot-password', 'sendResetLink')->name('password.email');

    Route::get('/set-password', 'showSetPasswordForm')->name('password.set');
    Route::post('/set-password', 'setPassword')->name('password.update');
});

// Second factor: reachable only while "password verified, OTP pending" —
// admin.guest still applies since Auth::guard('admin')->check() stays false
// until the OTP step succeeds, so an already-authenticated admin is bounced
// to the dashboard same as it would be from /login.
Route::middleware(['admin.guest'])->controller('Auth\OtpController')->group(function () {
    Route::get('/login/otp', 'show')->name('login.otp');
    Route::post('/login/otp/verify', 'verify')->name('login.otp.verify')->middleware('throttle:10,1');
    Route::post('/login/otp/resend', 'resend')->name('login.otp.resend')->middleware('throttle:6,1');
});

Route::middleware(['admin'])->controller('Auth\LoginController')->group(function () {
    Route::get('/logout', 'logout')->name('logout');
});

RoutesHelper::registerAdminRoutes(function () {
    Route::controller('DonorController')->name('donor.')->prefix('donors')->group(function() {
        Route::get('/', 'list')->name('list');
        Route::get('/edit/{id}', 'edit')->name('edit');
        Route::post('/edit/{id?}/save', 'save')->name('save');
    });
    
    Route::controller('AdminController')->group(function () {
        // Both the bare panel root and /dashboard render the same dashboard action.
        // Only the /dashboard URI owns the "dashboard" route name (-> admin.dashboard);
        // giving both the same name breaks route:cache serialization (duplicate name).
        // The root route is given its own distinct name (-> admin.root) rather than
        // being left anonymous, since an anonymous route here still inherits the bare
        // "admin." group name and collides with other anonymous routes in this group.
        Route::get('/', 'dashboard')->name('root');
        Route::get('/dashboard', 'dashboard')->name('dashboard');

        Route::prefix('/setting')->name('setting.')->group(function () {
            Route::get('/profile', 'profile')->name('profile');
            Route::post('/profile', 'updateProfile')->name('profile.update');
            Route::get('/password', 'password')->name('password');
            Route::post('/password', 'updatePassword')->name('password.update');
            Route::get('/general', 'generalSetting')->name('general');
            Route::get('/notification', 'notificationSetting')->name('notification');
            Route::post('/notification/send-test-mail', 'sendTestMail')->name('notification.test_mail');
            Route::post('/notification/send-test-sms', 'sendTestSMS')->name('notification.test_sms');
            Route::post('/notification', 'updateNotificationSetting')->name('notification.update');
            Route::post('/general', 'updateGeneralSetting')->name('general.update');
            Route::get('/configuration', 'configurationSetting')->name('configuration');
            Route::get('/sms', 'smsSetting')->name('sms');
            Route::post('/sms', 'updateSmsSetting')->name('sms.update');
            Route::get('/server-information', 'serverInformation')->name('server.information');
            Route::post('/configuration', 'updateConfigurationSetting')->name('configuration.update');
        });
    });

    Route::prefix('/setting')->name('setting.')->group(function () {
        Route::prefix('language')->controller('LanguageController')->name('language.')->group(function () {
            Route::get('/', 'list')->name('list');
            Route::get('/new', 'new')->name('new');
            Route::post('/save/{id?}', 'save')->name('save');
            Route::get('/keywords', 'keywords')->name('keywords');
        });
    });

    Route::controller('CallLogController')->prefix('call-logs')->name('call_log.')->group(function () {
        Route::post('/', 'store')->name('store');
        Route::post('/{id}/delete', 'delete')->name('delete');
    });

    Route::controller('MemberCategoryController')
        ->prefix('/user/categories')
        ->name('user.category.')
        ->group(function () {
            Route::get('/', 'list')->name('list');
            Route::post('/store/{id?}', 'store')->name('store');
            Route::post('/delete/{id}', 'delete')->name('delete');
    });

    Route::controller('UserController')->prefix('/users')->name('user.')->group(function () {
        Route::post('/send-sms/{id}/sms', 'sendSMS')->name('send_sms');
        Route::post('/contact-sms/{id}/sms', 'sendContactSMS')->name('contact_sms');
        Route::get('/', 'list')->name('list');
        Route::get('/email-unverified', 'emailUnverified')->name('email.unverfied');
        Route::get('/mobile-unverified', 'mobileUnverified')->name('mobile.unverfied');
        Route::get('/new-users', 'newUsers')->name('new_list');
        Route::get('/new', 'new')->name('new');
        Route::get('/inactive', 'inactive')->name('inactive');        
        Route::get('/details/{id}', 'details')->name('details');
        Route::get('/login/{id}', 'loginUser')->name('login');
        Route::get('/edit/{id}', 'edit')->name('edit');
        Route::post('/edit/{id?}', 'save')->name('save');
        Route::post('/delete/{id}', 'delete')->name('delete');
        Route::post('change-status/{id}', 'changeStatus')->name('status.change');
    });

    Route::controller('ManageMemberController')->prefix('/members')->name('member.')->group(function () {
        Route::get('/', 'list')->name('list');
        Route::get('/create', 'create')->name('create');
        Route::get('/edit/{id}', 'edit')->name('edit');
        Route::post('/store/{id?}', 'store')->name('store');
        Route::post('/delete/{id}', 'delete')->name('delete');
    });

    Route::controller('ExpenseCategoryController')->prefix('/expense-categories')->name('expense_category.')->group(function () {
        Route::get('/', 'list')->name('list');
        Route::post('/store/{id?}', 'store')->name('save');
        Route::post('/delete/{id}', 'delete')->name('delete');
    });

    Route::controller('NoticeController')
        ->prefix('notices')
        ->name('notice.')
        ->group(function () {
            Route::get('/', 'list')->name('list');
            Route::post('/store/{id?}', 'store')->name('save');
            Route::post('/delete/{id}', 'delete')->name('delete');
        });

    Route::controller('ExpenseController')->prefix('/expenses')->name('expense.')->group(function () {
        Route::get('/', 'list')->name('list');
        Route::get('/approved', 'approved')->name('approved');
        Route::get('/unapproved', 'unapproved')->name('unapproved');
        Route::post('/store/{id?}', 'store')->name('save');
        Route::post('/upload-csv', 'uploadCsv')->name('upload');
        Route::post('/delete/{id}', 'delete')->name('delete');
        Route::post('/approve/{id}', 'approve')->name('status.approve');
        Route::post('/unapprove/{id}', 'unapprove')->name('status.unapprove');
    });

    Route::controller('MediaController')->group(function () {
        Route::get('/media/index', 'index')->name('media.index');
        Route::post('/media/upload', 'upload')->name('media.upload');
        Route::get('/show-media/{file}', 'show')->name('media.show');
    });

    Route::controller('PaymentGatewayController')->name('payment_gateway.')->prefix('payment-gateways')->group(function () {
        Route::get('/manual', 'manualList')->name('manual.list');

        Route::get('/', 'list')->name('list');
        Route::get('/new', 'new')->name('new');
        Route::get('/edit/{key}', 'edit')->name('edit');
        Route::post('/manual/{key?}', 'save')->name('save');
    });

    Route::controller('ReportController')->prefix('/report')->name('report.')->group(function () {
        Route::controller('PaymentController')->name('payment.')->group(function () {
            Route::get('/payments', 'list')->name('list');
            Route::post('/payments/{id}/delete', 'deletePayment')->name('delete');
        });

        Route::get('/notifications/{id}/read', 'notificationRead')->name('notifications.read');
        Route::post('/notifications/{id}/delete', 'deleteNotification')->name('notifications.delete');
        Route::post('/notifications/delete-delete', 'deleteAllNotification')->name('notifications.delete.all');
        Route::get('/notifications/mark-all-as-read', 'markAllAsRead')->name('notifications.read.all');
        Route::get('/notifications', 'notifications')->name('notifications');
        Route::get('/admin-logins', 'adminLogins')->name('admin_login');
        Route::get('/call-logs', 'callLogs')->name('call_logs');
        Route::get('/contact-submissions', 'contactSubmissions')->name('contact_submissions');
        Route::post('/delete-contact-submissions/{id}', 'deleteContactSubmission')->name('contact_submission.delete');
        Route::get('/user-by-area', 'usersByArea')->name('user_by_area');
        Route::get('/account-summary', 'accountSummary')->name('account_summary');
        Route::get('/account-report', 'reportSummary')->name('report.summary');
        Route::get('/downlod/pdf', 'reportSummaryPdf')->name('report.pdf');
    });

    Route::controller('ProjectController')->prefix('/projects')->name('project.')->group(function () {
        Route::get('/', 'projectList')->name('list');
        Route::get('/create', 'createProject')->name('create');
        Route::post('/save', 'saveProject')->name('save');
        Route::post('/save/{id}', 'saveProject')->name('update');
        Route::get('/edit/{id}', 'editProject')->name('edit');
        Route::post('/delete/{id}', 'deleteProject')->name('delete');
    });

    Route::controller('BlogPostController')->prefix('/blog-posts')->name('blog_post.')->group(function () {
        Route::get('/', 'blogPostList')->name('list');
        Route::get('/create', 'create')->name('create');
        Route::get('/published', 'published')->name('published');
        Route::post('/save/{id?}', 'save')->name('save');
        Route::get('/edit/{id}', 'edit')->name('edit');
        Route::post('/delete/{id}', 'delete')->name('delete');
    });
    
    Route::controller('CommitteeController')->prefix('/committees')->name('committee.')->group(function () {
        Route::get('/', 'list')->name('list');
        Route::get('/create', 'create')->name('create');
        Route::post('/save/{id?}', 'save')->name('save');
        Route::get('/edit/{id}', 'edit')->name('edit');
        Route::post('/delete/{id}', 'delete')->name('delete');
    });

    Route::controller('DonationCategoryController')->prefix('/donation-categories')->name('donation_category.')->group(function () {
        Route::get('/', 'list')->name('list');
        Route::get('/create', 'create')->name('create');
        Route::post('/save/{id?}', 'save')->name('save');
        Route::get('/edit/{id}', 'edit')->name('edit');
        Route::post('/delete/{id}', 'delete')->name('delete');
    });

    Route::controller('GalleryCategoryController')->prefix('/gallery-categories')->name('gallery_category.')->group(function () {
        Route::get('/', 'list')->name('list');
        Route::get('/create', 'create')->name('create');
        Route::post('/save/{id?}', 'save')->name('save');
        Route::get('/edit/{id}', 'edit')->name('edit');
        Route::post('/delete/{id}', 'delete')->name('delete');
    });


    Route::controller('GalleryController')->prefix('/galleris')->name('gallery.')->group(function () {
        Route::get('/', 'list')->name('list');
        Route::get('/create', 'create')->name('create');
        Route::post('/save/{id?}', 'save')->name('save');
        Route::get('/edit/{id}', 'edit')->name('edit');
        Route::post('/delete/{id}', 'delete')->name('delete');
    });

    Route::controller('ACLController')->prefix('/acl')->name('acl.')->group(function () {
        Route::get('/roles', 'roles')->name('role.list');
        Route::get('/roles/create', 'createRole')->name('role.create');
        Route::post('/roles/store/{id?}', 'storeRole')->name('role.store');
        Route::get('/roles/edit/{id}', 'editRole')->name('role.edit');
        Route::post('/roles/delete/{id}', 'deleteRole')->name('role.delete');
        Route::post('/abilities/generate', 'genreateAbilities')->name('ability.generate');
    });

    Route::controller('DonationController')->prefix('/donations')->name('donation.')->group(function () {
        Route::get('/manual-submissions', 'manualSubmissions')->name('manual_submissions');
        Route::post('/manual-submissions/{id?}', 'manualSubmissionsSave')->name('manual_submissions.save');
        Route::post('/delete-manual-submissions/{id}', 'deleteManualSubmission')->name('manual_submissions.delete');
        Route::post('/upload-csv', 'uploadCsv')->name('upload');
        Route::get('/create-manual', 'manualForm')->name('manual');
        Route::post('/create-manual-submit', 'manualFormSubmit')->name('manual.submit');
        Route::get('/', 'list')->name('list');
        Route::get('/pending', 'pending')->name('pending');
        Route::get('/rejected', 'rejected')->name('rejected');
        Route::get('/approved', 'approved')->name('approved');
        Route::post('/delete/{id}', 'delete')->name('delete');
        Route::post('/approve/{id}', 'approve')->name('approve');
        Route::post('/reject/{id}', 'reject')->name('reject');
    });

    Route::controller('WebsiteController')->prefix('website')->name('website.')->group(function () {

        Route::prefix('/pages')->name('page.')->group(function () {
            Route::get('/', 'pages')->name('list');
            Route::get('/{id}/edit', 'editPage')->name('edit');
            Route::get('/new', 'newPage')->name('new');
            Route::post('/new', 'saveNewPage')->name('save');
            Route::post('/{id}/update', 'updatePage')->name('update');
            Route::post('/{id}/delete', 'deletePage')->name('delete');
        });

        Route::prefix('/sections')->name('section.')->group(function () {
            Route::get('/', 'sections')->name('list');
            Route::get('/{key}/edit', 'editSection')->name('edit');
            Route::post('/{key}/update', 'updateSection')->name('update');
        });
    });
});
