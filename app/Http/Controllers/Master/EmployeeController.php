<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Branch;
use App\Models\Department;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\Validator;



class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::with(['branch','department'])
            ->orderBy('name')
            ->paginate(10);

        $i = 0;
        return view('master.employee.index', compact('employees','i'));
    }

    public function create()
    {
        $branches = Branch::orderBy('name')->get();
        $departments = Department::orderBy('name')->get();

        return view('master.employee.create', compact('branches','departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'branch_id'     => 'required|exists:branches,id',
            'department_id' => 'required|exists:departments,id',
            'name'          => 'required|string|max:100',
            'designation'   => 'required|string|max:100',
            'email'         => 'required|email|unique:employees,email',
        ]);

        Employee::create($request->only(
            'branch_id',
            'department_id',
            'name',
            'designation',
            'email',
            'description'
        ));

        Alert::toast('Employee added successfully','success');
        return redirect()->route('employee.index');
    }

    public function edit(Employee $employee)
    {
        $branches = Branch::orderBy('name')->get();
        $departments = Department::orderBy('name')->get();

        return view('master.employee.create', compact(
            'employee','branches','departments'
        ));
    }

    public function update(Request $request, Employee $employee)
    {
        $request->validate([
            'branch_id'     => 'required|exists:branches,id',
            'department_id' => 'required|exists:departments,id',
            'name'          => 'required|string|max:100',
            'designation'   => 'required|string|max:100',
            'email'         => 'required|email|unique:employees,email,'.$employee->id,
        ]);

        $employee->update($request->only(
            'branch_id',
            'department_id',
            'name',
            'designation',
            'email',
            'description'
        ));

        Alert::toast('Employee updated successfully','success');
        return redirect()->route('employee.index');
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();
        Alert::toast('Employee deleted successfully','success');
        return redirect()->route('employee.index');
    }

    public function downloadFormat()
    {
        $fileName = 'employee_format.csv';

        $headers = [
            "Content-Type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
        ];

        $columns = [
            'name',
            'designation',
            'branch_id',
            'department_id',
            'email'
        ];

        $callback = function () use ($columns) {
            $file = fopen('php://output', 'w');

            // Header row
            fputcsv($file, $columns);

            // Sample row
            fputcsv($file, [
                'John Doe',
                'Manager',
                1,
                2,
                'john@example.com'
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }


    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt'
        ]);

        $file = fopen($request->file('file')->getRealPath(), 'r');

        // Skip header row
        fgetcsv($file);

        $inserted = 0;
        $skipped  = 0;

        while (($row = fgetcsv($file)) !== false) {

            // Map CSV columns to field names
            $data = [
                'name'          => $row[0] ?? null,
                'designation'   => $row[1] ?? null,
                'branch_id'     => $row[2] ?? null,
                'department_id' => $row[3] ?? null,
            ];

            
            $validator = Validator::make($data, [
                'branch_id'     => 'required|exists:branches,id',
                'department_id' => 'required|exists:departments,id',
                'name'          => 'required|string|max:100',
                'designation'   => 'required|string|max:100',
            ]);

            if ($validator->fails()) {
                $skipped++;
                continue;
            }

            // Optional email handling
            $email = $row[4] ?? null;

            if ($email && Employee::where('email', $email)->exists()) {
                $skipped++;
                continue;
            }

            Employee::create([
                'name'          => $data['name'],
                'designation'   => $data['designation'],
                'branch_id'     => $data['branch_id'],
                'department_id' => $data['department_id'],
                'email'         => $email,
            ]);

            $inserted++;
        }

        fclose($file);

        Alert::toast(
            "Imported: $inserted | Skipped: $skipped",
            'success'
        );

        return redirect()->route('employee.index');
    }

    public function export()
    {
        $fileName = 'employees.csv';

        $headers = [
            "Content-Type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
        ];

         $callback = function () {
            $file = fopen('php://output', 'w');

            // CSV Header
            fputcsv($file, [
                'Name',
                'Designation',
                'Branch',
                'Department',
                'Email'
            ]);

            $employees = Employee::with(['branch', 'department'])->get();

            foreach ($employees as $emp) {
                fputcsv($file, [
                    $emp->name,
                    $emp->designation,
                    $emp->branch->name ?? '',
                    $emp->department->name ?? '',
                    $emp->email,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function getDepartments($branchId)
    {
        return Department::where('branch_id', $branchId)
            ->orderBy('name')
            ->pluck('name', 'id');
    }


}




