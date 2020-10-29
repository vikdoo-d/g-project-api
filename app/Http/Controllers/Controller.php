<?php

namespace App\Http\Controllers;
use App\Common\ErrorDefine;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    //



    /**
     *
     * 汉字编码处理
     */
    static function decodeUnicode($str) {
        return preg_replace_callback('/\\\\u([0-9a-f]{4})/i', create_function( '$matches', 'return mb_convert_encoding(pack("H*", $matches[1]), "UTF-8", "UCS-2BE");' ), $str);
    }
    /**
     * @param string $data
     * @param string $count
     * @param int $code
     * @param string $msg
     *
     * 正确返回
     */
    static function ajaxReturn( $code = 0,$msg ='',$data= [],$count=0)
    {

        $message =[
            'code' => $code,
            'msg' => $msg,
            'count'=>$count,
            'data' => $data
        ];
        return $message;
    }

    /**
     *
     * @param string $code
     * @param string $msg
     * @return array
     *
     * 错误信息
     */
    static function showError($code= '',$msg ='')
    {
        if(empty($msg)) {
            $errorMessage = ErrorDefine::errorMessage($code);
        }

        $message =[
            'code' => $code,
            'msg' => $errorMessage,
        ];

        return $message;
    }


}
