<?php

namespace App\Imports;

use App\Models\Bin;
use App\Models\Item;
use App\Models\OpeningStock;
use Illuminate\Support\Facades\Redirect;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OpeningStockImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
    * @param Collection $collection
    */

    private $openingstockno;

    public function __construct($openingstockno)
    {
        $this->openingstockno = $openingstockno;
    }

    public function model(array $row)
    {

        
    //    dd($row);
        $item_id=Item::select('id')->where('part_no',$row['part_number'])->first();
        $bin_id=Bin::select('id','storage_loc_id')->where('Bin_Name',$row['bin'])->first();

        return new OpeningStock([
            'opening_no'  => $this->openingstockno,
            'opening_item'    => $item_id->id,
            'opening_loc'    => $bin_id->storage_loc_id, 
            'opening_bin_name'    => $bin_id->id, 
            'opening_manf_date' => Date::excelToDateTimeObject($row['date_of_manufacturing'])->format('y-m-d'),
            'opening_expiry_date'         => Date::excelToDateTimeObject($row['date_of_expiry'])->format('y-m-d') , 
            'opening_qty'       => $row['total_quantity'], 
            'batch'       => $row['batch'], 

           
        ]);
    }

    public function rules(): array
    {
        

              return [
            '*.part_number' => ['required',
                                'exists:items,part_no'],
            
            '*.bin' => ['required','exists:bins,Bin_Name'],
            '*.batch' => 'required',
            '*.date_of_manufacturing' => 'required',
            '*.date_of_expiry' => 'required',
            '*.total_quantity' =>['required','numeric'
            // Rule::ignore('items','total_quantity')->where(function ($query) {
            //     $query->whereRaw('quantity % total_quantity = 0');
            // })
                                ],
            ];
    }

   
    public function withValidator($validator)
    {
       
        
        
        $validator->after(function ($validator) {
            $rows = $validator->validated();
            $i=1;
            foreach ($rows as $row) {
                $i++;
                $part = Item::where('part_no', $row['part_number'])->first();
                
                if (!$part) {
                    $validator->errors('part_number', 'Invalid part number');
                } else if ($row['total_quantity'] % $part->spq_quantity != 0) {
                   
                    $validator->errors()->add($i, 'Quantity must be a multiple of the quantity in the SQL table');
                }
            }
        });
    }
}
