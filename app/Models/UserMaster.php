<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use illuminate\Support\Facades\DB;

class UserMaster extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable=[
        'user_name',
        'login_user_name',
        'user_type',
        'location_id',
        'permission_level',
        'password'
    ];
    protected $softDelete = true;
    protected $dates=['deleted_at'];

    public static function getUsermaster(){
        $records=DB::table('users')
        ->leftjoin('locations','locations.id','=','users.location_id')
        ->select(
            'users.user_name',
            'users.login_user_name',
            'users.user_type',
            'locations.Loc_Name',
            'users.permission_level'
            )->whereNull('users.deleted_at')->get()->toArray();

            $data=[];
            $i=0;
            foreach($records as $re){
                $data[$i]['user_name']=$re->user_name;
                $data[$i]['login_user_name']=$re->login_user_name;
                if($re->user_type==1){
                    $data[$i]['user_type']="Standard User";
                }else {
                    $data[$i]['user_type']="Administrator";
                }
                $data[$i]['location_id']=$re->Loc_Name;
                if($re->permission_level==1){
                    $data[$i]['permission_level']="Top Level";
                }else if($re->permission_level==2){
                    $data[$i]['permission_level']="Oranization Level";
                }else{
                    $data[$i]['permission_level']="Location Level";
                }
                $i++;
            }
            // dd($data);
        return $data;
    }
    public function location(){
        return $this->belongsTo('App\Models\Location','location_id');
    }
}
