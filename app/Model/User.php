<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    // 与模型关联的数据表
    protected $table = 'user_extras';
    // 主键 ID
    protected $primaryKey = 'user_id';
}
