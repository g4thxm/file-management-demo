<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use illuminate\Support\Facades\DB;

class PurchaseOrder extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable=[
        'purchase_no',
        'vendor_id',
        'purchase_date',
        'status',

    ];

    protected $softDelete = true;
    protected $dates=['deleted_at'];

    public static function nextNumber(){

        $prefix = 'PR'.date('ym');
        
        $start = '00001';

        $len=strlen($prefix);

        $purchase_no= DB::select("SELECT

        CONCAT( '$prefix',

        LPAD(max_purchase_no+1,GREATEST(5,LENGTH(max_purchase_no+1)),'0')

        ) purchase_no

        FROM

        (SELECT IFNULL(MAX(SUBSTRING(purchase_no,$len+1)),'0') max_purchase_no 

            FROM purchase_orders WHERE  purchase_no LIKE '$prefix%') P2");
    
        return $purchase_no[0]->purchase_no;
    }

      /*Purchase Order Summary report*/
      public static function reportsummary($filters=array())
      {
  
          $purchase = (new PurchaseOrder)->getTable();
          $purchasesub = (new PurchaseOrderSub)->getTable();
          $item = (new Item)->getTable();
          $vendor = (new Vendor)->getTable();

          $user=User::where('id',auth()->user()->id)->get();
  
  
  
          $items =PurchaseOrder::select("$purchase.id","$purchase.purchase_no","$purchase.purchase_date","$vendor.Vendor_Name","$purchase.status")
                  ->join($purchasesub,"$purchasesub.purchase_order_id", '=' ,"$purchase.id")
                  ->join($item,"$item.id", '=' ,"$purchasesub.item_id")
                  ->join($vendor,"$vendor.id", '=' ,"$purchase.vendor_id")
                  ->whereNull("$purchase.deleted_at")
                  ->whereNull("$item.deleted_at")
                  ->whereNull("$vendor.deleted_at")

                  ->GroupBy("$purchase.purchase_no");
  
                  if($filters['from_date']) $items = $items->where("$purchase.purchase_date",'>=',date($filters['from_date']));
                  if($filters['to_date']) $items = $items->where("$purchase.purchase_date",'<=',date($filters['to_date']));
                  if($filters['orderno']) $items = $items->where("$purchase.purchase_no",'=',$filters['orderno']);
                  if($filters['item_id']) $items = $items->where("$purchasesub.item_id",'=',$filters['item_id']);

  
                  return $items->get();
  
  
      }

      public static function reportdetailed($filters=array())
      {
  
          $purchase = (new PurchaseOrder)->getTable();
          $purchasesub = (new PurchaseOrderSub)->getTable();
          $item = (new Item)->getTable();
         
          $user=User::where('id',auth()->user()->id)->get();
  
  
  
          $items =PurchaseOrder::select("$item.item_name","$item.part_no","$purchasesub.picked_qty","$purchasesub.qty","$purchase.id","$purchase.purchase_no","$purchase.purchase_date")
                  ->join($purchasesub,"$purchasesub.purchase_order_id", '=' ,"$purchase.id")
                  ->join($item,"$item.id", '=' ,"$purchasesub.item_id")

                  ->whereNull("$purchase.deleted_at")
                  ->whereNull("$item.deleted_at");
  
                  if($filters['from_date']) $items = $items->where("$purchase.purchase_date",'>=',date($filters['from_date']));
                  if($filters['to_date']) $items = $items->where("$purchase.purchase_date",'<=',date($filters['to_date']));
                  if($filters['orderno']) $items = $items->where("$purchase.purchase_no",'=',$filters['orderno']);
                  if($filters['item_id']) $items = $items->where("$purchasesub.item_id",'=',$filters['item_id']);

  
                  return $items->get();
  
  
      }
}
