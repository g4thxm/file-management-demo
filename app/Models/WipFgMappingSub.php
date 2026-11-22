<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WipFgMappingSub extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'wip_fg_mapping_sub';
    protected $fillable = ['fg_mapping_id', 'wip_stage_id'];
}