<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use illuminate\Support\Facades\DB;

class Vendor extends Model
{
  
    use HasFactory;

    use SoftDeletes;

    protected $fillable=['Vendor_Name','vendor_code','location','address','city','state','zip_code'];
    protected $dates=['deleted_at'];

    public static function getVendor(){

        $vendor = new Vendor;

        $VendorDatas = $vendor->get(['Vendor_Name','vendor_code','location','address','city','state','zip_code'])->sortBy('Vendor_Name');

        if(!sizeof($VendorDatas)){
            return redirect()->route('vendor.index')->with('error','Nothing to download');
        }

        $data = array();

        foreach ($VendorDatas as $vendorData) {

            $d = array();
    
            $d['Vendor_Name'] = $vendorData['Vendor_Name'];
            $d['vendor_code'] = $vendorData['vendor_code'];
            $d['location'] = $vendorData['location'];
            $d['address'] = $vendorData['address'];
            $d['city'] = $vendorData['city'];
            $d['state'] = $vendorData['state'];
            $d['zip_code'] = $vendorData['zip_code'];

            $data[] = $d;

        }

        return $data;

    }


}
