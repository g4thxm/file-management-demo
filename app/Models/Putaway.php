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


// public static function reportSummary($filters = array())
// {
//     $putaway = (new self)->getTable();
    
//     // Get unique groups with their latest created_at
//     $latestRecords = self::select(
//         'item_code',
//         'bin_code',
//         'stock_uom',
//         DB::raw('MAX(created_at) as latest_date')
//     )
//     ->whereNotNull('item_code')
//     ->where('item_code', '!=', '')
//     ->whereNotNull('bin_code')
//     ->where('bin_code', '!=', '')
//     ->groupBy('item_code', 'bin_code', 'stock_uom')
//     ->get();
    
//     $results = collect();
    
//     foreach ($latestRecords as $group) {
//         // Get the latest record for this group
//         $latest = self::where('item_code', $group->item_code)
//             ->where('bin_code', $group->bin_code)
//             ->where('stock_uom', $group->stock_uom)
//             ->where('created_at', $group->latest_date)
//             ->first();
            
//         if ($latest) {
//             // Get totals for this group
//             $totals = self::where('item_code', $group->item_code)
//                 ->where('bin_code', $group->bin_code)
//                 ->where('stock_uom', $group->stock_uom)
//                 ->select(
//                     DB::raw('COUNT(*) as total_scans'),
//                     DB::raw('COUNT(DISTINCT roll_no) as total_rolls'),
//                     DB::raw('SUM(CAST(quantity AS DECIMAL(10,2))) as total_quantity')
//                 )
//                 ->first();
            
//             // Add totals to the latest record
//             $latest->total_scans = $totals->total_scans ?? 0;
//             $latest->total_rolls = $totals->total_rolls ?? 0;
//             $latest->total_quantity = $totals->total_quantity ?? 0;
            
//             $results->push($latest);
//         }
//     }
    
//     // Apply filters
//     $filteredResults = $results->filter(function($record) use ($filters) {
//         $pass = true;
        
//         if (!empty($filters['from_date'])) {
//             $pass = $pass && (strtotime($record->created_at) >= strtotime($filters['from_date']));
//         }
        
//         if (!empty($filters['to_date'])) {
//             $pass = $pass && (strtotime($record->created_at) <= strtotime($filters['to_date']));
//         }
        
//         if (!empty($filters['item_code'])) {
//             $pass = $pass && ($record->item_code == $filters['item_code']);
//         }
        
//         if (!empty($filters['scanned_by'])) {
//             $pass = $pass && ($record->scanned_by == $filters['scanned_by']);
//         }
        
//         if (!empty($filters['bin_code'])) {
//             $pass = $pass && ($record->bin_code == $filters['bin_code']);
//         }
        
//         if (!empty($filters['stock_uom'])) {
//             $pass = $pass && ($record->stock_uom == $filters['stock_uom']);
//         }
        
//         if (!empty($filters['warehouse'])) {
//             $pass = $pass && ($record->warehouse == $filters['warehouse']);
//         }
        
//         if (!empty($filters['floor'])) {
//             $pass = $pass && ($record->floor == $filters['floor']);
//         }
        
//         return $pass;
//     });
    
//     // Sort by latest date
//     return $filteredResults->sortByDesc('created_at')->values();
// }

public static function reportSummary($filters = array())
{
    $putaway = (new self)->getTable();
    $uomTable = (new \App\Models\Uom())->getTable();
    
    // Get the latest record for each group with UOM name
    $subquery = self::select(
        'item_code',
        'bin_code',
        'stock_uom',
        DB::raw('MAX(id) as latest_id') // Get latest ID instead of created_at
    )
    ->whereNotNull('item_code')
    ->where('item_code', '!=', '')
    ->whereNotNull('bin_code')
    ->where('bin_code', '!=', '')
    ->groupBy('item_code', 'bin_code', 'stock_uom');
    
    // Apply date filters to subquery if provided
    if (!empty($filters['from_date'])) {
        $subquery->whereDate('created_at', '>=', $filters['from_date']);
    }
    
    if (!empty($filters['to_date'])) {
        $subquery->whereDate('created_at', '<=', $filters['to_date']);
    }
    
    $latestIds = $subquery->pluck('latest_id');
    
    // Main query to get all required data
    $query = self::select(
        "$putaway.*",
        "$uomTable.uom_name as uom_name", // Get UOM name
        DB::raw("(SELECT COUNT(*) FROM $putaway p2 
                  WHERE p2.item_code = $putaway.item_code 
                  AND p2.bin_code = $putaway.bin_code 
                  AND p2.stock_uom = $putaway.stock_uom) as total_scans"),
        DB::raw("(SELECT COUNT(DISTINCT roll_no) FROM $putaway p3 
                  WHERE p3.item_code = $putaway.item_code 
                  AND p3.bin_code = $putaway.bin_code 
                  AND p3.stock_uom = $putaway.stock_uom) as total_rolls"),
        DB::raw("(SELECT SUM(CAST(quantity AS DECIMAL(10,2))) FROM $putaway p4 
                  WHERE p4.item_code = $putaway.item_code 
                  AND p4.bin_code = $putaway.bin_code 
                  AND p4.stock_uom = $putaway.stock_uom) as total_quantity")
    )
    ->leftJoin($uomTable, "$putaway.stock_uom", '=', "$uomTable.id")
    ->whereIn("$putaway.id", $latestIds);
    
    // Apply remaining filters
    if (!empty($filters['item_code'])) {
        $query->where("$putaway.item_code", $filters['item_code']);
    }
    
    if (!empty($filters['scanned_by'])) {
        $query->where("$putaway.scanned_by", $filters['scanned_by']);
    }
    
    if (!empty($filters['bin_code'])) {
        $query->where("$putaway.bin_code", $filters['bin_code']);
    }
    
    if (!empty($filters['stock_uom'])) {
        // Filter by UOM name
        $query->where("$uomTable.uom_name", $filters['stock_uom']);
    }
    
    if (!empty($filters['warehouse'])) {
        $query->where("$putaway.warehouse", $filters['warehouse']);
    }
    
    if (!empty($filters['floor'])) {
        $query->where("$putaway.floor", $filters['floor']);
    }
    
    return $query->orderBy("$putaway.created_at", 'desc')->get();
}

public static function reportDetailed($filters = array())
{
    $putaway = (new self)->getTable();
    $uomTable = (new \App\Models\Uom())->getTable();
    
    $query = self::select(
        "$putaway.id",
        "$putaway.item_code",
        "$putaway.roll_no",
        "$putaway.quantity",
        "$uomTable.uom_name as stock_uom", // Get UOM name
        "$putaway.stock_uom as stock_uom_id", // Keep ID if needed
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
    ->leftJoin($uomTable, "$putaway.stock_uom", '=', "$uomTable.id")
    ->whereNotNull("$putaway.item_code")
    ->where("$putaway.item_code", '!=', '');
    
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
    
    if (!empty($filters['bin_code'])) {
        $query->where("$putaway.bin_code", '=', $filters['bin_code']);
    }
    
    if (!empty($filters['stock_uom'])) {
        $query->where("$putaway.stock_uom", '=', $filters['stock_uom']);
    }
    
    if (!empty($filters['scanned_by'])) {
        $query->where("$putaway.scanned_by", '=', $filters['scanned_by']);
    }
    
    // Order by latest scans first
    $query->orderBy("$putaway.created_at", 'desc');
    
    return $query->get();
}
}