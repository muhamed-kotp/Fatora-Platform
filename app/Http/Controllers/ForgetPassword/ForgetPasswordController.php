<?php

namespace App\Http\Controllers\ForgetPassword;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Mail\ForgetPasswordMail;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ForgetPasswordController extends Controller
{
    public function forgetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json($errors);
        }
        $token = Str::random(64);
        DB::table('password_reset_tokens')->insert([
            'email' => $request->email,
            'token' => $token,
            'created_at'=> Carbon::now()
        ]);

        Mail::to($request->email)->send(new ForgetPasswordMail($token));

        return response()->json([
            'status' => true,
            'token' => $token,
            'message' => 'We have send an email to reset password',
        ],200);

    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|confirmed|max:100|min:5',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json($errors);
        }

        $updatePassword = DB::table('password_reset_tokens')
        ->where([
            "email" =>$request->email,
            "token" => $request->token
        ])->first();

        if(!$updatePassword){
            return response()->json([
                'status' => false,
                'error' => 'invalid',
            ]);
        }
        User::where('email', $request->email)
        ->update([
            'password'=> Hash::make($request->password),
        ]);

        DB::table('password_reset_tokens')
        ->where([
            "email" =>$request->email,
        ])->delete();

        return response()->json([
                'status' => true,
                'message' => 'Password Reset Success',
            ],200);
    }
}