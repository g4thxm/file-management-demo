<?php
namespace App\Imports;
use App\Models\ProductionPlan;
use App\Models\Item;
use Illuminate\Support\Facades\Redirect;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Auth;
use Alert;
use DB;
use Illuminate\Validation\ValidationException;


class ProductionPlanImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */

    public function model(array $row)
    {

        $item_id=Item::select('id')->where('item_name',$row['production_item'])->where('item_type','FG')->first();
        $userid=Auth::user()->id;
        $branchid=Auth::user()->branch_id;
        $plan_date = date('Y-m-d');
        
        $prefix = 'PP'.date('ym');
        $start = '00001';
        $len=strlen($prefix);
        $planno= DB::select("SELECT
        CONCAT( '$prefix',LPAD(max_plan_no+1,GREATEST(4,LENGTH(max_plan_no+1)),'0')) plan_no
        FROM (SELECT IFNULL(MAX(SUBSTRING(plan_no,$len+1)),'0') max_plan_no 
        FROM production_plans WHERE  plan_no LIKE '$prefix%') P2");
        $plan_no= $planno[0]->plan_no;
        if ($item_id) {

                return new ProductionPlan([
                'plan_no'    => $plan_no,
                "plan_date" => $plan_date,
                'plan_item'       => $item_id->id, 
                'branch_id'       => $branchid, 
                'user_id'    => $userid, 
                'planned_qyt'    =>$row['quantity'], 
                'picked_qty' => 0,
                'status' => 0,
                
            ]);
        }
        else{
            throw ValidationException::withMessages(['plan_item' => 'The selected production_item is invalid.']);
        }

    }

 
    public function rules(): array
    {
        return [

            '*.production_item' => 'required|exists:items,item_name',
            '*.quantity' => 'required|numeric|min:1',

        ];
                    
    }

}
