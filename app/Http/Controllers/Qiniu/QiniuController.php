<?php

namespace App\Http\Controllers\Qiniu;
//require '../vendor/autoload.php';
use App\Common\ErrorDefine;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

//导入七牛相关类
use Qiniu\Auth;
use Qiniu\Storage\BucketManager;
use Qiniu\Storage\UploadManager;

class QiniuController extends Controller
{
    public  function __construct()
    {
    }


    public function deleteImg(Request $request)
    {
        $accessKey = 'lTnFoh7GVY4wKSs46__kGOEd2UEGWGz3bDBtubZY';
        $secretKey = 'QOJA2kMrzVdQAaZyIevXTD2aQ0bPwD9sVcXDG9N2';
        $bucket = 'gejia';
        $key = $request;
        $auth = new Auth($accessKey, $secretKey);
        $config = new \Qiniu\Config();
        $bucketManager = new BucketManager($auth, $config);
        $err = $bucketManager->delete($bucket, $key);
        if ($err) {
            print_r($err);
        }
    }

    /**
     * @param Request $request
     * @return string
     * 上传图片七牛云
     */
    public function uploadImg(Request $request)
    {
        $idCardFrontImg = '';
        $token=$this->getToken();
        $uploadManager=new UploadManager();
        $img= $request->file('file');
        if ($img) {

            //获取文件的原文件名 包括扩展名
            $yuanname= $img->getClientOriginalName();

            //获取文件的扩展名
            $kuoname=$img->getClientOriginalExtension();

            //获取文件的类型
            $type=$img->getClientMimeType();

            //获取文件的绝对路径，但是获取到的在本地不能打开
            $filePath=$img->getRealPath();

            //要保存的文件名 时间+扩展名
            $filename='upload/image/' . date('Y-m-d') . '/' . uniqid() .'.'.$kuoname;
            //token，存储的文件名，真是路径，参数，和文件类型
            list($ret,$err)=$uploadManager->putFile($token,$filename,$filePath,null,$type,false);
            //保存文件          配置文件存放文件的名字  ，文件名，路径
//            $bool= Storage::disk('uploadimg')->put($filename,file_get_contents($filePath));

            if($err){//上传失败
                return json_encode($idCardFrontImg);//返回错误信息到上传页面
            }else{//成功
                //添加信息到数据库
                $img_url = config('qiniu.path') . $ret['key'];
                return json_encode(['code'=>0,'msg'=>'success','filepath'=>$img_url]);//返回结果到上传页面
            }
            //

        }else{

            return json_encode($idCardFrontImg);
        }
    }

    public function postDoupload()
    {
        $token=$this->getToken();
        $uploadManager=new UploadManager();
        $name=$_FILES['file']['name'];
        $filePath=$_FILES['file']['tmp_name'];
        $type=$_FILES['file']['type'];
        //token，存储的文件名，真是路径，参数，和文件类型
        list($ret,$err)=$uploadManager->putFile($token,$name,$filePath,null,$type,false);
        if($err){//上传失败
            var_dump($err);
            return redirect()->back()->with('err',$err);//返回错误信息到上传页面
        }else{//成功
            //添加信息到数据库
            return redirect()->back()->with('key',$ret['key']);//返回结果到上传页面
        }
    }
    /**
     * 生成上传凭证
     * @return string
     */
    private function getToken()
    {
        $accessKey=config('qiniu.accessKey');
        $secretKey=config('qiniu.secretKey');
        $auth=new Auth($accessKey, $secretKey);
        $bucket=config('qiniu.bucket');//上传空间名称
        //设置put policy的其他参数
        //$opts=['callbackUrl'=>'http://www.callback.com/','callbackBody'=>'name=$(fname)&hash=$(etag)','returnUrl'=>"http://www.baidu.com"];
        return $auth->uploadToken($bucket);//生成token
    }
}
