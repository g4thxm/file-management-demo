<?php

namespace App\Imports;

use App\Models\TransferOrder;
use App\Models\Location;
use Illuminate\Support\Facades\Redirect;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Auth;
use Alert;
use DB;
use Illuminate\Validation\ValidationException;


class DispathPlanImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */

    public function model(array $row)
    {
        $from_location_id=Location::select('id')->where('Loc_Name',$row['from_location'])->first();
        $to_location_id=Location::select('id')->where('Loc_Name',$row['to_location'])->first();
        $date = date('Y-m-d H:i:s');
        $order_no = TransferOrder::nextNumber();

        if ($row['type'] = "Transfer") {

              $transfer = new TransferOrder([
                'order_no' => $order_no,
                'date'=>$date,
                'from_location' => $from_location_id->id,
                'to_location' => $to_location_id->id,
                'type' => $row['type'],
                'status' => 0,
             
            ]);

        }
        else{
            throw ValidationException::withMessages(['type' => 'The selected production_item is invalid.']);
        }

    }

 
    public function rules(): array
    {
        return [

            '*.type' => 'required|in:Transfer,Sales|validate_type',
            '*.from_location' => 'exists:locations,Loc_Name|required|validate_location',
            // '*.row_data' => 'required|same:column1,column2',
        ];
                    
    }

}
