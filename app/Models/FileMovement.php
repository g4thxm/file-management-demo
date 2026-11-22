<?php

namespace App\Models;

use App\Models\File;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;


class FileMovement extends Model
{
    use HasFactory;

    protected $table = 'file_movements';

    // 🔥 REQUIRED (because updated_at does NOT exist)
    public $timestamps = false;

    protected $fillable = [
        'file_id',

        // entry (creator)
        'entry_user_id',
        'entry_branch_id',
        'entry_department_id',

        // assigned
        'to_user_id',
        'to_branch_id',
        'to_department_id',

        'accessibility',
        'subject',
        'remarks',
        'status',
        'created_at',
    ];
    

    /* ================= ACCESSORS ================= */

    public function getAccessibilityTextAttribute()
    {
        // Transferred
        if ((int) $this->status === 4) {
            return 'Transferred';
        }

        // Assigned but same holder
        if ((int) $this->accessibility === 1) {
            return 'Assigned';
        }

        // Default
        return 'Creator';
    }


   public function getCreatedDateAttribute()
    {
        return $this->created_at
            ? Carbon::parse($this->created_at)->format('d M Y')
            : '-';
    }

    public function getCreatedTimeAttribute()
    {
        return $this->created_at
            ? Carbon::parse($this->created_at)->format('H:i:s')
            : '-';
    }

   public function getToBranchDisplayAttribute()
    {
        return $this->status == 5
            ? 'Closed'
            : ($this->toUser?->branch?->name ?? '-');
    }

    public function getToDepartmentDisplayAttribute()
    {
        return $this->status == 5
            ? 'Closed'
            : ($this->toUser?->department?->name ?? '-');
    }

    public function getToUserDisplayAttribute()
    {
        return $this->status == 5
            ? 'Closed'
            : ($this->toUser?->name ?? '-');
    }




    public function getStatusLabelAttribute()
    {
        return match ((int) $this->status) {
            0 => 'Not Received',
            1 => 'Received',
            2 => 'On Hold',
            3 => 'In Progress',
            4 => 'Transferred',
            5 => 'Completed',
            default => 'Unknown',
        };
    }

    public function getStatusBadgeAttribute()
    {
        return match ((int) $this->status) {
            0 => 'secondary',
            1 => 'success',
            2 => 'warning',
            3 => 'primary',
            4 => 'info',
            5 => 'dark',
            default => 'light',
        };
    }

    /* ================= RELATIONSHIPS ================= */

    public function file()
    {
        return $this->belongsTo(File::class, 'file_id');
    }

    public function entryUser()
    {
        return $this->belongsTo(Employee::class, 'entry_user_id');
    }

    public function entryBranch()
    {
        return $this->belongsTo(Branch::class, 'entry_branch_id');
    }

    public function entryDepartment()
    {
        return $this->belongsTo(Department::class, 'entry_department_id');
    }

    public function toUser()
    {
        return $this->belongsTo(Employee::class, 'to_user_id');
    }

    public function toBranch()
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }

    public function toDepartment()
    {
        return $this->belongsTo(Department::class, 'to_department_id');
    }
}
