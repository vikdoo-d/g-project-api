<?php
/**
 * Created by PhpStorm.
 * User: dongkang
 * Date: 2020/8/31
 * Time: 16:24
 */

namespace App\Http\Controllers\Property;
use App\Http\Controllers\Controller;
use App\Http\Controllers\PublicController;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Common\ErrorDefine;
use Illuminate\Support\Facades\Crypt;
use App\Model\G_property;

class PropertyController extends Controller
{

    private $property;
    private $public;

    //构造函数
    public function __construct(PublicController $public)
    {
        $this->property = new G_property();
        $this->public = $public;
    }


    /**
     * @param Request $request
     * @return array
     * 获取新闻列表
     */
    public function getPropertyList(Request $request)
    {

        if (!$request->exists('token') || $request->input('token') == '') {

            return self::showError(ErrorDefine::ACCOUNT_LOGIN_EXPIRED);
        }


        $property = new G_property();
        //根据条件查询

        if ($request->has('title') && !empty($request->input('title'))) {
            $property = $property->where('title', 'like', "%{$request->input('title')}%");
        }

        if ($request->has('cate') && !empty($request->input('cate'))) {
            $property = $property->where('cate', '=', $request->input('cate'));
        }
        if ($request->has('status') && !empty($request->input('status'))) {
            $property = $property->where('status', '=', $request->input('status'));
        }
        if ($request->has('start_date') && !empty($request->input('start_date'))) {
            $property = $property->where('created_at', '>', $request->input('start_date'));

        }

        if ($request->has('end_date') && !empty($request->input('end_date'))) {
            $property = $property->where('created_at', '<', $request->input('end_date'));
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
        $data = $property->offset($this->limit * $this->page)->limit($this->limit)->orderBy('created_at', 'desc')->get()->toArray();
        $count = $property->count();

        foreach ($data as &$value) {
            //1）资讯中心 2）本地关注 3）城建动态
//            switch ($value['cate']) {
//                case 1:
//                    $value['category'] = '资讯中心';
//                    break;
//                case 2:
//                    $value['category'] = '本地关注';
//                    break;
//                case 3:
//                    $value['category'] = '城建动态';
//                    break;
//                default:
//                    $value['category'] = '未知分类';
//            }

            $value['huxing'] = $value['countF'].'室'.$value['countT'].'厅'.$value['countW'].'卫';

            $value['address'] = $value['city'].$value['district'].$value['addr'];
            if ($value['type'] == '1') {
                $value['type_name'] = '二手房';
            } else {
                $value['type_name'] = '新房';
            }
//            if ($value['status'] == '1') {
//                $value['status_name'] = '已发布';
//            } else {
//                $value['status_name'] = '未发布';
//            }
        }


        return self::ajaxReturn(0, 'success', $data, $count);
    }






    /**
     * @param Request $request
     * @return array
     * 新闻资讯软删除
     */
    public function doPropertySoftDelete(Request $request)
    {
        if(!$request->has('token') && empty($request->input('token'))){

            return self::showError(ErrorDefine::ACCOUNT_LOGIN_EXPIRED);
        }

        if(empty($request->input('id')) || !$request->has('id')){

            return self::ShowError(ErrorDefine::INVALID_PARAMS);
        }


        $id =  $request->input('id');

        $property= G_property::find($id);

        if($property == NULL){

            return self::ShowError(ErrorDefine::INVALID_PARAMS);
        }

        try{

            $property->delete();
            return self::ajaxReturn(0,'success');

        }catch (\Exception $exception){

//            return $exception;
            return self::ShowError(ErrorDefine::DATABASE_ERROR);

        }
    }

    /**
     * @param Request $request
     * @return array
     * 检测数据库是否存在房源编号
     */
    public function checkPpyUuidExist(Request $request)
    {
        if(empty($request->input('ppy_num')) || !$request->has('ppy_num'))
        {

            return self::ShowError(ErrorDefine::INVALID_PARAMS);
        }
        $property = G_property::where('ppy_num',$request->input('ppy_num'))->first();

        return $property;
    }

    /**
     * @param Request $request
     * @return array
     * 添加新闻
     */
    public  function doErshoufangAdd(Request $request)
    {

        if(empty($request->all()))
        {
            return self::ShowError(ErrorDefine::INVALID_PARAMS,'参数为空');
        }


        if(!$request->has('token') && empty($request->input('token'))){

            return self::showError(ErrorDefine::ACCOUNT_LOGIN_EXPIRED);
        }

        //  房源标题
        if(empty($request->input('title')) || !$request->has('title'))
        {

            return self::ShowError(ErrorDefine::INVALID_PARAMS);
        }


        if(empty($request->input('ppy_num')) || !$request->has('ppy_num'))
        {

            return self::ShowError(ErrorDefine::INVALID_PARAMS);
        }

        $ppy_num_check = $this->public->checkPpyUuidExist($request->input('ppy_num'));
        if( $ppy_num_check != NULL){
            return self::showError(ErrorDefine::INVALID_PARAMS);
        };


        if(empty($request->input('build_type')) || !$request->has('build_type'))
        {
            return self::ShowError(ErrorDefine::INVALID_PARAMS);
        }

        if(empty($request->input('type')) || !$request->has('type'))
        {
            return self::ShowError(ErrorDefine::INVALID_PARAMS);
        }

        if(empty($request->input('img_small')) || !$request->has('img_small'))
        {
            return self::ShowError(ErrorDefine::INVALID_PARAMS);
        }

        if(empty($request->input('usage_type')) || !$request->has('usage_type'))
        {
            return self::ShowError(ErrorDefine::INVALID_PARAMS);
        }
        if(empty($request->input('square')) || !$request->has('square'))
        {
            return self::ShowError(ErrorDefine::INVALID_PARAMS);
        }
        if(empty($request->input('district')) || !$request->has('district'))
        {
            return self::ShowError(ErrorDefine::INVALID_PARAMS);
        }
        if(empty($request->input('bizcircle')) || !$request->has('bizcircle'))
        {
            return self::ShowError(ErrorDefine::INVALID_PARAMS);
        }
        if(empty($request->input('addr')) || !$request->has('addr'))
        {

            return self::ShowError(ErrorDefine::INVALID_PARAMS);
        }
        if(empty($request->input('countF')) || !$request->has('countF'))
        {

            return self::ShowError(ErrorDefine::INVALID_PARAMS);
        }

        if(empty($request->input('countT')) || !$request->has('countT'))
        {

            return self::ShowError(ErrorDefine::INVALID_PARAMS);
        }

        if(empty($request->input('countW')) || !$request->has('countW'))
        {

            return self::ShowError(ErrorDefine::INVALID_PARAMS);
        }
        if(empty($request->input('countY')) || !$request->has('countY'))
        {

            return self::ShowError(ErrorDefine::INVALID_PARAMS);
        }

        if(empty($request->input('floor')) || !$request->has('floor'))
        {
            return self::ShowError(ErrorDefine::INVALID_PARAMS);
        }

        if(empty($request->input('floor_total')) || !$request->has('floor_total'))
        {

            return self::ShowError(ErrorDefine::INVALID_PARAMS);
        }

        if(empty($request->input('orientation')) || !$request->has('orientation'))
        {

            return self::ShowError(ErrorDefine::INVALID_PARAMS);
        }
        if(empty($request->input('decoration')) || !$request->has('decoration'))
        {

            return self::ShowError(ErrorDefine::INVALID_PARAMS);
        }

        if(empty($request->input('recommend')) || !$request->has('recommend'))
        {
            return self::ShowError(ErrorDefine::INVALID_PARAMS);
        }

        if(empty($request->input('sell_price')) || !$request->has('sell_price'))
        {

            return self::ShowError(ErrorDefine::INVALID_PARAMS);
        }

        if(empty($request->input('sell_price_unit')) || !$request->has('sell_price_unit'))
        {

            return self::ShowError(ErrorDefine::INVALID_PARAMS);
        }
        if(empty($request->input('ppy_des')) || !$request->has('ppy_des'))
        {

            return self::ShowError(ErrorDefine::INVALID_PARAMS);
        }




        $title              =       $request->input('title');
        $ppy_num            =       $request->input('ppy_num');
        $usage_type         =       $request->input('usage_type');
        $build_type         =       $request->input('build_type');
        $type               =       $request->input('type');
        $ppy_des            =       $request->input('ppy_des');
        $sell_price_unit    =       $request->input('sell_price_unit');
        $sell_price         =       $request->input('sell_price');
        $addr               =       $request->input('addr');
        $recommend          =       $request->input('recommend');
        $decoration         =       $request->input('decoration');
        $orientation        =       $request->input('orientation');
        $floor_total        =       $request->input('floor_total');
        $floor              =       $request->input('floor');
        $square             =       $request->input('square');
        $img_small          =       $request->input('img_small');
        $district           =       $request->input('district');
        $bizcircle          =       $request->input('bizcircle');
        $countF             =       $request->input('countF');
        $countT             =       $request->input('countT');
        $countW             =       $request->input('countW');
        $countY             =       $request->input('countY');
        $created_at         =       date('Y-m-d H:i:s');

        $params = [
            'title'                 =>          $title,
            'addr'                  =>          $addr,
            'district'              =>          $district,
            'bizcircle'             =>          $bizcircle,
            'ppy_num'               =>          $ppy_num,
            'usage_type'            =>          $usage_type,
            'build_type'            =>          $build_type,
            'type'                  =>          $type,
            'ppy_des'               =>          $ppy_des,
            'sell_price'            =>          $sell_price,
            'sell_price_unit'       =>          $sell_price_unit,
            'recommend'             =>          $recommend,
            'decoration'            =>          $decoration,
            'orientation'           =>          $orientation,
            'floor_total'           =>          $floor_total,
            'floor'                 =>          $floor,
            'square'                =>          $square,
            'img_small'             =>          $img_small,
            'countF'                =>          $countF,
            'countT'                =>          $countT,
            'countW'                =>          $countW,
            'countY'                =>          $countY,
            'city'                  =>          '芜湖市',
            'created_at'            =>          $created_at,
        ];


        try{

            G_property::insert($params);

            return self::ajaxReturn(0,'success');

        }catch (\Exception $exception){

            return self::ShowError(ErrorDefine::DATABASE_ERROR);
        }

    }

    /**
     * @param Request $request
     * @return array
     * 二手房更新
     */
    public function doErshoufangUpdate(Request $request)
    {
        if(!$request->has('token') && empty($request->input('token'))){

            return self::showError(ErrorDefine::ACCOUNT_LOGIN_EXPIRED);
        }

        if(empty($request->input('id')) || !$request->has('id')){

            return self::ShowError(ErrorDefine::INVALID_PARAMS);
        }


        $id =  $request->input('id');
        $property = G_property::find($id);

        if($property == NULL){
            return self::showError(ErrorDefine::INVALID_PARAMS);
        }

        if(!empty($request->input('title')) && $request->has('title')){

            $property->title = $request->input('title');
        }

        if(!empty($request->input('ppy_des')) && $request->has('ppy_des')){

            $property->ppy_des = $request->input('ppy_des');
        }

        if(!empty($request->input('district')) && $request->has('district')){

            $property->district = $request->input('district');
        }

        if(!empty($request->input('addr')) && $request->has('addr')){

            $property->addr = $request->input('addr');
        }

        if(!empty($request->input('type')) && $request->has('type')){

            $property->type = $request->input('type');
        }

        if(!empty($request->input('img_small')) && $request->has('img_small')){

            $property->img_small = $request->input('img_small');
        }


        if(!empty($request->input('recommend')) && $request->has('recommend')){

            $property->recommend = $request->input('recommend');
        }

        if(!empty($request->input('sell_price')) && $request->has('sell_price')){

            $property->sell_price = $request->input('sell_price');
        }
        if(!empty($request->input('square')) && $request->has('square')){

            $property->square = $request->input('square');
        }
        if(!empty($request->input('sell_price_unit')) && $request->has('sell_price_unit')){

            $property->sell_price_unit = $request->input('sell_price_unit');
        }
        if(!empty($request->input('bizcircle')) && $request->has('bizcircle')){

            $property->bizcircle = $request->input('bizcircle');
        }

        if(!empty($request->input('usage_type')) && $request->has('usage_type')){

            $property->usage_type = $request->input('usage_type');
        }

        if(!empty($request->input('decoration')) && $request->has('decoration')){

            $property->decoration = $request->input('decoration');
        }

        if(!empty($request->input('orientation')) && $request->has('orientation')){

            $property->orientation = $request->input('orientation');
        }
        try{

            $property->save();
            return self::ajaxReturn(0,'success');

        }catch (\Exception $exception){

            return self::ShowError(ErrorDefine::DATABASE_ERROR);

        }
    }

}