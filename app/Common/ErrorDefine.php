<?php
namespace App\Common;


class ErrorDefine
{
    const INVALID_PARAMS						= 10001;
    const MOBILE_IS_EXIST						= 10002;
    const INVALID_CODE							= 10003;
    const ADMIN_EXIST							= 10004;
    const DATABASE_ERROR						= 10005;
    const CATE_EXIST							= 10006;
    const KEYWORD_EXIST							= 10007;
    const PASSWORD_NOT_MATCH					= 10008;
    const SET_PASSWORD_FALSE					= 10009;
    const ACCOUNT_LOGIN_EXPIRED					= 10010;
    const SUBMIT_FALSE							= 10011;
    const ADD_FAILD								= 10012;
    const NAME_OR_PWD_ERROR						= 10013;
    const ACCOUNT_OR_PASSWORD_INVALID			= 10014;
    const CATE_NOT_EXIST						= 10015;
    const KEYWORD_NOT_EXIST						= 10016;

    static $arrErrorMessage = array(

        self::INVALID_PARAMS					=>'参数错误',
        self::MOBILE_IS_EXIST					=>'手机号已存在',
        self::INVALID_CODE						=>'无效的验证码',
        self::ADMIN_EXIST						=>'管理员已存在',
        self::DATABASE_ERROR					=>'数据库错误',
        self::NAME_OR_PWD_ERROR					=>'用户名或密码错误',
        self::CATE_EXIST						=>'分类已存在',
        self::KEYWORD_EXIST						=>'关键词已存在',
        self::CATE_NOT_EXIST					=>'分类不存在',
        self::KEYWORD_NOT_EXIST					=>'关键词不存在',
        self::PASSWORD_NOT_MATCH				=>'密码不一致',
        self::SET_PASSWORD_FALSE				=>'设置密码失败',
        self::ACCOUNT_OR_PASSWORD_INVALID		=>'账户或密码错误',
        self::ACCOUNT_LOGIN_EXPIRED				=>'账户登录过期',
        self::SUBMIT_FALSE						=>'提交失败',
        self::ADD_FAILD							=>'添加失败',
    );



    static function errorMessage($errorCode)
    {
        if(isset(self::$arrErrorMessage[$errorCode])) {
            return self::$arrErrorMessage[$errorCode];
        } else {
            return "未知错误码";
        }
    }
}

