<?php

namespace App\Imports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation ;

class CustomerImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {

        // dd($row);    
        return new Customer([
            'customer_name'    => $row['customer_name'],
            'customer_code'    => $row['customer_code'], 
            'shipping_code'    => $row['shipping_code'], 
            'customer_address' => $row['customer_address'], 
            'zip_code'         => $row['zip_code'], 
            'gst_number'       => $row['gst_number'], 
            
        ]);
    }

    public function rules(): array
    {
        return [
            '*.customer_name' => ['unique:customers,customer_name'],
            '*.customer_code' => ['unique:customers,customer_code'],
            '*.shipping_code' => ['unique:customers,shipping_code'],
            '*.customer_address' => ['unique:customers,customer_address'],
            '*.zip_code' => ['unique:customers,zip_code'],
            '*.gst_number' => ['unique:customers,gst_number'],
        ];
    }
}
