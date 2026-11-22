<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use illuminate\Support\Facades\DB;

class Reason extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable=['reason','description'];

    protected $softDelete = true;
    protected $dates=['deleted_at'];

    public static function getReason(){
        $records=DB::table('reasons')->select('reason','description')->whereNull('deleted_at')->get()->toArray();
        return $records;
    }
}
