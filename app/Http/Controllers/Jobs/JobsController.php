<?php

namespace App\Http\Controllers\Jobs;
use App\Http\Controllers\Controller;
use App\Http\Controllers\PublicController;
use App\Model\G_jobs;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Common\ErrorDefine;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class JobsController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
        $this->jobs  = new G_jobs();
    }

    /**
     * @return array
     * 职位列表
     */
    public function getJobsList(Request $request)
    {

        if(!$request->has('token') || $request->input('token') == ''){

            return self::showError(ErrorDefine::ACCOUNT_LOGIN_EXPIRED);
        }

        //根据条件查询

        if($request->has('job_name') && !empty($request->input('job_name')) )
        {
            $this->jobs =  $this->jobs->where('job_name','like',"%{$request->input('job_name')}%");
        }

        if($request->has('start_date') && !empty($request->input('start_date')) )
        {
            $this->jobs =  $this->jobs->where('created_at','>',$request->input('start_date'));
        }

        if($request->has('end_date') && !empty($request->input('end_date')) )
        {
            $this->jobs =  $this->jobs->where('created_at','<',$request->input('end_date'));
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
        try{
            $data = $this->jobs->offset($this->limit*$this->page)->limit($this->limit)
                ->orderBy('created_at','asc')->get(['id','job_name','job_dept','job_at','job_des','job_needs','job_submit','job_status','created_at'])
                ->toArray();

            foreach($data as &$value) {

                if ($value['job_status'] == '1') {
                    $value['status_name'] = '招聘中';
                } else {
                    $value['status_name'] = '停止招聘';
                }
            }
            $count = $this->jobs->count();

            return self::ajaxReturn(0,'success',$data,$count);
        }catch (\Exception $exception){

            return self::ShowError(ErrorDefine::DATABASE_ERROR);
        }


    }

    /**
     * @param Request $request
     * @return array
     * 添加岗位
     */
    public function doJobsAdd(Request $request)
    {

        $job_at = '芜湖';
        $job_status = 1;
        if(empty($request->all()))
        {

            return self::ShowError(ErrorDefine::INVALID_PARAMS);
        }

        if(!$request->has('token') && empty($request->input('token'))){

            return self::showError(ErrorDefine::ACCOUNT_LOGIN_EXPIRED);
        }
        if(empty($request->input('job_name')) || !$request->has('job_name'))
        {
            return self::ShowError(ErrorDefine::INVALID_PARAMS);
        }
        if(empty($request->input('job_dept')) || !$request->has('job_dept'))
        {
            return self::ShowError(ErrorDefine::INVALID_PARAMS);
        }
        if(empty($request->input('job_num')) || !$request->has('job_num'))
        {
            return self::ShowError(ErrorDefine::INVALID_PARAMS);
        }
        if(empty($request->input('job_submit')) || !$request->has('job_submit'))
        {
            return self::ShowError(ErrorDefine::INVALID_PARAMS);
        }
        //职位描述
        if(empty($request->input('job_des')) || !$request->has('job_des'))
        {
            return self::ShowError(ErrorDefine::INVALID_PARAMS);
        }
        //职位要求
        if(empty($request->input('job_needs')) || !$request->has('job_needs'))
        {

            return self::ShowError(ErrorDefine::INVALID_PARAMS);
        }

        //工作地点
        if($request->has('job_at') && !empty($request->input('job_at')))
        {
            $job_at = $request->input('job_at');
        }
        if($request->has('job_status') && !empty($request->input('job_status')))
        {
            $job_status = $request->input('job_status')? 1 : $request->input('job_status');
        }

        $job_name = $request->input('job_name');
        $job_num = $request->input('job_num');
        $job_dept = $request->input('job_dept');
        $job_des = $request->input('job_des');
        $job_needs = $request->input('job_needs');
        $job_submit = $request->input('job_submit');
        $created_at = date('Y-m-d');

        $params = [
            'job_name'=>$job_name,
            'job_num'=>$job_num,
            'job_dept'=>$job_dept,
            'job_at'=>$job_at,
            'job_des' =>$job_des,
            'job_needs'=>$job_needs,
            'job_status'=>$job_status,
            'job_submit'=>$job_submit,
            'created_at' =>$created_at
        ];
        try{

            $this->jobs->insert($params);

            return self::ajaxReturn(0,'success');

        }catch (\Exception $exception){

            return self::ShowError(ErrorDefine::DATABASE_ERROR);
        }

    }

    /**
     * @param Request $request
     * @return array
     * 招聘信息删除
     */
    public function doJobsDelete(Request $request)
    {

        if(!$request->has('token') && empty($request->input('token'))){

            return self::showError(ErrorDefine::ACCOUNT_LOGIN_EXPIRED);
        }
        if(!$request->has('id') && empty($request->input('id'))){

            return self::showError(ErrorDefine::ACCOUNT_LOGIN_EXPIRED);
        }
        $id =  $request->input('id');
        $jobs = G_jobs::find($id);

        if($jobs == NULL){

            return self::ShowError(ErrorDefine::INVALID_PARAMS);
        }

        try{

            $jobs->delete();
            return self::ajaxReturn(0,'success');

        }catch (\Exception $exception){

            return self::ShowError(ErrorDefine::DATABASE_ERROR);

        }

    }

    /**
     * @param Request $request
     * @return array
     * 招聘信息修改
     */
    public function doJobsUpdate(Request $request)
    {
        if(!$request->has('token') && empty($request->input('token'))){

            return self::showError(ErrorDefine::ACCOUNT_LOGIN_EXPIRED);
        }
        if(!$request->has('id') && empty($request->input('id'))){

            return self::showError(ErrorDefine::ACCOUNT_LOGIN_EXPIRED);
        }
        $id =  $request->input('id');
        $jobs = G_jobs::find($id);

        if($jobs == NULL){

            return self::ShowError(ErrorDefine::INVALID_PARAMS);
        }

        if(!empty($request->input('job_name')) && $request->has('job_name')){

            $jobs->job_name = $request->input('job_name');
        }
        if(!empty($request->input('job_num')) && $request->has('job_num')){

            $jobs->job_num = $request->input('job_num');
        }
        if(!empty($request->input('job_dept')) && $request->has('job_dept')){

            $jobs->job_name = $request->input('job_dept');
        }
        if(!empty($request->input('job_status')) && $request->has('job_status')){

            $jobs->job_status = $request->input('job_status');
        }

        if(!empty($request->input('job_des')) && $request->has('job_des')){

            $jobs->job_des = $request->input('job_des');
        }

        if(!empty($request->input('job_needs')) && $request->has('job_needs')){
            $jobs->job_needs = $request->input('job_needs');
        }
        try{

            $jobs->save();
            return self::ajaxReturn(0,'success');

        }catch (\Exception $exception){

            return self::ShowError(ErrorDefine::DATABASE_ERROR);

        }

    }

/****************官网页面**********************/


    /**'
     * @param Request $request
     * @return array
     * 招聘列表
     */
    public function getJobsListForWeb(Request $request)
    {
        //根据条件查询

        if($request->has('job_name') && !empty($request->input('job_name')) )
        {
            $this->jobs =  $this->jobs->where('job_name','like',"%{$request->input('job_name')}%");
        }

        if($request->has('start_date') && !empty($request->input('start_date')) )
        {
            $this->jobs =  $this->jobs->where('created_at','>',$request->input('start_date'));
        }

        if($request->has('end_date') && !empty($request->input('end_date')) )
        {
            $this->jobs =  $this->jobs->where('created_at','<',$request->input('end_date'));
        }

        if($request->input('page') )
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
        try{
            $data = $this->jobs->offset($this->limit*$this->page)->limit($this->limit)
                ->orderBy('created_at','desc')->get(['id','job_name','job_dept','job_num','job_status','created_at'])
                ->toArray();

            foreach($data as &$value) {

                if ($value['job_status'] == '1') {
                    $value['status_name'] = '招聘中';
                } else {
                    $value['status_name'] = '停止招聘';
                }
            }
            $count = $this->jobs->count();

            return self::ajaxReturn(0,'success',$data,$count);
        }catch (\Exception $exception){

            return self::ShowError(ErrorDefine::DATABASE_ERROR);
        }
    }

    /**
     * @param Request $request
     * @return array
     * 职位详情
     */
    public function getJobsInfo(Request $request)
    {
        if($request->has('id') && !empty($request->input('id')) )
        {
           $id = $request->input('id');
        }
        try{
            // $data = $this->jobs->find($id)
            //         ->get(['id','job_name','job_dept','job_at','job_num','job_des','job_needs','job_submit','job_status','created_at']);
            $data = G_jobs::where('id', $id)->first();
            $data->page_view += 1;
            $data->save();
            if ($data->job_status == '1') {
                $data->status_name = '招聘中';
            } else {
                $data->status_name= '停止招聘';
            }
            $data->created_at = date('Y-m-d',strtotime($data->created_at));

            // $data->list = $list;
            return self::ajaxReturn(0,'success',$data,$data->page_view);
        }catch (\Exception $exception){

            return self::ShowError(ErrorDefine::DATABASE_ERROR);
        }

    }


}
