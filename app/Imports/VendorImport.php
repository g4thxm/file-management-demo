<?php

namespace App\Imports;

use App\Models\Vendor;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation ;

class VendorImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {

        // dd($row);
        return new Vendor([
            'Vendor_Name'     => $row['vendor_name'],
            'vendor_code'    => $row['vendor_code'], 
            'location'    => $row['location'], 
            'address'    => $row['address'], 
            'city'    => $row['city'], 
            'state'    => $row['state'], 
            'zip_code'    => $row['zip_code'], 
        ]);
    }

    public function rules(): array
    {
        return [
            '*.vendor_name' => ['unique:vendors,Vendor_Name'],
            '*.vendor_code' => ['unique:vendors,vendor_code'],
            '*.location' => ['unique:vendors,location']

        ];
    }
}
