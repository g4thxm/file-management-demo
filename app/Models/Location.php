<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use illuminate\Support\Facades\DB;

class Location extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable=['Loc_Name','Prefix','branch_id','NAV_loc_code','loc_type','Loc_Desc'];
    protected $dates=['deleted_at'];


    public static function getLocation(){



        $location = new Location;

        $locationDatas = $location->get(['Loc_Name','Prefix','branch_id','NAV_loc_code','loc_type','Loc_Desc'])->sortBy('Loc_Name');

        if(!sizeof($locationDatas)){
            return redirect()->route('location.index')->with('error','Nothing to download');

        }

        $data = array();

        foreach ($locationDatas as $locationData) {

            $d = array();

            $d['Loc_Name'] = $locationData['Loc_Name'];
            $d['Prefix'] = $locationData['Prefix'];
            $d['NAV_loc_code'] = $locationData['NAV_loc_code'];
            $d['loc_type'] = $locationData['loc_type'];
            $d['Loc_Desc'] = $locationData['Loc_Desc'];
            $d['branch_id'] = $locationData->branch->name;


            $data[] = $d;

        }



        return $data;
    }


    public function branch(){

        return $this->belongsTo('App\Models\Branch','branch_id');
    }
}
