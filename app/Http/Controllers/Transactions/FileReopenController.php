<?php

namespace App\Http\Controllers\Transactions;

use App\Http\Controllers\Controller;
use App\Models\File;
use App\Models\FileMovement;
use App\Models\FileReopen;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FileReopenController extends Controller
{
    /**
     * Show reopen screen
     */
    public function index()
    {
        $employeeId = auth()->user();

        // ONLY closed files
        $files = File::where('status', 1)
            ->orderBy('official_file_number')
            ->get(['id', 'official_file_number']);

        $branches = Branch::orderBy('name')->get(['id', 'name']);

        return view('transactions.filereopen', compact('files', 'branches'));
    }

    /**
     * Fetch closed file details + movement history
     */
   public function fetch(File $file)
    {
        // Only CLOSED files
        if ($file->status != 1) {
            return response()->json([
                'error' => 'File is not closed'
            ], 400);
        }

        $file->load(['branch', 'department']);

        $movements = FileMovement::with([
            'entryUser',
            'entryBranch',
            'entryDepartment',
            'toUser',
            'toBranch',
            'toDepartment'
        ])
        ->where('file_id', $file->id)
        ->orderBy('created_at', 'desc')
        ->get();

        return response()->json([
            'file' => $file,
            'movements' => $movements
        ]);
    }


    /**
     * Reopen file
     */
    public function reopen(Request $request)
    {
        $request->validate([
            'file_id' => 'required|exists:files,id',
            'to_branch_id' => 'required|exists:branches,id',
            'to_department_id' => 'required|exists:departments,id',
            'to_user_id' => 'required|exists:employees,id',
            'remarks' => 'nullable|string'
        ]);

        DB::beginTransaction();

        try {
            // 1️⃣ Insert into file_reopens
            FileReopen::create([
                'file_id' => $request->file_id,
                'reopened_by' => auth()->user()->employee_id,
                'reopened_date' => now()->toDateString(),
                'reopened_time' => now()->toTimeString(),
            ]);

            // 2️⃣ Insert NEW movement (OPEN)
            FileMovement::create([
                'file_id' => $request->file_id,
                'entry_user_id' => auth()->user()->employee_id,
                'entry_branch_id' => auth()->user()->employee->branch_id,
                'entry_department_id' => auth()->user()->employee->department_id,
                'to_branch_id' => $request->to_branch_id,
                'to_department_id' => $request->to_department_id,
                'to_user_id' => $request->to_user_id,
                'status' => 4, // OPEN
                'accessibility' => 1,
                'remarks' => $request->remarks
            ]);

            // 3️⃣ Update files table
            File::where('id', $request->file_id)->update([
                'status' => 0 // OPEN
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'File reopened successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Failed to reopen file'
            ], 500);
        }
    }

    /**
     * Branch → Departments
     */
    public function departments($branchId)
    {
        return Department::where('branch_id', $branchId)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    /**
     * Department → Employees
     */
    public function employees($departmentId)
    {
        return Employee::where('department_id', $departmentId)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    
}
