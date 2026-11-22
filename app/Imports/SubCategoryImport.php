<?php

namespace App\Imports;

use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Support\Facades\Redirect;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation ;

class SubCategoryImport implements ToModel, WithHeadingRow, WithValidation
{
   /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */

    public function model(array $row)
    {

        $category_id=Category::select('id')->where('category_name',$row['category'])->first();

          return new SubCategory([
            'sub_category_name'    => $row['sub_category_name'],
            'sub_category_code'    => $row['sub_category_code'], 
            'category_id'    => $category_id->id, 
            'description'       => $row['description'], 
           
        ]);

    }

    public function rules(): array
    {

        return [

            '*.sub_category_name' => 'required|unique:sub_categories,sub_category_name',
            '*.sub_category_code' => 'required|unique:sub_categories,sub_category_code',
            '*.category' => 'required|exists:categories,category_name',
        ];
                    
    }
}
