<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use illuminate\Support\Facades\DB;

class SubCategory extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable=[
        'category_id',
        'name',
        'description'
    ];
    
    protected $dates=['deleted_at'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
