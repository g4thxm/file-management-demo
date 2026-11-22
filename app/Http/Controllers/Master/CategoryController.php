<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\Validator;
use RealRashid\SweetAlert\Facades\Alert;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = Category::orderBy('name')->paginate(10);
        $i = 0;
        return view('master.categorylist', compact('categories' , 'i'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('master.categorycreate');
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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        Category::create($request->all());

        Alert::toast('Branch Added Successfully', 'success')->autoClose(3000);
        return redirect()->route('category.index');
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
    public function edit(Category $category)
    {
        return view('master.categorycreate', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255' . $category->id,
            'description' => 'nullable|string',
        ]);

        $category->update($request->all());

        Alert::toast('Category Updated Successfully', 'success')->autoClose(3000);
        return redirect()->route('category.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Category $category)
    {
        $category->delete();
        Alert::toast('Category Deleted Successfully', 'success')->autoClose(3000);
        return redirect()->route('category.index');
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
                'name'  => $row['name'] ?? null,
                'description'       => $row['description'] ?? null,
            ];

            $validator = Validator::make($data, [
                'name'  => 'required|string|max:255',
                'description'       => 'nullable|string',
            ]);

            if ($validator->fails()) {
                $skipped++;
                continue;
            }

            Category::create($data);
            $inserted++;
        }

        fclose($file);

        Alert::toast("Imported: $inserted | Skipped: $skipped", 'success');
        return redirect()->route('category.index');
    }


    public function downloadFormat()
    {
        return response()->stream(function () {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'name',
                'description'
            ]);

            fputcsv($file, [
                'Sample Category',
                'This is a sample description for the category.'
            ]);

            fclose($file);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=category_format.csv',
        ]);
    }


    public function export()
    {
        return response()->stream(function () {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'Name',
                'Description'
            ]);

            foreach (Category::all() as $category) {
                fputcsv($file, [
                    $category->name,
                    $category->description,
                ]);
            }

            fclose($file);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=categories.csv',
        ]);
    }
}
