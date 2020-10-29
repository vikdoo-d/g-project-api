<?php
/**
 * Created by PhpStorm.
 * User: dongkang
 * Date: 2020/8/31
 * Time: 16:44
 */


namespace App\Http\Controllers\Feedback;
use App\Http\Controllers\Controller;
use App\Http\Controllers\PublicController;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Common\ErrorDefine;
use Illuminate\Support\Facades\Crypt;
use App\Model\G_feedback;

class FeedbackController extends Controller
{

    private $feedback;
    private $public;

    //构造函数
    public function __construct(PublicController $public)
    {
        $this->feedback = new G_feedback();
        $this->public = $public;
    }


    /**
     * @param Request $request
     * @return array
     * 获取客户反馈列表
     */
    public function getFeedbackList(Request $request)
    {
        if(!$request->exists ('token') || $request->input('token') == ''){

            return self::showError(ErrorDefine::ACCOUNT_LOGIN_EXPIRED);
        }

        $feedback = new G_feedback();
        //根据条件查询

        if($request->has('guest_name') && !empty($request->input('guest_name')) )
        {
            $feedback =  $feedback->where('guest_name','like',"%{$request->input('guest_name')}%");
        }

        if($request->has('mobile') && !empty($request->input('mobile')) )
        {
            $feedback =  $feedback->where('mobile','like',"%{$request->input('mobile')}%");
        }

        if($request->has('fb_type') && !empty($request->input('fb_type')) )
        {
            $feedback =  $feedback->where('fb_type','=',$request->input('fb_type'));
        }

        if($request->has('start_date') && !empty($request->input('start_date')) )
        {
            $feedback =  $feedback->where('created_at','>',$request->input('start_date'));
        }

        if($request->has('end_date') && !empty($request->input('end_date')) )
        {
            $feedback =  $feedback->where('created_at','<',$request->input('end_date'));
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
        $data = $feedback->offset($this->limit*$this->page)->limit($this->limit)->orderBy('created_at','desc')->get()->toArray();
        $count = $feedback->count();

        foreach ($data as &$value) {
            /**
             * 客户反馈分类
             * 1）买房 2）卖房 3）租房 4）留言
             */
            if(!$value['fb_type']){
                $value['fb_type_name'] = '留言';
            }

             switch ($value['fb_type']) {
                case 1:
                    $value['fb_type_name'] = '买房';
                    break;
                case 2:
                    $value['fb_type_name'] = '卖房';
                    break;
                case 3:
                    $value['fb_type_name'] = '租房';
                    break;
                default:
                    $value['fb_type_name'] = '留言';
            }

            /**
             *处理进度
             *
             */
//            switch ($value['psc_type']) {
//                case 1:
//                    $value['psc_type_name'] = '未处理';
//                    break;
//                case 2:
//                    $value['psc_type_name'] = '跟进中';
//                    break;
//                default:
//                    $value['fb_type_name'] = '已完成';
//            }

        }



        return self::ajaxReturn(0,'success',$data,$count);
    }
}