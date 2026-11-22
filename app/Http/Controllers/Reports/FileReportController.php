<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\File;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Barryvdh\DomPDF\Facade\Pdf;

class FileReportController extends Controller
{
    /**
     * Show report form
     */
    public function form()
    {
        return view('report.file_report');
    }

    /**
     * Handle View, CSV Export and PDF Export
     */
    public function view(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'from_date' => 'required|date',
                'to_date'   => 'required|date|after_or_equal:from_date',
            ],
            [
                'from_date.required' => 'From Date is required',
                'to_date.required'   => 'To Date is required',
                'to_date.after_or_equal' =>
                    'To Date must be greater than or equal to From Date',
            ]
        );

        if ($validator->fails()) {
            Alert::error('Validation Error', $validator->errors()->first());

            return redirect()
                ->route('file_report.form')
                ->withErrors($validator)
                ->withInput();
        }

        $files = File::with([
                'category',
                'subCategory',
                'branch',
                'department',
                'employee',
                'createdBy'
            ])
            ->whereDate('start_date', '>=', $request->from_date)
            ->whereDate('start_date', '<=', $request->to_date)
            ->orderBy('start_date')
            ->get();

        // CSV export
        if ($request->action === 'csv') {
            Alert::success('Export Started', 'Your CSV file is downloading');
            return $this->exportCsv($files, $request);
        }

        // PDF export (like excel but PDF)
        if ($request->action === 'pdf') {
            Alert::success('Export Started', 'Your PDF file is downloading');
            return $this->exportPdf($files, $request);
        }

        // normal view
        return view('report.file_report_detailed', compact('files'));
    }

    /**
     * CSV export
     */
    private function exportCsv($files, Request $request): StreamedResponse
    {
        $fileName = 'file_report_' .
            $request->from_date . '_to_' . $request->to_date . '.csv';

        return response()->stream(function () use ($files) {

            $handle = fopen('php://output', 'w');

            // header row
            fputcsv($handle, [
                'Official File No',
                'File Name',
                'Subject',
                'Priority',
                'Category',
                'Sub Category',
                'Branch',
                'Department',
                'Assigned Employee',
                'Created By',
                'Start Date',
                'Due Date',
                'Status',
            ]);

            foreach ($files as $file) {
                fputcsv($handle, [
                    $file->official_file_number,
                    $file->file_name,
                    $file->subject,
                    ucfirst($file->priority),
                    $file->category->name ?? '',
                    $file->subCategory->name ?? '',
                    $file->branch->name ?? '',
                    $file->department->name ?? '',
                    $file->employee->name ?? '',
                    $file->createdBy->name ?? '',
                    $file->start_date ? "\t" . \Carbon\Carbon::parse($file->start_date)->format('d-m-Y') : '',
                    $file->due_date ? "\t" . \Carbon\Carbon::parse($file->due_date)->format('d-m-Y') : '',
                    $file->status == 1 ? 'Closed' : 'Open',
                ]);
            }

            fclose($handle);

        }, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
        ]);
    }

    /**
     * PDF export
     */
    private function exportPdf($files, Request $request)
    {
        $fileName = 'file_report_' .
            $request->from_date . '_to_' . $request->to_date . '.pdf';

        $pdf = Pdf::loadView('report.file_report_pdf', [
                'files' => $files,
                'from'  => $request->from_date,
                'to'    => $request->to_date,
            ])
            ->setPaper('a4', 'landscape');

        return $pdf->download($fileName);
    }

    /**
     * View attached PDF (single file)
     */
    public function viewPdf($id)
    {
        $file = File::findOrFail($id);

        if (!$file->upload_file) {
            abort(404, 'No file attached');
        }

        $filePath = storage_path('app/public/' . $file->upload_file);

        if (!file_exists($filePath)) {
            abort(404, 'File not found in storage');
        }

        $displayName = $file->official_file_number . ' - ' . $file->file_name . '.pdf';

        return response()->file($filePath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $displayName . '"'
        ]);
    }

    /**
     * Download attached PDF (single file)
     */
    public function downloadPdf($id)
    {
        $file = File::findOrFail($id);

        if (!$file->upload_file) {
            abort(404, 'No file attached');
        }

        $filePath = storage_path('app/public/' . $file->upload_file);

        if (!file_exists($filePath)) {
            abort(404, 'File not found in storage');
        }

        $downloadName = $file->official_file_number . ' - ' . $file->file_name . '.pdf';

        return response()->download($filePath, $downloadName, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $downloadName . '"'
        ]);
    }
}
