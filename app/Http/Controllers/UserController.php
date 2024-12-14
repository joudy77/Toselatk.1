<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\TemporaryUser;
use Illuminate\Http\Request;
//use Illuminate\Support\Facade\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerificationCodeMail;
use Brick\Math\BigInteger;

use function Laravel\Prompts\password;


/*
// مثلا تحديث كلمة مرور مستخدم معين
$user = User::find(2); // وضع رقم المعرف الصحيح للمستخدم
$user->password = Hash::make('your_plain_password');
$user->save();
*/

class UserController extends Controller
{

  
    public function login(Request $request) {
        $request->validate([
            'number' => 'required|string|max:10',
            'password' => 'required|string|max:15|min:8',
        ]);
    
        $credentials = $request->only('number', 'password');
    
        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'invalid number or password'], 401);
        }
    
        $user = User::where('number', $request->number)->firstOrFail();
        $token = $user->createToken('myToken')->plainTextToken;
    
        return response()->json(['message' => 'login Successful', 'token' => $token], 200);
    }
    
   
  

    public function logout(Request $request){
$request->user()->currentAccessToken()->delete();
return response()->json(['message =>logout Successful'],200);
    }
    public function sendVerificationCode(Request $request)
{
    $validatedData = $request->validate([
        'email' => 'required|string|email|max:30|unique:temporary_users',
        'number' => 'required|string|max:15',
        'password'=>'required|string|max:15|min:8'
    ]);

    $verificationCode = Str::random(4);

    TemporaryUser::create([
        'email' => $validatedData['email'],
        'number' => $validatedData['number'],
        'verification_code' => $verificationCode,
        'password' =>$validatedData['password'],
        'expires_at' => now()->addMinutes(30)
    ]);

    Mail::to($validatedData['email'])->send(new VerificationCodeMail($verificationCode));

    return response()->json(['message'=>'Verification code sent.'],200);
}
public function verifyCode(Request $request)
{ 
   
    $validatedData = $request->validate([
        'email' => 'required|string|email|max:45',
        'code' => 'required|string|max:4',
    ]);

    $temporaryUser = TemporaryUser::where('email', $validatedData['email'])
        ->where('verification_code', $validatedData['code'])
        ->where('expires_at', '>=', now())
        ->first();
        session_start();
    if ($temporaryUser) {
        // حفظ البريد الإلكتروني في الجلسة
        session(['verified_email' => $validatedData['email']]);
        return response()->json(['message' => 'Code verified.'],200);
    } else {
        return response()->json(['message' => 'Invalid or expired code.'], 400);
    }
}

public function dataEntry(Request $request)
{
    $validatedData = $request->validate([
        'first_name'=>'required|string|max:15',
        'last_name'=>'required|string|max:15',
         'profile_picture'=>'string',
          'location'=>'required|string|max:100',
          'card_number'=>'required|string|unique:users|max:20',
          'email'=>'string|unique:users|max:45',
           'number' => 'required|string|max:15',
        'password'=>'required|string|max:15|min:8'


       
    ]);
   /*$email = session('verified_email');
   
    if (!$email) 
    return response()->json(['message' => 'No verified email found in session.'], 400);
    $tempUser = TemporaryUser::where('email', $email)->first();
      if (!$tempUser) 
     return response()->json(['message' => 'Temporary user not found.'], 404);*/
     if (empty($validatedData['profile_picture'])) 
      $validatedData['profile_picture'] = 'https://arablite.com/wp-content/uploads/2017/12/een-beheerder-toevoegen-aan-je-facebookpagina-310-w1200.jpg"';
   $user= User::create([
        'first_name' => $validatedData['first_name'],
        'last_name' => $validatedData['last_name'],
        //'password' => $tempUser->password,
       // 'email' => $tempUser->email,
        //'number' => $tempUser->number,
        'card_number' => $validatedData['card_number'],
        'profile_picture' => $validatedData['profile_picture'],
        'location' => $validatedData['location'],
        'email' => $validatedData['email'],
        'number' => $validatedData['number'],
        'password' =>bcrypt( $validatedData['password'])

    ]);
    $token = $user->createToken('myToken')->plainTextToken;
    //$tempUser->delete();
   // session()->forget('verified_email');

   return response()->json(['message' => 'User registered successfully.','token'=>$token],201);
}


    //
}
