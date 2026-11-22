<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $dates = ['deleted_at'];
    
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'employee_id',
        'user_type',
        'location_id',
        'permission_level',
        'branch_id',
        'darkmode',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public static function getUsermaster(){
        $records=DB::table('users')
        ->leftjoin('locations','locations.id','=','users.location_id')
        ->select(
            'users.name',
            'users.username',
            'users.user_type',
            'locations.Loc_Name',
            'users.permission_level'
            )->whereNull('users.deleted_at')->get()->toArray();
            $data=[];
            $i=0;
            foreach($records as $re){
                $data[$i]['name']=$re->name;
                $data[$i]['username']=$re->username;
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

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
