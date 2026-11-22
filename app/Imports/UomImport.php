<?php

namespace App\Imports;

use App\Models\Uom;
use Illuminate\Support\Facades\Redirect;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation ;

class UomImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */

    public function model(array $row)
    {

          return new Uom([
            'uom_name'    => $row['uom_name'],
            'uom_code'    => $row['uom_code'], 
        ]);

    }

     public function rules(): array
    {

        return [

            '*.uom_name' => 'required|unique:uoms,uom_name',
            '*.uom_code' => 'required|unique:uoms,uom_code',

        ];
                    
    }
}
