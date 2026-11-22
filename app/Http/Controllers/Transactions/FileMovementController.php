<?php

namespace App\Http\Controllers\Transactions;

use App\Http\Controllers\Controller;
use App\Models\File;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\FileMovement;
use App\Models\FileClosure;
use App\Models\User;
use App\Mail\FileTransferredMail;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FileMovementController extends Controller
{
    /* =========================================================
       FILE MOVEMENT LIST (ONLY CURRENT HOLDER, OPEN FILES)
    ========================================================== */
   public function index()
    {
        $employeeId = auth()->user()->employee_id;

        $files = File::where('status', 0)
            ->whereHas('movements', function ($q) use ($employeeId) {
                $q->where('to_user_id', $employeeId)
                ->whereHas('toUser', function($userQuery) {
                    $userQuery->whereNull('deleted_at'); // Only active employees
                })
                ->whereRaw('file_movements.id = (
                        SELECT id FROM file_movements fm2
                        WHERE fm2.file_id = file_movements.file_id
                        ORDER BY fm2.created_at DESC, fm2.id DESC
                        LIMIT 1
                )');
            })
            ->distinct()
            ->get(['id', 'official_file_number']);

        $branches = Branch::orderBy('name')->get(['id', 'name']);

        return view('transactions.filemovement', compact('files', 'branches'));
    }

    /* =========================================================
       FETCH FILE DETAILS (STRICT ACCESS CHECK)
    ========================================================== */
    public function fetch($fileId)
    {
        $employeeId = auth()->user()->employee_id;

        $latestMovement = FileMovement::where('file_id', $fileId)
            ->orderByDesc('created_at')->orderByDesc('id')
            ->first();

        if (!$latestMovement || $latestMovement->to_user_id != $employeeId) {
            return response()->json([
                'error' => 'You do not have access to this file'
            ], 403);
        }

        $file = File::with(['branch', 'department'])->findOrFail($fileId);

        $movements = FileMovement::with([
                'entryUser',
                'entryBranch',
                'entryDepartment',
                'toUser',
                'toBranch',
                'toDepartment'
            ])
            ->where('file_id', $fileId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'file' => [
                'id' => $file->id,
                'official_file_number' => $file->official_file_number,
                'file_name' => $file->file_name,
                'subject' => $file->subject,
                'start_date' => $file->start_date?->format('d M Y') ?? '-',
                'due_date' => $file->due_date?->format('d M Y') ?? '-',
                'priority' => $file->priority,
                'status' => $file->status,
                'branch' => $file->branch,
                'department' => $file->department,
                // Add any other fields you need
            ],
            'movements' => $movements
        ]);
    }

    /* =========================================================
       UPDATE STATUS / TRANSFER
    ========================================================== */
    public function updateStatus(Request $request)
    {
        $request->validate([
            'file_id' => 'required|exists:file_movements,file_id',
            'status'  => 'required|integer',
            'remarks' => 'nullable|string',
            'to_branch_id'     => 'nullable|exists:branches,id',
            'to_department_id' => 'nullable|exists:departments,id',
            'to_user_id'       => 'nullable|exists:employees,id',
        ]);

        $latestMovement = FileMovement::where('file_id', $request->file_id)
            ->orderByDesc('created_at')->orderByDesc('id')
            ->firstOrFail();

        // only current holder can update
        if ($latestMovement->to_user_id != auth()->user()->employee_id) {
            return response()->json([
                'error' => 'Unauthorized action'
            ], 403);
        }

        $data = [
            'file_id' => $request->file_id,
            'status'  => $request->status,
            'remarks' => $request->remarks,
            'entry_user_id' => auth()->user()->employee_id,
            'entry_branch_id' => auth()->user()->employee->branch_id,
            'entry_department_id' => auth()->user()->employee->department_id,
        ];

        if ($request->status == 4) {
            // TRANSFER
            $data['to_branch_id']     = $request->to_branch_id;
            $data['to_department_id'] = $request->to_department_id;
            $data['to_user_id']       = $request->to_user_id;
            $data['accessibility']    = 1;
        } else {
            // SAME HOLDER
            $data['to_branch_id']     = $latestMovement->to_branch_id;
            $data['to_department_id'] = $latestMovement->to_department_id;
            $data['to_user_id']       = $latestMovement->to_user_id;
            $data['accessibility']    = 0;
        }

        FileMovement::create($data);

        //  $movement = FileMovement::create($data);


        /*
        |--------------------------------------------------------------------------
        | SEND EMAIL ONLY ON TRANSFER (STATUS = 4)
        |--------------------------------------------------------------------------
        */
       if ($request->status == 4 && $request->to_user_id) {

    // to_user_id = employee_id
        $toEmployee = Employee::find($request->to_user_id);

        if ($toEmployee && $toEmployee->user && $toEmployee->user->email) {

            $file = File::find($request->file_id);

            $subject = 'File transferred: ' . $file->official_file_number;

            $body = "Hello {$toEmployee->name},\n\n"
                . "A file has been transferred to you.\n\n"
                . "File No: {$file->official_file_number}\n"
                . "Transferred By: " . auth()->user()->name . "\n"
                . "Remarks: " . ($request->remarks ?? '-') . "\n\n"
                . "Please log in to the system to take action.";

            Mail::raw($body, function ($message) use ($toEmployee, $subject) {
                $message->to($toEmployee->user->email)
                        ->subject($subject);
            });
        }
    }


        return response()->json([
            'success' => true,
            'message' => 'File movement updated successfully'
        ]);

    }

    /* =========================================================
       FILE CLOSURE
    ========================================================== */
    public function closeFile(Request $request)
{
    $request->validate([
        'file_id' => 'required|exists:files,id',
        'remarks' => 'nullable|string'
    ]);

    DB::beginTransaction();

    try {

        $latestMovement = FileMovement::where('file_id', $request->file_id)
            ->orderByDesc('created_at')->orderByDesc('id')
            ->first();

        if (!$latestMovement || $latestMovement->to_user_id != auth()->user()->employee_id) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // ✅ UPDATED: Check if file is currently closed (status = 1)
        // instead of checking if closure record exists
        $file = File::findOrFail($request->file_id);
        
        if ($file->status == 1) {
            return response()->json([
                'status' => false,
                'message' => 'File is already closed'
            ], 400);
        }

        // Create new closure record (keeping history)
        FileClosure::create([
            'file_id'     => $request->file_id,
            'closed_by'   => auth()->id(),
            'closed_date' => now()->toDateString(),
            'closed_time' => now()->toTimeString(),
            'remarks'     => $request->remarks,
        ]);

        FileMovement::create([
            'file_id' => $request->file_id,
            'entry_user_id' => auth()->user()->employee_id,
            'entry_branch_id' => auth()->user()->employee->branch_id,
            'entry_department_id' => auth()->user()->employee->department_id,
            'status' => 5, // Completed
            'remarks' => $request->remarks,
            'accessibility' => 0
        ]);

        File::where('id', $request->file_id)->update(['status' => 1]); // Closed

        DB::commit();

        return response()->json([
            'status' => true,
            'message' => 'File closed successfully'
        ]);

    } catch (\Exception $e) {
        DB::rollBack();

        return response()->json([
            'status' => false,
            'message' => 'Error closing file: ' . $e->getMessage()
        ], 500);
    }
}
    /* =========================================================
       AJAX HELPERS
    ========================================================== */
    public function getDepartments($branchId)
    {
        return Department::where('branch_id', $branchId)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function getEmployees($departmentId)
    {
        return Employee::where('department_id', $departmentId)
        ->whereNull('deleted_at') // Exclude soft deleted
        ->orderBy('name')
        ->get(['id', 'name']);
    }

    /* =========================================================
       RFID Search
    ========================================================== */
    public function search(Request $request)
    {
        $request->validate([
            'file_number' => 'required|string'
        ]);

        $employeeId = auth()->user()->employee_id;
        $value = $request->file_number;

        $file = File::where(function ($q) use ($value) {
                $q->where('rfid_code', $value)
                ->orWhere('official_file_number', $value);
            })
            ->where('status', 0) // ONLY OPEN FILES
            ->whereHas('movements', function ($q) use ($employeeId) {
                $q->where('to_user_id', $employeeId)
                ->whereRaw('file_movements.id = (
                        SELECT id FROM file_movements fm2
                        WHERE fm2.file_id = file_movements.file_id
                        ORDER BY fm2.created_at DESC, fm2.id DESC
                        LIMIT 1
                )');
            })
            ->first();

        if (!$file) {
            return response()->json([
                'message' => 'This file is not assigned to you'
            ], 404);
        }

        return response()->json([
            'file_id' => $file->id
        ]);
    }

    

}
