<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Support\Facades\DB;
use App\Model\Update;
use Illuminate\Http\Request;

class UpdateController extends BaseController
{
	// 关于我们
    public function about(Update $update) {
    	$post = $update->find(98);
    	return response()->json($post);
    }

    // 更新列表
    public function appupdate(Request $request, Update $update) 
    {
    	$page = $request->input('page',1);
    	$type = $request->input('type',1);

    	$posts = $update->select('id','title','public_time')
    					->where('type',$type)
    					->orderBy('id','desc')
    					->forPage($page,20)
    					->get();
    	return response()->json($posts);
    }

    // 更新内容详细
    public function post(Update $update, $id)
    {
    	$post = $update->find($id);
    	return response()->json($post);
    }
}
