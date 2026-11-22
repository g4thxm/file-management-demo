<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use RealRashid\SweetAlert\Facades\Alert;


class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $companies = Company::all();

        return view('master.company.index', [
            'companies' => $companies,
            'i' => 0
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('master.company.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:10|unique:companies,code',
        ]);

        Company::create($request->only('name', 'code'));

        Alert::toast('Company Added Successfully', 'success')->autoClose(3000);
        return redirect()->route('company.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $company = Company::findOrFail($id);
        return view('master.company.edit', compact('company'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
         $company = Company::findOrFail($id);

        $request->validate([
            'code' => 'required|string|max:10|unique:companies,code,' . $company->id,
            'name' => 'required|string|max:100',
        ]);

        $company->update($request->only('code', 'name'));

        Alert::toast('Company Updated Successfully', 'success')->autoClose(3000);
        return redirect()->route('company.index');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
         Company::findOrFail($id)->delete();

         Alert::toast('Company Deleted Successfully', 'success')->autoClose(3000);
    
        return redirect()->route('company.index');

    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|mimes:csv,txt']);

        $file = fopen($request->file('file')->getRealPath(), 'r');
        fgetcsv($file); // header

        $inserted = $skipped = 0;

        while (($row = fgetcsv($file)) !== false) {

            $data = [
                'name'    => $row[0] ?? null,
                'code'    => $row[1] ?? null,
            ];

            $validator = Validator::make($data, [
                'name' => 'required|string|max:100',
                'code' => 'required|string|max:10|unique:companies,code',
            ]);

            if ($validator->fails()) {
                $skipped++;
                continue;
            }

            Company::create($data);
            $inserted++;
        }

        fclose($file);

        Alert::toast("Imported: $inserted | Skipped: $skipped", 'success');
        return redirect()->route('company.index');
    }

    public function downloadFormat()
    {
        return response()->stream(function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['name', 'code']);
            fputcsv($file, ['ABC Pvt Ltd', 'ABC123']);
            fclose($file);
        }, 200, [
            "Content-Type" => "text/csv",
            "Content-Disposition" => "attachment; filename=company_format.csv",
        ]);
    }

    public function export()
    {
        return response()->stream(function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Name', 'code']);

            foreach (Company::all() as $c) {
                fputcsv($file, [$c->name, $c->code]);
            }

            fclose($file);
        }, 200, [
            "Content-Type" => "text/csv",
            "Content-Disposition" => "attachment; filename=companies.csv",
        ]);
    }


}
