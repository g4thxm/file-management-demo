<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use GuzzleHttp\Psr7\Request;
use Illuminate\Contracts\Session\Session;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Permission extends Model
{
    use HasFactory;
	use Notifiable;
    use SoftDeletes;


    protected $table = 'permission';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id','submenu_id'];

    public function user(){
        return $this->belongsTo('App\User','user_id');
    }
    public function submenu(){
        return $this->belongsTo('App\Submenu','submenu_id');
    }

    public function subroute(){
        $user=Auth::id();
//         $data=Permission::pluck('submenu_id')
//            ->where('user_id',$user)
//            ->toArray();
        $permission=(new Permission)->getTable();
        $submenu=(new Submenu)->getTable();
        $data=DB::table($permission)
            ->join($submenu,"$submenu.id","=","$permission.submenu_id")
            ->whereNull("$permission.deleted_at")
            ->whereNull("$submenu.deleted_at");
        $data=$data->pluck("$submenu.link")
            ->where("$permission.user_id",$user)
            ->toArray();
        return $data;
    }
}
