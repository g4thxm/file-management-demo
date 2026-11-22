<?php

namespace App\Imports;

use App\Models\Location;
use App\Models\Branch;
use Illuminate\Support\Facades\Redirect;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation ;


class LocationImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */

    public function model(array $row)
    {

        $branch_id=Branch::select('id')->where('name',$row['branch_name'])->first();

        $location_type=0;
        if($row['location_type']=='QC Pending')
        {
            $location_type=1;
        }

        if($row['location_type']=='Storage')
        {
            $location_type=2;
        }

        if($row['location_type']=='Rejection')
        {
            $location_type=3;
        }

          if($row['location_type']=='Dispatch')
        {
            $location_type=4;
        }

          return new Location([
            'Loc_Name'    => $row['location_name'],
            'Prefix'    => $row['prefix'], 
            'NAV_loc_code'       => $row['erp_code'], 
            'loc_type'       => $location_type, 
            'Loc_Desc'       => $row['description'], 
            'branch_id'    => $branch_id->id, 
           
        ]);

    }

    public function rules(): array
    {

        return [

            '*.location_name' => 'required|unique:locations,Loc_Name',
            '*.branch_name' => 'required|exists:branches,name',
            '*.erp_code' => 'required|unique:locations,NAV_loc_code',
            '*.prefix' => 'required',
            '*.location_type' => 'required',
            '*.description' => 'required',

        ];
                    
    }
}
