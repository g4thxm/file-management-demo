<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use illuminate\Support\Facades\DB;

class Item extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable=[
        'item_name',
        'part_no',
        'category_id',
        'sub_category_id',
        'uom_id',
        'type_of_packing',
        'spq_quantity',
        'net_weight',
        'gross_weight',
        'item_type',
        'primary_shell_life',
        'secondary_shell_life',
        'best_before_type',
        'best_before_value',
        'dispatch_alert',
        'hsn_code',
        'single_packet_weight',
        'description'
    ];

    protected $softDelete = true;
    protected $dates=['deleted_at'];

    public static function getItem(){


        
        $records=DB::table('items')
        ->leftjoin('categories','categories.id','=','items.category_id')
        ->leftjoin('sub_categories','sub_categories.id','=','items.sub_category_id')
        ->leftjoin('uoms','uoms.id','=','items.uom_id')
        ->select(
            'items.item_name',
            'items.part_no',
            'categories.category_name',
            'sub_categories.sub_category_name',
            'uoms.uom_name',
            'items.type_of_packing',
            'items.spq_quantity',
            'items.net_weight',
            'items.gross_weight',
            'items.item_type',
            'items.primary_shell_life',
            'items.secondary_shell_life',
            'items.best_before_value',
            'items.dispatch_alert',
            'items.hsn_code',
            'items.description'
            )->whereNull('items.deleted_at')->get()->toArray();

            $data=[];
            $i=0;
            foreach($records as $re){
                $data[$i]['item_name']=$re->item_name;
                $data[$i]['part_no']=$re->part_no;
                $data[$i]['category_name']=$re->category_name;
                $data[$i]['sub_category_name']=$re->sub_category_name;
                $data[$i]['uom_name']=$re->uom_name;
                $data[$i]['type_of_packing']=$re->type_of_packing;
                $data[$i]['spq_quantity']=$re->spq_quantity;
                $data[$i]['net_weight']=$re->net_weight;
                $data[$i]['gross_weight']=$re->gross_weight;
                $data[$i]['item_type']=$re->item_type;
                $data[$i]['primary_shell_life']=$re->primary_shell_life;
                $data[$i]['secondary_shell_life']=$re->secondary_shell_life;
                $data[$i]['best_before_value']=$re->best_before_value;
                $data[$i]['dispatch_alert']=$re->dispatch_alert;
                $data[$i]['hsn_code']=$re->hsn_code;
                $data[$i]['description']=$re->description;
                $i++;
            }
            // dd($data);
        return $data;



    }
    public function category(){
        return $this->belongsTo('App\Models\Category','category_id');
    }
    public function subcategory(){
        return $this->belongsTo('App\Models\SubCategory','sub_category_id');
    }
    public function uom(){
        return $this->belongsTo('App\Models\Uom','uom_id');
    }
    // public static function check(){
    //     return 1;
    // }

}
