<?php
namespace App\Imports;
use App\Models\Bin;
use App\Models\Item;
use App\Models\BinMapping;
use Illuminate\Support\Facades\Redirect;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation ;

class BinMappingImport implements ToModel, WithHeadingRow, WithValidation
{
   /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */

    public function model(array $row)
    {

        $bin_id=Bin::select('id')->where('Bin_Name',$row['bin_name'])->first();
        $item_id=Item::select('id')->where('item_name',$row['item_name'])->first();



          return new BinMapping([
            'bin_id'    => $bin_id->id, 
            'item_id'    => $item_id->id, 
        
        ]);

    }

    public function rules(): array
    {

        return [

            '*.bin_name' => 'required|exists:bins,bin_name',
            '*.item_name' => 'required|exists:items,item_name',
        ];
                    
    }
}
