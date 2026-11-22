<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class FileReopen extends Model
{
    use HasFactory;

    protected $table = 'file_reopens';

    protected $fillable = [
        'file_id',
        'reopened_by',
        'reopened_date',
        'reopened_time',
        'remarks',
        'to_branch_id',
        'to_department_id',
        'to_user_id',
    ];

    public function getOpenedDateAttribute()
    {
         return isset($this->attributes['reopened_date'])
        ? Carbon::parse($this->attributes['reopened_date'])->format('d M Y')
        : '-';
    }

    public function file()
    {
        return $this->belongsTo(File::class);
    }

    public function toBranch()
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }

    public function toDepartment()
    {
        return $this->belongsTo(Department::class, 'to_department_id');
    }

    public function toEmployee()
    {
        return $this->belongsTo(Employee::class, 'to_user_id');
    }


    public function reopenedBy()
    {
        return $this->belongsTo(Employee::class, 'reopened_by');
    }
}
