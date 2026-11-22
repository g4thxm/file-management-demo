<?php

namespace App\Imports;


use Illuminate\Support\Facades\Redirect;
use Maatwebsite\Excel\Concerns\ToModel;

use Illuminate\Validation\ValidationException;


class DispathPlanImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */

    public function model(array $row)
    {

    }

 
   

}
