<?php



namespace App\Models;



use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use illuminate\Support\Facades\DB;


class Branch extends Model
{

    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'Branch_Code',
        'Address',
        'Gst_No',
    ];
    protected $softDelete = true;



    //softdelete



    protected $dates=['deleted_at'];




    public function company()
    {
        return $this->belongsTo(Company::class);
    }


}

