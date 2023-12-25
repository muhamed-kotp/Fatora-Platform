<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{

    public function profile()
    {
        $user = User::findOrFail(Auth::user()->id) ;
        $user-> recieveFrindRequest;
        $user->posts;

        return response()->json([
            'user' => $user,
        ]);

    }

        //function to handle Register operation
    public function Register(Request $request)
    {
            $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email|max:100',
            'password' => 'required|string|confirmed|max:100|min:5',
            'bio' => 'string',
            'img'=>'image|mimes:jpg,png',
            'phone'=>'string|max:20',
            'address' =>'string'
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json($errors);
        }

        if ($request->hasFile('img'))
        {
            // move
            $img = $request->file('img');
            $ext = $img->getClientOriginalExtension();
            $name = "UserImage-" . uniqid() . ".$ext";
            $img->move(public_path('uploads/Users_images'), $name);
        }
        else{
            $name = null ;
        }

         //store in database
       $user = User::create([
            'name' => $request->name  ,
            'email'=> $request->email ,
            'password'=> Hash::make($request->password),
            'bio' => $request->bio ,
            'img'=>$name,
            'phone'=>$request->phone,
            'address' =>$request->address
        ]);
        return response()->json([
            'status' => true,
            'message' => 'user created successfully',
            'token' => $user->createToken("API TOKEN")->plainTextToken
        ],200);

    }//End Method

    //function to handle Login operation
    public function Login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:100',
            'password' => 'required|string|max:100|min:5',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json($errors);
        }
        if (!Auth::attempt(['email' => $request->email, 'password' => $request->password]) )
        {
            $error = 'the credintials is not correct';
            return response()->json($error);
        }
        $user = User::where('email',$request->email)->first();
        return response()->json([
            'status' => true,
            'message' => 'user Logged in successfully',
            'token' => $user->createToken("API TOKEN")->plainTextToken
        ],200);
    }//End Method

    //function to allow Searching Usres
    public function search(Request $request)
    {
        $keyword = $request->keyword ;
        $users = User::where('name','like',"%$keyword%")->get();
        return response()->json($users);
    }

    //function to allow users to Update thier settings
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'password' => 'required|string|confirmed|max:100|min:5',
            'bio' => 'string',
            'img'=>'image|mimes:jpg,png',
            'phone'=>'string|max:20',
            'address' =>'string'
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json($errors);
        }

        $user = $request->user();
        $name = $user->img ;

        if ($request->hasFile('img')) {
            if ($name !== null) {
                unlink(public_path('uploads/Users_images/') . $name);
            };
         // move
            $img = $request->file('img');
            $ext = $img->getClientOriginalExtension();
            $name = "UserImage-" . uniqid() . ".$ext";
            $img->move(public_path('uploads/Users_images'), $name);

        }

         //store in database
        $user->update([
            'name' => $request->name  ,
            'password'=> Hash::make($request->password),
            'bio' => $request->bio ,
            'img'=>$name,
            'phone'=>$request->phone,
            'address' =>$request->address
        ]);
        return response()->json([
            'status' => true,
            'message' => 'Settiings updated successfully',
        ],200);

    }//End Method

    //function to handle logout operation
    public function logout(Request $request)
    {
        $user = $request->user();
        $user->tokens()->delete();
        return response()->json([
        'status' => true,
        'message' => 'Logged out successfully'
        ]);
    }//End Method



    public function redirectToProvider ()
    {
        return Socialite::driver('github')->redirect();
    }
    public function handleProviderCallback ()
    {
        $githubUser = Socialite::driver('github')->user();
        $email= $githubUser->email ;

        $db_user =User::where('email',$email)->first();

        if($db_user==null){
            $user = User::updateOrCreate([
                'name' => $githubUser->name,
                'email' => $githubUser->email,
                'password' =>Hash::make(123456),
                'oauth_token' => $githubUser->token,
            ]);

            Auth::login($user);
        }else{
            Auth::login($db_user);
        }
        return redirect(route('category.index'));
    }


}