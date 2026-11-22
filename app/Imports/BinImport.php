<?php

namespace App\Imports;

use App\Models\Bin;
use App\Models\Location;
use Illuminate\Support\Facades\Redirect;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation ;

class BinImport implements  ToModel, WithHeadingRow, WithValidation
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */

    public function model(array $row)
    {

        $storage_loc_id=Location::select('id')->where('Loc_Name',$row['storage_location'])->first();


        return new Bin([
            'Bin_Name'    => $row['bin_name'],
            'bin_code'    => $row['erp_code'], 
            'storage_loc_id'    => $storage_loc_id->id, 
            'Bin_Desc'       => $row['description'], 
           
        ]);

    }

    public function rules(): array
    {

        return [

            '*.bin_name' => 'required|unique:bins,Bin_Name',
            '*.erp_code' => 'required|unique:bins,bin_code',
            '*.storage_location' => 'required|exists:locations,Loc_Name',
            '*.description' => 'required',

        ];
                    

    }

}
