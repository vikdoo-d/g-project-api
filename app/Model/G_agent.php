<?php
namespace App\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Testing\DatabaseMigrations;

class G_agent extends Model
{
    use SoftDeletes;

    protected $table = 'agent';
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
