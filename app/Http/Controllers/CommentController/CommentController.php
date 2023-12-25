<?php

namespace App\Http\Controllers\CommentController;

use App\Models\Comment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Traits\AuthorizeCheck;

class CommentController extends Controller
{
    use AuthorizeCheck;
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string',
            'post_id' => 'required|exists:posts,id',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json($errors);
        }
        $comment =Comment::create([
            'content' => $request->content,
            'post_id' => $request->post_id,
            'user_id' => Auth::user()->id,
        ]);
        return response()->json([
            'success'=>'The Comment created sucssefully',
            'comment' => $comment
        ],200);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $comment = Comment::findOrFail($id);
        return response()->json([
        'success'=>true,
            'comment' => $comment
        ],200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $comment = Comment::findOrFail($id);
        $this->authorizCheck($comment->user_id);
            return response()->json([
                'success'=>true,
                'comment' => $comment
            ],200);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $comment = Comment::findOrFail($id);
        $this->authorizCheck($comment->user_id);
        $validator = Validator::make($request->all(), [
            'content' => 'required|string',
            'post_id' => 'required|exists:posts,id',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json($errors);
        }
        $comment->update([
            'content' => $request->content,
            'post_id' => $request->post_id,
            'user_id' => Auth::user()->id,
        ]);

        return response()->json([
            'success'=>'The Comment is successfully updated',
            'comment' => $comment
        ],200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $comment = Comment::findOrFail($id);
        $this->authorizCheck($comment->user_id);
        $comment->delete();
        $success= 'The Comment is Deleted sucssefully' ;
        return response()->json($success);


    }
}