<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use JWTAuth;


class PostController extends BaseController
{
    public function teststr() {
        // $str = '<div><img>kalskjdflk<photo>2</photo>qwjeoifjwqaejf我###<photo>6935</photo>秒发嗯要乃么》';
        // $abc = '/<photo>(\d+)<\/photo>/';
        
        // preg_match_all($abc, $str, $result);

        $tagids = '1,3,56,83,1,56,3,4,7,3,7';

        $tagArr = array_unique(explode(',', $tagids));
        print_r($tagArr);
    }

	// 发布文章
    public function publish(Request $request) {
    	$title = $request->input('title');
    	$post_type = $request->input('type',0);
    	$subject = $request->input('subject');
    	$content = $request->input('content');
    	$comment = $request->input('comment',0);
    	$tagids = $request->input('tags');
        $storage = '';
    	$user_id = 1;
    	$tagArr = [];

        // 获取图片id
        preg_match_all('/<photo>(\d+)<\/photo>/', $content, $result);

        $imgArr = $result[1];

    	// 判断标签是否为空 标签去重复未完成
    	if($tagids != null) {
    		$tag_str = '';
    		$tagArr = array_unique(explode(',', $tagids));
    		$tags = DB::table('tags')->select('id','tag_name')
    								->whereIn('id',$tagArr)
    								->get();

    		foreach($tags as $tag) {
    			$tag_str .=  $tag->id.':'. $tag->tag_name .';';
    		}

    		$tag_str = mb_substr($tag_str, 0, -1);
    	}

        if($imgArr) {
            $font = DB::table('post_photos')->where('id',$imgArr[0])->first();
            $storage = $font->url;
        } 

        // 裁剪封面图片

	   	$data = [
	   		'title' => $title,
	   		'storage' => $storage,
	   		'post_type' => $post_type,
	   		'subject' => $subject,
	   		'content' => $content,
            'tags' => $tag_str,
	   		'user_id' => $user_id,
	   		'comment' => $comment,
	   		'publish_time' => time(),
	   	];

	   	$post_id = DB::table('post')->insertGetId($data);

	   	if($post_id > 0) {
            // 更新文章图片
            if($imgArr) {
                DB::table('post_photos')->whereIn('id',$imgArr)->update(['post_id' => $post_id]);
            }
	   		// 更新我的文章数
	   		DB::table('user_extras')->where('user_id',$user_id)->increment('posts_count',1);
	   		// 增加标签文章数
	   		DB::table('tags')->whereIn('id',$tagArr)->increment('post_count',1);
	   		//增加文章标签对应关系
	   		foreach($tags as $tag) {
	   			DB::table('user_post_tags')->insert([
	   				'user_id' => 1,
	   				'post_id' => $post_id,
	   				'tag_id' => $tag->id
	   			]);
	   		}
	   		return response()->json(['message'=>'success','status_code' => 200]);
	   	} else {
	   		return response()->json(['message'=>'Publish post fail','status_code' => 500]);
	   	}
    }

    public function edit(Request $request, $id) {
        $post_id = $id;
        $user_id = 1;
        $tags = [];
        $photos = [];

        if(!$user_id) {
            return response()->json(['message'=>'Post is null','status_code' => 500]);
        }

        $post = DB::table('post')->where(['id' => $id, 'user_id' => $user_id])->first();
        $imgArr = DB::table('post_photos')->where(['post_id' => $post_id])->get();

       if($post->tags) {
           $tagArr = explode(';',$post->tags);
           foreach($tagArr as $tag) {
             $tagcollection = explode(':',$tag);
             $tags[$tagcollection[0]] = $tagcollection[1];
           }
       }

       foreach($imgArr as $img) {
         $photos[$img->id] = "http://abc.com".$img->url;
       }

       $data = [
           'id' => $post->id,
           'title' => $post->title,
           'content' => $post->content,
           'comment' => $post->comment,
           'tags' => $tags,
           'photos' => $photos,
       ];

       return response()->json($data);

    }

    // 喜欢文章
    public function like(Request $request, $id) {
    	$user_id = JWTAuth::parseToken()->authenticate()->id;
    	$post_id = $id;

        $like_id = DB::table('post_like')->where(['user_id'=> $user_id,'post_id'=>$post_id])->value('id');

        if($like_id == null) {
           $data = [
	            'user_id' => $user_id,
	            'post_id' => $post_id,
	            'create_time' =>time()
        	];

        	// 插入主表记录
        	$like_id = DB::table('post_like')->insertGetId($data);

            if($like_id > 0) {
                // 更新文章表喜欢数
                DB::table('post')->where('id',$post_id)->increment('like_count',1);

                // 发送短消息

                return response()->json(['message'=>'success','status_code' => 200]);
            } else {
            	return response()->json(['message'=>'fail','status_code' => 500]);
            }
        } else {
        	return response()->json(['message'=>'Post already like','status_code' => 500]);
        }
    }

    // 取消喜欢的文章
    public function unlike(Request $request, $id) {
    	
    	$user_id = JWTAuth::parseToken()->authenticate()->id;
    	$post_id = $id;

    	$like_id = DB::table('post_like')->where(['user_id'=> $user_id,'post_id'=>$post_id])->value('id');

    	if($like_id > 0) {
    		DB::table('post_like')->delete($like_id);
    		DB::table('post')->where('id',$post_id)->decrement('like_count',1);

    		// 发送短消息

    		return response()->json(['message'=>'success','status_code' => 200]);
    	} else {
    		return response()->json(['message'=>'Post already unlike','status_code' => 500]);
    	}  
    }

    public function collection(Request $request, $id) {
    	$user_id = JWTAuth::parseToken()->authenticate()->id;
        $post_id = $id;

        $collection_id = DB::table('post_collections')->where(['user_id'=> $user_id,'post_id'=>$post_id])->value('id');

        if($collection_id == null) {
           $data = [
	           'user_id' => $user_id,
	           'post_id' => $post_id,
	           'create_time' =>time()
        	];

        	// 插入主表记录
        	$id = DB::table('post_collections')->insertGetId($data);

            if($id > 0) {
                // 更新文章表喜欢数
                DB::table('post')->where('id',$post_id)->increment('collection_count',1);

                return response()->json(['message'=>'success','status_code' => 200]);
            } else {
            	return response()->json(['message'=>'fail','status_code' => 500]);
            }
        } else {
        	return response()->json(['message'=>'Post already collection','status_code' => 500]);
        }
    }

    public function uncollection(Request $request, $id) {
    	$user_id = $request->input('uid');
        $post_id = $id;
        $type = $request->input('type');

        if($type == 1) {
            // 普通单个取消收藏
            $collection_id = DB::table('post_collections')->where(['user_id'=> $user_id,'post_id'=>$post_id])->value('id');

            if($collection_id > 0) {
                DB::table('post_collections')->delete($collection_id);
                DB::table('post')->where('id',$post_id)->decrement('collection_count',1);

                return response()->json(['message'=>'success','status_code' => 200]);
            } else {
                return response()->json(['message'=>'Post already uncollection','status_code' => 500]);
            } 
        } elseif ($type == 2) {
            // 获取收藏文章ID
            $postidArr = DB::table('post_collections')->where('user_id',$user_id)->pluck('post_id');
            // 减少文章收藏数
            DB::table('post')->whereIn('id',$postidArr)->decrement('collection_count',1);
            // 删除文章和收藏对应数据表
            DB::table('post_collections')->where('user_id',$user_id)->delete();

            return response()->json(['message'=>'success','status_code' => 200]);
        } else {

        }
    }
}
