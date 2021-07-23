<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Api\PostRequest;
use App\Http\Requests\Api\UpdatePostRequest;
use App\Http\Resources\Api\PostResource;
use App\Post;

class PostApiController extends BaseApiController
{
    public function viewSinglePost(Post $post){
        try{
            //return single post
            $post =  new PostResource($post);

            $response['response']['responseDescription'] = "Here is your post";
            $response['status'] = true;
            $response['post'] = $post;

            return $this->respond($response);
            
        }catch(\Exception $e){
            return $this->respondWithError($e->getMessage());
        }catch(\Error $e){
            return $this->respondWithError($e->getMessage());
        }
    }

    public function viewAllPost(){
        try{
            //return all posts
            $posts =  Post::paginate(10);

            $response['response']['responseDescription'] = "Here all all posts";
            $response['status'] = true;
            $response['post'] = $posts;

            return $this->respond($response);

        }catch(\Exception $e){
            return $this->respondWithError($e->getMessage());
        }catch(\Error $e){
            return $this->respondWithError($e->getMessage());
        }
    }

    public function createPost(PostRequest $request){
        try{
            //create Post
            $post = Post::create($request->all());

            $response['response']['responseDescription'] = "New Post Created";
            $response['created_post'] = $post;

            return $this->respondCreated($response, "Post Created Successfully");

        }catch(\Exception $e){
            return $this->respondWithError($e->getMessage());
        }catch(\Error $e){
            return $this->respondWithError($e->getMessage());
        }
    }

    public function updatePost(PostRequest $request, Post $post){
        try{
            $input = $request->all();
            //update Post
            $update = $post->update($input);

            $post = $post->refresh();


            $response['response']['responseDescription'] = "Post Updated Successfully";
            $response['status'] = true;
            $response['updated_post'] = $post;

            return $this->respond($response);

        }catch(\Exception $e){
            return $this->respondWithError($e->getMessage());
        }catch(\Error $e){
            return $this->respondWithError($e->getMessage());
        }
    }

    public function deletePost(Post $post){
        try{
            if($post->delete()){

                $response['status'] = true;
                $response['response']['responseCode'] = 200;
                $response['response']['responseDescription'] = "Post Deleted Successfully";

                return $this->respond($response);

            }else{
                return $this->respondWithError("Post Delete Failed");
            }

        }catch(\Exception $e){
            return $this->respondWithError($e->getMessage());
        }catch(\Error $e){
            return $this->respondWithError($e->getMessage());
        }
    }
}
