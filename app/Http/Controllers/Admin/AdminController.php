<?php
/**
 * Created by PhpStorm.
 * User: dongkang
 * Date: 2020/8/26
 * Time: 15:04
 */
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Controllers\PublicController;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Common\ErrorDefine;
use Illuminate\Support\Facades\Crypt;
use App\Model\G_admin;

class AdminController extends Controller
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
     * @return Response
     */
    public function getAdminList(Request $request)
    {
        if(!$request->has('token') || $request->input('token') == ''){

            return self::showError(ErrorDefine::ACCOUNT_LOGIN_EXPIRED);
        }

        $account = new G_admin();
        //根据条件查询

        if($request->has('username') && !empty($request->input('username')) )
        {
            $account =  $account->where('username','like',"%{$request->input('username')}%");
        }


        if($request->has('start_date') && !empty($request->input('start_date')) )
        {
            $account =  $account->where('created_at','>',$request->input('start_date'));
        }

        if($request->has('end_date') && !empty($request->input('end_date')) )
        {
            $account =  $account->where('created_at','<',$request->input('end_date'));
        }



        if($request->input('page'))
        {
            $this->page  = $request->input('page');
            if($this->page){
                $this->page = $this->page - 1;
                if(is_numeric($this->page)){
                    if($this->page<0){
                        $this->page = 0;
                    }
                }
            }
        }

        if($request->input('limit'))
        {
            $this->limit = $request->input('limit');
            if($this->limit){
                if($this->limit <=0 ){
                    $this->limit =  10;
                }
            }
        }
        $data = $account->offset($this->limit*$this->page)->limit($this->limit)
            ->orderBy('created_at','asc')->get(['id','username','status','role_id','created_at'])
            ->toArray();
        $count = $account->count();

        foreach($data as &$value)
        {

            if($value['role_id'] == '1'){
                $value['role'] = '超级管理员';
             }else{
                $value['role'] = '普通管理员';
            }
            if($value['status'] == '1'){
                $value['state'] = '已启用';
            }else{
                $value['state'] = '已停用';
            }
        }
        return self::ajaxReturn(0,'success',$data,$count);
    }


    /**
     * @param Request $request
     * @return array|void
     * 添加管理员
     */
    public function doAdminAdd(Request $request)
    {
        if(empty($request->all()))
        {

            return self::ShowError(ErrorDefine::INVALID_PARAMS);
        }

        if(!$request->has('token') && empty($request->input('token'))){

            return self::showError(ErrorDefine::ACCOUNT_LOGIN_EXPIRED);
        }

        //  用户名
        if(empty($request->input('username')) || !$request->has('username'))
        {

            return self::ShowError(ErrorDefine::INVALID_PARAMS);
        }

        // 密码
        if(empty($request->input('password')) || !$request->has('password'))
        {

            return self::ShowError(ErrorDefine::INVALID_PARAMS);
        }


        if(empty($request->input('role_id')) || !$request->has('role_id'))
        {

            return self::ShowError(ErrorDefine::INVALID_PARAMS);
        }

        $remark='';
        if($request->input('remark')){
            $remark=$request->input('remark');
        }

        $username = $request->input('username');
        $password = $request->input('password');
        $role_id = $request->input('role_id');
        $created_at = date('Y-m-d H:i:s');


        $admin_add = $this->public->checkAdminExist($username);

        if($admin_add != NULL){

            return  self::ShowError(ErrorDefine::ADMIN_EXIST);;
        }

        $params = [
            'username'=>$username,
            'password'=>Crypt::encrypt($password),
            'role_id' =>$role_id,
            'created_at' =>$created_at,
            'remark'=>$remark
        ];

        try{

            $this->admin->insert($params);

            return self::ajaxReturn(0,'success');

        }catch (\Exception $exception){

            return self::ShowError(ErrorDefine::DATABASE_ERROR);
        }

    }


    /**
     * @param Request $request
     * @return array
     * 管理员信息修改
     */
    public function doAdminUpdate(Request $request)
    {
        if(!$request->has('token') && empty($request->input('token'))){

            return self::showError(ErrorDefine::ACCOUNT_LOGIN_EXPIRED);
        }

        if(empty($request->input('id')) || !$request->has('id')){

            return self::ShowError(ErrorDefine::INVALID_PARAMS);
        }


        $id =  $request->input('id');
        $accounts = G_admin::find($id);


        if($accounts == NULL){

            return self::ShowError(ErrorDefine::INVALID_PARAMS);
        }

        if(!empty($request->input('password')) && $request->has('password')){

            $accounts->password = Crypt::encrypt($request->input('password'));
        }

        if(!empty($request->input('status')) && $request->has('status')){

            $accounts->status = $request->input('status');
        }

        if(!empty($request->input('role_id')) && $request->has('role_id')){

            $accounts->role_id = $request->input('role_id');
        }

        if(!empty($request->input('remark')) && $request->has('remark')){

            $accounts->remark = $request->input('remark');
        }

        try{

            $accounts->save();
            return self::ajaxReturn(0,'success');

        }catch (\Exception $exception){

            return self::ShowError(ErrorDefine::DATABASE_ERROR);

        }


    }


    /**
     * @param Request $request
     * @return array
     * 软删除管理员账户
     */
    public function doAdminDelete(Request $request)
    {
        if(!$request->has('token') && empty($request->input('token'))){

            return self::showError(ErrorDefine::ACCOUNT_LOGIN_EXPIRED);
        }

        if(empty($request->input('id')) || !$request->has('id')){

            return self::ShowError(ErrorDefine::INVALID_PARAMS);
        }


        $id =  $request->input('id');
        $accounts = G_admin::find($id);

        if($accounts == NULL){

            return self::ShowError(ErrorDefine::INVALID_PARAMS);
        }

        try{

            $accounts->delete();
            return self::ajaxReturn(0,'success');

        }catch (\Exception $exception){

            return self::ShowError(ErrorDefine::DATABASE_ERROR);

        }
    }

}