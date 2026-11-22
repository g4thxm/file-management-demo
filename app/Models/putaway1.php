<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PutAway extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'putaways';

    /**
     * The attributes that can be filled.
     */
    protected $fillable = [
        'item_code',
        'roll_no', 
        'quantity',
        'item_description',
        'stock_uom',
        'bin_code',
        'warehouse',
        'floor',
        'row_no',
        'block',
        'bin_no',
        'scanned_by'

    ];


public static function reportSummary($filters = array())
    {
        $putaway = (new self)->getTable();
        
        // Base query - selecting all fields you need
        $query = self::select(
            "$putaway.id",
            "$putaway.item_code",
            "$putaway.roll_no",
            "$putaway.quantity",
            "$putaway.stock_uom",
            "$putaway.item_description",
            "$putaway.scanned_by",
            "$putaway.created_at",
            "$putaway.warehouse",
            "$putaway.floor",
            "$putaway.row_no",
            "$putaway.block",
            "$putaway.bin_code",
            "$putaway.bin_no"
        )
        ->whereNotNull("$putaway.item_code")
        ->where("$putaway.item_code", '!=', '');
        
        // Apply filters (same as purchase order)
        if (!empty($filters['from_date'])) {
            $query->whereDate("$putaway.created_at", '>=', $filters['from_date']);
        }
        
        if (!empty($filters['to_date'])) {
            $query->whereDate("$putaway.created_at", '<=', $filters['to_date']);
        }
        
        if (!empty($filters['item_code'])) {
            $query->where("$putaway.item_code", '=', $filters['item_code']);
        }
        
        if (!empty($filters['scanned_by'])) {
            $query->where("$putaway.scanned_by", '=', $filters['scanned_by']);
        }
        
       
        // Order by latest scans first
        $query->orderBy("$putaway.created_at", 'desc');
        
        return $query->get();
    }
}