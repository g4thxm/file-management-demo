<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use App\Models\File;
use App\Models\FileMovement;
use App\Models\FileReopen;

class DashboardController extends Controller
{
    /**
     * Show dashboard view
     */
    public function index()
    {
        $branches = Branch::orderBy('name')->get();
        return view('dashboard', compact('branches'));
    }

    /**
     * Dashboard data (AJAX)
     */
    public function getData(Request $request)
    {
        $user = auth()->user();

        // 🔐 BRANCH LOGIC
        if ($user->user_type == 1) {
            // ADMIN
            $branchId = $request->branch_id;
        } else {
            // USER → branch from employee
            $branchId = optional($user->employee)->branch_id;
        }

        // Department is selectable for both
        $deptId = $request->department_id;


        /*
        |--------------------------------------------------------------------------
        | BASE FILE QUERY (USED BY BOTH ADMIN & USER)
        |--------------------------------------------------------------------------
        */
        $filesQuery = File::query();

        if ($branchId) {
            $filesQuery->where('branch_id', $branchId);
        }

        if ($deptId) {
            $filesQuery->where('department_id', $deptId);
        }

        /*
        |--------------------------------------------------------------------------
        | FILE STATS (COMMON)
        |--------------------------------------------------------------------------
        */
        $totalFiles = (clone $filesQuery)->count();

        $closedFiles = (clone $filesQuery)
            ->where('status', 1) // ✅ 1 = closed
            ->count();

        $reopenedFiles = FileReopen::whereHas('file', function ($q) use ($branchId, $deptId) {
            if ($branchId) {
                $q->where('branch_id', $branchId);
            }
            if ($deptId) {
                $q->where('department_id', $deptId);
            }
        })->count();

        $movements = FileMovement::whereHas('file', function ($q) use ($branchId, $deptId) {
            if ($branchId) {
                $q->where('branch_id', $branchId);
            }
            if ($deptId) {
                $q->where('department_id', $deptId);
            }
        })->count();

        /*
        |--------------------------------------------------------------------------
        | ADMIN DASHBOARD
        |--------------------------------------------------------------------------
        */
        if ($user->user_type == 1) {
            return response()->json([
                'mode' => 'admin',

                // Master stats
                'branches'    => Branch::count(),
                'departments' => Department::count(),
                'employees'   => Employee::count(),
                'users'       => User::count(),
                'files'       => File::count(),

                // File stats (filtered)
                'total_files'    => $totalFiles,
                'closed_files'   => $closedFiles,
                'reopened_files' => $reopenedFiles,
                'movements'      => $movements,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | NORMAL USER DASHBOARD
        |--------------------------------------------------------------------------
        */
        return response()->json([
            'mode' => 'user',

            'total_files'    => $totalFiles,
            'closed_files'   => $closedFiles,
            'reopened_files' => $reopenedFiles,
            'movements'      => $movements,
        ]);
    }

    /**
     * Get departments by branch
     */
   public function getDepartments($branchId = null)

    {
        $user = auth()->user();

        // 🔐 USER → FORCE BRANCH FROM EMPLOYEE
        if ($user->user_type != 1) {
            $branchId = optional($user->employee)->branch_id;
        }

        // Safety check
        if (!$branchId) {
            return collect();
        }

        return Department::where('branch_id', $branchId)
            ->orderBy('name')
            ->pluck('name', 'id');
    }
}
