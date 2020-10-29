<?php
/**
 * Created by PhpStorm.
 * User: dongkang
 * Date: 2020/8/31
 * Time: 16:24
 */

namespace App\Http\Controllers\News;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Qiniu\QiniuController;
use App\Http\Controllers\PublicController;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Common\ErrorDefine;
use Illuminate\Support\Facades\Crypt;
use App\Model\G_news;


class NewsController extends Controller
{

    private $news;
    private $public;

    //构造函数
    public function __construct(PublicController $public)
    {
        $this->news = new G_news();
        $this->public = $public;
    }


    /**
     * @param Request $request
     * @return array
     * 获取新闻列表
     */
    public function getNewsList(Request $request)
    {

        if (!$request->exists('token') || $request->input('token') == '') {

            return self::showError(ErrorDefine::ACCOUNT_LOGIN_EXPIRED);
        }


        $news = new G_news();
        //根据条件查询

        if ($request->has('title') && !empty($request->input('title'))) {
            $news = $news->where('title', 'like', "%{$request->input('title')}%");
        }

        if ($request->has('cate') && !empty($request->input('cate'))) {
            $news = $news->where('cate', '=', $request->input('cate'));
        }
        if ($request->has('status') && !empty($request->input('status'))) {
            $news = $news->where('status', '=', $request->input('status'));
        }
        if ($request->has('start_date') && !empty($request->input('start_date'))) {
            $news = $news->where('created_at', '>', $request->input('start_date'));

        }

        if ($request->has('end_date') && !empty($request->input('end_date'))) {
            $news = $news->where('created_at', '<', $request->input('end_date'));
        }

        if ($request->input('page')) {
            $this->page = $request->input('page');
            if ($this->page) {
                $this->page = $this->page - 1;
                if (is_numeric($this->page)) {
                    if ($this->page < 0) {
                        $this->page = 0;
                    }
                }
            }
        }

        if ($request->input('limit')) {
            $this->limit = $request->input('limit');
            if ($this->limit) {
                if ($this->limit <= 0) {
                    $this->limit = 10;
                }
            }
        }
        $data = $news->offset($this->limit * $this->page)->limit($this->limit)->orderBy('created_at', 'desc')->get()->toArray();
        $count = $news->count();

        foreach ($data as &$value) {
            //1）资讯中心 2）本地关注 3）城建动态
            switch ($value['cate']) {
                case 1:
                    $value['category'] = '资讯中心';
                    break;
                case 2:
                    $value['category'] = '本地关注';
                    break;
                case 3:
                    $value['category'] = '城建动态';
                    break;
                default:
                    $value['category'] = '未知分类';
            }
            if ($value['type'] == '1') {
                $value['type_name'] = '原创';
            } else {
                $value['type_name'] = '转载';
            }
            if ($value['status'] == '1') {
                $value['status_name'] = '已发布';
            } else {
                $value['status_name'] = '未发布';
            }
        }


        return self::ajaxReturn(0, 'success', $data, $count);
    }



    /**
     * @param Request $request
     * @return array
     * 新闻资讯软删除
     */
    public function doNewsSoftDelete(Request $request)
    {
        if(!$request->has('token') && empty($request->input('token'))){

            return self::showError(ErrorDefine::ACCOUNT_LOGIN_EXPIRED);
        }

        if(empty($request->input('id')) || !$request->has('id')){

            return self::ShowError(ErrorDefine::INVALID_PARAMS);
        }


        $id =  $request->input('id');

        $news= G_news::find($id);

        if($news == NULL){

            return self::ShowError(ErrorDefine::INVALID_PARAMS);
        }

        try{

            $news->delete();
            return self::ajaxReturn(0,'success');

        }catch (\Exception $exception){

//            return $exception;
            return self::ShowError(ErrorDefine::DATABASE_ERROR);

        }
    }


    /**
     * @param Request $request
     * @return array
     * 添加新闻
     */
    public  function doNewsAdd(Request $request)
    {
        if(empty($request->all()))
        {

            return self::ShowError(ErrorDefine::INVALID_PARAMS);
        }

        if(!$request->has('token') && empty($request->input('token'))){

            return self::showError(ErrorDefine::ACCOUNT_LOGIN_EXPIRED);
        }

        //  用户名
        if(empty($request->input('title')) || !$request->has('title'))
        {

            return self::ShowError(ErrorDefine::INVALID_PARAMS);
        }

        // 密码
        if(empty($request->input('author')) || !$request->has('author'))
        {

            return self::ShowError(ErrorDefine::INVALID_PARAMS);
        }


        if(empty($request->input('cate')) || !$request->has('cate'))
        {

            return self::ShowError(ErrorDefine::INVALID_PARAMS);
        }

        if(empty($request->input('type')) || !$request->has('type'))
        {

            return self::ShowError(ErrorDefine::INVALID_PARAMS);
        }


        if(empty($request->input('img_url')) || !$request->has('img_url'))
        {

            return self::ShowError(ErrorDefine::INVALID_PARAMS);
        }


        if(empty($request->input('status')) || !$request->has('status'))
        {

            return self::ShowError(ErrorDefine::INVALID_PARAMS);
        }
        if(empty($request->input('summary')) || !$request->has('summary'))
        {

            return self::ShowError(ErrorDefine::INVALID_PARAMS);
        }
        if($request->has('news_url') && !empty($request->input('news_url')))
        {

            $news_url = $request->input('news_url')?:'';
        }

        if($request->has('content') && !empty($request->input('content')))
        {

            $content = $request->input('content')?:'';
        }
        $title = $request->input('title');
        $author = $request->input('author');
        $cate = $request->input('cate');
        $summary = $request->input('summary');//摘要
        $type = $request->input('type');

        $img_url = $request->input('img_url');

        $status = $request->input('status');
        $created_at = date('Y-m-d H:i:s');

        $params = [
            'title'=>$title,
            'author'=>$author,
            'cate' =>$cate,
            'created_at' =>$created_at,
            'summary'=>$summary,
            'type'=>$type,
            'news_url' =>$news_url,
            'img_url'=>$img_url,
            'content' =>$content,
            'status'=>$status,
        ];


        try{

            G_news::insert($params);

            return self::ajaxReturn(0,'success');

        }catch (\Exception $exception){

            return self::ShowError(ErrorDefine::DATABASE_ERROR);
        }

    }

    /**
     * @param Request $request
     * @return array
     * 新闻更新
     */
    public function doNewsUpdate(Request $request)
    {
        if(!$request->has('token') && empty($request->input('token'))){

            return self::showError(ErrorDefine::ACCOUNT_LOGIN_EXPIRED);
        }

        if(empty($request->input('id')) || !$request->has('id')){

            return self::ShowError(ErrorDefine::INVALID_PARAMS);
        }


        $id =  $request->input('id');
        $news = G_news::find($id);

        if($news == NULL){
            return self::showError(ErrorDefine::INVALID_PARAMS);
        }

        if(!empty($request->input('title')) && $request->has('title')){

            $news->title = $request->input('title');
        }

        if(!empty($request->input('author')) && $request->has('author')){

            $news->author = $request->input('author');
        }

        if(!empty($request->input('status')) && $request->has('status')){

            $news->status = $request->input('status');
        }
        if(!empty($request->input('summary')) && $request->has('summary')){

            $news->summary = $request->input('summary');
        }

        if(!empty($request->input('cate')) && $request->has('cate')){

            $news->cate = $request->input('cate');
        }

        if(!empty($request->input('type')) && $request->has('type')){

            $news->type = $request->input('type');
        }

        if(!empty($request->input('news_url')) && $request->has('news_url')){

            $news->news_url = $request->input('news_url');
        }
        if(!empty($request->input('img_url')) && $request->has('img_url')){
//            $qiniu = new QiniuController();
//            $qiniu->deleteImg($news->img_url);
            $news->img_url = $request->input('img_url');

        }
        if(!empty($request->input('content')) && $request->has('content')){

            $news->content = $request->input('content');
        }

        try{

            $news->save();
            return self::ajaxReturn(0,'success');

        }catch (\Exception $exception){

            return self::ShowError(ErrorDefine::DATABASE_ERROR);

        }
    }


    /**********************官网页面*****************************/


    public function getNewsListForWeb(Request $request)
    {
        $news = new G_news();
        //根据条件查询

        if ($request->has('title') && !empty($request->input('title'))) {
            $news = $news->where('title', 'like', "%{$request->input('title')}%");
        }

        if ($request->has('cate') && !empty($request->input('cate'))) {
            $news = $news->where('cate', '=', $request->input('cate'));
        }
        if ($request->has('type') && !empty($request->input('type'))) {
            $news = $news->where('type', '=', $request->input('type'));
        }
        if ($request->has('status') && !empty($request->input('status'))) {
            $news = $news->where('status', '=', $request->input('status'));
        }
        if ($request->has('start_date') && !empty($request->input('start_date'))) {
            $news = $news->where('created_at', '>', $request->input('start_date'));

        }

        if ($request->has('end_date') && !empty($request->input('end_date'))) {
            $news = $news->where('created_at', '<', $request->input('end_date'));
        }

        if ($request->input('page')) {
            $this->page = $request->input('page');
            if ($this->page) {
                $this->page = $this->page - 1;
                if (is_numeric($this->page)) {
                    if ($this->page < 0) {
                        $this->page = 0;
                    }
                }
            }
        }

        if ($request->input('limit')) {
            $this->limit = $request->input('limit');
            if ($this->limit) {
                if ($this->limit <= 0) {
                    $this->limit = 10;
                }
            }
        }
        try{
            $data = $news->where('status',1)->offset($this->limit * $this->page)->limit($this->limit)->orderBy('created_at', 'desc')->get()->toArray();
            $count = $news->where('status',1)->count();

            foreach ($data as &$value) {
                //1）资讯中心 2）本地关注 3）城建动态
                switch ($value['cate']) {
                    case 1:
                        $value['category'] = '资讯中心';
                        break;
                    case 2:
                        $value['category'] = '本地关注';
                        break;
                    case 3:
                        $value['category'] = '城建动态';
                        break;
                    default:
                        $value['category'] = '未知分类';
                }
                if ($value['type'] == '1') {
                    $value['type_name'] = '原创';
                } else {
                    $value['type_name'] = '转载';
                }
                if ($value['status'] == '1') {
                    $value['status_name'] = '已发布';
                } else {
                    $value['status_name'] = '未发布';
                }
            }


            return self::ajaxReturn(0, 'success', $data, $count);
        }catch (\Exception $exception){

            return self::ShowError(ErrorDefine::DATABASE_ERROR);
        }


    }

    public function getNewsInfoForWebById(Request $request)
    {
        $article_id = $request->input('id');
//        $data = G_news::where('id',$article_id)->first();
//        $data->page_view += 1;
//        $data->save();
        try {
            $data = G_news::where('id', $article_id)->first();
            $data->page_view += 1;
            $data->save();


//            foreach ($data as &$value) {
//                var_dump($value);
                //1）资讯中心 2）本地关注 3）城建动态
                switch ($data->cate) {
                    case 1:
                        $data->category = '资讯中心';
                        break;
                    case 2:
                        $data->category = '本地关注';
                        break;
                    case 3:
                        $data->category = '城建动态';
                        break;
                    default:
                        $data->category = '房产百科';
                }
                if ($data->type == '1') {
                    $data->type_name = '原创';
                } else {
                    $data->type_name = '转载';
                }
                if ($data->status == '1') {
                    $data->status_name = '已发布';
                } else {
                    $data->status_name = '未发布';
                }
//            }
            $data->created_at = date('Y-m-d',strtotime($data->created_at));
            $list = G_news::where('status',1)->inRandomOrder()
                    ->take(5)
                    ->get(['id','cate','created_at','title'])->toArray();

            $data->list = $list;

            return self::ajaxReturn(0, 'success', $data, $data->page_view);
        }catch (\Exception $exception){
            return self::ShowError(ErrorDefine::DATABASE_ERROR);
        }




    }




}
