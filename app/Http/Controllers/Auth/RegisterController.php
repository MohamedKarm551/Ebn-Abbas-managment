<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
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
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
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
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }
    protected function guard()
    {
        return Auth::guard();
    }
    protected function registered(Request $request, $user)
    {
        // ممكن تضيف أي logic هنا لو عايز تعمل حاجة بعد التسجيل مباشرة
        // لو معملتش return لحاجة، هيعمل redirect لـ $redirectTo
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
