<?php
namespace App\Imports;
use App\Models\Item;
use App\Models\Log;
use Auth;
use Illuminate\Support\Facades\Redirect;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation ;

class LogImport implements ToModel, WithHeadingRow, WithValidation
{
   /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */

    public function model(array $row)
    {

        $item_id=Item::select('id')->where('item_name',$row['item_name'])->first();
        $user_id = Auth::user()->id;

          return new log([
            'item_id'    => $item_id->id, 
            'description' => $row['description'], 
              'user_id'    => $user_id, 
           
        ]);

    }

    public function rules(): array
    {

        return [

            
            '*.item_name' => 'required|exists:items,item_name',
            '*.description' => 'required|unique:logs,description',
        ];
                    
    }
}

