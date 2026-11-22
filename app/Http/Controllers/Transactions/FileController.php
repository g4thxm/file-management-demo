<?php

namespace App\Http\Controllers\Transactions;

use App\Http\Controllers\Controller;

use App\Models\Department;
use App\Models\File;
use App\Models\SubCategory;
use App\Models\User;
use App\Models\Category;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\FileMovement;


use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File as FileFacade;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class FileController extends Controller
{
    // public function index(){
    //     $files = File::with(['creator', 'employee'])->orderBy('created_at', 'desc')->get();
    //     return view('filelist.index',compact('files'));
    // }

    public function create(){
        
        return view('transactions.filecreate',[
            'categories' => Category::orderBy('name')->get(),
            'subCategories' => SubCategory::orderBy('name')->get(),
            'branches' => Branch::orderby('name')->get(),
            'departments' => Department::orderby('name')->get(),
            'employees' => Employee::orderby('name')->get(),
        ]);
    }

    public function store(Request $request)
{   

    $validated = $request->validate([
        'rfid_code'            => 'required|string|max:100|unique:files,rfid_code',
        'start_date'           => 'required|date',
        'due_date'             => 'required|date|after_or_equal:start_date',
        'official_file_number' => 'required|string|max:255|unique:files,official_file_number',
        'file_name'            => 'required|string|max:255',
        'subject'              => 'required|string|max:255',
        'priority'             => 'required|string|max:50',
        'category_id'          => 'required|exists:categories,id',
        'sub_category_id'      => 'required|exists:sub_categories,id',
        'branch_id'            => 'required|exists:branches,id',
        'department_id'        => 'required|exists:departments,id',
        'employee_id'          => 'required|exists:employees,id',
        'remarks'              => 'nullable|string',
        'details'              => 'nullable|string',
        'upload_file'          => 'nullable|file|max:2048',
    ]);

    
    $filePath = null;
    if ($request->hasFile('upload_file')) {
        $filePath = $request->file('upload_file')->store('files', 'public');
    }

   DB::transaction(function () use ($validated, $filePath) {

    // Files table
    $file = File::create([
        'rfid_code'            => $validated['rfid_code'],
        'start_date'           => $validated['start_date'],
        'due_date'             => $validated['due_date'],
        'official_file_number' => $validated['official_file_number'],
        'file_name'            => $validated['file_name'],
        'subject'              => $validated['subject'],
        'details'              => $validated['details'] ?? null, 
        'remarks'              => $validated['remarks'] ?? null,
        'priority'             => $validated['priority'],
        'category_id'          => $validated['category_id'],
        'sub_category_id'      => $validated['sub_category_id'],
        'branch_id'            => $validated['branch_id'],
        'department_id'        => $validated['department_id'],
        'employee_id'          => $validated['employee_id'],
        'upload_file'          => $filePath,
        'created_by'           => auth()->id(),
        'status'               => 0,
    ]);

    // get creator + employee details
    $creatorUser = $file->createdBy;          // User model
    $creatorEmployee = $creatorUser->employee; // Employee model

    // File movements table
    FileMovement::create([
        'file_id' => $file->id,

        // entry
        'entry_user_id' => auth()->user()->employee_id,  // employee ID of the logged-in user
        'entry_branch_id'       => $creatorEmployee->branch_id,
        'entry_department_id'   => $creatorEmployee->department_id,

        // assigned to employee
        'to_user_id'            => $validated['employee_id'],
        'to_branch_id'          => $validated['branch_id'],
        'to_department_id'      => $validated['department_id'],

        'subject'               => $validated['subject'],
        'remarks'               => $validated['remarks'] ?? null,
        'accessibility'         => 0,
        'status'                => 0, // not received
        'created_at'            => now(),
    ]);
});

    $fileNO = $validated['official_file_number'];

    // Check checkbox from form
    $useTextPrn = $request->has('use_text_prn');

    $prnDir = public_path('prn');
    if (!FileFacade::exists($prnDir)) {
        FileFacade::makeDirectory($prnDir, 0755, true);
    }

    $prnFilePath = $prnDir . "/file_$fileNO.prn";

    if ($useTextPrn) {
        // CHECKBOX CHECKED = FULL LABEL.PRN WITH FILE NUMBER
        $templatePath = public_path('template/label.prn');
        
        if (!FileFacade::exists($templatePath)) {
            return redirect()->back()
                ->with('error', 'Label template not found!')
                ->withInput();
        }
        
        $template = FileFacade::get($templatePath);
        // Replace placeholder with actual file number
        $filePrn = str_replace('{{FILE_NO}}', $fileNO, $template);

    } else {
        // CHECKBOX UNCHECKED = SIMPLE TEXT FOR QR GENERATION
        $filePrn = "
^XA
^FO350,80^A0N,60,60^FD{$fileNO}^FS
^XZ
";
    }

    // Write final PRN file
    FileFacade::put($prnFilePath, $filePrn);

    // Generate QR Code image for display (if checkbox unchecked)
    if (!$useTextPrn) {
        try {
            $qrDir = public_path('qrcodes');
            if (!FileFacade::exists($qrDir)) {
                FileFacade::makeDirectory($qrDir, 0755, true);
            }
            
            // Generate QR code as SVG
            $qrCodeSvg = QrCode::size(300)
                ->margin(2)
                ->generate($fileNO);
            
            $qrCodePathSvg = $qrDir . "/file_$fileNO.svg";
            FileFacade::put($qrCodePathSvg, $qrCodeSvg);
            
        } catch (\Exception $e) {
            Log::error('QR Code generation failed: ' . $e->getMessage());
        }
    }

    // Pass checkbox state to session for JavaScript
    return redirect()->route('filecreate')
        ->with('success', 'File created successfully!')
        ->with('print_prn_file', $fileNO)
        ->with('use_text_prn', $useTextPrn);
}
    public function show($id)
    {
        $file = File::with([
            'creator',
            'employee',
            'category',
            'subCategory',
            'branch',
            'department'
        ])->findOrFail($id);

        return view('filelist.show',compact('file'));

    }

  public function smartSearch($value)
    {
        $file = File::select('id', 'official_file_number', 'rfid_code', 'status')
            ->where('rfid_code', $value)
            ->orWhere('official_file_number', $value)
            ->first();

        if (!$file) {
            return response()->json([
                'success' => false,
                'message' => 'File not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'file' => $file
        ]);
    }




//     public function edit($id){
//         $files = File::findOrFail($id);

//         return view('filelist.edit',[
//             'file' => $files,
//             'categories' => Category::orderBy('name')->get(),
//             'sub_categories' => SubCategory::orderBy('name')->get(),
//             'branches' => Branch::orderby('name')->get(),
//             'departments' => Department::orderby('name')->get(),
//             'employees' => Employee::orderby('name')->get(),
//         ]);
//     }

//     public function update(Request $request, $id){
//         $request->validate([
//             'start_date' =>'required|date',
//             'due_date' => 'required|date|after_or_equal:start_date',
//             'file_name' => 'required|string|max:255',
//             'subject' => 'required|string|max:255',
//             'priority' => 'required|string|max:50',
//             'category_id' =>'required|existes:categories,id',
//             'sun_category_id' => 'required|exists:sub_categories,id',
//             'branch_id' => 'reqired|exists:branches,id',
//             'depatment_id' => 'required|exists:departments,id',
//             'employee_id' => 'required|exists:employees,id',
//             'upload_file' => 'nullable|file|max:2048|mimes:pdf,doc,docx,jpg,png',
//             'details' => 'nullable|string',
//             'remarks' => 'nullable|string',        
//         ]);

//         $file = File::findOrFail($id);

//         if($request->hasfiles('upload_file')){
//             $file->upload_file = $request->file('upload_file')->store('files','public');
//         }

//         $file->update($request->except('upload_file'));

//         Alert::toast('File update successfully','success')->autoclose(3000);
//         return redirect()->route('files.index');
//     }

//     public function destroy($id){
//         File::findOrFail($id)->delete();

//         Alert::toast('File deleted successfully','success')->autoclose(3000);
//         return redirect()->route('files.index');
//     }

}
