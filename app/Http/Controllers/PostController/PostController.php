<?php

namespace App\Http\Controllers\PostController;

use App\Models\Like;
use App\Models\Post;
use App\Models\Share;
use Illuminate\Http\Request;
use App\Traits\AuthorizeCheck;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    use AuthorizeCheck;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $user = Auth::user();
        //Group All friends Ids to show their posts
        $all_friends = [];
        array_push($all_friends,$user->id);
        if(count($user-> friends)>0){
            foreach ($user-> friends as $key => $value) {
                array_push($all_friends,$value->id);
            }
        }
        if(count($user-> friendships)>0){
            foreach ($user-> friendships as $key => $value) {
                array_push($all_friends,$value->id);
            }
        }
        //Group Posts of friends
        $all_posts = [];
        $posts = Post::orderBy('id','DESC')->paginate(6);
        foreach ($posts as $key => $value)
        {
            if(in_array($value->user_id,$all_friends))
            {
                array_push($all_posts,$value);
                        $value->comments;
                        $value->likes;
                        $value->shares;
            }
        }
        return response()->json([
            'posts' => $all_posts
        ]);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        // validation
        $validator = Validator::make($request->all(), [
            'content' => 'string',
            'img' => 'image|mimes:jpg,png',
            'video' => 'mimetypes:video/avi,video/mpeg,video/quicktime,video/mp4'
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json($errors);
        }

        if ($request->hasFile('img')){
            // move
            $img = $request->file('img');
            $ext = $img->getClientOriginalExtension();
            $imgName = "items-" . uniqid() . ".$ext";
            $img->move(public_path('uploads/posts/images'), $imgName);
        }
        else{
            $imgName = NULL;
        }
        if ($request->hasFile('video')){
            // move
            $video = $request->file('video');
            $ext = $video->getClientOriginalExtension();
            $videoName = "video-" . uniqid() . ".$ext";
            $video->move(public_path('uploads/posts/videos'),$videoName);
        }
        else{
            $videoName = NULL;
        }

        $post = Post::create([
            'content' => $request->content,
            'img' => $imgName,
            'video' => $videoName,
            'user_id' => Auth::user()->id,
        ]);

        return response()->json([
            'success'=>'The Post is Created sucssefully',
            'post' => $post
        ],200);
    }//End Method


    /**
     * Display the specified resource.
     */
    public function show( $id)
    {
        $post = Post::findOrFail($id);
        $post->comments;
        $post->likes;
        $post->shares;
        return response()->json([
          'success'=>true,
            'post' => $post
        ],200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $post = Post::findOrFail($id);
        $this->authorizCheck($post->user_id);
        return response()->json([
          'success'=>true,
            'post' => $post
        ],200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $post = Post::findOrFail($id);
        $this->authorizCheck($post->user_id);
        $validator = Validator::make($request->all(), [
            'content' => 'string',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json($errors);
        }
        $post->update([
            'content' => $request->content,
            'img' => $request->img,
            'video' => $request->video,
            'user_id' => Auth::user()->id,
        ]);
        return response()->json([
            'success'=>'The Post is Updated sucssefully',
            'post' => $post
        ],200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $post = Post::findOrFail($id);
        $this->authorizCheck($post->user_id);
        if ($post->img !== null) {
            unlink(public_path('uploads/posts/images/') . $post->img);
        }
        if ($post->video !== null) {
            unlink(public_path('uploads/posts/videos/') . $post->video);
        }
        $post-> delete();
        $success= 'The Post is Deleted sucssefully' ;
        return response()->json($success);
    }

    public function like (Request $request)
    {
        $validator = Validator::make($request->all(), [
            'post_id' => 'required|exists:posts,id',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json($errors);
        }
        $like = DB::table('likes')  ->where('user_id',Auth::user()->id)
                                    ->where('post_id',$request->post_id)
                                    ->get();
        //if you allready Like The post, then UnLike It, and delete the record
        if(count($like)>0)
        {
            $like = Like::findOrFail($like[0]->id);
            $like->delete();
            return response()->json([
                'success'=>'You Successfully Unliked the Post',
            ],200);
        }
        //Like The Post Successfully
        else{
            Like::create([
                'post_id' => $request->post_id,
                'user_id' => Auth::user()->id
            ]);
            return response()->json([
                'success'=>'You Successfully Liked the Post',
            ],200);
        }
    }
    public function share (Request $request)
    {
        $validator = Validator::make($request->all(), [
            'post_id' => 'required|exists:posts,id',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json($errors);
        }

        $post = Post::findOrFail($request->post_id);

            Post::create([
                'content' => $post->content,
                'img' => $post->img,
                'video' => $post->video,
                'user_id' => Auth::user()->id,
            ]);
            Share::create([
                'post_id' => $request->post_id,
                'user_id' => Auth::user()->id
            ]);

            return response()->json([
                'success'=>'You Successfully Shared this Post',
                'post' =>$post
            ],200);

    }




}
