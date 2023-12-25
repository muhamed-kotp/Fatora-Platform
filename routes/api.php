<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CommentController\CommentController;
use App\Http\Controllers\ForgetPassword\ForgetPasswordController;
use App\Http\Controllers\PostController\PostController;
use App\Http\Controllers\RelationshipSystem\RelationshipController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::group(['middleware' => ["auth:sanctum"]], function () {

    //Profile
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');

    //Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    //Searching users
    Route::post('/search', [AuthController::class, 'search'])->name('search');

    //Update
    Route::post('/user/update', [AuthController::class, 'update'])->name('user.update');

    //Remove Friends

    //Posts Resource
    Route::resource('posts',PostController::class);

    //Like Posts
    Route::post('/like',[PostController::class, 'like'])->name('like');
    //Share Posts
    Route::post('/share',[PostController::class, 'share'])->name('share');

    //Comments Resource
    Route::resource('comments',CommentController::class);
    //Relationship System
    Route::group(['prefix' =>'friend'], function () {
        //Get all Your Friends
        Route::get('/allfriends',  [RelationshipController::class, 'index'  ]);
        //Sending requests
        Route::post('/send',  [RelationshipController::class, 'send'  ])->name('send');
        //Accept requests
        Route::post('/accept',[RelationshipController::class, 'accept'])->name('accept');
        //Reject requests
        Route::post('/reject',[RelationshipController::class, 'reject'])->name('reject');
        //Remove Friends
        Route::post('/delete',[RelationshipController::class, 'delete'])->name('friend.delete');
    });
});


//Registeration
Route::post('/register',[AuthController::class,'Register'])->name('register');
//Login
Route::post('/login',[AuthController::class,'Login'])->name('login');
//Forget Password
Route::post('/forget-password',[ForgetPasswordController::class,'forgetPassword'])->name('forget.password');
//Reset Pasword
Route::post('/reset-password',[ForgetPasswordController::class,'resetPassword'])->name('reset.password');
//login with github
Route::get('/login/github',[AuthController::class,'redirectToProvider'])->name('auth.github.redirect');
Route::get('/login/github/callback',[AuthController::class,'handleProviderCallback'])->name('auth.github.callback');
