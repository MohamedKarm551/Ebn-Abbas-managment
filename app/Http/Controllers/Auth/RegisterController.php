<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Company; // ✅ إضافة استيراد موديل الشركة

use App\Traits\RegistersUsers; // Ensure the trait exists in your application or adjust the namespace accordingly
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:Admin,employee,Company'], // ✅ إضافة تحقق من الرول
        ];

        // ✅ إضافة شرط للشركة إذا كان النوع شركة
        if (isset($data['role']) && $data['role'] === 'Company') {
            $rules['company_id'] = ['required', 'exists:companies,id'];
        }

        return Validator::make($data, $rules, [
            'role.required' => 'يرجى اختيار نوع المستخدم',
            'role.in' => 'نوع المستخدم غير صالح',
            'company_id.required' => 'يرجى اختيار الشركة',
            'company_id.exists' => 'الشركة المختارة غير موجودة',
            'name.required' => 'الاسم مطلوب',
            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.email' => 'البريد الإلكتروني غير صالح',
            'email.unique' => 'البريد الإلكتروني مستخدم بالفعل',
            'password.required' => 'كلمة المرور مطلوبة',
            'password.min' => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل',
            'password.confirmed' => 'تأكيد كلمة المرور غير متطابق',
        ]);
    }


    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        $userData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'], // ✅ إضافة الرول
        ];

        // ✅ إضافة company_id إذا كان النوع شركة
        if ($data['role'] === 'Company' && isset($data['company_id'])) {
            $userData['company_id'] = $data['company_id'];
        }

        return User::create($userData);
    }

    protected function guard()
    {
        return Auth::guard(); // تأكد من استخدام الحارس الصحيح
    }
        protected function registered(Request $request, $user)
    {
        // ✅ إضافة إشعار عن المستخدم الجديد
        \App\Models\Notification::create([
            'user_id' => 1, // ID الأدمن الرئيسي
            'title' => 'مستخدم جديد',
            'message' => "تم تسجيل مستخدم جديد: {$user->name} ({$user->role})",
            'type' => 'تسجيل جديد',
        ]);

        // ✅ توجيه حسب نوع المستخدم
        if ($user->role === 'Company') {
            return redirect()->route('company.availabilities.index');
        } elseif ($user->role === 'Admin') {
            return redirect()->route('bookings.index');
        } else {
            return redirect()->route('bookings.index');
        }
    }
     /**
     * ✅ إضافة دالة لعرض نموذج التسجيل مع الشركات
     */
    public function showRegistrationForm()
    {
        $companies = Company::orderBy('name')->get();
        return view('auth.register', compact('companies'));
    }
}

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse; // <-- ضيف السطر ده هنا

// *** نهاية الـ use ***

trait RegistersUsers
{
    // Add methods and logic for user registration here.
    // Example placeholder method:
    public function register(Request $request) // <-- لازم نستقبل الـ Request
    {
        // 1. التحقق من صحة البيانات باستخدام دالة validator اللي في الكنترولر
        $this->validator($request->all())->validate();

        // 2. إطلاق حدث التسجيل (عشان لو فيه أي listeners عايزة تعمل حاجة)
        event(new Registered($user = $this->create($request->all())));

        // 3. تسجيل دخول المستخدم الجديد
        $this->guard()->login($user);

        // 4. التحقق لو فيه رد مخصص بعد التسجيل
        if ($response = $this->registered($request, $user)) {
            return $response;
        }

        // 5. لو مفيش رد مخصص، نعمل redirect للصفحة المحددة أو للصفحة المطلوبة
        //    أو نرجع JSON لو الطلب كان AJAX
        return $request->wantsJson()
            ? new JsonResponse([], 201)
            : redirect($this->redirectPath()); // redirectPath() دي دالة helper بتجيب قيمة $redirectTo
    }
    public function redirectPath()
    {
        if (method_exists($this, 'redirectTo')) {
            return $this->redirectTo();
        }

        return property_exists($this, 'redirectTo') ? $this->redirectTo : '/home';
    }
}
