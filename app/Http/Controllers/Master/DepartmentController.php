<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Branch;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\Validator;


class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::with('branch')
            ->orderBy('name')
            ->paginate(10);

        $i = 0;
        return view('master.department.index', compact('departments', 'i'));
    }

    public function create()
    {
        $branches = Branch::orderBy('name')->get();
        return view('master.department.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'name'      => 'required|string|max:100',
            'prefix'    => 'required|string|size:2',
        ]);

        Department::create([
            'branch_id'  => $request->branch_id,
            'name'       => $request->name,
            'prefix'     => strtoupper($request->prefix),
            'description'=> $request->description,
        ]);

        Alert::toast('Department added successfully', 'success');
        return redirect()->route('department.index');
    }

    public function edit(Department $department)
    {
        $branches = Branch::orderBy('name')->get();
        return view('master.department.create', compact('department', 'branches'));
    }

    public function update(Request $request, Department $department)
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'name'      => 'required|string|max:100',
            'prefix'    => 'required|string|size:2',
        ]);

        $department->update([
            'branch_id'  => $request->branch_id,
            'name'       => $request->name,
            'prefix'     => strtoupper($request->prefix),
            'description'=> $request->description,
        ]);

        Alert::toast('Department updated successfully', 'success');
        return redirect()->route('department.index');
    }

    public function destroy(Department $department)
    {
        $department->delete();
        Alert::toast('Department deleted successfully', 'success');
        return redirect()->route('department.index');
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|mimes:csv,txt']);

        $file = fopen($request->file('file')->getRealPath(), 'r');
        $header = fgetcsv($file); // read header

        $inserted = $skipped = 0;

        while (($row = fgetcsv($file)) !== false) {

            $row = array_combine($header, $row);

            $data = [
                'branch_id'   => $row['branch_id'] ?? null,
                'name'        => $row['name'] ?? null,
                'prefix'      => strtoupper($row['prefix'] ?? ''),
                'description' => $row['description'] ?? null,
            ];

            $validator = Validator::make($data, [
                'branch_id'   => 'required|exists:branches,id',
                'name'        => 'required|string|max:100',
                'prefix'      => 'required|string|size:2|unique:departments,prefix',
                'description' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                $skipped++;
                continue;
            }

            // Same safe behavior as Branch
            Department::updateOrCreate(
                ['prefix' => $data['prefix']],
                $data
            );

            $inserted++;
        }

        fclose($file);

        Alert::toast("Imported: $inserted | Skipped: $skipped", 'success');
        return redirect()->route('department.index');
    }

    public function downloadFormat()
    {
        return response()->stream(function () {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'branch_id',
                'name',
                'prefix',
                'description'
            ]);

            fputcsv($file, [
                3,
                'Zeus',
                'ZA',
                'Blah'
            ]);

            fclose($file);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=department_format.csv',
        ]);
    }

    public function export()
    {
        return response()->stream(function () {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'branch_id',
                'name',
                'prefix',
                'description'
            ]);

            foreach (Department::all() as $dept) {
                fputcsv($file, [
                    $dept->branch_id,
                    $dept->name,
                    $dept->prefix,
                    $dept->description,
                ]);
            }

            fclose($file);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=departments.csv',
        ]);
    }
}
