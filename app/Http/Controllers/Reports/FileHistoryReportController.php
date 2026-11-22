<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\File;
use App\Models\FileClosure;
use App\Models\FileMovement;
use App\Models\FileReopen;
use Illuminate\Support\Facades\Storage;

class FileHistoryReportController extends Controller
{
    public function index()
    {
        $files = File::orderBy('official_file_number')->get();
        return view('report.file_history', compact('files'));
    }

    public function view(Request $request)
    {
        $request->validate([
            'file_id' => 'required|exists:files,id',
        ]);

        $files = File::orderBy('official_file_number')->get();

        $file = File::with([
            'category',
            'subCategory',
            'branch',
            'department',
            'employee',
            'createdBy',
            'movements' => function ($q) {
                $q->orderByDesc('id');
            },
            'movements.entryUser',
            'movements.entryBranch',
            'movements.entryDepartment',
            'movements.toUser',
            'movements.toBranch',
            'movements.toDepartment',
        ])->findOrFail($request->file_id);


        $closures = FileClosure::with([
            'closedBy.employee.branch',
            'closedBy.employee.department',
        ])->where('file_id', $file->id)
        ->orderByDesc('id')
        ->get();

        // Get reopens with the related file movement data
        $reopens = FileReopen::with('reopenedBy')
            ->where('file_id', $file->id)
            ->get()
            ->map(function($reopen) {
                // Find the file movement created at the same time or right after the reopen
                $movement = FileMovement::with(['toBranch', 'toDepartment', 'toUser'])
                    ->where('file_id', $reopen->file_id)
                    ->where('status', 4) // OPEN 
                    ->where('created_at', '>=', $reopen->created_at)
                    ->orderByDesc('id')
                    ->first();
                
                $reopen->movement = $movement;
                return $reopen;
            });

        return view('report.file_history', compact(
            'files',
            'file',
            'closures',
            'reopens'
        ));
    }

}
