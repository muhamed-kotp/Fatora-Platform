<?php

namespace App\Http\Controllers\RelationshipSystem;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class RelationshipController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $all_friends = [];
        if(count($user-> friends)>0){
            foreach ($user-> friends as $key => $value) {
                array_push($all_friends,$value);
            }
        }
        if(count($user-> friendships)>0){
            foreach ($user-> friendships as $key => $value) {
                array_push($all_friends,$value);
            }
        }
        return response()->json( [
            'status' => true,
            'friends' => $user
        ],200);
    }

    //Handlin Sending Friend requests
    public function send(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json($errors);
        }
        //first serching if there is allready a friend request
        $friendRequest = DB::table('relationships')
        ->where('user1_id',Auth::user()->id)
        ->where('user2_id',$request->user_id)
        ->orWhere('user1_id',$request->user_id)
        ->where('user2_id',Auth::user()->id)
        ->get();

        //then serching if there are allready friends
        $friend = DB::table('friends')
        ->where('friend1_id',Auth::user()->id)
        ->where('friend2_id',$request->user_id)
        ->orWhere('friend1_id',$request->user_id)
        ->where('friend2_id',Auth::user()->id)
        ->get();

        //if The Friend Request exists
        if(count($friendRequest)>0){
            return response()->json([
                'error'=>'The friend request allready sent',
            ],404);
        }
        //if there are allready friends
        else if(count($friend)>0)
        {
            return response()->json([
                'error'=>'You are allready friends',
            ],404);
        }
        //if User trying to send request to himself
        else if (Auth::user()->id == $request->user_id)
        {
            return response()->json([
                'error'=>'You can not send request to your self',
            ],404);
        }
        //send the request correctly
        $user1 = User::findOrFail(Auth::user()->id) ;
        $user1->sendFrindRequest()->attach($request->user_id);
        $success= 'The friend request sent successfully' ;
        return response()->json($success);
    }
    //user2 accept the friend request from user1
    public function accept(Request $request)
    {
    //Searching for The friend request in the relationships table
        $friendRequest = DB::table('relationships')
        ->where('user1_id',$request->user_id)
        ->where('user2_id',Auth::user()->id)
        ->get();
    //if not found
        if(count($friendRequest)==0){
            return response()->json([
                'error'=>'There is No friend requests to accept ',
            ],404);
        }
        else{
            //Delete The friend request from relationships table
                DB::table('relationships')
                ->where('user1_id',$request->user_id)
                ->where('user2_id',Auth::user()->id)
                ->delete();
            //Add User1 and User2 in friends table
                $user = User::findOrFail($request->user_id) ;
                $user->friends()->attach(Auth::user()->id);
                return response()->json([
                    'succes' =>"Congratulation! you become a friend now with $user->name",
                    'friend' => $user
                ],200);
        }
    }

    public function reject (Request $request)
    {
    //Searching for The friend request in the relationships table
        $friendRequest = DB::table('relationships')
        ->where('user1_id',$request->user_id)
        ->where('user2_id',Auth::user()->id)
        ->get();
    //if not found
        if(count($friendRequest)==0){
            return response()->json([
                'error'=>'There is No friend requests to Reject ',
            ],404);
        }
        else{
            //Delete The friend request from relationships table
                DB::table('relationships')
                ->where('user1_id',$request->user_id)
                ->where('user2_id',Auth::user()->id)
                ->delete();

                return response()->json([
                    'succes' =>"you rejected the friend request successfully",
                ],200);
        }
    }


    public function delete(Request $request)
    {
        //serching in friends table
        $friend = DB::table('friends')
        ->where('friend1_id',Auth::user()->id)
        ->where('friend2_id',$request->user_id)
        ->orWhere('friend1_id',$request->user_id)
        ->where('friend2_id',Auth::user()->id)
        ->get();


         //if it's not your friend
        if(count($friend)==0)
        {
            return response()->json([
                'error'=>"It's not your friend",
            ],404);
        }

        $friend = DB::table('friends')
        ->where('friend1_id',Auth::user()->id)
        ->where('friend2_id',$request->user_id)
        ->orWhere('friend1_id',$request->user_id)
        ->where('friend2_id',Auth::user()->id)
        ->delete();

        return response()->json([
            'success'=>"You removed it from your frinds list  ",
        ],404);
    }
}
