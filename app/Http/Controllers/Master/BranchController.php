<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\Company;
use Excel;
use App\Exports\BranchExport;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\Validator;

class BranchController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        $branch = Branch::with('company')
            ->when($search, function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('Branch_Code', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate(10);

        return view('master.branchlist', [
            'master' => $branch,
            'i' => 0,
            'search' => $search,
            'perpage' => 10
        ]);
    }

    public function create()
    {
        $companies = Company::orderBy('name')->get();
        return view('master.branchcreate', compact('companies'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_id'  => 'required|exists:companies,id',
            'name'        => 'required',
            'Branch_Code' => 'required|unique:branches,Branch_Code',
        ]);

        Branch::create([
            'company_id'  => $request->company_id,
            'name'        => $request->name,
            'Branch_Code' => strtoupper($request->Branch_Code),
            'Address'     => $request->Address,
            'GST_No'      => $request->GST_No,
        ]);

        Alert::toast('Branch Added Successfully', 'success')->autoClose(3000);
        return redirect()->route('branch.index');
    }

    public function edit(Branch $branch)
    {
        $companies = Company::orderBy('name')->get();
        return view('master.branchcreate', compact('branch', 'companies'));
    }

    public function update(Request $request, Branch $branch)
    {
        $request->validate([
            'company_id'  => 'required|exists:companies,id',
            'name'        => 'required',
            'Branch_Code' => 'required|unique:branches,Branch_Code,' . $branch->id,
        ]);

        $branch->update([
            'company_id'  => $request->company_id,
            'name'        => $request->name,
            'Branch_Code' => strtoupper($request->Branch_Code),
            'Address'     => $request->Address,
            'GST_No'      => $request->GST_No,
        ]);

        Alert::toast('Branch Updated Successfully', 'success')->autoClose(3000);
        return redirect()->route('branch.index');
    }

    public function destroy(Branch $branch)
    {
        $branch->delete();
        Alert::toast('Branch Deleted Successfully', 'success')->autoClose(3000);
        return redirect()->route('branch.index');
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|mimes:csv,txt']);

        $file = fopen($request->file('file')->getRealPath(), 'r');

        $header = fgetcsv($file); // read header row

        $inserted = $skipped = 0;

        while (($row = fgetcsv($file)) !== false) {

            // Map header => value
            $row = array_combine($header, $row);

            $data = [
                'company_id'  => $row['company_id'] ?? null,
                'code'        => $row['code'] ?? null,
                'name'        => $row['name'] ?? null,
                'Branch_Code' => strtoupper($row['Branch_Code'] ?? ''),
                'Address'     => $row['Address'] ?? null,
                'Gst_No'      => $row['Gst_No'] ?? null,
            ];

            $validator = Validator::make($data, [
                'company_id'  => 'nullable|exists:companies,id',
                'code'        => 'nullable|string|max:10',
                'name'        => 'required|string|max:100',
                'Branch_Code' => 'required|string|max:10|unique:branches,Branch_Code',
                'Address'     => 'nullable|string|max:255',
                'Gst_No'      => 'nullable|string|max:20',
            ]);

            if ($validator->fails()) {
                $skipped++;
                continue;
            }

            Branch::create($data);
            $inserted++;
        }

        fclose($file);

        Alert::toast("Imported: $inserted | Skipped: $skipped", 'success');
        return redirect()->route('branch.index');
    }


    public function downloadFormat()
    {
        return response()->stream(function () {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'company_id',
                'code',
                'name',
                'Branch_Code',
                'Address',
                'Gst_No'
            ]);

            fputcsv($file, [
                1,
                'HO',
                'Sample Office',
                'KC',
                'Kochi, Ernakulam',
                'KCH01'
            ]);

            fclose($file);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=branch_format.csv',
        ]);
    }


    public function export()
    {
        return response()->stream(function () {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'company_id',
                'code',
                'name',
                'Branch_Code',
                'Address',
                'Gst_No'
            ]);

            foreach (Branch::all() as $branch) {
                fputcsv($file, [
                    $branch->company_id,
                    $branch->code,
                    $branch->name,
                    $branch->Branch_Code,
                    $branch->Address,
                    $branch->Gst_No,
                ]);
            }

            fclose($file);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=branches.csv',
        ]);
    }
}
