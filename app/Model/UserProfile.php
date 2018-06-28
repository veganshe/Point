<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use App\Model\UserExtra;

class UserProfile extends Model
{
    // 与模型关联的数据表
    protected $table = 'user_profiles';
    // 主键 ID
    protected $primaryKey = 'uid';
    // 关闭日期
    public $timestamps = false;

    public function aa() {
    	return $this->hasOne('App\Model\UserExtra','uid','user_id');
    }

    public function bb() {
    	return $this->hasMany('App\Model\UserFollow','uid','user_id');
    }
}

