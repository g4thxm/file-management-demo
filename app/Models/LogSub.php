<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use illuminate\Support\Facades\DB;

class LogSub extends Model
{
    use HasFactory;
    use HasFactory;
    use SoftDeletes;
    protected $fillable=[
        'item_id',
        'description',
        'user_id'
    ];

    protected $softDelete = true;
    protected $dates=['deleted_at'];

}

