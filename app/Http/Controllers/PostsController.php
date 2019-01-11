<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Post;
use Illuminate\Support\Facades\Storage;

class PostsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except'=> ['index', 'show']]);
    }
    public function index(){
        $posts = Post::orderBy('created_at', 'desc')->paginate(10);
        return view('posts.index')->with('posts', $posts);
    }
    public function create(){
        return view('posts.create');
    }
    public function edit($id){
        $post = Post::find($id);
        if(auth()->user()->id !== $post->user_id)
        {
            return redirect('posts')->with('error', 'Unauthorized Page');
        }
        return view('posts.edit')->with('post', $post);
    }
    public function show($id){
        $post = Post::find($id);
        return view('posts.show')->with('post', $post);
    }
    public function store(Request $request){
        $this->validate($request,[
            'title' =>'required',
            'body' => 'required',
            'cover_image' => 'image|nullable|max:10000'
        ]);
        if($request->hasFile('cover_image')){
            $fileNameWithExt = $request->file('cover_image')->getClientOriginalName();
            $filename = pathinfo($fileNameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('cover_image')->getClientOriginalExtension();
            $fileNameToStore = $filename.'_'.time().'.'.$extension;
            $path = $request->file('cover_image')->storeAs('public/cover_images', $fileNameToStore);
        }else{
            $fileNameToStore = 'noimage.jpq';
        }
        $post = new Post;
        $post->title = $request->input('title');
        $post->body = $request->input('body');
        $post->user_id = auth()->user()->id;
        $post->cover_image = $fileNameToStore;
        $post->save();
        return redirect('/home')->with('success', 'Post Created');
    }
    public function update(Request $request, $id){
        $this->validate($request,[
            'title' =>'required',
            'body' => 'required',
            'cover_image' => 'image|nullable|max:10000'
        ]);
        if($request->hasFile('cover_image')){
            $fileNameWithExt = $request->file('cover_image')->getClientOriginalName();
            $filename = pathinfo($fileNameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('cover_image')->getClientOriginalExtension();
            $fileNameToStore = $filename.'_'.time().'.'.$extension;
            $path = $request->file('cover_image')->storeAs('public/cover_images', $fileNameToStore);
        }
        $post = Post::find($id);
        $post->title = $request->input('title');
        $post->body = $request->input('body');
        if($request->hasFile('cover_image')){
            $post->cover_image = $fileNameToStore;
        }
        $post->save();
        return redirect('/home')->with('success', 'Post Updated');
    }
    public function destroy($id){
        $post = Post::find($id);
        if(auth()->user()->id !== $post->user_id)
        {
            return redirect('posts')->with('error', 'Unauthorized Page');
        }
        if($post->cover_image != 'noimage.jpq'){
            Storage::delete('public/cover_image/'.$post->cover_image);
        }
        $post->delete();
        return redirect('/home')->with('success', 'Post Removed');
    }

}
