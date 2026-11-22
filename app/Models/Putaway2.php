<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
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
        
        // GROUPED query - ONLY item_code, warehouse, floor in GROUP BY
        $query = self::select(
            "$putaway.item_code",
            "$putaway.warehouse",
            "$putaway.floor",
            // Aggregate functions for other fields
            DB::raw("MAX($putaway.item_description) as item_description"),
            DB::raw("MAX($putaway.stock_uom) as stock_uom"),
            DB::raw("COUNT(*) as scan_count"),
            DB::raw("COUNT(DISTINCT $putaway.roll_no) as roll_count"),
            DB::raw("SUM(CAST($putaway.quantity AS DECIMAL(10,2))) as total_quantity"),
            DB::raw("GROUP_CONCAT(DISTINCT $putaway.scanned_by SEPARATOR ', ') as scanned_by"),
            DB::raw("MAX($putaway.created_at) as last_updated"),
            DB::raw("GROUP_CONCAT(DISTINCT CONCAT($putaway.row_no, '/', $putaway.block, '/', $putaway.bin_no) SEPARATOR ', ') as bin_details")
        )
        ->whereNotNull("$putaway.item_code")
        ->where("$putaway.item_code", '!=', '')
        
        ->groupBy(
            "$putaway.item_code",
            "$putaway.warehouse",
            "$putaway.floor"
        );
        
        // Apply filters
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
        
        
        // Order by
        $query->orderBy("$putaway.item_code")
              ->orderBy("$putaway.warehouse")
              ->orderBy("$putaway.floor");
        
        return $query->get();
    }
    
}