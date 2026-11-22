<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class File extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'files';

    protected $casts = [
        'start_date' => 'date',
        'due_date'   => 'date',
    ];


    protected $fillable = [
        'rfid_code',
        'start_date',
        'due_date',
        'official_file_number',
        'file_name',
        'subject',
        'details',
        'remarks',
        'priority',
        'category_id',
        'sub_category_id',
        'branch_id',
        'department_id',
        'employee_id',
        'upload_file',
        'status',
        'created_by',
    ];


   public function getStatusLabelAttribute()
    {
        return match ((int) $this->status) {
            0 => 'Open',
            1 => 'Closed',
            // 2 => 'On Hold',
            // 3 => 'In Progress',
            // 4 => 'Transferred',
            // 5 => 'Closed',
            default => 'Unknown',
        };
    }

    public function getStatusBadgeAttribute()
    {
       return match ((int) $this->status) {
            0 => 'primary',
            1 => 'dark',
            // 2 => 'warning',
            // 3 => 'primary',
            // 4 => 'info',
            // 5 => 'dark',
            default => 'light',
        };
    }

    public function scopeByRfid($query, $rfid)
    {
        return $query->where('rfid_code', $rfid);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function movements()
    {
        return $this->hasMany(FileMovement::class, 'file_id');
    }
    
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }


}
