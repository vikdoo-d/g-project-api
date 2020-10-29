<?php

namespace App\Http\Controllers\Agent;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Model\G_agent;
use Session;
use Config;
use Illuminate\Support\Facades\Crypt;
use DB;
use Illuminate\Support\Str;

class AgentController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
        $this->agent  = new G_agent();
    }

    public function getAgentList(Request $request)
    {
        $data = $this->agent->get()->toArray();
        return self::ajaxReturn(0,'success',$data);
    }
}
