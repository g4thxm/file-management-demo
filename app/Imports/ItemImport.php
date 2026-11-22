<?php

namespace App\Imports;

use App\Models\Category;
use App\Models\Item;
use App\Models\SubCategory;
use App\Models\Uom;
use Illuminate\Support\Facades\Redirect;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation ;

class ItemImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {

        // dd($row);

          
        $catageory_id=Category::select('id')->where('category_name',$row['category'])->first();

        $sub_category_id=SubCategory::select('id')->where('sub_category_name',$row['sub_category'])->first();

        $uom_id=Uom::select('id')->where('uom_name',$row['uom'])->first();

        $best_before_type=0;
        if($row['best_before_type']=='Days')
        {
            $best_before_type=1;
        }

        if($row['best_before_type']=='Months')
        {
            $best_before_type=2;
        }

        if($row['best_before_type']=='Years')
        {
            $best_before_type=3;
        }


        // if(($row['item_type']=='FG') && ($row['primary_shell_life']==''))
        // {
        //     return Item::check(); 
        // }

        // dd($uom_id->id);

        return new Item([
            'item_name'    => $row['item_name'],
            'part_no'    => $row['part_no'], 
            'category_id'    => $catageory_id->id, 
            'sub_category_id' => $sub_category_id->id, 
            'uom_id'         => $uom_id->id, 
            'type_of_packing'       => $row['type_of_packing'], 
            'spq_quantity'       => $row['spq_quantity'], 
            'net_weight'       => $row['net_weight'], 
            'gross_weight'       => $row['gross_weight'], 
            'item_type'       => $row['item_type'], 
            'primary_shell_life'       => $row['primary_shell_life'], 
            'secondary_shell_life'       => $row['secondary_shell_life'], 
            'best_before_type'       => $best_before_type, 
            'best_before_value'       => $row['best_before_value'], 
            'dispatch_alert'       => $row['dispatch_alert'], 
            'hsn_code'       => $row['hsn_code'], 
            'single_packet_weight'       => $row['single_packet_weight'], 
            'description'       => $row['description'], 
           
        ]);
    }
    public function rules(): array
    {
    //    dd($row);
 
    // $item_type ="";
        // return [
            
            

            // '*.item_type' => function($attribute, $value, $onFailure) {/
                        

                // if ($value == 'FG') {

                    // dd($value);

              return [
            '*.type_of_packing' => 'required',
            
            '*.part_no' => ['unique:items,part_no'],
            '*.category' => ['exists:categories,category_name'],
            '*.sub_category' => ['exists:sub_categories,sub_category_name'],        
            '*.uom' => ['exists:uoms,uom_name'],
            '*.item_type' => 'required',
            
            
            '*.spq_quantity' => 'required',
            '*.net_weight' => 'required',
            '*.gross_weight' =>'required',
            '*.primary_shell_life' => 'required',
            '*.secondary_shell_life' => 'required',
            '*.dispatch_alert' =>'required',
            '*.hsn_code' => 'required',

            ];
                    
            //  }

        //      else
        //      {
        //         return [
            
        //             '*.part_no' => ['unique:items,part_no'],
        //             '*.category' => ['exists:categories,category_name'],
        //             '*.sub_category' => ['exists:sub_categories,sub_category_name'],        
        //             '*.uom' => ['exists:uoms,uom_name'],
        //             '*.item_type' => 'required',
                    
        //             // '*.type_of_packing' => ['required'],
        //             // '*.type_of_packing' => 'required',
        //             '*.spq_quantity' => 'required',
        //             '*.net_weight' => 'required',
        //             '*.gross_weight' => 'required',
        //             // '*.primary_shell_life' => ['required'],
        //             // '*.secondary_shell_life' => ['required'],
        //             // '*.dispatch_alert' => ['required'],
        //             '*.hsn_code' => 'required',
                    
        
                   
                    
        //         ];
        //      }

                          
        //     }
            
        // ];


        
            


    }
}
