<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use illuminate\Support\Facades\DB;

class PurchaseOrderCancel extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable=[
        'purchase_order_id',
        'canceled_user_id',
    ];

    protected $softDelete = true;
    protected $dates=['deleted_at'];
}
