<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Notification;
use Jenssegers\Agent\Agent;

class AuthController extends Controller
{
    /**
     * عرض صفحة الترحيب
     */
    public function welcome()
    {
        return view('welcome');
    }

    /**
     * إعادة توجيه من مسار /login إلى الصفحة الرئيسية
     */
    public function redirectToHome()
    {
        return redirect('/');
    }
    /**
     * تسجيل الدخول المخصص
     */
    public function manualLogin(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $user = Auth::user();
            $agent = new Agent();

            // جمع معلومات الجهاز للتسجيل
            $device = $agent->device();
            $platform = $agent->platform();
            $browser = $agent->browser();
            $ip = $request->ip();

            if ($user) {
                // إرسال إشعار تسجيل الدخول للمدراء
                $adminUsers = User::where('role', 'Admin')->get();
                foreach ($adminUsers as $admin) {
                    Notification::create([
                        'user_id' => $admin->id,
                        'title' => 'تسجيل دخول',
                        'message' => "تم تسجيل دخول المستخدم {$user->name} من جهاز {$device} ونظام {$platform} ومتصفح {$browser} من IP: {$ip}",
                        'type' => 'login',
                    ]);
                }
            }

            // التوجيه حسب نوع المستخدم
            if ($user->role === 'Company') {
                return redirect('/company/land-trips');
            } else {
                return redirect('/bookings');
            }
        }

        return back()->withErrors(['email' => 'بيانات الدخول غير صحيحة'])->withInput();
    }

    /**
     * تسجيل الخروج
     */
    public function logout(Request $request)
    {
        $user = Auth::user();
        $agent = new Agent();

        // جمع معلومات الجهاز للتسجيل
        $device = $agent->device();
        $platform = $agent->platform();
        $browser = $agent->browser();
        $ip = $request->ip();

        if ($user) {
            // إرسال إشعار تسجيل الخروج للمدراء
            $adminUsers = User::where('role', 'Admin')->get();
            foreach ($adminUsers as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'title' => 'تسجيل خروج',
                    'message' => "تم تسجيل خروج المستخدم {$user->name} من جهاز {$device} ونظام {$platform} ومتصفح {$browser} من IP: {$ip}",
                    'type' => 'logout',
                ]);
            }
        }

        Auth::logout();
        return redirect('/');
    }

    /**
     * الحماية من أدوات المطور
     */
    public function devtoolsLogout()
    {
        if (Auth::check()) {
            Notification::create([
                'user_id' => Auth::id(),
                'message' => "محاولة فحص الصفحة أو التعديل عبر أدوات المطور.",
                'type' => 'تنبيه أمني',
            ]);
            Auth::logout();
        }
        return response()->json(['status' => 'ok']);
    }

    /**
     * عرض صفحة تسجيل مستخدم جديد (للأدمن فقط)
     */
    public function showRegisterForm()
    {
        if (Auth::user()->role !== 'Admin') {
            abort(403, 'غير مصرح لك بالوصول لهذه الصفحة');
        }

        return app(\App\Http\Controllers\Auth\RegisterController::class)->showRegistrationForm();
    }

    /**
     * تسجيل مستخدم جديد (للأدمن فقط)
     */
    public function register(Request $request)
    {
        if (Auth::user()->role !== 'Admin') {
            abort(403, 'غير مصرح لك بالوصول لهذه الصفحة');
        }

        return app(\App\Http\Controllers\Auth\RegisterController::class)->register($request);
    }
}
