<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $posts = Post::all();

        return view('posts.index', ['posts' => $posts]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        $users = User::all(); // Retrieve all users

        return view('posts.create', compact('users'));

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $request->validate([
            'content' => 'required|string|max:255',
            'user_id' => 'required|exists:users,id', // Ensure user_id exists in the users table
        ]);
        $post = new Post;
        $post->content = $request->input('content');
        $post->user_id = $request->input('user_id');
        $post->save();

        return redirect()->route('posts.index')->with('success', 'Post created successfully!');

    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        //
        return view('posts.show', compact('post'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $post)
    {
        //
        $users = User::all(); // Retrieve all users

        return view('posts.edit', compact('post', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post)
    {
        //
        $request->validate([
            'content' => 'required|string|max:255',
            'user_id' => 'required|exists:users,id', // Ensure user_id exists in the users table
        ]);

        // Update the post with validated data
        $post->content = $request->input('content');
        $post->user_id = $request->input('user_id');
        $post->save();

        // Redirect back to the index page with success message
        return redirect()->route('posts.index')->with('success', 'Post updated successfully!');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        //
        $post->delete();

        return redirect()->route('posts.index')->with('success', 'Post updated successfully!');

    }
}
