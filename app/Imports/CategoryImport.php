<?php

namespace App\Imports;

use App\Models\Category;
use Illuminate\Support\Facades\Redirect;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation ;


class CategoryImport implements ToModel, WithHeadingRow, WithValidation
{
   /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */

    public function model(array $row)
    {
        return new Category([
            'category_name'    => $row['category_name'],
            'erp_code'    => $row['erp_code'], 
            'description'       => $row['description'], 
        ]);

    }

     public function rules(): array
    {

        return [

            '*.category_name' => 'required|unique:categories,category_name',
            '*.erp_code' => 'required|unique:categories,erp_code',
            
        ];

    }


}
