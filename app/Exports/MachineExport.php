<?php

namespace App\Exports;

use App\Models\Machine;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithStyles;

class MachineExport implements FromCollection,WithHeadings,WithStyles,ShouldAutoSize,WithEvents
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function headings():array{
        return[
    
            'Machine Name'
            
        ];
    
    
        }

        public function registerEvents(): array
        {
            return [
                AfterSheet::class    => function(AfterSheet $event) {
                    $cellRange = 'A1:D1';
                    $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setSize(12);
                    $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setBold(true);
                },
            ];
        }


        public  function  styles(Worksheet $sheet)
        {
            $sheet->getStyle('1')->getFont()->setBold(true);
    
            $sheet->getStyle('A1:D1')->applyFromArray([
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'color' => ['argb' => "FFaeaaaa"]
                ]
            ]);
        }
        public function collection()
        {
            return collect(Machine::getMachine());
        }
}
