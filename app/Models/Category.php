<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use illuminate\Support\Facades\DB;

class Category extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable=[
        'name',
        'description'
    ];

    public function subCategories()
    {
        return $this->hasMany(SubCategory::class);
    }
}
 