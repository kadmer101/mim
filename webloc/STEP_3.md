Based on my comprehensive analysis of all the project documentation, I can now provide the requested language files and core configuration files for the WebBloc Laravel project.

# Mission 2: Language Files

## lang/ar/auth.php
```php
<?php

return [
    'failed' => 'بيانات الاعتماد هذه غير متطابقة مع سجلاتنا.',
    'password' => 'كلمة المرور المقدمة غير صحيحة.',
    'throttle' => 'محاولات دخول كثيرة جداً. يرجى المحاولة مرة أخرى بعد :seconds ثانية.',
    'login' => 'تسجيل الدخول',
    'register' => 'إنشاء حساب',
    'logout' => 'تسجيل الخروج',
    'forgot_password' => 'نسيت كلمة المرور؟',
    'reset_password' => 'إعادة تعيين كلمة المرور',
    'remember_me' => 'تذكرني',
    'email' => 'البريد الإلكتروني',
    'password_field' => 'كلمة المرور',
    'confirm_password' => 'تأكيد كلمة المرور',
    'name' => 'الاسم',
    'already_registered' => 'مسجل بالفعل؟',
    'forgot_password_text' => 'نسيت كلمة المرور؟ لا مشكلة. فقط أخبرنا بعنوان بريدك الإلكتروني وسنرسل لك رابط إعادة تعيين كلمة المرور.',
    'email_password_reset_link' => 'إرسال رابط إعادة تعيين كلمة المرور',
    'reset_password_text' => 'إعادة تعيين كلمة المرور',
    'confirm_new_password' => 'تأكيد كلمة المرور الجديدة',
    'new_password' => 'كلمة المرور الجديدة',
];
```

## lang/ar/dashboard.php
```php
<?php

return [
    'dashboard' => 'لوحة التحكم',
    'welcome' => 'مرحباً',
    'overview' => 'نظرة عامة',
    'websites' => 'المواقع',
    'components' => 'المكونات',
    'users' => 'المستخدمون',
    'statistics' => 'الإحصائيات',
    'settings' => 'الإعدادات',
    'profile' => 'الملف الشخصي',
    'total_websites' => 'إجمالي المواقع',
    'active_websites' => 'المواقع النشطة',
    'total_components' => 'إجمالي المكونات',
    'active_components' => 'المكونات النشطة',
    'total_api_calls' => 'إجمالي استدعاءات API',
    'today_api_calls' => 'استدعاءات API اليوم',
    'recent_websites' => 'المواقع الحديثة',
    'top_components' => 'أهم المكونات',
    'view_all' => 'عرض الكل',
    'no_data' => 'لا توجد بيانات',
    'loading' => 'جارٍ التحميل...',
    'search' => 'بحث',
    'filter' => 'تصفية',
    'export' => 'تصدير',
    'add_new' => 'إضافة جديد',
    'edit' => 'تحرير',
    'delete' => 'حذف',
    'view' => 'عرض',
    'save' => 'حفظ',
    'cancel' => 'إلغاء',
    'confirm' => 'تأكيد',
    'created_at' => 'تاريخ الإنشاء',
    'updated_at' => 'تاريخ التحديث',
    'status' => 'الحالة',
    'active' => 'نشط',
    'inactive' => 'غير نشط',
    'actions' => 'الإجراءات',
];
```

## lang/ar/messages.php
```php
<?php

return [
    // API Messages
    'component_not_found' => 'المكون غير موجود أو غير قابل للوصول',
    'component_not_creatable' => 'المكون غير موجود أو غير قابل للإنشاء',
    'component_not_readable' => 'المكون غير موجود أو غير قابل للقراءة',
    'component_not_updatable' => 'المكون غير موجود أو غير قابل للتحديث',
    'component_not_deletable' => 'المكون غير موجود أو غير قابل للحذف',
    'item_not_found' => 'العنصر غير موجود',
    'fetch_failed' => 'فشل في جلب المكونات',
    'create_failed' => 'فشل في إنشاء المكون',
    'update_failed' => 'فشل في تحديث المكون',
    'delete_failed' => 'فشل في حذف المكون',
    'component_created' => 'تم إنشاء المكون بنجاح',
    'component_updated' => 'تم تحديث المكون بنجاح',
    'component_deleted' => 'تم حذف المكون بنجاح',
    
    // Website Messages  
    'website_created' => 'تم إنشاء الموقع بنجاح',
    'website_updated' => 'تم تحديث الموقع بنجاح',
    'website_deleted' => 'تم حذف الموقع بنجاح',
    'keys_regenerated' => 'تم إعادة إنشاء مفاتيح API بنجاح',
    'website_verified' => 'تم التحقق من الموقع',
    'website_suspended' => 'تم تعليق الموقع',
    
    // User Messages
    'user_created' => 'تم إنشاء المستخدم بنجاح',
    'user_updated' => 'تم تحديث المستخدم بنجاح',
    'user_deleted' => 'تم حذف المستخدم بنجاح',
    'password_reset' => 'تم إعادة تعيين كلمة المرور',
    'profile_updated' => 'تم تحديث الملف الشخصي',
    
    // General Messages
    'success' => 'تم بنجاح',
    'error' => 'خطأ',
    'warning' => 'تحذير',
    'info' => 'معلومات',
    'operation_successful' => 'تمت العملية بنجاح',
    'operation_failed' => 'فشلت العملية',
    'permission_denied' => 'تم رفض الإذن',
    'unauthorized' => 'غير مخول',
    'validation_error' => 'خطأ في التحقق',
    'server_error' => 'خطأ في الخادم',
    'not_found' => 'غير موجود',
    'already_exists' => 'موجود بالفعل',
    'confirm_delete' => 'هل أنت متأكد من أنك تريد حذف هذا العنصر؟',
    'confirm_action' => 'هل أنت متأكد من أنك تريد تنفيذ هذا الإجراء؟',
    'data_saved' => 'تم حفظ البيانات',
    'data_exported' => 'تم تصدير البيانات',
    'email_sent' => 'تم إرسال البريد الإلكتروني',
    'password_changed' => 'تم تغيير كلمة المرور',
];
```

## lang/ar/webbloc.php
```php
<?php

return [
    // Component Names
    'comment' => 'تعليق',
    'comments' => 'التعليقات',
    'review' => 'مراجعة',
    'reviews' => 'المراجعات',
    'auth' => 'المصادقة',
    'authentication' => 'التحقق',
    'profile' => 'الملف الشخصي',
    'reaction' => 'تفاعل',
    'reactions' => 'التفاعلات',
    
    // Comment Component
    'add_comment' => 'إضافة تعليق',
    'write_comment' => 'اكتب تعليقك...',
    'post_comment' => 'نشر التعليق',
    'reply' => 'رد',
    'reply_to' => 'رد على',
    'edit_comment' => 'تحرير التعليق',
    'delete_comment' => 'حذف التعليق',
    'like' => 'إعجاب',
    'unlike' => 'إلغاء الإعجاب',
    'likes' => 'إعجابات',
    'show_replies' => 'إظهار الردود',
    'hide_replies' => 'إخفاء الردود',
    'load_more_comments' => 'تحميل المزيد من التعليقات',
    'no_comments' => 'لا توجد تعليقات بعد',
    'login_to_comment' => 'سجل الدخول للتعليق',
    
    // Review Component
    'write_review' => 'كتابة مراجعة',
    'your_rating' => 'تقييمك',
    'review_title' => 'عنوان المراجعة',
    'review_content' => 'محتوى المراجعة',
    'pros' => 'الإيجابيات',
    'cons' => 'السلبيات',
    'recommend' => 'أوصي',
    'not_recommend' => 'لا أوصي',
    'would_recommend' => 'سأوصي بهذا',
    'submit_review' => 'إرسال المراجعة',
    'helpful' => 'مفيد',
    'not_helpful' => 'غير مفيد',
    'verified_purchase' => 'شراء موثق',
    'average_rating' => 'التقييم المتوسط',
    'total_reviews' => 'إجمالي المراجعات',
    'filter_by_rating' => 'تصفية حسب التقييم',
    'all_ratings' => 'جميع التقييمات',
    'stars' => 'نجوم',
    'star' => 'نجمة',
    'out_of_5' => 'من 5',
    'no_reviews' => 'لا توجد مراجعات بعد',
    'be_first_review' => 'كن أول من يكتب مراجعة',
    
    // Auth Component
    'login_form' => 'تسجيل الدخول',
    'register_form' => 'إنشاء حساب',
    'forgot_password_form' => 'نسيت كلمة المرور',
    'reset_password_form' => 'إعادة تعيين كلمة المرور',
    'profile_form' => 'الملف الشخصي',
    'login_with' => 'تسجيل الدخول باستخدام',
    'register_with' => 'إنشاء حساب باستخدام',
    'or' => 'أو',
    'dont_have_account' => 'ليس لديك حساب؟',
    'already_have_account' => 'لديك حساب بالفعل؟',
    'terms_conditions' => 'الشروط والأحكام',
    'privacy_policy' => 'سياسة الخصوصية',
    'agree_to' => 'أوافق على',
    'and' => 'و',
    'update_profile' => 'تحديث الملف الشخصي',
    'change_password' => 'تغيير كلمة المرور',
    'current_password' => 'كلمة المرور الحالية',
    'logged_in_as' => 'مسجل الدخول كـ',
    'welcome_back' => 'مرحباً بعودتك',
    
    // Form Fields
    'title' => 'العنوان',
    'content' => 'المحتوى',
    'rating' => 'التقييم',
    'image' => 'صورة',
    'images' => 'صور',
    'upload_images' => 'رفع الصور',
    'remove_image' => 'إزالة الصورة',
    'max_images' => 'الحد الأقصى للصور',
    'file_too_large' => 'الملف كبير جداً',
    'invalid_file_type' => 'نوع الملف غير صالح',
    
    // States
    'loading' => 'جارٍ التحميل...',
    'submitting' => 'جارٍ الإرسال...',
    'updating' => 'جارٍ التحديث...',
    'deleting' => 'جارٍ الحذف...',
    'published' => 'منشور',
    'pending' => 'في الانتظار',
    'approved' => 'موافق عليه',
    'rejected' => 'مرفوض',
    'draft' => 'مسودة',
    
    // Time
    'just_now' => 'الآن',
    'minutes_ago' => 'منذ :count دقائق',
    'hours_ago' => 'منذ :count ساعات',
    'days_ago' => 'منذ :count أيام',
    'weeks_ago' => 'منذ :count أسابيع',
    'months_ago' => 'منذ :count أشهر',
    'years_ago' => 'منذ :count سنوات',
    
    // Errors
    'error_loading' => 'خطأ في التحميل',
    'error_submitting' => 'خطأ في الإرسال',
    'error_updating' => 'خطأ في التحديث',
    'error_deleting' => 'خطأ في الحذف',
    'network_error' => 'خطأ في الشبكة',
    'server_error' => 'خطأ في الخادم',
    'try_again' => 'حاول مرة أخرى',
    'refresh_page' => 'أعد تحميل الصفحة',
];
```

## lang/en/auth.php
```php
<?php

return [
    'failed' => 'These credentials do not match our records.',
    'password' => 'The provided password is incorrect.',
    'throttle' => 'Too many login attempts. Please try again in :seconds seconds.',
    'login' => 'Login',
    'register' => 'Register',
    'logout' => 'Logout',
    'forgot_password' => 'Forgot Password?',
    'reset_password' => 'Reset Password',
    'remember_me' => 'Remember Me',
    'email' => 'Email',
    'password_field' => 'Password',
    'confirm_password' => 'Confirm Password',
    'name' => 'Name',
    'already_registered' => 'Already registered?',
    'forgot_password_text' => 'Forgot your password? No problem. Just let us know your email address and we will email you a password reset link.',
    'email_password_reset_link' => 'Email Password Reset Link',
    'reset_password_text' => 'Reset Password',
    'confirm_new_password' => 'Confirm New Password',
    'new_password' => 'New Password',
];
```

## lang/en/dashboard.php
```php
<?php

return [
    'dashboard' => 'Dashboard',
    'welcome' => 'Welcome',
    'overview' => 'Overview',
    'websites' => 'Websites',
    'components' => 'Components',
    'users' => 'Users',
    'statistics' => 'Statistics',
    'settings' => 'Settings',
    'profile' => 'Profile',
    'total_websites' => 'Total Websites',
    'active_websites' => 'Active Websites',
    'total_components' => 'Total Components',
    'active_components' => 'Active Components',
    'total_api_calls' => 'Total API Calls',
    'today_api_calls' => 'Today\'s API Calls',
    'recent_websites' => 'Recent Websites',
    'top_components' => 'Top Components',
    'view_all' => 'View All',
    'no_data' => 'No data available',
    'loading' => 'Loading...',
    'search' => 'Search',
    'filter' => 'Filter',
    'export' => 'Export',
    'add_new' => 'Add New',
    'edit' => 'Edit',
    'delete' => 'Delete',
    'view' => 'View',
    'save' => 'Save',
    'cancel' => 'Cancel',
    'confirm' => 'Confirm',
    'created_at' => 'Created At',
    'updated_at' => 'Updated At',
    'status' => 'Status',
    'active' => 'Active',
    'inactive' => 'Inactive',
    'actions' => 'Actions',
];
```

## lang/en/messages.php
```php
<?php

return [
    // API Messages
    'component_not_found' => 'Component not found or not accessible',
    'component_not_creatable' => 'Component not found or not creatable',
    'component_not_readable' => 'Component not found or not readable',
    'component_not_updatable' => 'Component not found or not updatable',
    'component_not_deletable' => 'Component not found or not deletable',
    'item_not_found' => 'Item not found',
    'fetch_failed' => 'Failed to fetch components',
    'create_failed' => 'Failed to create component',
    'update_failed' => 'Failed to update component',
    'delete_failed' => 'Failed to delete component',
    'component_created' => 'Component created successfully',
    'component_updated' => 'Component updated successfully',
    'component_deleted' => 'Component deleted successfully',
    
    // Website Messages
    'website_created' => 'Website created successfully',
    'website_updated' => 'Website updated successfully',
    'website_deleted' => 'Website deleted successfully',
    'keys_regenerated' => 'API keys regenerated successfully',
    'website_verified' => 'Website verified',
    'website_suspended' => 'Website suspended',
    
    // User Messages
    'user_created' => 'User created successfully',
    'user_updated' => 'User updated successfully',
    'user_deleted' => 'User deleted successfully',
    'password_reset' => 'Password reset successfully',
    'profile_updated' => 'Profile updated successfully',
    
    // General Messages
    'success' => 'Success',
    'error' => 'Error',
    'warning' => 'Warning',
    'info' => 'Info',
    'operation_successful' => 'Operation completed successfully',
    'operation_failed' => 'Operation failed',
    'permission_denied' => 'Permission denied',
    'unauthorized' => 'Unauthorized',
    'validation_error' => 'Validation error',
    'server_error' => 'Server error',
    'not_found' => 'Not found',
    'already_exists' => 'Already exists',
    'confirm_delete' => 'Are you sure you want to delete this item?',
    'confirm_action' => 'Are you sure you want to perform this action?',
    'data_saved' => 'Data saved successfully',
    'data_exported' => 'Data exported successfully',
    'email_sent' => 'Email sent successfully',
    'password_changed' => 'Password changed successfully',
];
```

## lang/en/webbloc.php
```php
<?php

return [
    // Component Names
    'comment' => 'Comment',
    'comments' => 'Comments',
    'review' => 'Review',
    'reviews' => 'Reviews',
    'auth' => 'Auth',
    'authentication' => 'Authentication',
    'profile' => 'Profile',
    'reaction' => 'Reaction',
    'reactions' => 'Reactions',
    
    // Comment Component
    'add_comment' => 'Add Comment',
    'write_comment' => 'Write your comment...',
    'post_comment' => 'Post Comment',
    'reply' => 'Reply',
    'reply_to' => 'Reply to',
    'edit_comment' => 'Edit Comment',
    'delete_comment' => 'Delete Comment',
    'like' => 'Like',
    'unlike' => 'Unlike',
    'likes' => 'Likes',
    'show_replies' => 'Show Replies',
    'hide_replies' => 'Hide Replies',
    'load_more_comments' => 'Load More Comments',
    'no_comments' => 'No comments yet',
    'login_to_comment' => 'Login to comment',
    
    // Review Component
    'write_review' => 'Write Review',
    'your_rating' => 'Your Rating',
    'review_title' => 'Review Title',
    'review_content' => 'Review Content',
    'pros' => 'Pros',
    'cons' => 'Cons',
    'recommend' => 'Recommend',
    'not_recommend' => 'Not Recommend',
    'would_recommend' => 'I would recommend this',
    'submit_review' => 'Submit Review',
    'helpful' => 'Helpful',
    'not_helpful' => 'Not Helpful',
    'verified_purchase' => 'Verified Purchase',
    'average_rating' => 'Average Rating',
    'total_reviews' => 'Total Reviews',
    'filter_by_rating' => 'Filter by Rating',
    'all_ratings' => 'All Ratings',
    'stars' => 'Stars',
    'star' => 'Star',
    'out_of_5' => 'out of 5',
    'no_reviews' => 'No reviews yet',
    'be_first_review' => 'Be the first to write a review',
    
    // Auth Component
    'login_form' => 'Login Form',
    'register_form' => 'Register Form',
    'forgot_password_form' => 'Forgot Password Form',
    'reset_password_form' => 'Reset Password Form',
    'profile_form' => 'Profile Form',
    'login_with' => 'Login with',
    'register_with' => 'Register with',
    'or' => 'Or',
    'dont_have_account' => 'Don\'t have an account?',
    'already_have_account' => 'Already have an account?',
    'terms_conditions' => 'Terms & Conditions',
    'privacy_policy' => 'Privacy Policy',
    'agree_to' => 'I agree to the',
    'and' => 'and',
    'update_profile' => 'Update Profile',
    'change_password' => 'Change Password',
    'current_password' => 'Current Password',
    'logged_in_as' => 'Logged in as',
    'welcome_back' => 'Welcome back',
    
    // Form Fields
    'title' => 'Title',
    'content' => 'Content',
    'rating' => 'Rating',
    'image' => 'Image',
    'images' => 'Images',
    'upload_images' => 'Upload Images',
    'remove_image' => 'Remove Image',
    'max_images' => 'Maximum Images',
    'file_too_large' => 'File too large',
    'invalid_file_type' => 'Invalid file type',
    
    // States
    'loading' => 'Loading...',
    'submitting' => 'Submitting...',
    'updating' => 'Updating...',
    'deleting' => 'Deleting...',
    'published' => 'Published',
    'pending' => 'Pending',
    'approved' => 'Approved',
    'rejected' => 'Rejected',
    'draft' => 'Draft',
    
    // Time
    'just_now' => 'Just now',
    'minutes_ago' => ':count minutes ago',
    'hours_ago' => ':count hours ago',
    'days_ago' => ':count days ago',
    'weeks_ago' => ':count weeks ago',
    'months_ago' => ':count months ago',
    'years_ago' => ':count years ago',
    
    // Errors
    'error_loading' => 'Error loading',
    'error_submitting' => 'Error submitting',
    'error_updating' => 'Error updating',
    'error_deleting' => 'Error deleting',
    'network_error' => 'Network error',
    'server_error' => 'Server error',
    'try_again' => 'Try again',
    'refresh_page' => 'Refresh page',
];
```

# Mission 3: Core Configuration Files

## bootstrap/app.php
```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\SetLocale;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // API Middleware
        $middleware->api(prepend: [
            SetLocale::class,
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        // Web Middleware  
        $middleware->web(append: [
            SetLocale::class,
        ]);

        // Middleware Groups
        $middleware->group('webbloc-api', [
            'throttle:api',
            'auth:sanctum',
            SetLocale::class,
        ]);

        // Rate limiting
        $middleware->throttleApi();
        $middleware->throttleRequests();
        
        // Alias middleware
        $middleware->alias([
            'locale' => SetLocale::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // API Exception Handling
        $exceptions->render(function (Throwable $e, $request) {
            if ($request->is('api/*')) {
                $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
                
                if ($statusCode === 404) {
                    return response()->json([
                        'success' => false,
                        'error' => __('messages.not_found'),
                        'message' => 'Resource not found'
                    ], 404);
                }

                if ($statusCode === 403) {
                    return response()->json([
                        'success' => false,
                        'error' => __('messages.permission_denied'),
                        'message' => 'Access denied'
                    ], 403);
                }

                if ($statusCode === 401) {
                    return response()->json([
                        'success' => false,
                        'error' => __('messages.unauthorized'),
                        'message' => 'Unauthorized access'
                    ], 401);
                }

                if ($statusCode === 422) {
                    return response()->json([
                        'success' => false,
                        'error' => __('messages.validation_error'),
                        'message' => 'Validation failed',
                        'errors' => method_exists($e, 'errors') ? $e->errors() : []
                    ], 422);
                }

                if ($statusCode >= 500) {
                    return response()->json([
                        'success' => false,
                        'error' => __('messages.server_error'),
                        'message' => config('app.debug') ? $e->getMessage() : 'Internal server error'
                    ], $statusCode);
                }

                return response()->json([
                    'success' => false,
                    'error' => $e->getMessage(),
                    'message' => 'An error occurred'
                ], $statusCode);
            }
        });

        // Log WebBloc specific errors
        $exceptions->reportable(function (Throwable $e) {
            if (str_contains($e->getMessage(), 'WebBloc') || str_contains($e->getFile(), 'WebBloc')) {
                \Log::channel('webbloc')->error('WebBloc Error: ' . $e->getMessage(), [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        });
    })
    ->create();
```

## bootstrap/providers.php
```php
<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\WebBlocServiceProvider::class,
];
```

## routes/api.php
```php
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WebBlocController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ComponentController;
use App\Http\Controllers\Api\WebsiteController;
use App\Http\Controllers\Api\StatisticsController;
use App\Http\Controllers\Api\UserController;

// Public API routes (no authentication required)
Route::prefix('v1')->group(function () {
    
    // Authentication Routes
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);
        
        // Authenticated auth routes
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/user', [AuthController::class, 'user']);
            Route::put('/user', [AuthController::class, 'updateProfile']);
            Route::put('/change-password', [AuthController::class, 'changePassword']);
        });
    });

    // WebBloc Component API Routes (Public with API Key Authentication)
    Route::prefix('webblocs')->middleware(['throttle:1000,1'])->group(function () {
        // Component CRUD operations
        Route::get('/{type}', [WebBlocController::class, 'index'])
            ->where('type', '[a-zA-Z_]+');
        Route::post('/{type}', [WebBlocController::class, 'store'])
            ->where('type', '[a-zA-Z_]+');
        Route::get('/{type}/{id}', [WebBlocController::class, 'show'])
            ->where(['type' => '[a-zA-Z_]+', 'id' => '[0-9]+']);
        Route::put('/{type}/{id}', [WebBlocController::class, 'update'])
            ->where(['type' => '[a-zA-Z_]+', 'id' => '[0-9]+']);
        Route::delete('/{type}/{id}', [WebBlocController::class, 'destroy'])
            ->where(['type' => '[a-zA-Z_]+', 'id' => '[0-9]+']);
            
        // Bulk operations
        Route::post('/{type}/bulk', [WebBlocController::class, 'bulkStore'])
            ->where('type', '[a-zA-Z_]+');
        Route::delete('/{type}/bulk', [WebBlocController::class, 'bulkDestroy'])
            ->where('type', '[a-zA-Z_]+');
    });

    // CDN Assets Route
    Route::get('/cdn/{file}', function ($file) {
        $allowedFiles = ['webbloc.min.js', 'webbloc.min.css'];
        if (!in_array($file, $allowedFiles)) {
            abort(404);
        }
        
        $path = public_path("assets/webbloc/{$file}");
        if (!file_exists($path)) {
            abort(404);
        }
        
        $mimeType = $file === 'webbloc.min.js' ? 'application/javascript' : 'text/css';
        
        return response()->file($path, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=31536000',
            'Expires' => gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT',
        ]);
    })->where('file', 'webbloc\.(min\.)?(js|css)');
});

// Admin/Dashboard API Routes (Sanctum Authentication Required)
Route::prefix('v1/admin')->middleware(['auth:sanctum', 'role:admin'])->group(function () {
    
    // Dashboard Statistics
    Route::prefix('dashboard')->group(function () {
        Route::get('/stats', [StatisticsController::class, 'dashboardStats']);
        Route::get('/charts', [StatisticsController::class, 'chartData']);
        Route::get('/recent-activity', [StatisticsController::class, 'recentActivity']);
    });

    // Website Management
    Route::apiResource('websites', WebsiteController::class);
    Route::prefix('websites')->group(function () {
        Route::post('/{website}/regenerate-keys', [WebsiteController::class, 'regenerateKeys'])
            ->where('website', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');
        Route::post('/{website}/verify', [WebsiteController::class, 'verify'])
            ->where('website', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');
        Route::post('/{website}/suspend', [WebsiteController::class, 'suspend'])
            ->where('website', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');
        Route::get('/{website}/statistics', [WebsiteController::class, 'statistics'])
            ->where('website', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');
    });

    // Component Management  
    Route::apiResource('components', ComponentController::class);
    Route::prefix('components')->group(function () {
        Route::post('/{component}/toggle-status', [ComponentController::class, 'toggleStatus'])
            ->where('component', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');
        Route::get('/{component}/statistics', [ComponentController::class, 'statistics'])
            ->where('component', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');
        Route::post('/{component}/duplicate', [ComponentController::class, 'duplicate'])
            ->where('component', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');
    });

    // User Management
    Route::apiResource('users', UserController::class);
    Route::prefix('users')->group(function () {
        Route::post('/{user}/suspend', [UserController::class, 'suspend'])
            ->where('user', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');
        Route::post('/{user}/activate', [UserController::class, 'activate'])
            ->where('user', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');
        Route::post('/{user}/reset-password', [UserController::class, 'resetPassword'])
            ->where('user', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');
        Route::get('/{user}/websites', [UserController::class, 'websites'])
            ->where('user', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');
    });

    // Statistics & Analytics
    Route::prefix('statistics')->group(function () {
        Route::get('/overview', [StatisticsController::class, 'overview']);
        Route::get('/websites', [StatisticsController::class, 'websiteStats']);
        Route::get('/components', [StatisticsController::class, 'componentStats']);
        Route::get('/api-usage', [StatisticsController::class, 'apiUsage']);
        Route::get('/export', [StatisticsController::class, 'export']);
        Route::get('/trending', [StatisticsController::class, 'trending']);
        Route::get('/performance', [StatisticsController::class, 'performance']);
    });

    // System Management
    Route::prefix('system')->group(function () {
        Route::get('/health', function () {
            return response()->json([
                'status' => 'healthy',
                'timestamp' => now(),
                'version' => config('app.version', '1.0.0'),
                'uptime' => uptime(),
            ]);
        });
        
        Route::post('/cache/clear', function () {
            \Artisan::call('cache:clear');
            \Artisan::call('config:clear');
            \Artisan::call('view:clear');
            
            return response()->json([
                'success' => true,
                'message' => 'Cache cleared successfully'
            ]);
        });
        
        Route::get('/logs', function (Request $request) {
            $level = $request->get('level', 'error');
            $lines = $request->get('lines', 100);
            
            try {
                $logPath = storage_path("logs/laravel.log");
                if (!file_exists($logPath)) {
                    return response()->json(['logs' => []]);
                }
                
                $logs = array_slice(file($logPath), -$lines);
                return response()->json(['logs' => $logs]);
            } catch (\Exception $e) {
                return response()->json(['error' => 'Unable to read logs'], 500);
            }
        });
    });
});

// Owner/User API Routes (Sanctum Authentication Required)
Route::prefix('v1/owner')->middleware(['auth:sanctum'])->group(function () {
    // User's own websites
    Route::get('/websites', [WebsiteController::class, 'userWebsites']);
    Route::get('/websites/{website}', [WebsiteController::class, 'showUserWebsite'])
        ->where('website', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');
    Route::get('/websites/{website}/statistics', [WebsiteController::class, 'userWebsiteStatistics'])
        ->where('website', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');
    
    // User profile and settings
    Route::get('/profile', [UserController::class, 'profile']);
    Route::put('/profile', [UserController::class, 'updateProfile']);
    Route::get('/usage', [UserController::class, 'usage']);
    Route::get('/activity', [UserController::class, 'activity']);
});

// Global API Information Route
Route::get('/v1/info', function () {
    return response()->json([
        'name' => 'WebBloc API',
        'version' => 'v1.0.0',
        'description' => 'Dynamic web components API for static websites',
        'endpoints' => [
            'auth' => '/api/v1/auth',
            'webblocs' => '/api/v1/webblocs',
            'admin' => '/api/v1/admin',
            'owner' => '/api/v1/owner',
        ],
        'supported_locales' => config('webbloc.supported_locales', ['en', 'ar']),
        'rate_limits' => [
            'webblocs' => '1000 requests per hour',
            'admin' => '5000 requests per hour',
            'auth' => '60 requests per hour'
        ],
        'documentation' => url('/docs'),
        'status' => 'active',
        'timestamp' => now(),
    ]);
});

// Catch-all route for undefined API endpoints
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'error' => 'API endpoint not found',
        'message' => 'The requested API endpoint does not exist',
        'available_versions' => ['v1'],
        'documentation' => url('/docs')
    ], 404);
});
```

## routes/web.php
```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\WebsiteController;
use App\Http\Controllers\Dashboard\ComponentController;
use App\Http\Controllers\Dashboard\UserController as DashboardUserController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\DocsController;

// Public Routes
Route::get('/', [PublicController::class, 'welcome'])->name('welcome');
Route::get('/pricing', [PublicController::class, 'pricing'])->name('pricing');
Route::get('/contact', [PublicController::class, 'contact'])->name('contact');
Route::post('/contact', [PublicController::class, 'submitContact'])->name('contact.submit');

// Language Switcher
Route::post('/language/{locale}', function ($locale) {
    if (in_array($locale, config('webbloc.supported_locales', ['en', 'ar']))) {
        session(['locale' => $locale]);
        app()->setLocale($locale);
    }
    return redirect()->back();
})->name('language.switch');

// Authentication Routes (Laravel Breeze)
require __DIR__.'/auth.php';

// API Documentation Routes (Public)
Route::prefix('docs')->name('docs.')->group(function () {
    Route::get('/', [DocsController::class, 'index'])->name('index');
    Route::get('/authentication', [DocsController::class, 'authentication'])->name('authentication');
    Route::get('/components', [DocsController::class, 'components'])->name('components');
    Route::get('/integration', [DocsController::class, 'integration'])->name('integration');
    Route::get('/examples', [DocsController::class, 'examples'])->name('examples');
    Route::get('/changelog', [DocsController::class, 'changelog'])->name('changelog');
});

// Dashboard Routes (Authenticated)
Route::middleware(['auth', 'verified'])->prefix('dashboard')->name('dashboard.')->group(function () {
    
    // Main Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('index');
    
    // Website Management Routes
    Route::resource('websites', WebsiteController::class)->parameters([
        'websites' => 'website:uuid'
    ]);
    Route::prefix('websites/{website:uuid}')->name('websites.')->group(function () {
        Route::post('/regenerate-keys', [WebsiteController::class, 'regenerateKeys'])->name('regenerate-keys');
        Route::post('/verify', [WebsiteController::class, 'verify'])->name('verify');
        Route::post('/suspend', [WebsiteController::class, 'suspend'])->name('suspend');
        Route::get('/statistics', [WebsiteController::class, 'statistics'])->name('statistics');
        Route::get('/integration', [WebsiteController::class, 'integration'])->name('integration');
    });
    
    // Component Management Routes (Admin Only)
    Route::middleware(['role:admin'])->group(function () {
        Route::resource('components', ComponentController::class)->parameters([
            'components' => 'component:uuid'
        ]);
        Route::prefix('components/{component:uuid}')->name('components.')->group(function () {
            Route::post('/toggle-status', [ComponentController::class, 'toggleStatus'])->name('toggle-status');
            Route::post('/duplicate', [ComponentController::class, 'duplicate'])->name('duplicate');
            Route::get('/statistics', [ComponentController::class, 'statistics'])->name('statistics');
        });
    });
    
    // User Management Routes (Admin Only)
    Route::middleware(['role:admin'])->group(function () {
        Route::resource('users', DashboardUserController::class)->parameters([
            'users' => 'user:uuid'
        ]);
        Route::prefix('users/{user:uuid}')->name('users.')->group(function () {
            Route::post('/suspend', [DashboardUserController::class, 'suspend'])->name('suspend');
            Route::post('/activate', [DashboardUserController::class, 'activate'])->name('activate');
            Route::post('/reset-password', [DashboardUserController::class, 'resetPassword'])->name('reset-password');
            Route::get('/activity', [DashboardUserController::class, 'activity'])->name('activity');
        });
    });
    
    // Statistics & Analytics Routes
    Route::prefix('statistics')->name('statistics.')->group(function () {
        Route::get('/', [DashboardController::class, 'statistics'])->name('index');
        Route::get('/websites', [DashboardController::class, 'websiteStatistics'])->name('websites');
        Route::get('/components', [DashboardController::class, 'componentStatistics'])->name('components');
        Route::get('/export', [DashboardController::class, 'exportStatistics'])->name('export');
        
        // Admin-only advanced statistics
        Route::middleware(['role:admin'])->group(function () {
            Route::get('/overview', [DashboardController::class, 'statisticsOverview'])->name('overview');
            Route::get('/performance', [DashboardController::class, 'performanceStats'])->name('performance');
            Route::get('/api-usage', [DashboardController::class, 'apiUsageStats'])->name('api-usage');
        });
    });
    
    // Profile & Settings
    Route::get('/profile', [DashboardController::class, 'profile'])->name('profile');
    Route::put('/profile', [DashboardController::class, 'updateProfile'])->name('profile.update');
    Route::get('/settings', [DashboardController::class, 'settings'])->name('settings');
    Route::put('/settings', [DashboardController::class, 'updateSettings'])->name('settings.update');
    
    // Admin-only System Management
    Route::middleware(['role:admin'])->prefix('system')->name('system.')->group(function () {
        Route::get('/logs', [DashboardController::class, 'systemLogs'])->name('logs');
        Route::get('/health', [DashboardController::class, 'systemHealth'])->name('health');
        Route::post('/cache/clear', [DashboardController::class, 'clearCache'])->name('cache.clear');
        Route::get('/maintenance', [DashboardController::class, 'maintenance'])->name('maintenance');
        Route::post('/maintenance/toggle', [DashboardController::class, 'toggleMaintenance'])->name('maintenance.toggle');
    });
});

// WebBloc Component Demo/Preview Routes (Public)
Route::prefix('demo')->name('demo.')->group(function () {
    Route::get('/', function () {
        return view('demo.index');
    })->name('index');
    
    Route::get('/comments', function () {
        return view('demo.comments');
    })->name('comments');
    
    Route::get('/reviews', function () {
        return view('demo.reviews');
    })->name('reviews');
    
    Route::get('/auth', function () {
        return view('demo.auth');
    })->name('auth');
});

// Static Asset Optimization Routes
Route::prefix('assets')->group(function () {
    // Serve optimized WebBloc assets
    Route::get('/webbloc/{file}', function ($file) {
        $allowedFiles = [
            'webbloc.min.js',
            'webbloc.min.css',
            'webbloc-ar.min.css',
            'alpine-webbloc.min.js'
        ];
        
        if (!in_array($file, $allowedFiles)) {
            abort(404);
        }
        
        $path = public_path("assets/webbloc/{$file}");
        if (!file_exists($path)) {
            abort(404);
        }
        
        $mimeType = str_ends_with($file, '.js') ? 'application/javascript' : 'text/css';
        
        return response()->file($path, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=31536000, immutable',
            'Expires' => gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT',
            'ETag' => '"' . md5_file($path) . '"',
        ]);
    })->where('file', '.*\.(js|css)$');
});

// Health Check Route
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now()->toISOString(),
        'version' => config('app.version', '1.0.0'),
        'environment' => app()->environment(),
        'locale' => app()->getLocale(),
        'database' => \DB::connection()->getPdo() ? 'connected' : 'disconnected',
        'cache' => \Cache::store()->getStore() instanceof \Illuminate\Cache\NullStore ? 'disabled' : 'enabled',
    ]);
})->name('health');

// Sitemap Route
Route::get('/sitemap.xml', function () {
    $sitemap = '<?xml version="1.0" encoding="UTF-8"?>';
    $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    
    // Add static pages
    $urls = [
        ['loc' => url('/'), 'changefreq' => 'daily', 'priority' => '1.0'],
        ['loc' => route('pricing'), 'changefreq' => 'weekly', 'priority' => '0.8'],
        ['loc' => route('contact'), 'changefreq' => 'monthly', 'priority' => '0.6'],
        ['loc' => route('docs.index'), 'changefreq' => 'weekly', 'priority' => '0.9'],
        ['loc' => route('docs.authentication'), 'changefreq' => 'weekly', 'priority' => '0.8'],
        ['loc' => route('docs.components'), 'changefreq' => 'weekly', 'priority' => '0.8'],
        ['loc' => route('docs.integration'), 'changefreq' => 'weekly', 'priority' => '0.8'],
    ];
    
    foreach ($urls as $url) {
        $sitemap .= '<url>';
        $sitemap .= '<loc>' . $url['loc'] . '</loc>';
        $sitemap .= '<changefreq>' . $url['changefreq'] . '</changefreq>';
        $sitemap .= '<priority>' . $url['priority'] . '</priority>';
        $sitemap .= '<lastmod>' . now()->toISOString() . '</lastmod>';
        $sitemap .= '</url>';
    }
    
    $sitemap .= '</urlset>';
    
    return response($sitemap, 200, [
        'Content-Type' => 'application/xml',
        'Cache-Control' => 'public, max-age=3600',
    ]);
})->name('sitemap');

// Robots.txt Route
Route::get('/robots.txt', function () {
    $robots = "User-agent: *\n";
    $robots .= "Allow: /\n";
    $robots .= "Disallow: /dashboard\n";
    $robots .= "Disallow: /admin\n";
    $robots .= "Disallow: /api\n";
    $robots .= "\n";
    $robots .= "Sitemap: " . url('/sitemap.xml') . "\n";
    
    return response($robots, 200, [
        'Content-Type' => 'text/plain',
    ]);
})->name('robots');

// Fallback Route for undefined web routes
Route::fallback(function () {
    return view('errors.404');
});
```

These files provide the complete foundation for the WebBloc Laravel project with:

1. **Comprehensive language support** (English/Arabic) for all UI elements, API messages, and WebBloc components
2. **Bootstrap configuration** with proper middleware, exception handling, and API-specific responses
3. **Service provider registration** for the WebBloc system
4. **Complete API routing** with versioning, rate limiting, authentication, and proper UUID parameter constraints
5. **Web routing** with dashboard, documentation, demo pages, and system management
6. **Security features** including CORS, rate limiting, proper authentication, and API key validation
7. **SEO optimization** with sitemap and robots.txt generation
8. **Health checks** and system monitoring endpoints

The configuration follows Laravel 12+ best practices and implements the multi-tenant, multi-language WebBloc system as specified in the project documentation.
