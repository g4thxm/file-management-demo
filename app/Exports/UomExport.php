<?php

namespace App\Exports;

use App\Models\Uom;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithStyles;

class UomExport implements FromCollection,WithHeadings,WithStyles,ShouldAutoSize,WithEvents
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function headings():array{
        return[
    
            'Uom Name',
            'Uom Code'
        ];
    
    
        }
        public function registerEvents(): array
        {
            return [
                AfterSheet::class    => function(AfterSheet $event) {
                    $cellRange = 'A1:B1';
                    $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setSize(12);
                    $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setBold(true);
                },
            ];
        }
        public  function  styles(Worksheet $sheet)
    {
        $sheet->getStyle('1')->getFont()->setBold(true);

        $sheet->getStyle('A1:B1')->applyFromArray([
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => ['argb' => "FFaeaaaa"]
            ]
        ]);
    }
    public function collection()
    {
        return collect(Uom::getUom());
    }
}
