<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon;

class Setting extends Model
{
    protected $table = '_setting';
    protected $fillable = ['date_format', 'datetime_format', 'label', 'type'];
    use SoftDeletes;


    public function getJsDateFormatAttribute()
    {
        $jsFormat = array('Y' => 'Y', 'm' => 'M', 'd' => 'D');
        return strtr($this->pluck('date_format')->first(), $jsFormat);
    }

    /**
     * Convert date from user defined function to mysql date format
     * @param $date
     * @return static
     */
    public function realDate($date, $format = null)
    {
        if (!$date) return $date;
        $format = $format ? $format : $this->pluck('date_format')->first();
        $date = Carbon\Carbon::createFromFormat($format, $date);
        return $date->format('Y-m-d');
    }
    public function dateformat($format)
    {
        if ($format == 'Y-mm-d') {
            $format = 'Y-m-d';
            return $format;
        } elseif ($format == 'd-mm-YY') {
            $format = 'd-m-y';
            return $format;
        } elseif ($format == 'd-mm-Y') {
            $format = 'd-m-Y';
            return $format;
        } elseif ($format == 'd-mmm-Y') {
            $format = 'd-M-Y';
            return $format;
        } else {
            return false;
        }
    }
    public function displayDate($date, $format = null)
    {
        if (!$date) return $date;
        $format = $format ? $format : $this->dateformat($this->pluck('date_format')->first());
        $date = Carbon\Carbon::createFromFormat('Y-m-d', $date);
        return $date->format($format);
    }

    public function realDateTime($date, $format = null)
    {
        if (!$date) return $date;
        $date_format = $format ? $format : $this->pluck('date_format')->first();
        $datetime_format = $format ? $format : $this->pluck('datetime_format')->first();
        $format = $date_format . " " . $datetime_format;
        $date = Carbon\Carbon::createFromFormat($format, $date);
        return $date->format('Y-m-d H:i:s');
    }

    public function displayDateTime($datetime, $format = null)
    {
        if (!$datetime) return $datetime;
        $date_format = $format ? $format : $this->dateformat($this->pluck('date_format')->first());
        $datetime_format = $format ? $format : $this->pluck('datetime_format')->first();
        $format = $date_format . " " . $datetime_format;
        $datetime = Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $datetime);
        return $datetime->format($format);
    }



    /**
     * Round a number as per settings
     * @param $number
     * @return float
     */
}
