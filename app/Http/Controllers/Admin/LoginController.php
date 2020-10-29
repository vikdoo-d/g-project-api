<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Controllers\PublicController;

use Illuminate\Http\Request;
use App\Common\ErrorDefine;
use Illuminate\Support\Facades\Crypt;
use App\Model\G_admin;
class LoginController extends Controller
{
    private $admin;
    private $public;

    public function __construct(PublicController $public)
    {
        $this->admin = new G_admin();
        $this->public = $public;
    }

    /**
     * @param Request $request
     * @return array
     * 登录
     */

    public function doLogin(Request $request){

        if(empty($request->all()))
        {

            return self::ShowError(ErrorDefine::INVALID_PARAMS);
        }

        //  用户名
        if(empty($request->input('username')) || !$request->has('username'))
        {

            return self::ShowError(ErrorDefine::INVALID_PARAMS);
        }
        $username = $request->input('username');

        if(empty($request->input('password')) || !$request->has('password'))
        {

            return self::ShowError(ErrorDefine::INVALID_PARAMS);
        }
        $password = $request->input('password');

        $admin = $this->public->checkAdminExist($username);



        if($admin == NULL)
        {
            return self::ShowError(ErrorDefine::NAME_OR_PWD_ERROR);
        }

        if($password != Crypt::decrypt($admin->password))
        {
            return self::ShowError(ErrorDefine::NAME_OR_PWD_ERROR);
        }

        $admin->token = Crypt::encrypt($admin->id);
        $admin->last_login = date('Y-m-d H:i:s');
        $admin->save();

        $params = [
            'id'=>$admin->id,
            'username' => $admin->username,
            'token' => $admin->token,
            'last_login'=>$admin->last_login,
            'role_id'=>$admin->role_id
        ];

        return self::ajaxReturn(0,'success',$params);
    }

}
