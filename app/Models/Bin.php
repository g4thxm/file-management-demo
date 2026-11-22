<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use illuminate\Support\Facades\DB;

class Bin extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable=['Bin_Name','bin_code','storage_loc_id','Bin_Desc'];
    protected $dates=['deleted_at'];

    public static function getBin(){



        $bin = new Bin;

        $binDatas = $bin->get(['Bin_Name','bin_code','storage_loc_id','Bin_Desc'])->sortBy('Bin_Name');

        if(!sizeof($binDatas)){
            return redirect()->route('bin.index')->with('error','Nothing to download');

        }

        $data = array();

        foreach ($binDatas as $binData) {

            $d = array();

            $d['Bin_Name'] = $binData['Bin_Name'];
            $d['bin_code'] = $binData['bin_code'];
            $d['Bin_Desc'] = $binData['Bin_Desc'];
            $d['storage_loc_id'] = $binData->location->Loc_Name;
          
            $data[] = $d;

        }



        return $data;
    }

    public function location(){

        return $this->belongsTo('App\Models\Location','storage_loc_id');
    }
}
