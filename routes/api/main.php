<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\V1\PlaceController;
use App\Http\Controllers\V1\Lesson\LessonController;
use App\Http\Controllers\V1\Lesson\CoverPhotoController;
use App\Http\Controllers\V1\Lesson\EnrollmentController;
use App\Http\Controllers\V1\Auth\MasterProfileController;
use App\Http\Controllers\V1\Lesson\MasterLessonController;
use App\Http\Controllers\V1\Master\MeetOurMasterController;
use App\Http\Controllers\V1\Auth\MyLessonPreferenceController;
use App\Http\Controllers\V1\Auth\Connect\RequestPayoutController;
use App\Http\Controllers\V1\EnrollmentPaymentCalculatorController;
use App\Http\Controllers\V1\Lesson\CancelScheduledLessonController;
use App\Http\Controllers\V1\Lesson\EnrollmentAttendanceController;
use App\Http\Controllers\V1\Auth\Connect\BalanceController;
use App\Http\Controllers\V1\Lesson\LessonAddressController;
use App\Http\Controllers\V1\Lesson\LessonLimitChecker;
use App\Http\Controllers\V1\Lesson\LessonReportController;
use App\Http\Controllers\V1\Lesson\LessonToLearnController;
use App\Http\Controllers\V1\Lesson\LessonToTeachController;
use App\Http\Controllers\V1\Lesson\LessonScheduleController;
use App\Http\Controllers\V1\Lesson\MasterPastLessonController;
use App\Http\Controllers\V1\Lesson\PastLessonController;
use App\Http\Controllers\V1\Lesson\RescheduleLessonController;
use App\Http\Controllers\V1\Lesson\StudentPastLessonController;
use App\Http\Controllers\V1\Master\MasterRatingController;
use App\Models\MasterLesson;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['prefix' => 'v1', 'namespace' => 'V1'], function () {

    Route::group(['prefix' => 'auth', 'namespace' => 'Auth'], function ($router) {

        Route::post('social', 'SocialAuthController')->name('auth.social');

        Route::post('check-email', 'CheckController@checkEmail')->name('auth.checkEmail');
        Route::post('check-username', 'CheckController@checkUsername')->name('auth.checkUsername');
        Route::post('check-email-availability', 'CheckController@checkEmailAvailability')->name('auth.checkEmailAvailability');

        Route::post('login', 'AuthController@login')->name('auth.login');
        Route::post('logout', 'AuthController@logout')->name('auth.logout');
        Route::get('me', 'AuthController@me')->name('auth.me');

        Route::get('/profile', 'ProfileController@index');
        Route::match(['put', 'patch'], '/profile', 'ProfileController@update');
        Route::post('/profile/avatar', 'ProfileAvatarController@store');

        Route::post('register', 'RegisterController')->name('register');
        Route::post('forgot-password', 'ForgotPasswordController')->name('forgotPassword');
        Route::post('reset-password', 'ResetPasswordController')->name('resetPassword');
        Route::post('reset-password/check', 'ResetPasswordController@checkToken')->name('resetPassword.check');
        Route::post('reset-password/get-verified-account', 'ResetPasswordController@getVerifiedAccount')
            ->name('resetPassword.get-verified-account');
        Route::post('verification/verify', 'VerificationController@verify')->name('verification.verify');
        Route::post('verification/resend', 'VerificationController@resend')->name('verification.resend');

        Route::post('otp/generate', 'OneTimePassword\\OneTimePasswordController@generate');

        Route::post('onboarding/email', 'OnBoardingController@email');
        Route::post('onboarding/complete', 'OnBoardingController@complete');



        Route::group(['namespace' => 'AccountSettings'], function () {
            Route::post('change/email', 'ChangeEmailController@change');
            Route::post('change/email/verify', 'ChangeEmailController@verify');

            Route::post('change/phone-number', 'ChangePhoneNumberController@change');
            Route::post('change/phone-number/verify', 'ChangePhoneNumberController@verify');

            Route::post('change/password', 'ChangePasswordController');

            Route::post('account/verification-token', 'VerificationTokenController');

            Route::delete('account', 'DeleteAccountController');

            Route::group(['prefix' => 'addresses', 'namespace' => 'Address'], function () {
                Route::get('delivery', 'DeliveryController@index');
                Route::post('delivery', 'DeliveryController@store');
            });
        });

        Route::get('master-profile', [MasterProfileController::class, 'show'])
            ->name('master_profile.show');
        Route::get('check/master-profile', [MasterProfileController::class, 'checkProfileAvailability'])
            ->name('master_profile.check-profile');
        Route::delete('master-profile', [MasterProfileController::class, 'destroy'])
            ->name('master_profile.destroy');

        Route::match(['post', 'put', 'patch'], 'master-profile', [MasterProfileController::class, 'update'])
            ->name('master_profile.update');

        // User lesson preferences
        Route::get('lesson-preferences', [MyLessonPreferenceController::class, 'index'])
            ->name('my.lesson_preferences');
        Route::match(['post', 'put'], 'lesson-preferences', [MyLessonPreferenceController::class, 'store'])
            ->name('my.lesson_preferences.store');

        // Payments
        Route::post('payment-methods/{cardId}/default', 'PaymentMethodController@markAsDefault');

        Route::apiResource('payment-methods', 'PaymentMethodController');
        Route::post('payment-methods/create-client-secret', 'ClientSecretController');

        // Payout
        Route::name('connect.')->group(function () {
            Route::apiResource('connect/account', 'Connect\\AccountController')
                ->only('index', 'store');

            Route::delete('connect/account', 'Connect\\AccountController@destroy')->name('account.delete');

            Route::post('connect/file-upload', 'Connect\\FileController')->name('file-upload');

            Route::post('connect/account-links', 'Connect\\AccountLinkController')->name('account_link');

            Route::apiResource('connect/external-accounts', 'Connect\\ExternalAccountController');

            Route::get('connect/balance', BalanceController::class)
                ->name('connect.balance');

            Route::post('connect/balance/payout', RequestPayoutController::class)
                ->name('connect.balance.create_payout');
        });

        Route::post('ephemeral-key', 'EphemeralKeyController');
    });

    Route::get('/media/{media}/responsive', 'MediaController@imageFactory')
        ->name('media.responsive');

    Route::apiResource('/media', 'MediaController')->only(['store', 'show', 'destroy']);

    Route::post('users/{id}/avatar', 'UserAvatarController@store')->name('user.avatar.store');
    Route::delete('users/{id}/avatar', 'UserAvatarController@destroy')->name('user.avatar.destroy');
    Route::get('users/{id}/avatar', 'UserAvatarController@show')->name('user.avatar.show');

    Route::get('users/{id}/avatar/thumb', 'UserAvatarController@showThumb')->name('user.avatar.showThumb');

    /** @deprecated version 1.6 use /report */
    Route::post('users/{user}/report', 'UserReportController')->name('users.report');

    Route::get('/users/export', 'UserExportController')->name('users.export');

    Route::apiResource('/users', 'UserController');

    Route::get('users/{user}/posts', 'User\\UserPostController')
        ->name('users.posts.index');

    Route::get('app/version-check', 'AppVersionCheckController');

    // Devices
    Route::group(['prefix' => 'devices'], function () {
        Route::post('/', 'DeviceController@store');
        Route::delete('/', 'DeviceController@destroy');
    });

    // Notifications
    Route::group(['prefix' => 'notifications'], function () {
        Route::get('/', 'NotificationController@index');
        Route::get('/today', 'NotificationController@today');
        Route::get('/this-week', 'NotificationController@thisWeek');
        Route::get('/read', 'NotificationController@read');
        Route::get('/unread', 'NotificationController@unread');
        Route::put('/', 'NotificationController@update');
        Route::post('/send', 'SendNotificationController');
    });

    Route::group(['prefix' => 'report'], function () {
        Route::post('/', 'ReportController')->name('report.store');
        // list of report categories
        Route::get('/categories', 'ReportCategoriesController@index')->name('report.categories');
    });

    Route::get('categories', 'CategoryController');

    Route::group(['namespace' => 'Job'], function () {

        Route::get('jobs/categories', 'JobController@categories');
        // Job Offers
        Route::apiResource('jobs', 'JobController');

        Route::post('jobs/{job}/favorite', 'JobController@favorite')
            ->name('jobs.favorite');

        Route::post('jobs/{job}/unfavorite', 'JobController@unfavorite')
            ->name('jobs.unfavorite');

        Route::post('jobs/{job}/report', 'JobController@report')
            ->name('jobs.report');

        // Job Offer Comments
        Route::apiResource('jobs.comments', 'JobCommentController')
            ->only('index', 'store');
    });

    Route::group(['namespace' => 'Comment'], function () {
        // Comments
        Route::apiResource('comments', 'CommentController')
            ->except('index', 'store');

        Route::post('comments/{comment}/report', 'CommentController@report')
            ->name('comments.report');

        // Comment Respond
        Route::apiResource('comments.responses', 'CommentResponseController')
            ->only('index', 'store');
    });

    Route::group(['namespace' => 'Post'], function () {

        Route::apiResource('posts', 'PostController');

        Route::post('posts/{post}/favorite', 'PostController@favorite')
            ->name('posts.favorite');

        Route::post('posts/{post}/unfavorite', 'PostController@unfavorite')
            ->name('posts.unfavorite');

        Route::post('posts/{post}/report', 'PostController@report')
            ->name('posts.report');

        Route::apiResource('posts.comments', 'PostCommentController')
            ->only('index', 'store');
    });

    // Pages URL
    Route::get('pages/{pageType}', 'PageController');


    Route::group(['namespace' => 'Subscription'], function () {
        Route::get('subscription/client-secret/{plan}', 'SubscriptionController@createSetupIntent')->name('create-setup-intent');
        Route::get('user/plans', 'PlanController@index')->name('plans.index');
        Route::get('user/active/subscription', 'PlanController@show')->name('plan.show');

        Route::post('user/subscribe', 'SubscriptionController@subscribe')->name('subscribe');
        Route::post('user/unsubscribe', 'SubscriptionController@unsubscribe')->name('unsubscribe');
        Route::put('user/update-subscription-payment', 'SubscriptionController@resumePayment')->name('resume.payment');
        Route::post('user/subscribe/free-trial/{plan}', 'SubscriptionController@freeTrial')->name('subscribe.free-trial');
    });


    // Location-related routes
    Route::get('/countries', 'CountryController@index');
    Route::get('/places', [PlaceController::class, 'index'])->name('places.index');

    // Lesson routes
    Route::get('lessons', [MasterLessonController::class, 'index'])->name('lessons.index');
    Route::get('lessons/{slug}', [MasterLessonController::class, 'show'])->name('lessons.show');
    Route::post('lessons', [MasterLessonController::class, 'store'])->name('lessons.store');
    Route::put('lessons/{lesson}', [MasterLessonController::class, 'update'])->name('lessons.update');
    Route::delete('lessons/{masterLesson}', [MasterLessonController::class, 'destroy'])->name('lessons.destroy');
    Route::post('lessons/{lesson}/report', LessonReportController::class)->name('lessons.report');


    Route::controller(CoverPhotoController::class)->group(function () {
        Route::post('lessons/{lesson}/cover-photos', 'store')->name('lessons.cover_photos.store');
        Route::delete('lessons/{lesson}/cover-photos/{media}', 'destroy')->name('lessons.cover_photos.store');
    });

    //Search Lesson
    Route::get('search/lessons', [LessonController::class, 'index'])->name('lessons.search');
    Route::get('popular/lessons', [LessonController::class, 'popular'])->name('lessons.popular');
    // Meet our masters
    Route::get('masters', MeetOurMasterController::class);

    // Lesson Enrollment
    Route::post('lessons/{lesson}/enroll', EnrollmentController::class)->name('lessons.enroll');
    Route::post('lesson/schedule', [LessonScheduleController::class, 'store'])->name('lesson.schedule.store');
    Route::delete('lesson/schedule/{lessonSchedule}', [LessonScheduleController::class, 'destroy'])->name('lesson.schedule.destroy');
    Route::post('lesson/schedule/duplicate/checker', [LessonScheduleController::class, 'duplicateChecker'])->name('lesson.schedule.duplicate.checker');

    // enrollment payment calculator
    Route::post('enrollment/calculate-payment', EnrollmentPaymentCalculatorController::class)
        ->name('enrollment.calculate_payment');

    Route::get('lessons/master/to-teach', [LessonToTeachController::class, 'index'])->name('lessons.master.toTeach.index');
    Route::get('lessons/master/to-teach/{schedule}', [LessonToTeachController::class, 'show'])->name('lessons.master.toTeach.show');
    Route::get('lessons/student/to-learn', [LessonToLearnController::class, 'index'])->name('lessons.student.toLearn.index');
    Route::get('lessons/student/to-learn/{referenceCode}', [LessonToLearnController::class, 'show'])->name('lessons.student.toLearn.show');
    Route::get('finished/lessons', [LessonToLearnController::class, 'finishedLessons'])->name('finished.lessons');

    // Cancellations
    Route::post(
        'enrollments/{enrollment:reference_code}/cancel',
        [CancelScheduledLessonController::class, 'cancelByStudent']
    )->name('enrollment.cancel');

    Route::post(
        'lessons/master/to-teach/{schedule}/cancel',
        [CancelScheduledLessonController::class, 'cancelByMaster']
    )->name('lessons.master.toTeach.cancel');

    // Reschedules
    Route::post(
        'enrollments/{enrollment:reference_code}/reschedule',
        [RescheduleLessonController::class, 'rescheduleByStudent']
    )->name('enrollment.reschedule');

    Route::post(
        'lessons/master/to-teach/{schedule}/reschedule',
        [RescheduleLessonController::class, 'bulkRescheduleByMaster']
    )->name('lessons.master.toTeach.reschedule');

    // Lesson Address
    Route::get('lessons/address/{masterLesson}', LessonAddressController::class)->name('lessons.address.show');

    // Ratings
    Route::post('rate/master', MasterRatingController::class)->name('rate.master.store');

    // Student Attendance
    Route::post('lesson/student/not-attended', EnrollmentAttendanceController::class)->name('lessons.attendance.store');

    Route::post('subscription/lesson/limit/checker', LessonLimitChecker::class)->name('lesson.limit.checker');

    Route::get('student/past/lessons', [StudentPastLessonController::class, 'index'])->name('student.past.lessons');
    Route::get('student/past/lessons/{referenceCode}', [StudentPastLessonController::class, 'show'])->name('student.past.lessons.show');

    Route::get('master/past/lessons', [MasterPastLessonController::class, 'index'])->name('master.past.lessons');
    Route::get('master/past/lessons/{schedule}', [MasterPastLessonController::class, 'show'])->name('master.past.lessons.show');
});
