<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class FileClosure extends Model
{
    use HasFactory;

    protected $table = 'file_closures';

    protected $fillable = [
        'file_id',
        'closed_date',
        'closed_time',
        'closed_by',
        'remarks',
    ];

    public function getClosedDateAttribute()
    {
         return isset($this->attributes['closed_date'])
        ? Carbon::parse($this->attributes['closed_date'])->format('d M Y')
        : '-';
    }

    // public function getCreatedTimeAttribute()
    // {
    //    return $this->closed_time
    //     ? $this->closed_time->format('H:i:s')
    //     : '-';
    // }

    public function closedBy()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function file()
    {
        return $this->belongsTo(File::class, 'file_id');
    }
}