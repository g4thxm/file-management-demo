<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use illuminate\Support\Facades\DB;

class PurchaseOrderSub extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable=[
        'purchase_order_id',
        'item_id',
        'qty',
        'picked_qty',
        'status'
    ];

    protected $softDelete = true;
    protected $dates=['deleted_at'];
}
