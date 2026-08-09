<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /* ===== VIEWS ===== */
    public function showRegisterForm() { return view('auth.register'); }
    public function showLoginForm()    { return view('auth.login'); }
    public function showResetForm()    { return view('auth.reset_password'); }

    /* ===== RESET PASSWORD (demo) ===== */
    public function handleReset(Request $request)
    {
        $request->validate(['email' => ['required','email']]);
        return back()->with('status','Nếu email tồn tại, hệ thống sẽ gửi liên kết đặt lại mật khẩu.');
    }

    private function generateUid3(string $prefix): string
    {
        return DB::transaction(function () use ($prefix) {
            $length = strlen($prefix) + 2; 

            $maxNum = DB::table('users')
                ->where('user_id', 'like', $prefix . '_%')
                ->selectRaw("MAX(CAST(SUBSTRING(user_id, $length) AS UNSIGNED)) as max_num")
                ->lockForUpdate()
                ->value('max_num');

            $next = (int)($maxNum ?? 0) + 1;

            return sprintf('%s_%03d', $prefix, $next);
        });
    }

    /* ===== REGISTER ===== */
    public function register(Request $request)
    {
        $data = $request->validate([
            'firstName'   => ['required','string','max:100'],
            'lastName'    => ['required','string','max:100'],
            'email'       => ['required','email:rfc,dns','max:255','unique:users,email'],
            'password'    => ['required','confirmed','min:6'],
            'birth_date'  => ['required','date'],
            'gender'      => ['required','in:Nam,Nữ,Khác'], 
            'phone'       => ['required','string','max:20'],
            'address'     => ['required','string','max:255'],
        ]);

        // Quy ước: @ad.com => admin (AD_), @staff.com => staff (ST_), @gmail.com => user (US_)
        $email   = Str::lower($data['email']);
        $isAdmin = Str::endsWith($email, '@ad.com');
        $isStaff = Str::endsWith($email, '@staff.com');
        $isUser = Str::endsWith($email, '@gmail.com');
        
        if ($isAdmin) {
            $role = 'admin';
            $prefix = 'AD';
        } elseif ($isStaff) {
            $role = 'staff';
            $prefix = 'ST';
        } elseif ($isUser) {
            $role = 'user';
            $prefix = 'US';
        } else {
            // Domain khác vẫn cho đăng ký như user để không chặn flow hiện tại.
            $role = 'user';
            $prefix = 'US';
        }

        $uid = $this->generateUid3($prefix);

        User::create([
            'user_id'    => $uid,
            'name'       => trim($data['firstName'].' '.$data['lastName']),
            'email'      => $data['email'],
            'password'   => Hash::make($data['password']),
            'role'       => $role,
            'birth_date' => $data['birth_date'],
            'gender'     => $data['gender'],   // 'Nam' / 'Nữ' / 'Khác'
            'phone'      => $data['phone'],
            'address'    => $data['address'],
        ]);

        return redirect()->route('auth.login')->with('status','Đăng ký thành công! Hãy đăng nhập.');
    }

    /* ===== LOGIN ===== */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required','email'],
            'password' => ['required'],
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $role = Auth::user()->role;
            if ($role === 'admin' || $role === 'staff') {
                return redirect()->route('dashboard.index')->with('success','Đăng nhập thành công!');
            }

            return redirect()->route('jobs.index')->with('success','Đăng nhập thành công!');
        }

        return back()->withErrors(['email' => 'Email hoặc mật khẩu không đúng.'])->onlyInput('email');
    }

    /* ===== LOGOUT ===== */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('auth.login')->with('status','Đã đăng xuất.');
    }
}
