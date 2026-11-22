<?php

namespace App\Http\Controllers\Transactions;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\File;

class FileClosureController extends Controller
{
   public function index()
    {

       $employeeId = auth()->user()->employee_id;
       
         $files = File::where('status', 0) // open files only
            ->whereHas('movements', function ($q) use ($employeeId) {
                $q->where('to_user_id', $employeeId)
                  ->whereRaw('file_movements.id = (
                        SELECT id FROM file_movements fm2
                        WHERE fm2.file_id = file_movements.file_id
                        ORDER BY fm2.created_at DESC, fm2.id DESC
                        LIMIT 1
                  )');
            })
            ->distinct()
            ->orderBy('official_file_number')
            ->get(['id', 'official_file_number']);
            return view('transactions.fileclosure', compact('files'));

    }
}
