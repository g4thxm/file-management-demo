<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
// use illuminate\Support\Facades\DB;
use DB;


class Barcode extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = [
        'serial_no',
        'transaction_id',
        'transaction_type',
        'branch_id',
        'location_id',
        'bin_id',
        'pallet_id',
        'item_id',
        'manf_date',
        'expiry_date',
        'lot_number',
        'batch_number',
        'shelf_life',
        'spq_qty',
        'net_weight',
        'grn_sqp_qty',
        'grn_net_weight',
        'barcode_time',
        'status',
        'user_id',
        'qc_approval_status',
        'price',
        'total_price'
    ];

    protected $softDelete = true;
    protected $dates = ['deleted_at'];


    public static function nextNumber($branchid)
    {

        $branch = Branch::findOrFail($branchid);
        // dd($branch);


        $prefix = date('ymd');
        // $prefix = $branch->Branch_Code . 'GN' . date('ymd'

        $start = '0000001';

        $len = strlen($prefix);

        $barcode = DB::select("SELECT

        CONCAT( '$prefix',

        LPAD(max_serial_no+1,GREATEST(6,LENGTH(max_serial_no+1)),'0')

        ) serial_no

        FROM

        (SELECT IFNULL(MAX(SUBSTRING(serial_no,$len+1)),'0') max_serial_no 

            FROM barcodes WHERE  serial_no LIKE '$prefix%') P2");

        return $barcode[0]->serial_no;


        // return DB::table( with(new self)->getTable() )
        //     ->select(DB::raw(" CONCAT('{$prefix}',IFNULL(SUBSTR(MAX(serial_no) FROM 1+LENGTH('{$prefix}'))+1,$start)) nextNumber "))
        //     ->whereRaw(" branch_id= '$branchid' ")
        //     ->get()->pluck('nextNumber')->first();
    }
    public static function newRepackBarcode($branchid)
    {

        $branch = Branch::findOrFail($branchid);
        // dd($branch);


        $prefix = $branch->Branch_Code . 'RP' . date('ymd');

        $start = '0000001';

        $len = strlen($prefix);

        $barcode = DB::select("SELECT

        CONCAT( '$prefix',

        LPAD(max_serial_no+1,GREATEST(6,LENGTH(max_serial_no+1)),'0')

        ) serial_no

        FROM

        (SELECT IFNULL(MAX(SUBSTRING(serial_no,$len+1)),'0') max_serial_no 

            FROM barcodes WHERE  serial_no LIKE '$prefix%') P2");

        return $barcode[0]->serial_no;
    }
    public static function stockreportsummary($filters = array())
    {
        $barcode = (new Barcode)->getTable();
        $item = (new Item)->getTable();
        $uom = (new Uom)->getTable();
        $location = (new Location)->getTable();
        $bin = (new Bin)->getTable();
        $branch = (new Branch)->getTable();
        $grn = (new Grn)->getTable();
        $grn_sub = (new Grn_sub)->getTable();

        $stock = Barcode::select(
            "$item.part_no",
            "$item.item_name",
            "$item.item_type",
            "$item.id as item_id",
            "$item.hsn_code",
            "$item.spq_quantity",
            "$uom.uom_name",
            "$branch.name as branch_name",
            "$branch.id as branch_id",
            "$location.Loc_Name",
            "$location.NAV_loc_code",
            "$bin.Bin_Name",
            "$bin.id",
            "$barcode.location_id",
            "$barcode.batch_number",
            "$barcode.manf_date",
            "$barcode.qc_approval_status",
            "$barcode.status",
            "$barcode.transaction_id",
            DB::raw('SUM(barcodes.net_weight) net_weight'),
            DB::raw('SUM(barcodes.spq_qty) spq_qty'),
            DB::raw('COUNT(barcodes.serial_no) count')
        );

        // if($filters['stock_type'] =='1') $stock.= " ,SUM($barcode.net_weight) weight";
        // if($filters['stock_type'] =='2') $stock.= " ,SUM($barcode.wip_qty) weight";
        // if($filters['stock_type'] =='3') $stock.= " ,SUM($barcode.dispatch_qty * $item.spq_qty ) weight";
        // if($filters['stock_type'] =='4') $stock.= " ,SUM($barcode.net_weight) weight";
        // if($filters['stock_type'] =='5') $stock.= " ,SUM($barcode.grn_net_weight) weight";

        $stock = $stock->join("$item", "$item.id", "=", "$barcode.item_id")
            ->join("$branch", "$branch.id", "=", "$barcode.branch_id")
            ->join("$location", "$location.id", "=", "$barcode.location_id")
            ->join("$bin", "$bin.id", "=", "$barcode.bin_id")
            ->join("$uom", "$uom.id", "=", "$item.uom_id");

        if ($filters['fromdate']) $stock = $stock->whereDate("$barcode.barcode_time", '>=', date($filters['fromdate']));
        if ($filters['todate']) $stock = $stock->whereDate("$barcode.barcode_time", '<=', date($filters['todate']));
        if ($filters['frombarcode']) $stock = $stock->where("$barcode.serial_no", '>=', $filters['frombarcode']);
        if ($filters['tobarcode']) $stock = $stock->where("$barcode.serial_no", '<=', $filters['tobarcode']);
        if ($filters['category_id']) $stock = $stock->where("$item.category_id", '=', $filters['category_id']);
        if ($filters['sub_category_id']) $stock = $stock->where("$item.sub_category_id", '=', $filters['sub_category_id']);
        if ($filters['item_id']) $stock = $stock->where("$item.id", '=', $filters['item_id']);
        if ($filters['branch_id']) $stock = $stock->where("$branch.id", '=', $filters['branch_id']);
        if ($filters['location_id']) $stock = $stock->where("$barcode.location_id", '=', $filters['location_id']);
        if ($filters['grn_type']) $stock = $stock->where("$item.item_type", '=', $filters['grn_type']);
        // if($filters['qc_type']) $stock = $stock->where("$barcode.qc_approval_status",'=',$filters['qc_type']);
        if ($filters['bin_id']) $stock = $stock->where("$bin.id", '=', $filters['bin_id']);
        if ($filters['batch_number']) $stock = $stock->where("$barcode.batch_number", '=', $filters['batch_number']);
        if ($filters['stock_type']) $stock = $stock->where("$barcode.status", '=', $filters['stock_type']);

        // if($filters['stock_type'] =='1') $stock = $stock->where("$barcode.net_weight",'>',"0")->where("$barcode.status",'!=',"8");
        // if($filters['stock_type'] =='2') $stock = $stock->where("$barcode.wip_qty",'>',"0");
        // if($filters['stock_type'] =='3') $stock = $stock->where("$barcode.dispatch_qty",'>',"0");
        // if($filters['stock_type'] =='4') $stock = $stock->where("$barcode.Transaction_Type",'=',"Rejection");
        // if($filters['stock_type'] =='5') $stock = $stock->where("$barcode.status",'=',"5");


        // $stock= $stock->groupBy("$barcode.transaction_id");
        $stock = $stock->groupBy("$branch.id")
            ->groupBy("$barcode.location_id")
            ->groupBy("$item.id");
        if ($filters['stock_type'] == '4') $stock = $stock->groupBy("$barcode.transaction_id");

        // dd($stock->get());
        return $stock->get();
    }

    public static function stockreportbinwise($filters = array())
    {
        $barcode = (new Barcode)->getTable();
        $item = (new Item)->getTable();
        $uom = (new Uom)->getTable();
        $location = (new Location)->getTable();
        $bin = (new Bin)->getTable();
        $branch = (new Branch)->getTable();
        $grn = (new Grn)->getTable();
        $grn_sub = (new Grn_sub)->getTable();

        $stock = Barcode::select(
            "$branch.id as branch_id",
            "$item.part_no",
            "$item.item_name",
            "$item.item_type",
            "$item.id as item_id",
            "$item.hsn_code",
            "$item.spq_quantity",
            "$uom.uom_name",
            "$branch.name as branch_name",
            "$branch.id as branch_id",
            "$location.Loc_Name",
            "$location.NAV_loc_code",
            "$bin.Bin_Name",
            "$bin.id",
            "$barcode.location_id",
            "$barcode.batch_number",
            "$barcode.manf_date",
            "$barcode.qc_approval_status",
            "$barcode.status",
            "$barcode.transaction_id",
            DB::raw('SUM(barcodes.net_weight) net_weight'),
            DB::raw('SUM(barcodes.spq_qty) spq_qty'),
            DB::raw('COUNT(barcodes.serial_no) count')
        );

        // if($filters['stock_type'] =='1') $stock.= " ,SUM($barcode.net_weight) weight";
        // if($filters['stock_type'] =='2') $stock.= " ,SUM($barcode.wip_qty) weight";
        // if($filters['stock_type'] =='3') $stock.= " ,SUM($barcode.dispatch_qty * $item.spq_qty ) weight";
        // if($filters['stock_type'] =='4') $stock.= " ,SUM($barcode.net_weight) weight";
        // if($filters['stock_type'] =='5') $stock.= " ,SUM($barcode.grn_net_weight) weight";

        $stock = $stock->join("$item", "$item.id", "=", "$barcode.item_id")
            ->leftjoin("$branch", "$branch.id", "=", "$barcode.branch_id")
            ->leftjoin("$location", "$location.id", "=", "$barcode.location_id")
            ->leftjoin("$bin", "$bin.id", "=", "$barcode.bin_id")
            ->leftjoin("$uom", "$uom.id", "=", "$item.uom_id");

        if ($filters['fromdate']) $stock = $stock->whereDate("$barcode.barcode_time", '>=', date($filters['fromdate']));
        if ($filters['todate']) $stock = $stock->whereDate("$barcode.barcode_time", '<=', date($filters['todate']));
        if ($filters['frombarcode']) $stock = $stock->where("$barcode.serial_no", '>=', $filters['frombarcode']);
        if ($filters['tobarcode']) $stock = $stock->where("$barcode.serial_no", '<=', $filters['tobarcode']);
        if ($filters['category_id']) $stock = $stock->where("$item.category_id", '=', $filters['category_id']);
        if ($filters['sub_category_id']) $stock = $stock->where("$item.sub_category_id", '=', $filters['sub_category_id']);
        if ($filters['item_id']) $stock = $stock->where("$item.id", '=', $filters['item_id']);
        if ($filters['branch_id']) $stock = $stock->where("$branch.id", '=', $filters['branch_id']);
        if ($filters['location_id']) $stock = $stock->where("$barcode.location_id", '=', $filters['location_id']);
        if ($filters['grn_type']) $stock = $stock->where("$item.item_type", '=', $filters['grn_type']);
        // if($filters['qc_type']) $stock = $stock->where("$barcode.qc_approval_status",'=',$filters['qc_type']);
        if ($filters['bin_id']) $stock = $stock->where("$bin.id", '=', $filters['bin_id']);
        if ($filters['transaction_id']) $stock = $stock->where("$barcode.transaction_id", '=', $filters['transaction_id']);
        if ($filters['batch_number']) $stock = $stock->where("$barcode.batch_number", '=', $filters['batch_number']);
        if ($filters['stock_type']) $stock = $stock->where("$barcode.status", '=', $filters['stock_type']);
        // if($filters['batch']) $stock = $stock->where("$barcode.barcode_time",'>=',$filters['batch']);

        // if($filters['stock_type'] =='1') $stock = $stock->where("$barcode.net_weight",'>',"0")->where("$barcode.status",'!=',"8");
        // if($filters['stock_type'] =='2') $stock = $stock->where("$barcode.wip_qty",'>',"0");
        // if($filters['stock_type'] =='3') $stock = $stock->where("$barcode.dispatch_qty",'>',"0");
        // if($filters['stock_type'] =='4') $stock = $stock->where("$barcode.Transaction_Type",'=',"Rejection");
        // if($filters['stock_type'] =='5') $stock = $stock->where("$barcode.status",'=',"5");

        $stock = $stock->groupBy("$branch.id")
            ->groupBy("$barcode.location_id")
            ->groupBy("$bin.id")
            ->groupBy("$item.id");
        if ($filters['stock_type'] == '4') $stock = $stock->groupBy("$barcode.transaction_id");

        return $stock->get();
    }
    public static function stockreportdetailed($filters = array())
    {
        $barcode = (new Barcode)->getTable();
        $item = (new Item)->getTable();
        $uom = (new Uom)->getTable();
        $location = (new Location)->getTable();
        $bin = (new Bin)->getTable();
        $branch = (new Branch)->getTable();
        $grn = (new Grn)->getTable();
        $grn_sub = (new Grn_sub)->getTable();
        $category = (new Category)->getTable();
        $sub_category = (new SubCategory)->getTable();
        $storage_scan = (new StorageScan)->getTable();

        $stock = Barcode::select(
            "$storage_scan.scan_time",
            "$category.category_name",
            "$sub_category.sub_category_name",
            "$item.part_no",
            "$item.item_name",
            "$item.item_type",
            "$item.id",
            "$item.type_of_packing",
            "$item.hsn_code",
            "$item.spq_quantity",
            "$uom.uom_name",
            "$branch.name as branch_name",
            "$branch.id as branch_id",
            "$location.Loc_Name",
            "$location.NAV_loc_code",
            "$bin.Bin_Name",
            "$bin.id",
            "$barcode.net_weight",
            "$barcode.serial_no",
            "$barcode.expiry_date",
            "$barcode.location_id",
            "$barcode.batch_number",
            "$barcode.manf_date",
            "$barcode.qc_approval_status",
            "$barcode.status",
            "$barcode.spq_qty",
            "$barcode.price",
            "$barcode.total_price",
            DB::raw('IFNULL(grn_branches.name,production_branch.name) GRN_Branch_Name'),
            DB::raw('IFNULL(grn_locations.Loc_Name,production_locations.Loc_Name) GRN_Loc_Name'),
            DB::raw('DATEDIFF(barcodes.expiry_date,CURDATE()) bb_diff')
        );
        // if($filters['stock_type'] =='4') $stock.="$storage_scan.scan_time";
        // else{ $stock.="$storage_scan.scan_time";}

        // if($filters['stock_type'] =='1') $stock.= " ,SUM($barcode.net_weight) weight";
        // if($filters['stock_type'] =='2') $stock.= " ,SUM($barcode.wip_qty) weight";
        // if($filters['stock_type'] =='3') $stock.= " ,SUM($barcode.dispatch_qty * $item.spq_qty ) weight";
        // if($filters['stock_type'] =='4') $stock.= " ,SUM($barcode.net_weight) weight";
        // if($filters['stock_type'] =='5') $stock.= " ,SUM($barcode.grn_net_weight) weight";

        $stock = $stock->leftjoin("$item", "$item.id", "=", "$barcode.item_id")
            ->leftjoin("$branch", "$branch.id", "=", "$barcode.branch_id")
            ->leftjoin("$location", "$location.id", "=", "$barcode.location_id")
            ->leftjoin("$bin", "$bin.id", "=", "$barcode.bin_id")
            ->leftjoin("$uom", "$uom.id", "=", "$item.uom_id")
            ->leftjoin("$category", "$category.id", "=", "$item.category_id")
            ->leftjoin("$storage_scan", "$storage_scan.barcode", "=", "$barcode.serial_no")
            ->leftjoin("$sub_category", "$sub_category.id", "=", "$item.sub_category_id")
            ->leftjoin("branches  as grn_branches", "grn_branches.id", "barcodes.branch_id")
            ->leftjoin("branches  as production_branch", "production_branch.id", "barcodes.branch_id")
            ->leftjoin("locations as grn_locations", "grn_locations.id", "barcodes.location_id")
            ->leftjoin("locations as production_locations", "production_locations.id", "barcodes.location_id");
        // ->leftjoin(DB::raw('branches grn_branches on grn_branches.id = barcodes.branch_id'))
        // ->leftjoin(DB::raw('branches production_branch on production_branch.id = barcodes.branch_id'))
        // ->leftjoin(DB::raw('locations grn_locations on grn_locations.id = barcodes.location_id'))
        // ->leftjoin(DB::raw('locations production_locations on production_locations.id = barcodes.location_id'));

        if ($filters['fromdate']) $stock = $stock->whereDate("$barcode.barcode_time", '>=', date($filters['fromdate']));
        if ($filters['todate']) $stock = $stock->whereDate("$barcode.barcode_time", '<=', date($filters['todate']));
        if ($filters['frombarcode']) $stock = $stock->where("$barcode.serial_no", '>=', $filters['frombarcode']);
        if ($filters['tobarcode']) $stock = $stock->where("$barcode.serial_no", '<=', $filters['tobarcode']);
        if ($filters['category_id']) $stock = $stock->where("$item.category_id", '=', $filters['category_id']);
        if ($filters['sub_category_id']) $stock = $stock->where("$item.sub_category_id", '=', $filters['sub_category_id']);
        if ($filters['item_id']) $stock = $stock->where("$item.id", '=', $filters['item_id']);
        if ($filters['branch_id']) $stock = $stock->where("$branch.id", '=', $filters['branch_id']);
        if ($filters['location_id']) $stock = $stock->where("$barcode.location_id", '=', $filters['location_id']);
        if ($filters['grn_type']) $stock = $stock->where("$item.item_type", '=', $filters['grn_type']);
        // if($filters['qc_type']) $stock = $stock->where("$barcode.qc_approval_status",'=',$filters['qc_type']);
        if ($filters['bin_id']) $stock = $stock->where("$bin.id", '=', $filters['bin_id']);
        if ($filters['stock_type']) $stock = $stock->where("$barcode.status", '=', $filters['stock_type']);
        if ($filters['transaction_id']) $stock = $stock->where("$barcode.transaction_id", '=', $filters['transaction_id']);
        if ($filters['batch_number']) $stock = $stock->where("$barcode.batch_number", '=', $filters['batch_number']);

        // if($filters['batch']) $stock = $stock->where("$barcode.barcode_time",'>=',$filters['batch']);

        // if($filters['stock_type'] =='1') $stock = $stock->where("$barcode.net_weight",'>',"0")->where("$barcode.status",'!=',"8");
        // if($filters['stock_type'] =='2') $stock = $stock->where("$barcode.wip_qty",'>',"0");
        // if($filters['stock_type'] =='3') $stock = $stock->where("$barcode.dispatch_qty",'>',"0");
        // if($filters['stock_type'] =='4') $stock = $stock->where("$barcode.Transaction_Type",'=',"Rejection");
        // if($filters['stock_type'] =='5') $stock = $stock->where("$barcode.status",'=',"5");

        $stock = $stock->groupBy("$barcode.serial_no");
        if ($filters['stock_type'] == '4') $stock = $stock->groupBy("$barcode.transaction_id");

        return $stock->get();
    }
    public static function stockreportexpirewise($filters = array())
    {
        $barcode = (new Barcode)->getTable();
        $item = (new Item)->getTable();
        $uom = (new Uom)->getTable();
        $location = (new Location)->getTable();
        $bin = (new Bin)->getTable();
        $branch = (new Branch)->getTable();
        $grn = (new Grn)->getTable();
        $grn_sub = (new Grn_sub)->getTable();
        $category = (new Category)->getTable();
        $sub_category = (new SubCategory)->getTable();
        $storage_scan = (new StorageScan)->getTable();

        $stock = Barcode::select(
            "$item.item_name",
            "$item.part_no",
            DB::raw('COUNT(CASE WHEN expiry_date < NOW() THEN 1 END) AS already_expired'),
            DB::raw('COUNT(CASE WHEN expiry_date >= NOW() AND expiry_date <= DATE_ADD(NOW(), INTERVAL 1 MONTH) THEN 1 END) AS expiring_soon'),
            DB::raw('COUNT(CASE WHEN expiry_date > DATE_ADD(NOW(), INTERVAL 1 MONTH) AND expiry_date <= DATE_ADD(NOW(), INTERVAL 6 MONTH) THEN 1 END) AS expiring_in_1_to_6_months'),
            DB::raw('COUNT(CASE WHEN expiry_date > DATE_ADD(NOW(), INTERVAL 6 MONTH) AND expiry_date <= DATE_ADD(NOW(), INTERVAL 1 YEAR) THEN 1 END) AS expiring_in_6_to_12_months'),
            DB::raw('COUNT(CASE WHEN expiry_date > DATE_ADD(NOW(), INTERVAL 1 YEAR) THEN 1 END) AS expiring_more_than_1_year')
        )->leftjoin("$item", "$item.id", "=", "$barcode.item_id")
            ->leftjoin("$branch", "$branch.id", "=", "$barcode.branch_id")
            ->leftjoin("$bin", "$bin.id", "=", "$barcode.bin_id");

        if ($filters['fromdate']) $stock = $stock->whereDate("$barcode.barcode_time", '>=', date($filters['fromdate']));
        if ($filters['todate']) $stock = $stock->whereDate("$barcode.barcode_time", '<=', date($filters['todate']));
        if ($filters['frombarcode']) $stock = $stock->where("$barcode.serial_no", '>=', $filters['frombarcode']);
        if ($filters['tobarcode']) $stock = $stock->where("$barcode.serial_no", '<=', $filters['tobarcode']);
        if ($filters['category_id']) $stock = $stock->where("$item.category_id", '=', $filters['category_id']);
        if ($filters['sub_category_id']) $stock = $stock->where("$item.sub_category_id", '=', $filters['sub_category_id']);
        if ($filters['item_id']) $stock = $stock->where("$item.id", '=', $filters['item_id']);
        if ($filters['branch_id']) $stock = $stock->where("$branch.id", '=', $filters['branch_id']);
        if ($filters['location_id']) $stock = $stock->where("$barcode.location_id", '=', $filters['location_id']);
        if ($filters['grn_type']) $stock = $stock->where("$item.item_type", '=', $filters['grn_type']);
        if ($filters['bin_id']) $stock = $stock->where("$bin.id", '=', $filters['bin_id']);
        if ($filters['stock_type']) $stock = $stock->where("$barcode.status", '=', $filters['stock_type']);
        if ($filters['transaction_id']) $stock = $stock->where("$barcode.transaction_id", '=', $filters['transaction_id']);
        if ($filters['batch_number']) $stock = $stock->where("$barcode.batch_number", '=', $filters['batch_number']);
        $stock = $stock->groupBy("item_name");
        return $stock->get();
    }

    public static function getstockcount($filters = array())
    {
        $barcode = (new Barcode)->getTable();
        $branchtable  = (new Branch)->getTable();

        $items = DB::table($barcode)
            ->join($branchtable, "$branchtable.id", "=", "$barcode.branch_id")
            ->where('barcodes.status', '=', '1');
        if ($filters['branch_id'] != 0) $items = $items->where("$barcode.branch_id", "=", $filters['branch_id']);
        return  $items = $items->count();
    }

    public static function getqcpendingcount($filters = array())
    {
        $barcode = (new Barcode)->getTable();
        $branchtable  = (new Branch)->getTable();

        $items = DB::table($barcode)
            ->join($branchtable, "$branchtable.id", "=", "$barcode.branch_id")
            ->where('barcodes.qc_approval_status', '=', '0');
        if ($filters['branch_id'] != 0) $items = $items->where("$barcode.branch_id", "=", $filters['branch_id']);
        return  $items = $items->count();
    }

    public static function getsalescount($filters = array())
    {
        $barcode = (new Barcode)->getTable();
        $branchtable  = (new Branch)->getTable();

        $items = DB::table($barcode)
            ->join($branchtable, "$branchtable.id", "=", "$barcode.branch_id")
            ->where('barcodes.status', '=', '2');
        if ($filters['branch_id'] != 0) $items = $items->where("$barcode.branch_id", "=", $filters['branch_id']);
        return  $items = $items->count();
    }

    public static function gettransfercount($filters = array())
    {
        $barcode = (new Barcode)->getTable();
        $branchtable  = (new Branch)->getTable();
        $returntable  = (new TransferScan)->getTable();


        $items = DB::table($returntable)
            ->join($barcode, "$barcode.serial_no", "=", "$returntable.serial_no")
            ->join($branchtable, "$branchtable.id", "=", "$barcode.branch_id");
        if ($filters['branch_id'] != 0) $items = $items->where("$barcode.branch_id", "=", $filters['branch_id']);
        return  $items = $items->count();
    }


    public static function getstockcountlessthan30($filters = array())
    {
        $barcode = (new Barcode)->getTable();
        $branchtable  = (new Branch)->getTable();

        $items = DB::table($barcode)
            ->join($branchtable, "$branchtable.id", "=", "$barcode.branch_id")
            ->where('barcodes.status', '=', '1')
            ->where(DB::raw('DATEDIFF(barcodes.expiry_date,CURDATE())'), '<', '30')
            ->where(DB::raw('DATEDIFF(barcodes.expiry_date,CURDATE())'), '>', '0');
        if ($filters['branch_id'] != 0) $items = $items->where("$barcode.branch_id", "=", $filters['branch_id']);
        return  $items = $items->count();
    }
    public static function getstockcountexpired($filters = array())
    {
        $barcode = (new Barcode)->getTable();
        $branchtable  = (new Branch)->getTable();

        $items = DB::table($barcode)
            ->join($branchtable, "$branchtable.id", "=", "$barcode.branch_id")
            ->where('barcodes.status', '=', '1')
            ->where(DB::raw('DATEDIFF(barcodes.expiry_date,CURDATE())'), '<=', '0');
        if ($filters['branch_id'] != 0) $items = $items->where("$barcode.branch_id", "=", $filters['branch_id']);
        return  $items = $items->count();
    }

    public static function stock31_60($filters = array())
    {
        $barcode = (new Barcode)->getTable();
        $branchtable  = (new Branch)->getTable();

        $items = DB::table($barcode)
            ->join($branchtable, "$branchtable.id", "=", "$barcode.branch_id")
            ->where('barcodes.status', '=', '1')
            ->where(DB::raw('DATEDIFF(barcodes.expiry_date,CURDATE())'), '>', '31')
            ->where(DB::raw('DATEDIFF(barcodes.expiry_date,CURDATE())'), '<', '60');
        if ($filters['branch_id'] != 0) $items = $items->where("$barcode.branch_id", "=", $filters['branch_id']);
        return  $items = $items->count();
    }
    public static function stock61_90($filters = array())
    {
        $barcode = (new Barcode)->getTable();
        $branchtable  = (new Branch)->getTable();

        $items = DB::table($barcode)
            ->join($branchtable, "$branchtable.id", "=", "$barcode.branch_id")
            ->where('barcodes.status', '=', '1')
            ->where(DB::raw('DATEDIFF(barcodes.expiry_date,CURDATE())'), '>', '61')
            ->where(DB::raw('DATEDIFF(barcodes.expiry_date,CURDATE())'), '<', '90');
        if ($filters['branch_id'] != 0) $items = $items->where("$barcode.branch_id", "=", $filters['branch_id']);
        return  $items = $items->count();
    }
    public static function stockpreater90($filters = array())
    {
        $barcode = (new Barcode)->getTable();
        $branchtable  = (new Branch)->getTable();

        $items = DB::table($barcode)
            ->join($branchtable, "$branchtable.id", "=", "$barcode.branch_id")
            ->where('barcodes.status', '=', '1')
            ->where(DB::raw('DATEDIFF(barcodes.expiry_date,CURDATE())'), '>', '90');
        if ($filters['branch_id'] != 0) $items = $items->where("$barcode.branch_id", "=", $filters['branch_id']);
        return  $items = $items->count();
    }

    public static function fgcount($filters = array())
    {
        $barcode = (new Barcode)->getTable();
        $branchtable  = (new Branch)->getTable();
        $item  = (new Item)->getTable();

        $items = DB::table($barcode)
            ->join($branchtable, "$branchtable.id", "=", "$barcode.branch_id")
            ->join($item, "$item.id", "=", "$barcode.item_id")
            ->where('barcodes.status', '=', '1')
            ->where('items.item_type', '=', 'FG')
            ->where(DB::raw('DATEDIFF(barcodes.expiry_date,CURDATE())'), '>', '0');
        if ($filters['branch_id'] != 0) $items = $items->where("$barcode.branch_id", "=", $filters['branch_id']);
        return  $items = $items->count();
    }
    public static function rmcount($filters = array())
    {
        $barcode = (new Barcode)->getTable();
        $branchtable  = (new Branch)->getTable();
        $item  = (new Item)->getTable();

        $items = DB::table($barcode)
            ->join($branchtable, "$branchtable.id", "=", "$barcode.branch_id")
            ->join($item, "$item.id", "=", "$barcode.item_id")
            ->where('barcodes.status', '=', '1')
            ->where('items.item_type', '=', 'RM')
            ->where(DB::raw('DATEDIFF(barcodes.expiry_date,CURDATE())'), '>', '0');
        if ($filters['branch_id'] != 0) $items = $items->where("$barcode.branch_id", "=", $filters['branch_id']);
        return  $items = $items->count();
    }

    public static function historyreport($filters = array())
    {
        $barcode = (new Barcode)->getTable();
        $item = (new Item)->getTable();
        $uom = (new Uom)->getTable();
        $location = (new Location)->getTable();
        $bin = (new Bin)->getTable();
        $branch = (new Branch)->getTable();
        $grn = (new Grn)->getTable();
        $grn_sub = (new Grn_sub)->getTable();
        $category = (new Category)->getTable();
        $sub_category = (new SubCategory)->getTable();
        $storage_scans = (new StorageScan)->getTable();
        $vendor = (new Vendor)->getTable();
        $users = (new User)->getTable();
        $rejectionscan = (new RejectionScan)->getTable();








        $stock = Barcode::select(
            "$category.category_name",
            "$sub_category.sub_category_name",
            "$item.part_no",
            "$item.item_name",
            "$item.item_type",
            "$item.id",
            "$item.type_of_packing",
            "$item.hsn_code",
            "$item.spq_quantity",
            "$uom.uom_name",
            "$branch.name as branch_name",
            "$branch.id as branch_id",
            "$location.Loc_Name",
            "$location.NAV_loc_code",
            "$bin.bin_code",
            "$bin.Bin_Name",
            "$bin.id",
            "$barcode.net_weight",
            "$barcode.serial_no",
            "$barcode.expiry_date",
            "$barcode.location_id",
            "$barcode.batch_number",
            "$barcode.manf_date",
            "$barcode.qc_approval_status",
            "$barcode.status",
            "$barcode.spq_qty"
        );


        $stock = $stock->leftjoin("$item", "$item.id", "=", "$barcode.item_id")
            ->leftjoin("$branch", "$branch.id", "=", "$barcode.branch_id")
            ->leftjoin("$location", "$location.id", "=", "$barcode.location_id")
            ->leftjoin("$bin", "$bin.id", "=", "$barcode.bin_id")
            ->leftjoin("$uom", "$uom.id", "=", "$item.uom_id")
            ->leftjoin("$category", "$category.id", "=", "$item.category_id")
            ->leftjoin("$sub_category", "$sub_category.id", "=", "$item.sub_category_id");



        if ($filters['barcode']) $stock = $stock->where("$barcode.serial_no", '=', $filters['barcode']);


        /*GRN Details*/
        $grn_det = Barcode::select(
            "$grn.grn_no",
            "$grn.po_no",
            "$grn.invoice_num",
            "$grn.invoice_date",
            "$grn.grn_time",
            "$item.part_no",
            "$item.item_name",
            "$vendor.Vendor_Name",
            "$barcode.batch_number",
            "$barcode.manf_date",
            "$barcode.grn_net_weight",
            "$location.Loc_Name",
            "$location.NAV_loc_code",
            "$uom.uom_name",
            "$branch.name as branch_name",
            "$grn.status"
        );
        $grn_det = $grn_det->leftjoin("$item", "$item.id", "=", "$barcode.item_id")
            ->leftjoin("$grn", "$grn.id", "=", "$barcode.transaction_id")
            ->leftjoin("$branch", "$branch.id", "=", "$barcode.branch_id")
            ->leftjoin("$location", "$location.id", "=", "$grn.location_id")
            ->leftjoin("$bin", "$bin.id", "=", "$barcode.bin_id")
            ->leftjoin("$uom", "$uom.id", "=", "$item.uom_id")
            ->leftjoin("$category", "$category.id", "=", "$item.category_id")
            ->leftjoin("$sub_category", "$sub_category.id", "=", "$item.sub_category_id")
            ->leftjoin("$vendor", "$vendor.id", "=", "$grn.vendor_id");

        if ($filters['barcode']) $grn_det = $grn_det->where("$barcode.serial_no", '=', $filters['barcode']);
        $grn_det = $grn_det->groupBy("$barcode.serial_no");

        /*storage scan Details*/
        $storage_scan = Barcode::select("$item.part_no", "$item.item_name", "$location.Loc_Name", "$location.NAV_loc_code", "$bin.Bin_Name", "$bin.id", "$barcode.net_weight", "$barcode.serial_no", "$barcode.expiry_date", "$barcode.location_id", "$barcode.batch_number", "$barcode.manf_date", "$storage_scans.scanned_qty", "$storage_scans.scan_time", "$uom.uom_name", "$users.name as user_name");
        $storage_scan = $storage_scan->join("$item", "$item.id", "=", "$barcode.item_id")
            ->join("$storage_scans", "$storage_scans.barcode", "=", "$barcode.serial_no")
            ->join("$location", "$location.id", "=", "$barcode.location_id")
            ->join("$bin", "$bin.id", "=", "$barcode.bin_id")
            ->join("$uom", "$uom.id", "=", "$item.uom_id")
            ->join("$category", "$category.id", "=", "$item.category_id")
            ->join("$sub_category", "$sub_category.id", "=", "$item.sub_category_id")
            ->join("$users", "$users.id", "=", "$storage_scans.user_id");


        if ($filters['barcode']) $storage_scan = $storage_scan->where("$barcode.serial_no", '=', $filters['barcode']);
        $storage_scan = $storage_scan->groupBy("$barcode.serial_no");



        /*rejection scan Details*/
        $rejection_s = Barcode::select("$item.part_no", "$item.item_name", "$location.Loc_Name", "$location.NAV_loc_code", "$bin.Bin_Name", "$bin.id", "$barcode.net_weight", "$barcode.serial_no", "$barcode.expiry_date", "$barcode.location_id", "$barcode.batch_number", "$barcode.manf_date", "$rejectionscan.barcode as old_barcode", "$rejectionscan.rejected_qty", "$rejectionscan.scan_time", "$uom.uom_name", "$users.name as user_name");
        $rejection_s = $rejection_s->join("$item", "$item.id", "=", "$barcode.item_id")
            ->join("$rejectionscan", "$rejectionscan.barcode", "=", "$barcode.serial_no")
            ->join("$location", "$location.id", "=", "$barcode.location_id")
            ->join("$bin", "$bin.id", "=", "$barcode.bin_id")
            ->join("$uom", "$uom.id", "=", "$item.uom_id")
            ->join("$category", "$category.id", "=", "$item.category_id")
            ->join("$sub_category", "$sub_category.id", "=", "$item.sub_category_id")
            ->join("$users", "$users.id", "=", "$rejectionscan.user_id")
            ->where('barcodes.status', '=', '6');

        if ($filters['barcode']) $rejection_s = $rejection_s->where("$barcode.serial_no", '=', $filters['barcode']);
        $rejection_s = $rejection_s->groupBy("$barcode.serial_no");


        /*bin to bin transfer*/
        $bin_to_bin_t = Barcode::select("$item.part_no", "$item.item_name", "bins.Bin_Name as frombin", "a.Bin_Name as tobin", "barcodes.net_weight", "bin_to_bin_transfers.transfer_at", "bin_to_bin_transfers.reason", "bin_to_bin_transfers.transfer_no", "users.name as username");
        $bin_to_bin_t = $bin_to_bin_t->join("$item", "$item.id", "=", "$barcode.item_id")
            ->join("bin_to_bin_transfer_subs", "bin_to_bin_transfer_subs.serial_no", "=", "$barcode.serial_no")
            ->join("bin_to_bin_transfers", "bin_to_bin_transfers.id", "=", "bin_to_bin_transfer_subs.transfer_id")
            ->join("$location", "$location.id", "=", "$barcode.location_id")
            ->join("bins", "bins.id", "=", "bin_to_bin_transfer_subs.from_bin")
            ->join("bins as a", "a.id", "=", "bin_to_bin_transfer_subs.to_bin")
            ->join("$users", "$users.id", "=", "bin_to_bin_transfers.transfer_by");

        if ($filters['barcode']) $bin_to_bin_t = $bin_to_bin_t->where("$barcode.serial_no", '=', $filters['barcode']);
        $bin_to_bin_t = $bin_to_bin_t->groupBy("$barcode.serial_no");



        /*Production Storage Scan Details*/

        $production_S = Barcode::select("$item.part_no", "$item.item_name", "$location.Loc_Name", "$location.NAV_loc_code", "$bin.Bin_Name", "$bin.id", "$barcode.serial_no", "$barcode.expiry_date", "$barcode.location_id", "$barcode.batch_number", "$barcode.manf_date", "production_scans.qty", "production_scans.scan_time", "$uom.uom_name", "$users.name as user_name", "production_material_issues.issue_no");
        $production_S = $production_S->join("$item", "$item.id", "=", "$barcode.item_id")
            ->join("production_scans", "production_scans.barcode", "=", "$barcode.serial_no")
            ->join("production_material_issues", "production_material_issues.id", "=", "production_scans.issue_id")
            ->join("$location", "$location.id", "=", "$barcode.location_id")
            ->join("$bin", "$bin.id", "=", "$barcode.bin_id")
            ->join("$uom", "$uom.id", "=", "$item.uom_id")
            ->join("$users", "$users.id", "=", "production_scans.user_id");

        if ($filters['barcode']) $production_S = $production_S->where("$barcode.serial_no", '=', $filters['barcode']);
        $production_S = $production_S->groupBy("$barcode.serial_no");

        // dd($production_S->get());


        /*Dispatch Details*/


        $dispatch_S = Barcode::select("$item.part_no", "$item.item_name", "$barcode.serial_no", "$barcode.expiry_date", "$barcode.batch_number", "$barcode.manf_date", "dispatch_scans.scanned_qty", "dispatch_scans.scan_time", "$uom.uom_name", "$users.name as user_name", "dispatch_entries.dispatch_date", "dispatch_entries.dispatch_no", "dispatch_orders.order_no", "customers.customer_code", "customers.customer_name", "dispatch_entries.dispatch_confirm_status");
        $dispatch_S = $dispatch_S->join("$item", "$item.id", "=", "$barcode.item_id")
            ->join("dispatch_scans", "dispatch_scans.barcode", "=", "$barcode.serial_no")
            ->join("dispatch_entries", "dispatch_entries.id", "=", "dispatch_scans.dispatch_id")
            ->join("dispatch_orders", "dispatch_orders.id", "=", "dispatch_entries.order_id")
            ->join("customers", "customers.id", "=", "dispatch_entries.disatched_to")
            ->join("$uom", "$uom.id", "=", "$item.uom_id")
            ->join("$users", "$users.id", "=", "dispatch_scans.user_id");

        if ($filters['barcode']) $dispatch_S = $dispatch_S->where("$barcode.serial_no", '=', $filters['barcode']);
        $dispatch_S = $dispatch_S->groupBy("$barcode.serial_no");


        /*Sales Return Details*/

        $dispatch_return_S = Barcode::select("$item.part_no", "$item.item_name", "$barcode.serial_no", "$barcode.expiry_date", "$barcode.batch_number", "$barcode.manf_date", "sales_return_scans.spq_qty", "sales_return_scans.scan_time", "$uom.uom_name", "$users.name as user_name", "dispatch_entries.dispatch_date", "dispatch_entries.dispatch_no", "sales_return_scans.sales_return_no", "customers.customer_code", "customers.customer_name", "dispatch_entries.dispatch_confirm_status", "$bin.Bin_Name");
        $dispatch_return_S = $dispatch_return_S->join("$item", "$item.id", "=", "$barcode.item_id")
            ->join("sales_return_scans", "sales_return_scans.barcode", "=", "$barcode.serial_no")
            ->join("dispatch_entries", "dispatch_entries.id", "=", "sales_return_scans.dispatch_id")
            ->join("dispatch_orders", "dispatch_orders.id", "=", "dispatch_entries.order_id")
            ->join("customers", "customers.id", "=", "dispatch_entries.disatched_to")
            ->join("$uom", "$uom.id", "=", "$item.uom_id")
            ->join("$bin", "$bin.id", "=", "sales_return_scans.bin_id")

            ->join("$users", "$users.id", "=", "sales_return_scans.user_id");

        if ($filters['barcode']) $dispatch_return_S = $dispatch_return_S->where("$barcode.serial_no", '=', $filters['barcode']);
        $dispatch_return_S = $dispatch_return_S->groupBy("$barcode.serial_no");

        /*Transfer Details*/


        $transfer_S = Barcode::select("$item.part_no", "$item.item_name", "$barcode.serial_no", "$barcode.expiry_date", "$barcode.batch_number", "$barcode.manf_date", "transfer_scans.picked_qty", "transfer_scans.scan_time", "$uom.uom_name", "$users.name as user_name", "transfer_entries.transfer_date", "transfer_entries.transfer_no", "transfer_entries.transfer_confirm_status", "dispatch_orders.order_no", "locations.Loc_Name as from_location", "a.Loc_Name as to_location");
        $transfer_S = $transfer_S->join("$item", "$item.id", "=", "$barcode.item_id")
            ->join("transfer_scans", "transfer_scans.serial_no", "=", "$barcode.serial_no")
            ->join("transfer_entries", "transfer_entries.id", "=", "transfer_scans.transfer_id")
            ->join("dispatch_orders", "dispatch_orders.id", "=", "transfer_entries.order_id")
            ->join("locations", "locations.id", "=", "transfer_entries.from_location")
            ->join("locations as a", "a.id", "=", "transfer_entries.to_location")
            ->join("$uom", "$uom.id", "=", "$item.uom_id")
            ->join("$users", "$users.id", "=", "transfer_scans.user_id");

        if ($filters['barcode']) $transfer_S = $transfer_S->where("$barcode.serial_no", '=', $filters['barcode']);
        $transfer_S = $transfer_S->groupBy("$barcode.serial_no");



        /*Transfer Receipt Details*/

        $transfer_receipt_S = Barcode::select("$item.part_no", "$item.item_name", "$barcode.serial_no", "$barcode.expiry_date", "$barcode.batch_number", "$barcode.manf_date", "receipt_scans.scanned_qty", "receipt_scans.scan_time", "$uom.uom_name", "$users.name as user_name", "transfer_receipts.entry_tinme", "transfer_receipts.transfer_no", "transfer_receipts.status", "transfer_receipts.receipt_no", "locations.Loc_Name as from_location", "a.Loc_Name as to_location", "receipt_scans.scan_time");
        $transfer_receipt_S = $transfer_receipt_S->join("$item", "$item.id", "=", "$barcode.item_id")
            ->join("receipt_scans", "receipt_scans.serial_no", "=", "$barcode.serial_no")
            ->join("transfer_receipts", "transfer_receipts.id", "=", "receipt_scans.receipt_id")
            ->join("locations", "locations.id", "=", "transfer_receipts.from_location")
            ->join("locations as a", "a.id", "=", "transfer_receipts.to_location")
            ->join("$uom", "$uom.id", "=", "$item.uom_id")
            ->join("$users", "$users.id", "=", "receipt_scans.user_id");

        if ($filters['barcode']) $transfer_receipt_S = $transfer_receipt_S->where("$barcode.serial_no", '=', $filters['barcode']);
        $transfer_receipt_S = $transfer_receipt_S->groupBy("$barcode.serial_no");

        /*Stock Out Details*/


        $Stock_out = Barcode::select("reasons.reason", "stock_outs.created_at", "$uom.uom_name", "$users.name as user_name", "$barcode.net_weight");
        $Stock_out = $Stock_out->join("$item", "$item.id", "=", "$barcode.item_id")
            ->join("stock_outs", "stock_outs.serial_no", "=", "$barcode.serial_no")
            ->join("reasons", "reasons.id", "=", "stock_outs.reason_id")
            ->join("$uom", "$uom.id", "=", "$item.uom_id")
            ->join("$users", "$users.id", "=", "stock_outs.user_id");

        if ($filters['barcode']) $Stock_out = $Stock_out->where("$barcode.serial_no", '=', $filters['barcode']);
        $Stock_out = $Stock_out->groupBy("$barcode.serial_no");
        // dd($Stock_out->get());

        $arr = [];

        $arr['item_details'] = $stock->first();
        $arr['grn_details'] = $grn_det->first();
        $arr['storage_details'] = $storage_scan->first();
        $arr['rejection_details'] = $rejection_s->first();
        $arr['bin_to_bin_details'] = $bin_to_bin_t->first();
        $arr['production_storage_details'] = $production_S->first();
        $arr['dispatch_details'] = $dispatch_S->first();
        $arr['sales_return_details'] = $dispatch_return_S->first();
        $arr['transfer_details'] = $transfer_S->first();
        $arr['transfer_receipt_details'] = $transfer_receipt_S->first();
        $arr['stock_out_details'] = $Stock_out->first();



        // dd($arr);

        //     dd($grn_det->get() . $stock->get());
        return $arr;
    }
}
