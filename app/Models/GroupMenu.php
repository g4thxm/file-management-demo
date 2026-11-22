<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GroupMenu extends Model
{
    protected $table = "_group_menu";
    use SoftDeletes;

    // public function submenus(){
    //     return $this->hasMany('App\Submenu');
    // }

}
