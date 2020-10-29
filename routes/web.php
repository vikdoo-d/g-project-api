<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return 'The servers running';
});
$router->group(['prefix' => '/api/v1'], function () use ($router) {
    $router->post('agent/get_agent_list','Agent\AgentController@getAgentList'); //经纪人列表
    $router->post('admin/dologin','Admin\LoginController@doLogin');

    /*******新闻资讯********/
    $router->post('news/get_news_list','News\NewsController@getNewsList'); //新闻列表
    $router->post('news/news_soft_del','News\NewsController@doNewsSoftDelete');//新闻删除
    $router->post('news/news_add','News\NewsController@doNewsAdd');//新闻添加
    $router->post('news/news_update','News\NewsController@doNewsUpdate');//新闻添加


    /**********客户反馈************/
    $router->post('fb/get_fb_list','Feedback\FeedbackController@getFeedbackList'); //客户反馈列表

    /*********招聘信息************/
    $router->post('jobs/get_jobs_list','Jobs\JobsController@getJobsList');  //岗位列表
    $router->post('jobs/jobs_add','Jobs\JobsController@doJobsAdd');//添加招聘职位
    $router->post('jobs/jobs_delete','Jobs\JobsController@doJobsDelete');//删除该职位
    $router->post('jobs/jobs_update','Jobs\JobsController@doJobsUpdate');//停止招聘
    /*******管理员*******/
    $router->post('admin/get_admin_list','Admin\AdminController@getAdminList'); //管理员列表（初始化/搜索）
    $router->post('admin/admin_add','Admin\AdminController@doAdminAdd'); //管理员添加
    $router->post('admin/admin_update','Admin\AdminController@doAdminUpdate'); //管理员添加
    $router->post('admin/admin_soft_del','Admin\AdminController@doAdminDelete'); //管理员软删除

    /**************房源管理*************/

    $router->post('ppy/ershoufang_add','Property\PropertyController@doErshoufangAdd');  //房源添加
    $router->post('ppy/ershoufang_update','Property\@PropertyCOntroller@doEeshoufangUpdate');//二手房修改
    $router->post('ppy/get_ppy_list','Property\PropertyController@getPropertyList');  //列表
    $router->post('ppy/check_ppy_num','Property\PropertyController@checkPpyUuidExist');
    $router->post('ppy/ppy_soft_del','Property\PropertyController@doPropertySoftDelete');
    /*********************************/
});

/********官网页面掉用接口**********/
$router->group(['prefix' => '/web/v1'], function () use ($router) {
    /*********招聘信息************/
    $router->get('jobs/get_jobs_list','Jobs\JobsController@getJobsListForWeb');  //岗位列表
    $router->get('jobs/get_jobs_info','Jobs\JobsController@getJobsInfo');  //岗位详情
    /*********新闻消息************/
    $router->get('news/get_news_list','News\NewsController@getNewsListForWeb'); //新闻列表
    $router->get('news/get_news_info','News\NewsController@getNewsInfoForWebById'); //新闻详情
});

