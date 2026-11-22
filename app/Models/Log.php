<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use illuminate\Support\Facades\DB;
class Log extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable=[
      
        'item_id',
        'description',
        'user_id'
    ];

    protected $softDelete = true;
    protected $dates=['deleted_at'];

    public function item(){
        return $this->belongsTo('App\Models\item','item_id');
    }
    public static function getLog(){
        $records=DB::table('logs')->leftjoin('items','items.id','=','logs.item_id')->select('items.item_name','logs.description')->whereNull('logs.deleted_at')->get()->toArray();
        return $records;
    }
}

