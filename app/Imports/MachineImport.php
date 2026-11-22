<?php

namespace App\Imports;
use App\Models\Machine;
use Auth;
use Illuminate\Support\Facades\Redirect;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation ;

class MachineImport implements ToModel, WithHeadingRow, WithValidation
{
   /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */

    public function model(array $row)
    {

     
        $user_id = Auth::user()->id;

          return new machine([
            
            
            'name'       => $row['machine_name'], 
            'code'       => $row['machine_code'], 
            'user_id'    => $user_id, 
           
        ]);

    }

    public function rules(): array
    {

        return [

            
          
            '*.machine_name' => 'required',
            '*.machine_code' => 'required|unique:machines,code',
        ];
                    
    }
}

