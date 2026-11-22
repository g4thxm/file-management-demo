<?php

namespace App\Http\Controllers;

use App\Models\ActivityEntry;
use App\Models\Barcode;
use App\Models\Bin;
use App\Models\BinToBinTransfer;
use App\Models\BinToBinTransferSub;
use App\Models\BomSub;
use App\Models\Product;
use App\Models\Configuration;
use App\Models\Log;
use App\Models\BinMapping;
use App\Models\Branch;
use App\Models\Category;
use App\Models\ContractCreate;
use App\Models\Customer;
use App\Models\DispatchEntry;
use App\Models\DispatchOrder;
use App\Models\DispatchOrderSub;
use App\Models\DispatchPicklist;
use App\Models\Engs;
use App\Models\Grn;
use App\Models\Item;
use App\Models\Location;
use App\Models\Menu;
use App\Models\Permission;
use App\Models\Submenu;
use App\Models\User;
use App\Models\ProjectCreate;
use App\Models\ProjectTask;
use App\Models\SubCategory;
use App\Models\TransferentrySub;
use App\Models\TransferOrder;
use App\Models\TransferPicklist;
use App\Models\ProductionOrder;
use App\Models\ProductionPlan;
use App\Models\ProductionEntry;
use App\Models\OpeningStock;
use App\Models\Bom;
use App\Models\ProductionMaterialIssue;
use App\Models\ProductionPicklist;
use App\Models\TransferorderSub;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderSub;
use App\Models\Vendor;
use App\Models\WipFgMapping;
use App\Models\WipFgMappingSub;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AjaxController extends Controller
{

    public function darkmode($id)
    {
        // dd($id);
        $usr = User::find(Auth::user()->id);
        $usr->darkmode = $id;
        $usr->update();
        return redirect(route('config.index'));
    }

    public function getOrderNum(Request $request)
    {

        if ($request->type == 'Transfer') {

            // dd(1);
            $order_no = TransferOrder::nextNumber();
            // dd($order_no);

        }

        if ($request->type == 'Sales') {
            $order_no = DispatchOrder::nextNumber();
            // dd($order_no);
        }
        return response()->json($order_no);
    }
  
    public function userpermission()
    {
                                              
        $userid = auth()->id();
        $data = array();
        $permission = new Permission;
        $permission = $permission->where('user_id', $userid)->get();
        $data[] = [
            'menu' => $permission
        ];
        return response()->json($permission);
    }

    public function permissionmenu(Request $request)
    {
        // dd("bdks");
     
        $data = array();
        $menu = new Menu;
        $menu = $menu->select('id', 'title', 'link')->get()->sortBy('id');
        $submenu = new Submenu;
        $submenu = $submenu->select('id', 'title', 'menu_id')->get()->sortBy('id');
        $permission = new Permission;
        $permission = $permission->select('submenu_id')->where('user_id', $request->get('uid'))->get()->sortBy('submenu_id');

        //        dd($permission);
        $data[] = [
            'menu' => $menu,
            'submenu' => $submenu,
            'permission' => $permission
        ];
        // dd($data);
        return response()->json($data);
    }

    public function  activityentryclientlist(Request $request)
    {
        $project1 = new ProjectCreate;
        $project1 = $project1->where('client_id', $request->client_id)->where('project_status', '=', 1)->orderBy('project_name', 'ASC');
        $project1 = $project1->get();


        $data = array();

        foreach ($project1 as $projects1) {
            $data[] = [
                'projectname' => $projects1->project_name,
                'projectid' => $projects1->id
            ];
        }

        return response()->json($data);
    }

    public function  getprojectdetails(Request $request)
    {

        $data = array();
        $projecttask = new ProjectTask;
        $projecttask = $projecttask->select('id', 'start_date', 'targeted_date');
        $projecttask = $projecttask->where('id', $request->task_id);
        $projecttask = $projecttask->get();

        $hours = 0;
        $start_date = '';
        $target_date = '';
        foreach ($projecttask as $projecttasks) {
            $hours += ActivityEntry::where('task_id', $projecttasks->id)->where('status', 1)->sum('hours_spent');
            $start_date = $projecttasks->start_date;
            $target_date = $projecttasks->targeted_date;
        }



        $data[] = [
            'start_date' => $start_date,
            'target_date' => $target_date,
            'hours' => $hours
        ];
        return response()->json($data);
    }

    public function  activityentrytasklist(Request $request)
    {
        $project = new ProjectTask;
        $project = $project->where('project_id', $request->project_id)->where('status', '!=', 3)->orderBy('task', 'ASC');
        $project = $project->get();


        $data = array();

        foreach ($project as $projects) {
            $data[] = [
                'taskname' => $projects->task,
                'taskid' => $projects->id
            ];
        }

        return response()->json($data);
    }

    public function getcontractdata(Request $request)
    {

        $getdata = ContractCreate::where('deleted_at', NULL)->where('id', $request->contract_id)->select('contract_start_date', 'contract_end_date', 'contract_value')->first();
        return response()->json($getdata);
    }

    public function getCustomer(Request $request)
    {

        $project1 = new ContractCreate();
        $project1 = $project1->where('customer_id', $request->customer_name)->where('deleted_status', '0')->orderBy('customer_id', 'ASC');
        $project1 = $project1->get();
        $output = '<option value="">--Select--</option>';
        foreach ($project1 as $projects1) {
            $output .= '<option value="' . $projects1->id . '">' . $projects1->contract_id . '</option>';
        }
        return response()->json(['output' => $output]);
    }

    public function getdata(Request $request)
    {
        $data = array();
        $task = DB::table('contract_creates')->get();
        foreach ($task as $tasks) {
            $currentdate = date("Y-m-d");
            $total_minutes = 0;
            $enddate = date("Y-m-d", strtotime($tasks->contract_end_date));
            $diff =  strtotime($enddate) - strtotime($currentdate);
            $sum = (($diff / 86400));

            $expired = 0;

            if ($sum <= $enddate) {
                $expired;
            }
            if ($sum <= 30) {
                // $expired1;
                $expired1 = DB::table('contract_creates')
                    ->where('contract_end_date', '<=', $enddate)
                    ->where('status', '=', '0')
                    ->count();
            }

            if ($sum > 30 && $sum <= 60) {
                // $expired2;
                $expired2 = DB::table('contract_creates')
                    ->where('contract_end_date', '>', $currentdate)
                    ->where('contract_end_date', '<', $enddate)
                    ->where('status', '=', '0')
                    ->count();
            }

            if ($sum > 60) {
                // $expired3;
                $expired3 = DB::table('contract_creates')
                    ->where('contract_end_date', '>=', $enddate)
                    ->where('status', '=', '0')
                    ->count();
            }
        }
        $users = DB::table('users')->get();

        if ($users = $request->project_id) 
        {
            $works = DB::table('project_tasks')
                ->join('users', 'users.id', '=', 'project_tasks.user_id')
                //->select( 'project_id','user_id')
                ->where('project_tasks.user_id', $request->project_id)
                ->where('project_tasks.status', '=', '1')
                ->where('users.status', '=', '1')
                ->count();
            // dd($works);
            $hr = DB::table('activity_entries')
                ->join('users', 'users.id', '=', 'activity_entries.user_id')
                ->join('project_tasks', 'project_tasks.project_id', '=', 'activity_entries.user_id')
                // ->join('project_tasks','project_tasks.project_id','=','activity_entries.project_id')
                ->where('project_tasks.user_id', $request->project_id)
                // ->select( 'hours_spent') 
                ->where('project_tasks.status', '=', '1')
                ->where('users.status', '=', '1')
                // ->count();  
                ->sum('hours_spent');
            // dd($hr);
            $data[] = [
                'works' => $works,
                'hr' => $hr
            ];

            return response()->json($data);
        }
    }

    public function getsubcategory(Request $request)
    {
        $subcate = SubCategory::where('category_id', $request->category_id)->get();
        $data = "<select name='sub_category_id' class='form-control select2 control-label mandatory' style='width:100%' required='required'><option value=''>-------select--------</option>";
        foreach ($subcate as $sub) {
          $data .= "<option value='$sub->id'>$sub->sub_category_name</option>";
        }

        $data .= "<select>";
        return response()->json($data);

    }

    public function getUOM(Request $request)
    {
        $item_id = $request->item_id;
        $data = Item::leftjoin('uoms', 'uoms.id', '=', 'items.uom_id')->select('uoms.uom_name', 'items.item_name', 'items.part_no', 'items.spq_quantity')->where('items.id', $item_id)->first();
        // $uom=$data->uom_name;
        // dd($data);
        return response()->json($data);
    }


    public function filterItem(Request $request)
    {
        $item_type = $request->item_type;
        $result = "<select class='form-control select2 control-label mandatory' onchange='getUOM(this)' id='sub_item_id'><option value=''>----select----</option>";
        if ($item_type == 'RM' || $item_type == 'WIP') {
            $data = Item::select('id', 'item_name')->where('item_type', $item_type)->get();
            foreach ($data as $dat) {
                $result .= "<option value='$dat->id'>$dat->item_name</option>";
            }
        } else {
            $data = Item::select('id', 'item_name')->get();
            foreach ($data as $dat) {
                $result .= "<option value='$dat->id'>$dat->item_name</option>";
            }
        }
        $result .= "</select>";
        return response()->json($result);
    }
    public function getItemsName(Request $request)
    {
        $item_id = $request->item_id;           
        $data = Item::select('item_name')->where('id', $item_id)->first();
        //  $item_name=$data->item_name;
        return response()->json($item_id);
    }
    public function getEditBom(Request $request)
    {


        $bomsub = BomSub::leftjoin('items', 'items.id', 'bom_subs.item_id')->leftjoin('uoms', 'uoms.id', '=', 'items.uom_id')
            ->select(
                'bom_subs.id',
                'bom_subs.bom_id',
                'bom_subs.item_type',
                'bom_subs.component_type',
                'bom_subs.item_id',
                'bom_subs.quantity',
                'bom_subs.spq',
                'items.item_name',
                'uoms.uom_name'
            )
            ->where('bom_id', $request->bom_id)->get();

        return response()->json($bomsub);
    }

    public function getEditWipFgMapping(Request $request)
    {
        $wipfg = WipFgMappingSub::select(
            'wip_fg_mapping_sub.id',
            'wip_fg_mapping_sub.fg_mapping_id',
            'wip_fg_mapping.id',
            'wip_fg_mapping.item_id',
            'wip_fg_mapping_sub.wip_stage_id',
            'items.item_name',
            'wip.stage'
        )->leftJoin('wip_fg_mapping', 'wip_fg_mapping.id', '=', 'wip_fg_mapping_sub.fg_mapping_id')
            ->leftJoin('items', 'items.id', '=', 'wip_fg_mapping.item_id')
            ->leftJoin('wip', 'wip.id', '=', 'wip_fg_mapping_sub.wip_stage_id')

            ->where('fg_mapping_id', $request->fg_mapping_id)
            ->get();

        // dd($wipfg);
        return response()->json($wipfg);
    }

    public function getEditlog(Request $request)
    {

        $id = $request->log_id;
        $details = Log::find($id);
        $logId = $details->item_id;

        $log = Log::leftjoin('items', 'items.id', 'logs.item_id')
            ->select(
                'logs.id',
                'logs.item_id',
                'logs.description',
                'items.item_name',
            )
            ->where('item_id', $logId)->get();

        return response()->json($log);
    }
    public function getEditbinmapping(Request $request)
    {
        // dd($request);

        $id = $request->binmapping_id;
        $details = BinMapping::find($id);
        $binmappingId = $details->bin_id;

        $binmapping = BinMapping::leftJoin('bins', 'bins.id', '=', 'bin_mappings.bin_id')
            ->leftJoin('items', 'items.id', '=', 'bin_mappings.item_id')
            ->select(
                'bin_mappings.id',
                'bin_mappings.bin_id',
                'bin_mappings.item_id',
                'bins.Bin_Name',
                'items.item_name'
            )
            ->where('bin_id', $binmappingId)->get();
        return response()->json($binmapping);
    }

    public function filterItemGrn(Request $request)
    {
        $item_type = $request->item_type;

        $category_id = $request->category_id;
        $sub_category_id = $request->sub_category_id;

        $po_number = $request->po_number;
        $item_id = $request->item_id;
        $result = "<select class='form-control select2 control-label mandatory' onchange='getUOM(this)' id='sub_item_id'><option value=''>----select----</option>";

        if ($po_number) {
            // dd($po_number);

            $data = PurchaseOrder::select('items.id', 'items.item_name')
                ->join('purchase_order_subs', 'purchase_order_subs.purchase_order_id', '=', 'purchase_orders.id')
                ->join('items', 'items.id', '=', 'purchase_order_subs.item_id')
                ->where('purchase_order_subs.purchase_order_id', '=', $po_number)
                ->where('purchase_order_subs.status', '=', '0');
        } else {
            $data = Item::select('items.id', 'items.item_name');
        }

        // if ($item_type == 'RM' || $item_type == 'WIP' || $item_type == 'FG') {
        //     $data = $data->where('items.item_type', $item_type);

        //     if ($category_id != null) {
        //         $data = $data->where('items.category_id', $category_id);
        //     }
        //     if ($sub_category_id != null) {
        //         $data = $data->where('items.sub_category_id', $sub_category_id);
        //     }
        //     $data = $data->get();
        //     foreach ($data as $dat) {
        //         $result .= "<option value='$dat->id'>$dat->item_name</option>";
        //     }
        // } else {
        //     $data = $data->get();

        //     foreach ($data as $dat) {
        //         $result .= "<option value='$dat->id'>$dat->item_name</option>";
        //     }
        // }

        if ($item_type == 'RM' || $item_type == 'WIP' || $item_type == 'FG') {
            $data = $data->where('items.item_type', $item_type);
        }
        if ($category_id != null) {
            $data = $data->where('items.category_id', $category_id);
        }
        if ($sub_category_id != null) {
            $data = $data->where('items.sub_category_id', $sub_category_id);
        }
        $data = $data->get();
        foreach ($data as $dat) {
            $result .= "<option value='$dat->id'>$dat->item_name</option>";
        }

        $result .= "</select>";

        return response()->json($result);
    }

    public function getUOMgrn(Request $request)
    {
        $item_id = $request->item_id;
        $data = Item::leftjoin('uoms', 'uoms.id', '=', 'items.uom_id')->select('uoms.uom_name', 'items.item_name', 'items.spq_quantity', 'items.type_of_packing', 'items.spq_quantity')->where('items.id', $item_id)->first();
        // $uom=$data->uom_name;
        return response()->json($data);
    }


    public function filter_PoNumber(Request $request)
    {
        $vendor_id = $request->vendor_id;
        // dd($vendor_id);
        $result = "<select class='form-control select2 control-label mandatory'><option value=''>--select--</option>";
        $data = PurchaseOrder::where('vendor_id', $vendor_id)->get();
        foreach ($data as $dat) {
            $result .= "<option value='$dat->id'>$dat->purchase_no</option>";
        }
        // $result .= "</select>";

        return response()->json($result);
    }


    // public function filter_Catagory(Request $request)
    // {
    //     $category_id = $request->category_id;
    //     // dd($category_id);
    //     $result = "<select class='form-control select2 control-label mandatory'><option value=''>--select--</option>";
    //     $data = Item::where('category_id', $category_id)->get();
    //     foreach ($data as $dat) {
    //         $result .= "<option value='$dat->id'>$dat->item_name</option>";
    //     }
    //     // $result .= "</select>";

    //     return response()->json($result);
    // }






    public function getbin(Request $request)
    {

        $bin = new Bin;

        $bin = $bin->where('Bin_Name', $request->Bin_Name)->orderBy('Bin_Name', 'ASC');
        $bin = $bin->count();


        return response()->json($bin);
    }

    public function get_item_details(Request $request)
    {

        $grn_details = [];
        $item_details = "";

        if ($request->qc_type == 1) {
            $vendor_data = Grn::join('vendors', 'vendors.id', '=', 'grns.vendor_id')
                ->join('locations', 'locations.id', '=', 'grns.location_id')
                ->where('grns.id', $request->grn_id)
                ->get(['grns.invoice_num', 'grns.invoice_date', 'grns.invoice_num', 'locations.Loc_Name', 'locations.Prefix', 'vendors.Vendor_Name', 'vendors.vendor_code']);


            $grn_items = Grn::join('grn_subs', 'grn_subs.grn_id', '=', 'grns.id')
                ->join('items', 'items.id', '=', 'grn_subs.item_id')
                ->where('grns.id', $request->grn_id)
                ->get(['grns.*', 'grn_subs.*', 'items.item_name', 'items.part_no']);

            if (!$grn_items) {
                $grn_details = "<p class='text-danger text-center card-title'>no records found</p>";
            } else {

                // dd($grn_items);

                foreach ($vendor_data as $items) {
                    $indate =   date('d-m-Y', strtotime($items->invoice_date));
                    $grn_details = [
                        "Vendor_Name" => $items->Vendor_Name . '/' . $items->vendor_code,
                        "invoice_num" =>  $items->invoice_num,
                        "invoice_date" => $indate,

                    ];
                }


                $i = 0;
                foreach ($grn_items as $data) {
                    $i++;
                    $item_details .=  "<tr>
                <td align='left'> $i</td>                               
                <td align='left'> $data->item_name/$data->part_no <input type='hidden' class='form-control' id='item_id" . $i . "'  value='$data->item_id' name='item_id[]'></td> 
                <td align='left'> $data->batch_no <input type='hidden' class='form-control' id='batch_no" . $i . "' value='$data->batch_no' name='batch_no[]'></td> 
                <td align='left'> $data->qty <input type='hidden' class='form-control' id='qty" . $i . "' value='$data->qty' name='qty[]'></td> 
                <td align='left'> <input type='text' class='form-control' id='scan_qty" . $i . "' required='required'  name='scan_qty[]' onkeyup='qty_change(" . $i . ");'></td> 
                <td align='left'> <input type='text' class='form-control' id='reject_qty" . $i . "'  required='required' name='reject_qty[]' onkeyup='rjqty_change(" . $i . ");'></td> 
                </tr>";
                }

                $item_details .= " <tr style='display:none;'><td><input type='hidden' name='countvalue' id='countvalue' value='$i'/></td></tr>";
            }
        } else {

            $prod_items = ProductionEntry::join('items', 'items.id', '=', 'production_entries.item_id')
                ->where('production_entries.id', $request->grn_id)
                ->get(['production_entries.*', 'items.item_name', 'items.part_no']);

            if (!$prod_items) {
                $grn_details = "<p class='text-danger text-center card-title'>no records found</p>";
            } else {
                $i = 0;
                foreach ($prod_items as $data) {
                    $i++;
                    $item_details .=  "<tr>
                                <td align='left'> $i</td>                               
                                <td align='left'> $data->item_name/$data->part_no <input type='hidden' class='form-control' id='item_id" . $i . "'  value='$data->item_id' name='item_id[]'></td> 
                                <td align='left'> $data->batch_no <input type='hidden' class='form-control' id='batch_no" . $i . "' value='$data->batch_no' name='batch_no[]'></td> 
                                <td align='left'> $data->quantity <input type='hidden' class='form-control' id='qty" . $i . "' value='$data->quantity' name='qty[]'></td> 
                                <td align='left'> <input type='text' class='form-control' id='scan_qty" . $i . "' required='required'  name='scan_qty[]' onkeyup='qty_change(" . $i . ");'></td> 
                                <td align='left'> <input type='text' class='form-control' id='reject_qty" . $i . "'  required='required' name='reject_qty[]' onkeyup='rjqty_change(" . $i . ");'></td> 
                                </tr>";
                }

                $item_details .= " <tr style='display:none;'><td><input type='hidden' name='countvalue' id='countvalue' value='$i'/></td></tr>";
            }
        }
        return response()->json(['grn_details' => $grn_details, 'item_details' => $item_details, 'type' => $request->qc_type]);

        // dd($grn_items);

    }

    public function get_grn(Request $request)
    {

        $qc_type = $request->qc_type;
        $result = '<option value="">--select--</option>';



        if ($qc_type == 1) {
            $grn_no = Grn::select('id', 'grn_no')->where('qc_status', '0')->get();

            foreach ($grn_no  as $grn) {
                $result .= "<option value='$grn->id'>$grn->grn_no</option>";
            }
        }

        if ($qc_type == 2) {
            $prod_no = ProductionEntry::select('id', 'production_no')->where('qc_status', '0')->get();
            // dd($prod_no);
            foreach ($prod_no  as $prod) {
                $result .= "<option value='$prod->id'>$prod->production_no</option>";
            }
        }



        return response()->json($result);
    }

    public function getlocations(Request $request)
    {
        $userid = Auth::id();
        if ($userid == 1) {
            return response()->json($userid);
        } else {
            $loc = Auth::user();
            $locations = Location::where('id', $loc->location_id)->get();
            $data = "<option value=''>--select--</option>";
            foreach ($locations as $loc) {
                $data .= "<option value='$loc->id'>$loc->Loc_Name</option>";
            }
            return response()->json($data);
        }
    }

    public function getrjqty(Request $request)
    {
        $barcode = $request->barcode;

        $barcode_qty = Barcode::select('net_weight', 'spq_qty')->where('serial_no', $barcode)->first();

        $data = [
            'net_weight' => $barcode_qty->net_weight,
            'spq' => $barcode_qty->spq_qty
        ];



        return response()->json($data);
    }

    public function getbarcodeqty(Request $request)
    {
        $barcode = $request->barcode;

        $barcode_qty = Barcode::select('net_weight', 'spq_qty')->where('serial_no', $barcode)->first();

        $data = [
            'net_weight' => $barcode_qty->net_weight,
            'spq' => $barcode_qty->spq_qty
        ];



        return response()->json($data);
    }

    public function getbarcodeqty1(Request $request)
    {
        $barcode = $request->barcode;

        $barcode_qty = Barcode::select('grn_net_weight', 'grn_sqp_qty')->where('serial_no', $barcode)->first();

        $data = [
            'net_weight' => $barcode_qty->grn_net_weight,
            'spq' => $barcode_qty->grn_sqp_qty
        ];



        return response()->json($data);
    }

    public function getdispatch_details(Request $request)
    {
        // dd($request->all());

        $dispatch = $request->dispatch;

        $details = DispatchEntry::join('dispatch_orders', 'dispatch_orders.id', '=', 'dispatch_entries.order_id')
            ->join('customers', 'customers.id', '=', 'dispatch_orders.customer_id')
            ->where('dispatch_entries.dispatch_no', $dispatch)
            ->select('dispatch_orders.order_no', 'dispatch_entries.dispatch_date', 'customers.customer_name')->first();

        // dd($details);

        if ($details) {
            $data = [
                'order_no' => $details->order_no,
                'dispatch_date' => $details->dispatch_date,
                'customer_name' => $details->customer_name,
                'error' => 0,
            ];
        } else {
            $data = [
                'error' => 1,
            ];
        }





        return response()->json($data);
    }

    public function filterItem_report(Request $request)
    {
        $item_type = $request->item_type;
        $result = "<option value=''>----select----</option>";
        if ($item_type == 'RM' || $item_type == 'WIP' || $item_type == 'FG') {
            $data = Item::select('id', 'item_name')->where('item_type', $item_type);
        } else {
            $data = Item::select('id', 'item_name')->get();
            foreach ($data as $dat) {
                $result .= "<option value='$dat->id'>$dat->item_name</option>";
            }
        }

        return response()->json($result);
    }

    public function filtersubcategory(Request $request)
    {
        $cat_id = $request->cat_id;
        $result = "<option value=''>----select----</option>";
        $data = SubCategory::select('id', 'sub_category_name')->where('category_id', $cat_id)->get();
        foreach ($data as $dat) {
            $result .= "<option value='$dat->id'>$dat->sub_category_name</option>";
        }
        return response()->json($result);
    }

    public function getlocation(Request $request)
    {
        $br_id = $request->br_id;
        $result = "<option value=''>----select----</option>";
        $data = Location::select('id', 'Loc_Name')->where('branch_id', $br_id)->get();
        foreach ($data as $dat) {
            $result .= "<option value='$dat->id'>$dat->Loc_Name</option>";
        }
        return response()->json($result);
    }

    public function filterOrderEntry(Request $request)
    {
        $customer_id = $request->customer_id;
        $result = "<option value=''>--select--</option>";
        $data = DispatchOrder::select('id', 'order_no')->where('status', '0')->where('customer_id', $customer_id)->get();
        foreach ($data as $dat) {
            $result .= "<option value='$dat->id'>$dat->order_no</option>";
        }
        return response()->json($result);
    }

    public function get_available_item(Request $request)
    {
        $order_id = $request->order_id;
        $data = DispatchOrder::select('dispatch_order_subs.id as sub_id', 'dispatch_order_subs.item_id', 'dispatch_order_subs.order_qty', 'dispatch_order_subs.despatch_qty', 'items.item_name', 'items.spq_quantity', 'items.part_no', 'uoms.uom_name')
            ->leftjoin('dispatch_order_subs', 'dispatch_order_subs.order_id', '=', 'dispatch_orders.id')
            ->leftjoin('items', 'items.id', '=', 'dispatch_order_subs.item_id')
            ->leftjoin('uoms', 'uoms.id', '=', 'items.uom_id')
            ->where('dispatch_orders.id', $order_id)
            ->where('dispatch_order_subs.deleted_at', null)
            ->where('dispatch_order_subs.order_qty', '>', 'dispatch_order_subs.despatch_qty')
            ->get();
        $result = "";
        $i = 0;
        $ava = [];
        $datas = [];
        // dd(count($data));
        foreach ($data as $dat) {
            $getqty = Barcode::select(DB::raw('SUM(barcodes.net_weight)stockqty1'), DB::raw('SUM(barcodes.spq_qty)stockqty'), 'barcodes.expiry_date', 'barcodes.manf_date')
                ->leftjoin('locations', 'locations.id', '=', 'barcodes.location_id')
                ->where('barcodes.item_id', $dat->item_id)
                ->where("barcodes.qc_approval_status", DB::raw("'1'"))
                ->where(DB::raw('DATEDIFF(barcodes.expiry_date,CURDATE())'), '>', '0')
                ->whereNotNull('barcodes.spq_qty')
                ->get();

            $getserial = Barcode::select('barcodes.serial_no')
                ->leftjoin('locations', 'locations.id', '=', 'barcodes.location_id')
                ->where('barcodes.item_id', $dat->item_id)
                ->where("barcodes.qc_approval_status", DB::raw("'1'"))
                ->where(DB::raw('DATEDIFF(barcodes.expiry_date,CURDATE())'), '>', '0')
                ->whereNotNull('barcodes.spq_qty')
                ->get();

            $picklist_stockqty = 0;

            $transfer_picklist = TransferPicklist::select('serial_no')->where('status', '0')->get();
            foreach ($getserial as $srlno) {
                $check_picklist = DispatchPicklist::select(DB::raw('SUM(dispatch_picklists.sub_qty)stockqty'))->where('serial_no', $srlno->serial_no)->where('status', '0')->get();
                if ($check_picklist) {
                    foreach ($check_picklist as $pick) {
                        $picklist_stockqty = $picklist_stockqty + $pick->stockqty;
                    }
                } else {

                    $transfer_picklist = TransferPicklist::select(DB::raw('SUM(transfer_picklists.sub_qty)stockqty'))->where('serial_no', $srlno->serial_no)->where('status', '0')->get();
                    foreach ($transfer_picklist as $picl) {
                        $picklist_stockqty = $picklist_stockqty + $picl->stockqty;
                    }
                }
            }
            $avi_qty = 0;
            foreach ($getqty as $get) {
                $avi_qty = $avi_qty + $get->stockqty;
            }
            $actual_qty = $avi_qty;
            $avi_qty = $avi_qty - $picklist_stockqty;
            if ($avi_qty < 0) {
                $avi_qty = '0';
            }

            $picklistqty = DispatchPicklist::select(DB::raw('SUM(dispatch_picklists.sub_qty)picklistqty'))->where('order_id', $order_id)->where('item_id', $dat->item_id)->first();
            $ordrqty = DispatchOrderSub::select(DB::raw('SUM(dispatch_order_subs.order_qty)order_qty'))->where('order_id', $order_id)->where('item_id', $dat->item_id)->first();

            $datas[$i]['item_id'] = $dat->item_id;
            $datas[$i]['item_name'] = $dat->item_name;
            $datas[$i]['order_qty'] = $dat->order_qty;
            $datas[$i]['avi_qty'] = $avi_qty;
            $datas[$i]['act_av_qty'] = $actual_qty;
            $datas[$i]['picklist_stockqty'] = $picklist_stockqty;
            $datas[$i]['sub_id'] = $dat->sub_id;
            $datas[$i]['spq_quantity'] = $dat->spq_quantity;
            $datas[$i]['blncship'] = $ordrqty->order_qty - $picklistqty->picklistqty;
            $i++;
        }
        // dd($datas);
        $f_data['qty'] = $ava;
        $f_data['datas'] = $datas;
        return response()->json($f_data);
    }

    public function get_available_transfer_item(Request $request)
    {
        $order_id = $request->order_id;
        $data = TransferOrder::select('transferorder_subs.id as sub_id', 'transferorder_subs.item_id', 'transferorder_subs.order_qty', 'transferorder_subs.transfer_qty', 'items.item_name', 'items.part_no', 'uoms.uom_name', 'transfer_orders.from_location', 'items.spq_quantity', 'transfer_orders.to_location')
            ->leftjoin('transferorder_subs', 'transferorder_subs.order_id', '=', 'transfer_orders.id')
            ->leftjoin('items', 'items.id', '=', 'transferorder_subs.item_id')
            ->leftjoin('uoms', 'uoms.id', '=', 'items.uom_id')
            ->where('transfer_orders.id', $order_id)
            ->where('transferorder_subs.deleted_at', null)
            ->where('transferorder_subs.order_qty', '>', 'transferorder_subs.transfer_qty')
            ->get();
        $result = "";
        $i = 0;
        foreach ($data as $dat) {
            $getqty = Barcode::select(DB::raw('SUM(barcodes.net_weight)stockqty1'), DB::raw('SUM(barcodes.spq_qty)stockqty'), 'barcodes.expiry_date', 'barcodes.manf_date')
                ->leftjoin('locations', 'locations.id', '=', 'barcodes.location_id')
                ->where('barcodes.item_id', $dat->item_id)
                ->where("barcodes.qc_approval_status", DB::raw("'1'"))
                ->where(DB::raw('DATEDIFF(barcodes.expiry_date,CURDATE())'), '>', '0')
                ->whereNotNull('barcodes.spq_qty')
                ->get();

            $getserial = Barcode::select('barcodes.serial_no')
                ->leftjoin('locations', 'locations.id', '=', 'barcodes.location_id')
                ->where('barcodes.item_id', $dat->item_id)
                ->where("barcodes.qc_approval_status", DB::raw("'1'"))
                ->where(DB::raw('DATEDIFF(barcodes.expiry_date,CURDATE())'), '>', '0')
                ->whereNotNull('barcodes.spq_qty')
                ->get();
            $picklist_stockqty = 0;

            // dd($getserial);

            foreach ($getserial as $srlno) {
                $check_picklist = TransferPicklist::select(DB::raw('SUM(transfer_picklists.sub_qty)stockqty'))->where('serial_no', $srlno->serial_no)->where('status', '0')->first();
                $check_picklist_dis = DispatchPicklist::select(DB::raw('SUM(dispatch_picklists.sub_qty)stockqty'))->where('serial_no', $srlno->serial_no)->where('status', '0')->first();

                $picklist_stockqty = $picklist_stockqty + $check_picklist->stockqty;
                $picklist_stockqty = $picklist_stockqty + $check_picklist_dis->stockqty;
                // dd($check_picklist_dis);

            }
            // dd("5");
            $avi_qty = 0;
            foreach ($getqty as $get) {
                $avi_qty = $avi_qty + $get->stockqty;
            }
            $actual_qty = $avi_qty;
            $avi_qty = $avi_qty - $picklist_stockqty;
            if ($avi_qty < 0) {
                $avi_qty = '0';
            }

            $picklistqty = TransferPicklist::select(DB::raw('SUM(transfer_picklists.sub_qty)picklistqty'))->where('order_id', $order_id)->where('item_id', $dat->item_id)->first();
            $ordrqty = TransferorderSub::select(DB::raw('SUM(transferorder_subs.order_qty)order_qty'))->where('order_id', $order_id)->where('item_id', $dat->item_id)->first();

            $datas[$i]['item_id'] = $dat->item_id;
            $datas[$i]['item_name'] = $dat->item_name;
            $datas[$i]['order_qty'] = $dat->order_qty;
            $datas[$i]['avi_qty'] = $avi_qty;
            $datas[$i]['act_av_qty'] = $actual_qty;
            $datas[$i]['picklist_stockqty'] = $picklist_stockqty;
            $datas[$i]['sub_id'] = $dat->sub_id;
            $ava[$i]['order_qrt'] =   $dat->order_qty;
            $ava[$i]['avil'] =   $avi_qty;
            $datas[$i]['spq_quantity'] = $dat->spq_quantity;
            $datas[$i]['blncship'] = $ordrqty->order_qty - $picklistqty->picklistqty;

            $i++;
        }
        $f_data['qty'] = $ava;
        $f_data['datas'] = $datas;
        return response()->json($f_data);
    }

    public function get_available_transfer_locations(Request $request)
    {
        $order_id = $request->order_id;
        $data = TransferOrder::select('from_loc.id as from_location_id', 'from_loc.Loc_Name as from_location_name', 'to_loc.id as to_location_id', 'to_loc.Loc_Name as to_location_name')
            ->leftjoin('locations as from_loc', 'from_loc.id', '=', 'transfer_orders.from_location')
            ->leftjoin('locations as to_loc', 'to_loc.id', '=', 'transfer_orders.to_location')
            ->where('transfer_orders.id', $order_id)->get();

        // dd($data);
        $from = "";
        $to = "";
        foreach ($data as $dt) {
            // dd($dt);

            $from .= "<option value='$dt->from_location_id'>$dt->from_location_name</option>";
            $to .= "<option value='$dt->to_location_id'>$dt->to_location_name</option>";
        }

        $result = [
            'from' => $from,
            'to' => $to,
        ];
        return response()->json($result);
    }

    public function bintransferdetail(Request $request)
    {
        $data = BinToBinTransferSub::select(
            'items.item_name',
            'barcodes.serial_no',
            'f_bins.Bin_Name as fbin',
            't_bins.Bin_Name as tbin',
        )
            ->leftjoin('items', 'items.id', '=', 'bin_to_bin_transfer_subs.item_id')
            ->leftjoin('barcodes', 'barcodes.serial_no', '=', 'bin_to_bin_transfer_subs.serial_no')
            ->leftjoin('bins AS f_bins', 'f_bins.id', '=', 'bin_to_bin_transfer_subs.from_bin')
            ->leftjoin('bins AS t_bins', 't_bins.id', '=', 'bin_to_bin_transfer_subs.to_bin')
            ->where('bin_to_bin_transfer_subs.transfer_id', $request->transfer_no)
            ->get();
        $result = "";
        foreach ($data as $dat) {
            $result .= "<tr>
            <td>$dat->item_name</td>
            <td>$dat->serial_no</td>
            <td>$dat->fbin</td>
            <td>$dat->tbin</td>
            </tr>";
        }
        return response()->json($result);
    }

    public function transfer_details(Request $request)
    {
        $data = TransferentrySub::select(
            'items.item_name',
            'transferentry_subs.planned_qty',
            'transferentry_subs.scanned_qty',
        )
            ->leftjoin('items', 'items.id', '=', 'transferentry_subs.item_id')
            ->leftjoin('transfer_entries', 'transfer_entries.id', '=', 'transferentry_subs.transfer_entry_id')
            ->leftjoin('barcodes', 'barcodes.serial_no', '=', 'bin_to_bin_transfer_subs.serial_no')
            ->where('transferentry_subs.transfer_entry_id', $request->transfer_id)
            ->get();
        $result = "";
        foreach ($data as $dat) {
            $result .= "<tr>
                <td>$dat->item_name</td>
                <td>$dat->serial_no</td>
                <td>$dat->fbin</td>
                <td>$dat->tbin</td>
                </tr>";
        }
        return response()->json($result);
    }
    public function closebintransfer(Request $request)
    {
        BinToBinTransfer::where('id', $request->transfer_no)
            ->update([
                'transfer_status' => "1",
            ]);
        return response()->json("success");
    }

    public function transfer_scan_deatils(Request $request)
    {
        // dd($request->all());

        $data = TransferentrySub::select(
            'items.item_name',
            'transferentry_subs.planned_qty',
            'transferentry_subs.scanned_qty',
        )
            ->leftjoin('items', 'items.id', '=', 'transferentry_subs.item_id')
            ->leftjoin('transfer_entries', 'transfer_entries.id', '=', 'transferentry_subs.transfer_entry_id')
            ->where('transfer_entries.transfer_no', $request->transfer_no)
            ->get();
        $result = "";
        foreach ($data as $dat) {
            $result .= "<tr>
                <td>$dat->item_name</td>
                <td>$dat->planned_qty</td>
                <td>$dat->scanned_qty</td>
               
                </tr>";
        }
        return response()->json($result);
    }

    public function delete_dis_order_sub(Request $request)
    {

        $order_sub_id = $request->dis_sub_id;

        //order_id
        $order_sub_detail = DispatchOrderSub::select('order_id')->where('id', $order_sub_id)->first();

        //delete
        $order_sub = DispatchOrderSub::find($order_sub_id);
        $order_sub->delete();

        $order_id = $order_sub_detail->order_id;
        $data = DispatchOrder::select('dispatch_order_subs.id as sub_id', 'dispatch_order_subs.item_id', 'dispatch_order_subs.order_qty', 'dispatch_order_subs.despatch_qty', 'items.item_name', 'items.part_no', 'uoms.uom_name')
            ->leftjoin('dispatch_order_subs', 'dispatch_order_subs.order_id', '=', 'dispatch_orders.id')
            ->leftjoin('items', 'items.id', '=', 'dispatch_order_subs.item_id')
            ->leftjoin('uoms', 'uoms.id', '=', 'items.uom_id')
            ->where('dispatch_orders.id', $order_id)
            ->where('dispatch_order_subs.deleted_at', null)
            ->where('dispatch_order_subs.order_qty', '>', 'dispatch_order_subs.despatch_qty')
            ->get();
        $result = "";
        $i = 0;
        $ava = [];
        foreach ($data as $dat) {
            $getqty = Barcode::select(DB::raw('SUM(barcodes.net_weight)stockqty1'), DB::raw('SUM(barcodes.spq_qty)stockqty'), 'barcodes.expiry_date', 'barcodes.manf_date')
                ->leftjoin('locations', 'locations.id', '=', 'barcodes.location_id')
                ->where('barcodes.item_id', $dat->item_id)
                ->where("barcodes.qc_approval_status", DB::raw("'1'"))
                ->where(DB::raw('DATEDIFF(barcodes.expiry_date,CURDATE())'), '>', '0')
                ->whereNotNull('barcodes.spq_qty')
                ->get();

            $getserial = Barcode::select('barcodes.serial_no')
                ->leftjoin('locations', 'locations.id', '=', 'barcodes.location_id')
                ->where('barcodes.item_id', $dat->item_id)
                ->where("barcodes.qc_approval_status", DB::raw("'1'"))
                ->where(DB::raw('DATEDIFF(barcodes.expiry_date,CURDATE())'), '>', '0')
                ->whereNotNull('barcodes.spq_qty')
                ->get();
            $picklist_stockqty = 0;
            foreach ($getserial as $srlno) {
                $check_picklist = DispatchPicklist::select(DB::raw('SUM(dispatch_picklists.sub_qty)stockqty'))->where('serial_no', $srlno->serial_no)->where('status', '0')->get();
                foreach ($check_picklist as $pick) {
                    $picklist_stockqty = $picklist_stockqty + $pick->stockqty;
                }
                // dd($check_picklist);

            }
            $avi_qty = 0;
            foreach ($getqty as $get) {
                $avi_qty = $avi_qty + $get->stockqty;
            }
            $avi_qty = $avi_qty - $picklist_stockqty;
            if ($avi_qty < 0) {
                $avi_qty = '0';
            }
            $result .= "<tr>
            <td>" . ++$i . "</td>
            <td><input type='hidden' name='item[$i]' value='$dat->item_id'>$dat->item_name</td>
            <td><input type='hidden' name='order_qty[$i]' value='$dat->order_qty'>$dat->order_qty</td>";
            if ($avi_qty == 0) {
                $result .= "<td>0</td>";
            } else {
                $result .= "<td><input type='hidden' name='avilavle_qty[$i]' value='$avi_qty' >$avi_qty</td>";
            }

            $result .= "<td><input type='hidden' name='shiiping_qty[$i]' value='$dat->order_qty'>$dat->order_qty</td>
            <td> $picklist_stockqty</td>
            <td> <a id='dis_delete' style='cursor:pointer;' onclick='dispatch_delete($dat->sub_id)'><svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='bi bi-trash' viewBox='0 0 16 16'>
            <path d='M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z'/>
            <path fill-rule='evenodd' d='M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z'/>
          </svg></a></td>
            </tr>";
            $ava[$i]['order_qrt'] =   $dat->order_qty;
            $ava[$i]['avil'] =   $avi_qty;
        }
        $f_data['result'] = $result;
        $f_data['qty'] = $ava;
        return response()->json($f_data);
    }

    public function getbalance(Request $request)
    {

        $result = '';
        $grn_dt = DB::table('grns')
            ->select('id')
            ->where('grns.grn_no', "$request->grn_no")->first();


        $grn_id =  $grn_dt->id;

        $grn = DB::table('grns')
            ->join('grn_subs', 'grn_subs.grn_id', '=', 'grns.id')
            ->join('items', 'items.id', '=', 'grn_subs.item_id')
            ->select('grns.id', 'grns.grn_no', 'grn_subs.item_id', 'grn_subs.scanned_qty', 'grn_subs.rejected_qty', 'grn_subs.qty', 'items.item_name')
            ->where('grns.status', '0')->where('grns.qc_status', '1')
            ->where('grns.id', $grn_id)
            ->get();

        foreach ($grn as $gr) {
            $result .= "<tr><td>$gr->item_name</td><td>" . $gr->qty - ($gr->scanned_qty + $gr->rejected_qty) . "</td><td>$gr->scanned_qty</td><td>$gr->rejected_qty</td></tr>";
        }
        return $result;
    }

    public function getdispatchbalance(Request $request)
    {

        $result = '';
        $disp = DispatchEntry::select('id')->where('dispatch_no', "$request->dispatch_no")->first();


        $dispatch_id = $disp->id;

        $dispatch = DB::table('dispatch_entries')
            ->join('dispatch_entry_subs', 'dispatch_entry_subs.dispatch_entry_id', '=', 'dispatch_entries.id')
            ->join('items', 'dispatch_entry_subs.item_id', '=', 'items.id')
            ->where('dispatch_entries.status', 0)
            ->where('dispatch_entries.id', $dispatch_id)
            ->get();

        foreach ($dispatch as $dis) {
            $result .= "<tr><td>$dis->item_name</td><td>" . $dis->planned_qty - $dis->scanned_qty . "</td><td>$dis->scanned_qty</td></tr>";
        }
        return $result;
    }

    public function getCount($id)
    {

        $stock = Barcode::getstockcount([
            'branch_id' => $id
        ]);

        $qcpending = Barcode::getqcpendingcount([
            'branch_id' => $id
        ]);

        $sales = Barcode::getsalescount([
            'branch_id' => $id
        ]);

        $transfer = Barcode::gettransfercount([
            'branch_id' => $id
        ]);



        $rec = Barcode::join('branches', 'barcodes.branch_id', '=', 'branches.id')
            ->join('dispatch_scans', 'dispatch_scans.barcode', '=', 'barcodes.serial_no');

        for ($i = 0; $i < 6; $i++) {
            $rec = $rec->selectRaw("sum(case when date(`scan_time`) = '" . Carbon::now()->subDays($i)->format('Y-m-d') . "' then 1 else 0 end) as c" . $i);
        }
        if ($id != 0) $rec = $rec->where('barcodes.branch_id', '=', $id);
        $rec = $rec->get();
        if (count($rec)) {
            //$callLineData = $rec[0]->c0.",".$rec[0]->c1.",".$rec[0]->c2.",".$rec[0]->c3.",".$rec[0]->c4.",".$rec[0]->c5;
            $call[] = intval($rec[0]->c0);
            $call[] = intval($rec[0]->c1);
            $call[] = intval($rec[0]->c2);
            $call[] = intval($rec[0]->c3);
            $call[] = intval($rec[0]->c4);
            $call[] = intval($rec[0]->c5);
        } else {
            //$callLineData ="0,0,0,0,0,0";
            $call[] = 0;
            $call[] = 0;
            $call[] = 0;
            $call[] = 0;
            $call[] = 0;
            $call[] = 0;
        }
        $lne['name'] = "Sales";
        $lne['data'] = $call;

        $line[] = $lne;

        // dd($line);  


        $rec = Barcode::join('branches', 'barcodes.branch_id', '=', 'branches.id')
            ->join('sales_return_scans', 'sales_return_scans.barcode', '=', 'barcodes.serial_no')
            ->where('barcodes.status', '=', "1");

        for ($i = 0; $i < 6; $i++) {
            $rec = $rec->selectRaw("sum(case when date(`scan_time`) = '" . Carbon::now()->subDays($i)->format('Y-m-d') . "' then 1 else 0 end) as c" . $i);
        }
        if ($id != 0) $rec = $rec->where('barcodes.branch_id', '=', $id);
        $rec = $rec->get();
        if (count($rec)) {
            //$servLineData = $rec[0]->c0.",".$rec[0]->c1.",".$rec[0]->c2.",".$rec[0]->c3.",".$rec[0]->c4.",".$rec[0]->c5;
            $serv[] = intval($rec[0]->c0);
            $serv[] = intval($rec[0]->c1);
            $serv[] = intval($rec[0]->c2);
            $serv[] = intval($rec[0]->c3);
            $serv[] = intval($rec[0]->c4);
            $serv[] = intval($rec[0]->c5);
        } else {
            //$servLineData ="0,0,0,0,0,0";
            $serv[] = 0;
            $serv[] = 0;
            $serv[] = 0;
            $serv[] = 0;
            $serv[] = 0;
            $serv[] = 0;
        }
        $lne['name'] = "Sales Return";
        $lne['data'] = $serv;

        $line[] = $lne;

        // dd($line);

        return response()->json([
            'linechart' => $line,
            'stock' => $stock,
            'qcpending' => $qcpending,
            'sales' => $sales,
            'transfer' => $transfer,

        ]);
        // return response()->json($data);

    }

    public function get_items(Request $request)
    {

        $planno = $request->planno;

        $result = '<option value="">--select--</option>';



        if ($planno) {
            $planno = ProductionPlan::select('items.id', 'item_name')
                ->join('items', 'items.id', '=', 'production_plans.plan_item')
                ->where('status', '0')
                ->where('production_plans.id', "{$planno}")->get();

            foreach ($planno  as $plannos) {
                $result .= "<option value='$plannos->id'>$plannos->item_name</option>";
            }
        }

        return response()->json($result);
    }

    public function get_itemdet(Request $request)
    {


        $planno = $request->planno;
        $item = $request->item;



        $planno = ProductionPlan::select(DB::raw('SUM(production_plans.planned_qyt) planned_qyt'), DB::raw('SUM(production_plans.picked_qty) picked_qty'))
            ->where('status', '0')
            ->where('production_plans.id', "{$planno}")
            ->where('production_plans.plan_item', "{$item}")
            ->get();

        foreach ($planno  as $plannos) {
            $planned_qyt = $plannos->planned_qyt;
            $completed_qty = $plannos->picked_qty;
            $pending_qty = $planned_qyt - $completed_qty;
        }



        return response()->json([
            'planned_qty' => $planned_qyt,
            'completed_qty' => $completed_qty,
            'pending_qty' => $pending_qty

        ]);
    }

    public function get_order_item(Request $request)
    {


        $order_id = $request->order_id;

        $result = '<option value="">--select--</option>';



        if ($order_id) {
            $planno = ProductionOrder::select('items.id', 'item_name')
                ->join('items', 'items.id', '=', 'production_orders.item_id')
                ->where('status', '0')
                ->where('production_orders.id', "{$order_id}")->get();

            foreach ($planno  as $plannos) {
                $result .= "<option value='$plannos->id'>$plannos->item_name</option>";
            }
        }

        return response()->json($result);
    }

    public function get_issue_details(Request $request)
    {
        // dd($request->all());
        $order_id = $request->order_id;
        $fg_item_id = $request->item_id;

        // BomSub
        $get_qty = ProductionOrder::select('order_qty')
            ->where('production_orders.id', $order_id)
            ->where('production_orders.item_id', $fg_item_id)
            ->first();
        // dd($get_qty);

        $fg_qty = $get_qty->order_qty;

        $data = Bom::select('bom_subs.item_id', 'bom_subs.quantity', 'items.item_name')
            ->leftjoin('bom_subs', 'bom_subs.bom_id', '=', 'boms.id')
            ->leftjoin('items', 'items.id', '=', 'bom_subs.item_id')
            ->where('boms.item_id', $fg_item_id)
            ->where('boms.deleted_at', null)
            ->get();
        // dd($data);

        $result = "";
        $i = 0;
        $ava = [];
        $datas = [];
        $dis = 0;
        foreach ($data as $dat) {

            $rm_item_qty = $dat->quantity * $fg_qty;
            // dd($rm_item_qty);
            $getqty = round(Barcode::select('barcodes.expiry_date', 'barcodes.manf_date')
                ->where('barcodes.item_id', $dat->item_id)
                ->where("barcodes.qc_approval_status", DB::raw("'1'"))
                ->where("barcodes.status", DB::raw("'1'"))
                ->whereNotNull('barcodes.spq_qty')
                ->sum('barcodes.spq_qty'));
            // dd($getqty);
            // $picklistcount = ProductionPicklist::select('serial_no')
            //     ->where('production_picklists.item_id', $dat->item_id)
            //     ->where("production_picklists.status", DB::raw("'0'"))
            //     // ->whereNotNull('production_picklists.deleted_at')
            //     ->count();
            // dd($picklistcount);
            $picklistcount = ProductionPicklist::select('serial_no', 'picked_qty')
                ->where("production_picklists.status", DB::raw("'0'"))
                // ->whereNotNull('production_picklists.deleted_at')
                ->sum('production_picklists.picked_qty');

            // dd($picklistcount);


            $avaqty = $getqty - $picklistcount;
            // dd($avaqty);
            $result .= "<tr>
            <td>" . ++$i . "</td>
            <td><input type='hidden' name='rm_item[$i]' value='$dat->item_id'>$dat->item_name</td>
            <td><input type='hidden' name='rm_qty[$i]' value='$rm_item_qty'>$rm_item_qty</td>
            <td><input type='hidden' name='stock_qty[$i]' value='$getqty'>$getqty</td>
            <td><input type='hidden' name='planned_qty[$i]' value='$picklistcount'>$picklistcount</td>
            <td><input type='hidden' name='available_qty[$i]' value='$avaqty'>$avaqty</td>";
            if ($rm_item_qty > $avaqty) {
                $dis = 1;
                $result .= "<td><font color='red'>Not in Stock</font></td>";
            } else {
                $result .= "<td><font color='green'>IN Stock</font></td>";
            }
        }
        $f_data['result'] = $result;
        $f_data['fg_qty'] = $fg_qty;
        $f_data['dis'] = $dis;

        // $f_data['datas']=$datas;

        // dd($f_data);
        return response()->json($f_data);
    }

    public function get_items_entry(Request $request)
    {


        $order_id = $request->order_id;

        // $result = '<option value="">--select--</option>';



        if ($order_id) {
            $orderno = ProductionOrder::select('items.id', 'item_name')
                ->join('items', 'items.id', '=', 'production_orders.item_id')
                ->where('status', '1')
                ->where('production_orders.id', "{$order_id}")->get();


            foreach ($orderno  as $ordernos) {
                $result = "<option value='$ordernos->id'>$ordernos->item_name</option>";
            }
        }

        return response()->json($result);
    }

    public function get_item_prod_det(Request $request)
    {



        $order_id = $request->order_id;
        $item = $request->item;



        $order_no = ProductionOrder::select(DB::raw('SUM(production_orders.order_qty) planned_qyt'), DB::raw('SUM(production_orders.picked_qty) picked_qty'), DB::raw('SUM(production_entries.quantity) entry_qty'), 'uoms.uom_name', 'items.spq_quantity')
            ->leftjoin('production_entries', 'production_entries.order_id', '=', 'production_orders.id')
            ->join('items', 'items.id', '=', 'production_orders.item_id')
            ->join('uoms', 'uoms.id', '=', 'items.uom_id')
            ->where('status', '1')
            ->where('production_orders.id', "{$order_id}")
            // ->where('production_orders.item_id',"{$item}")
            ->get();


        foreach ($order_no  as $order_nos) {
            if ($order_nos->entry_qty == null) {
                $order_nos->entry_qty = 0;
            }

            $planned_qyt = $order_nos->planned_qyt;
            $completed_qty = $order_nos->picked_qty;
            $pending_qty = $planned_qyt - $order_nos->entry_qty;
            $uom = $order_nos->uom_name;
            $spq = $order_nos->spq_quantity;
        }

        $orderno1 = ProductionOrder::select('items.id', 'item_name')
            ->join('items', 'items.id', '=', 'production_orders.item_id')
            ->where('status', '1')
            ->where('production_orders.id', "{$order_id}")->get();


        foreach ($orderno1  as $orderno1s) {
            $result = "<option value='$orderno1s->id'>$orderno1s->item_name</option>";
        }


        return response()->json([
            'planned_qty' => $planned_qyt,
            'pending_qty' => $pending_qty,
            'uom' => $uom,
            'spq' => $spq,
            'result' => $result

        ]);
    }

    public function getplanbalance(Request $request)
    {

        $result = '';
        $disp = ProductionMaterialIssue::select('id')->where('issue_no', "$request->issue_no")->first();


        $issue_no_id = $disp->id;

        $dispatch = DB::table('production_material_issues')
            ->join('production_material_issue_subs', 'production_material_issue_subs.issue_id', '=', 'production_material_issues.id')
            ->join('items', 'production_material_issue_subs.rm_item_id', '=', 'items.id')
            ->where('production_material_issues.status', 0)
            ->where('production_material_issues.id', $issue_no_id)
            ->get();

        foreach ($dispatch as $dis) {
            $result .= "<tr><td>$dis->item_name</td><td>" . $dis->quantity - $dis->scanned_qty . "</td><td>$dis->scanned_qty</td></tr>";
        }
        return $result;
    }

    public function get_doc_qc(Request $request)
    {

        $qc_type = $request->qc_type;
        $result = '<option value="">--select--</option>';



        if ($qc_type == 1) {
            $grn_no = Grn::select('id', 'grn_no')->where('qc_status', '1')->get();

            foreach ($grn_no  as $grn) {
                $result .= "<option value='$grn->id'>$grn->grn_no</option>";
            }
        }

        if ($qc_type == 2) {
            $prod_no = ProductionEntry::select('id', 'production_no')->where('qc_status', '1')->get();
            // dd($prod_no);
            foreach ($prod_no  as $prod) {
                $result .= "<option value='$prod->id'>$prod->production_no</option>";
            }
        }



        return response()->json($result);
    }

    public function getBarChart(Request $request)
    {
        $id = $request->br_id;

        //expired
        $stockexpired = Barcode::getstockcountexpired([
            'branch_id' => $id
        ]);

        //<30
        $stockless30 = Barcode::getstockcountlessthan30([
            'branch_id' => $id
        ]);

        //31 - 60
        $stock31_60 = Barcode::stock31_60([
            'branch_id' => $id
        ]);

        //61 - 90
        $stock61_90 = Barcode::stock61_90([
            'branch_id' => $id
        ]);

        //>90
        $stockpreater90 = Barcode::stockpreater90([
            'branch_id' => $id
        ]);
        $serv = [$stockless30, $stock31_60, $stock61_90, $stockpreater90, $stockexpired];

        return response()->json([
            'barchart' => $serv,
        ]);
    }

    public function getPicklistBarChart(Request $request)
    {
        $id = $request->br_id;
        $picklist = $request->picklist;
        // dd($picklist);
        if ($picklist == 0) {
            //expired
            $picklistnotstarted = DispatchPicklist::getnotstartedcount([
                'branch_id' => $id
            ]);

            //<30
            $startedcount = DispatchPicklist::startedcount([
                'branch_id' => $id
            ]);
        } else if ($picklist == 1) {
            //expired
            $picklistnotstarted = TransferPicklist::getnotstartedcount([
                'branch_id' => $id
            ]);

            //<30
            $startedcount = TransferPicklist::startedcount([
                'branch_id' => $id
            ]);
        } else if ($picklist == 2) {
            //expired
            $picklistnotstarted = ProductionPicklist::getnotstartedcount([
                'branch_id' => $id
            ]);

            //<30
            $startedcount = ProductionPicklist::startedcount([
                'branch_id' => $id
            ]);
        }



        $serv = [$picklistnotstarted, $startedcount];

        return response()->json([
            'barchart' => $serv,
        ]);
    }
    public function loadstocktBarchart(Request $request)
    {
        $id = $request->br_id;
        $fg = Barcode::fgcount([
            'branch_id' => $id
        ]);

        //<30
        $rm = Barcode::rmcount([
            'branch_id' => $id
        ]);

        $serv = [$fg, $rm];


        return response()->json([
            'barchart' => $serv,
        ]);
    }

    public function getopeningitem(Request $request)
    {

        $openingno = $request->opening_no;

        $result = '<option value="">--select--</option>';



        if ($openingno) {
            $openingno = OpeningStock::select('items.id', 'item_name')
                ->join('items', 'items.id', '=', 'opening_stocks.opening_item')
                ->where('opening_status', '0')
                ->where('opening_stocks.opening_no', "{$openingno}")->get();
            // dd($openingno);

            foreach ($openingno  as $openingnos) {
                $result .= "<option value='$openingnos->id'>$openingnos->item_name</option>";
            }
        }

        return response()->json($result);
    }

    public function get_doc(Request $request)
    {

        $qc_type = $request->qc_type;
        $result = '<option value="">--select--</option>';



        if ($qc_type == 1) {
            $grn_no = Grn::select('id', 'grn_no')->get();

            foreach ($grn_no  as $grn) {
                $result .= "<option value='$grn->id'>$grn->grn_no</option>";
            }
        }

        if ($qc_type == 2) {
            $prod_no = ProductionEntry::select('id', 'production_no')->get();
            // dd($prod_no);
            foreach ($prod_no  as $prod) {
                $result .= "<option value='$prod->id'>$prod->production_no</option>";
            }
        }



        return response()->json($result);
    }

    public function po_qty(Request $request)
    {
        // dd($request->po_number);

        $pono = $request->po_number;
        $item = $request->item;
        $data = PurchaseOrder::select('purchase_order_subs.qty', 'purchase_order_subs.picked_qty')
            ->join('purchase_order_subs', 'purchase_order_subs.purchase_order_id', '=', 'purchase_orders.id')
            ->join('items', 'items.id', '=', 'purchase_order_subs.item_id')
            // ->where('purchase_no', '=', $pono)
            ->where('purchase_order_subs.purchase_order_id', '=', $pono)
            ->where('purchase_order_subs.item_id', '=', $item)
            ->where('purchase_order_subs.status', '=', '0')
            ->first();



        $total_qty = $data->qty - $data->picked_qty;

        return response()->json($total_qty);
    }

    public function getpodetails(Request $request)
    {


        $pono = $request->pono;
        $data = PurchaseOrder::select('purchase_no', 'purchase_order_subs.item_id', 'items.spq_quantity', 'items.part_no', 'items.item_name', 'purchase_order_subs.qty', 'purchase_order_subs.picked_qty')
            ->join('purchase_order_subs', 'purchase_order_subs.purchase_order_id', '=', 'purchase_orders.id')
            ->join('items', 'items.id', '=', 'purchase_order_subs.item_id')
            ->where('purchase_no', '=', $pono)
            ->where('purchase_order_subs.status', '=', '0')
            ->get();

        $batchno = Grn::nextBatchNumber();
        foreach ($data as $datas) {
            // dd($datas);
            $datas['batchno'] = $batchno;
            $batchno++;
        }

        $f_data['data'] = $data;

        return response()->json($f_data);
    }

    public function favicon(Request $request)
    {


        $config = Configuration::first();


        return response()->json($config->favi);
    }

    public function getbarcodedetailes(Request $request)
    {
        $barcode = $request->barcode;
        $data = Barcode::leftjoin('items', 'barcodes.item_id', 'items.id')->where('serial_no', $barcode)->where('barcodes.status', '1')->select('barcodes.spq_qty', 'barcodes.net_weight', 'items.item_name', 'items.part_no')->first();
        // dd($data);
        return response()->json($data);
    }

    public function get_orderitem_details(Request $request)
    {
        $order_id = $request->order_id;

        $data = DB::table('purchase_orders')
            ->leftJoin('purchase_order_subs', 'purchase_orders.id', '=', 'purchase_order_subs.purchase_order_id')
            ->leftJoin('items', 'purchase_order_subs.item_id', '=', 'items.id')
            ->select('purchase_orders.id', 'items.item_name', 'items.part_no', 'items.spq_quantity', 'purchase_order_subs.qty', 'purchase_order_subs.purchase_order_id')
            ->where('purchase_order_id', '=', $order_id)
            ->get();

        $result = "";
        $i = 0;
        $ava = [];
        $datas = [];
        $dis = 0;

        foreach ($data as $dat) {
            $result .= "<tr>
                <td>" . ++$i . "</td>
                <td>$dat->part_no</td>
                <td>$dat->item_name</td>
                <td>$dat->spq_quantity</td>
                <td>$dat->qty</td>";

            // dd($f_data);
        }

        $f_data['result'] = $result;
        $f_data['dis'] = $dis;
        return response()->json($f_data);
    }

    public function get_production_plan_details(Request $request)
    {
        $plan_id = $request->plan_id;

        $data = DB::table('production_plans')
            ->leftJoin('items', 'production_plans.plan_item', '=', 'items.id')
            ->select('production_plans.id', 'production_plans.plan_no', 'production_plans.planned_qyt', 'production_plans.plan_date', 'items.item_name')
            ->where('production_plans.id', '=', $plan_id)
            ->get();

        $result = "";
        $i = 0;
        $ava = [];
        $datas = [];
        $dis = 0;

        foreach ($data as $dat) {
            $result .= "<tr>
                <td>" . ++$i . "</td>
                <td>$dat->item_name</td>
                <td>$dat->planned_qyt</td>
                <td>$dat->plan_date</td>";

            // dd($f_data);
        }

        $f_data['result'] = $result;
        return response()->json($f_data);
    }

    public function get_production_order_details(Request $request)
    {
        $order_id = $request->order_id;

        $data = DB::table('production_orders')
            ->leftJoin('production_plans', 'production_plans.id', '=', 'production_orders.plan_id')
            ->leftJoin('items', 'production_orders.item_id', '=', 'items.id')
            ->select('production_plans.plan_no', 'items.item_name', 'production_orders.order_qty', 'production_orders.order_date', 'production_orders.id', 'production_plans.planned_qyt')
            ->where('production_orders.id', '=', $order_id)
            ->get();

        $result = "";
        $i = 0;
        $ava = [];
        $datas = [];
        $dis = 0;

        foreach ($data as $dat) {
            $result .= "<tr>
                <td>" . ++$i . "</td>
                <td>$dat->plan_no</td>
                <td>$dat->item_name</td>
                <td>$dat->planned_qyt</td>
                <td>$dat->order_qty</td>
                <td>$dat->order_date</td>";

            // dd($f_data);
        }

        $f_data['result'] = $result;
        $f_data['dis'] = $dis;
        return response()->json($f_data);
    }

    public function get_dispatch_transfer_details(Request $request)
    {
        $order_id = $request->order_id;

        $data = DB::table('transfer_orders')
            ->leftJoin('transferorder_subs', 'transfer_orders.id', '=', 'transferorder_subs.order_id')
            ->leftJoin('items', 'transferorder_subs.item_id', '=', 'items.id')
            ->leftJoin('locations as from_location_table', 'transfer_orders.from_location', '=', 'from_location_table.id')
            ->leftJoin('locations as to_location_table', 'transfer_orders.to_location', '=', 'to_location_table.id')
            ->select(
                'transfer_orders.type',
                'transfer_orders.date',
                'items.item_name',
                'transferorder_subs.order_qty',
                'from_location_table.Loc_Name as from_location_name',
                'to_location_table.Loc_Name as to_location_name'
            )
            ->where('transfer_orders.id', '=', $order_id)
            ->get();

        $result = "";
        $i = 0;
        $dis = 0;

        foreach ($data as $dat) {
            $result .= "<tr>
                <td>" . ++$i . "</td>
                <td>$dat->type</td>
                <td>$dat->date</td>
                <td>$dat->from_location_name</td>
                <td>$dat->to_location_name</td>
                <td>$dat->item_name</td>
                <td>$dat->order_qty</td>";

            // dd($f_data);
        }

        $f_data['result'] = $result;
        $f_data['dis'] = $dis;

        return response()->json($f_data);
    }

    public function get_dispatch_sales_details(Request $request)
    {
        $order_id = $request->order_id;

        $data = DB::table('dispatch_orders')
            ->leftJoin('dispatch_order_subs', 'dispatch_orders.id', '=', 'dispatch_order_subs.order_id')
            ->leftJoin('items', 'dispatch_order_subs.item_id', '=', 'items.id')
            ->leftJoin('locations', 'dispatch_orders.location_id', '=', 'locations.id')
            ->leftJoin('customers', 'dispatch_orders.customer_id', '=', 'customers.id')
            ->select(
                'dispatch_orders.type',
                'dispatch_orders.date',
                'items.item_name',
                'locations.Loc_Name',
                'customers.customer_name',
                'dispatch_order_subs.order_qty'
            )
            ->where('dispatch_orders.id', '=', $order_id)
            ->get();

        $result = "";
        $i = 0;
        $dis = 0;

        foreach ($data as $dat) {
            $result .= "<tr>
                <td>" . ++$i . "</td>
                <td>$dat->type</td>
                <td>$dat->date</td>
                <td>$dat->Loc_Name</td>
                <td>$dat->customer_name</td>
                <td>$dat->item_name</td>
                <td>$dat->order_qty</td>";

            // dd($f_data);
        }

        $f_data['result'] = $result;
        $f_data['dis'] = $dis;
        return response()->json($f_data);
    }

    public function get_sales_grid_details(Request $request)
    {
        $order_id = $request->order_id;
        $data = DB::table('dispatch_entries')
            ->leftJoin('dispatch_entry_subs', 'dispatch_entries.id', '=', 'dispatch_entry_subs.dispatch_entry_id')
            ->leftJoin('items', 'dispatch_entry_subs.item_id', '=', 'items.id')
            ->select('items.item_name', 'items.spq_quantity', 'dispatch_entry_subs.scanned_qty', 'dispatch_entry_subs.planned_qty')
            ->where('dispatch_entries.id', '=', $order_id)
            ->get();

        $result = "";
        $i = 0;
        $ava = [];
        $datas = [];
        $dis = 0;

        foreach ($data as $dat) {
            $result .= "<tr>
                <td>" . ++$i . "</td>
                <td>$dat->item_name</td>
                <td>$dat->spq_quantity</td>
                <td>$dat->planned_qty</td>
                <td>$dat->scanned_qty</td>
                <td>" . $dat->planned_qty - $dat->scanned_qty . "</td>";

            // dd($f_data);
        }

        $f_data['result'] = $result;
        $f_data['dis'] = $dis;
        return response()->json($f_data);
    }

    public function get_transfer_grid_details(Request $request)
    {
        $order_id = $request->order_id;

        $data = DB::table('transfer_entries')
            ->leftJoin('transferentry_subs', 'transfer_entries.id', '=', 'transferentry_subs.transfer_entry_id')
            ->leftJoin('items', 'transferentry_subs.item_id', '=', 'items.id')
            ->select('items.item_name', 'items.spq_quantity', 'transferentry_subs.scanned_qty', 'transferentry_subs.planned_qty')
            ->where('transfer_entries.id', '=', $order_id)
            ->get();


        $result = "";
        $i = 0;
        $ava = [];
        $datas = [];
        $dis = 0;

        foreach ($data as $dat) {
            $result .= "<tr>
                <td>" . ++$i . "</td>
                <td>$dat->item_name</td>
                <td>$dat->spq_quantity</td>
                <td>$dat->planned_qty</td>
                <td>$dat->scanned_qty</td>
                <td>" . $dat->planned_qty - $dat->scanned_qty . "</td>";

            // dd($f_data);
        }

        $f_data['result'] = $result;
        $f_data['dis'] = $dis;
        return response()->json($f_data);
    }


    public function getGrnGrid(Request $request)
    {

        $grn_id = $request->input('grn_id');

        // Fetch data from the database based on $grn_id
        $grnData = Grn::find($grn_id);
        if (!$grnData) {
            // Handle case where Grn record is not found
            return response()->json(['error' => 'Grn record not found'], 404);
        }
        $result = "<tr>
                    <td>{$grnData->grn_no}</td>
                    <td>{$grnData->vendor->Vendor_Name}</td>
                    <td>{$grnData->invoice_num}</td>
                    <td>{$grnData->invoice_date}</td>
                    <td>{$grnData->remark}</td>
                </tr>";

        $f_data['result'] = $result;
        $f_data['dis'] = 0;

        return response()->json($f_data);
    }

    public function get_material_manual_close(Request $request)
    {
        $order_id = $request->order_id;
        $product = ProductionMaterialIssue::where('id', '=', $order_id)->first();

        $production_id = $product->order_id;

        $data = DB::table('production_orders')
            ->leftJoin('items', 'production_orders.item_id', '=', 'items.id')
            ->select('production_orders.order_number', 'items.item_name', 'production_orders.order_qty', 'production_orders.order_date', 'production_orders.id')
            ->where('production_orders.id', '=', $production_id)
            ->get();

        $result = "";
        $i = 0;
        $ava = [];
        $datas = [];
        $dis = 0;

        foreach ($data as $dat) {
            $result .= "<tr>
                <td>" . ++$i . "</td>
                <td>$dat->order_number</td>
                <td>$dat->item_name</td>
                <td>$dat->order_qty</td>
                <td>$dat->order_date</td>";

            // dd($f_data);
        }

        $f_data['result'] = $result;
        $f_data['dis'] = $dis;
        return response()->json($f_data);
    }
}
