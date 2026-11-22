<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderUploadLog extends Model
{
    use HasFactory;
    protected $table = "purchase_order_upload_log";
    protected $fillable = [
        'order_id',
        'file',
        'uploaded_by',
        'uploaded_at'
    ];
}
