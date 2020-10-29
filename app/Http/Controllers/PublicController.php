<?php

namespace  App\Http\Controllers;

use App\Model\G_admin;
use App\Model\G_property;
use Illuminate\Support\Facades\Config;
use App\Common\ErrorDefine;
//use App\Model\Fg_keyword;
//use App\Model\Http_account;

class PublicController
{

    /**
     * @param $username
     * @return mixed
     * 检测管理员是否存在
     */
    static function checkAdminExist($username)
    {

        $admin = G_admin::where('username', $username)->first();
        if( $admin == NULL || $admin->status == 2){
            return NULL;
        }
        return $admin;
    }

    /**
     * @param $username
     * @return null
     * 检测管理员权限
     */
    static function checkAdminRole($username)
    {
        $admin = G_admin::where('username', $username)->first();
        if($admin->role_id != 1){
            return NULL;
        }
        return $admin;
    }


    /**
     * @param $ppy_num
     * @return mixed
     * 检测房源编号是否存在
     */
    static function checkPpyUuidExist($ppy_num)
    {
        $property = G_property::where('ppy_num',$ppy_num);

        return $property;
    }
}
