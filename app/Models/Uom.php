<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use illuminate\Support\Facades\DB;

class Uom extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable=['uom_name','uom_code'];
    protected $softDelete = true;
    protected $dates=['deleted_at'];

    public static function getUom(){
        $records=DB::table('uoms')->select('uom_name','uom_code')->whereNull('deleted_at')->get()->toArray();
        return $records;
    }
}
