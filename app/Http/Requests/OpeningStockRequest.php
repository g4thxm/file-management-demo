<?php

namespace App\Http\Requests;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OpeningStockRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function rules()
    {
        return [
            'excel' => 'required|file|mimes:xls,xlsx',
            'excel.*' => 'required|valid_excel_sheet:part_number,quantity'
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function messages()
    {
        return [
            'excel.*.valid_excel_sheet' => 'The Excel file must contain a sheet with the correct format, including part_number and quantity fields.',
            'excel.*.required' => 'The Excel file is required.'
        ];
    }

    public function withValidator($validator)
    {
        dd(1);
        $validator->after(function ($validator) {
            $rows = $validator->validated()['excel'];

            foreach ($rows as $row) {
                $part = Part::where('part_number', $row['part_number'])->first();

                if (!$part) {
                    $validator->errors()->add('part_number', 'Invalid part number');
                } else if ($row['quantity'] % $part->quantity != 0) {
                    $validator->errors()->add('quantity', 'Quantity must be a multiple of the quantity in the SQL table');
                }
            }
        });
    }
}

