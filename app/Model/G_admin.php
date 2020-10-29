<?php
namespace App\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class G_admin extends Model
{
    use SoftDeletes;
    protected $table = 'admin';

    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
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
