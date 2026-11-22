<?php

namespace App\Exports;

use App\Models\Location;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithStyles;

class LocationExport implements FromCollection,WithHeadings,ShouldAutoSize,WithEvents,WithStyles
{
    /**
    * @return \Illuminate\Support\Collection
    */


    public function headings():array{
        return[
            'Location Name',
			'Location Prefix',
			'Branch Name',
            'ERP Code',
			'Location Type',
			'Description',
        ];
    }


    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function(AfterSheet $event) {
                $cellRange = 'A1:F1';
                $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setSize(12);
                $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setBold(true);
            },
        ];
    }
    public function collection()
    {
        // return Location::all();
        $location=Location::
        join('branches', 'branches.id', '=', 'locations.branch_id');
        return $location->get(['locations.Loc_Name','locations.Prefix','branches.name','locations.NAV_loc_code','locations.loc_type','locations.Loc_Desc'])->sortBy('locations.Loc_Name');
    }
    public  function  styles(Worksheet $sheet)
    {
        $sheet->getStyle('1')->getFont()->setBold(true);

        $sheet->getStyle('A1:F1')->applyFromArray([
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => ['argb' => "FFaeaaaa"]
            ]
        ]);
    }

}
