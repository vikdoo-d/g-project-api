<?php
/**
 * Created by PhpStorm.
 * User: dongkang
 * Date: 2020/8/31
 * Time: 16:42
 */

namespace App\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class G_feedback extends Model
{
    use SoftDeletes;
    protected $table = 'feedback';
    protected $dates = ['deleted_at'];
    protected $primaryKey = 'id';
    public  $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */


    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */


//    protected $fillable = ['username', 'password','total'];
    public function getDateFormat(){
        return time();
    }

}
