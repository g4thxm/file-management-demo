<?php

namespace App\Exports;

use App\Models\Putaway;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class PutawayExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $filters;
    
    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }
    
    public function headings(): array
    {
        return [
            'BinCode',
            'ItemCode',
            'RollNo',
            'Scanned Qty',
        ];
    }
    
    public function collection()
    {
        $data = Putaway::reportDetailed($this->filters);
        
        return $data->map(function($item) {
            // Prepend TAB character (\t) to BinCode to force Excel to treat as text
            return [
                "\t" . $item->bin_code, // TAB character forces text format
                $item->item_code,
                $item->roll_no,
                $item->quantity,
            ];
        });
    }
}