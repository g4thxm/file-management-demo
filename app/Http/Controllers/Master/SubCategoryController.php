<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\SubCategory;
use App\Models\Category;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\Validator;

class SubCategoryController extends Controller
{
    public function index()
    {
        $subCategories = SubCategory::with('category')->orderBy('name')->paginate(10);
        $i = 0;
        return view('master.subcategory.index', compact('subCategories', 'i'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('master.subcategory.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string',
        ]);

        SubCategory::create($request->only('category_id','name','description'));
       
        Alert::toast('Sub Category created successfully','success');
        return redirect()->route('subcategory.index');
    }

    public function edit(SubCategory $subcategory)
    {
        $categories = Category::orderBy('name')->get();

        return view('master.subcategory.create',compact('subcategory', 'categories'));
    }

    public function update(Request $request, SubCategory $subcategory)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string',
        ]);

        $subcategory->update($request->only(
            'category_id',
            'name',
            'description'
        ));

        Alert::toast('Sub Category updated successfully','success');
        return redirect()->route('subcategory.index');;
    }   

    public function destroy(SubCategory $subcategory)
    {
        $subcategory->delete(); 
        Alert::toast('Sub Category deleted successfully','success');
        return redirect()->route('subcategory.index');
    }

    
    public function downloadFormat()
    {
        $fileName = 'SubCategory_format.csv';

        $headers = [
            "Content-Type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
        ];

        $columns = [
            'category_id',
            'name',
            'description'
        ];

        $callback = function () use ($columns) {
            $file = fopen('php://output', 'w');

            // Header row
            fputcsv($file, $columns);

            // Sample row
            // fputcsv($file, [
            //     1,
            //     'Sub Category Name',
            //     'Sub Category Description'
            // ]);

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
                'category_id'   => $row[0] ?? null,
                'name'          => $row[1] ?? null,
                'description'   => $row[2] ?? null,
            ];

            
            $validator = Validator::make($data, [
                'category_id'   => 'required|exists:categories,id',
                'name'          => 'required|string|max:100',
                'description'   => 'nullable|string',
            ]);

            if ($validator->fails()) {
                $skipped++;
                continue;
            }

            // Optional email handling
            $email = $row[4] ?? null;

            if ($email && SubCategory::where('email', $email)->exists()) {
                $skipped++;
                continue;
            }

            SubCategory::create([
                'category_id'   => $data['category_id'],
                'name'          => $data['name'],
                'description'   => $data['description'],
            ]);

            $inserted++;
        }

        fclose($file);

        Alert::toast(
            "Imported: $inserted | Skipped: $skipped",
            'success'
        );

        return redirect()->route('subcategory.index');
    }

    public function export()
    {
        $fileName = 'subcategories.csv';

            $headers = [
                "Content-Type"        => "text/csv",
                "Content-Disposition" => "attachment; filename=$fileName",
            ];

            $callback = function () {
                $file = fopen('php://output', 'w');

                // CSV Header
                fputcsv($file, [
                    'Category ID',
                    'Name',
                    'Description',
                ]);

                $subcategories = SubCategory::with(['category'])->get();

                foreach ($subcategories as $subcategory) {
                    fputcsv($file, [
                        $subcategory->category_id,
                        $subcategory->name,
                        $subcategory->description,
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

}



