@extends('layout.master')

@push('plugin-styles')
<link rel="stylesheet" href="{{ asset('assets/plugins/dropify/css/dropify.min.css') }}">
<link href="{{ asset('vendor/sweetalert/sweetalert2.min.css') }}" rel="stylesheet">
<style>
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #e8e8e8;
    }
    
    .page-header h5 {
        margin: 0;
        color: #333;
        font-weight: 600;
    }
    
    .form-section {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 2rem;
        margin-bottom: 2rem;
    }
    
    .section-heading {
        font-size: 1rem;
        font-weight: 600;
        color: #333;
        margin: 0 0 1.5rem 0;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid #007bff;
        display: inline-block;
        width: 100%;
    }
    
    .form-group {
        margin-bottom: 1.25rem;
    }
    
    .form-group.row {
        display: flex;
        align-items: center;
    }
    
    .control-label {
        font-weight: 500;
        color: #555;
        margin-bottom: 0;
        padding-top: 0.5rem;
    }
    
    .text-required {
        color: #dc3545;
        margin-left: 2px;
    }
    
    .form-control {
        border: 2px solid #e0e0e0;
        border-radius: 6px;
        padding: 0.6rem 0.9rem;
        transition: all 0.2s;
    }
    
    .form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
    }
    
    .form-control-sm {
        padding: 0.5rem 0.75rem;
        font-size: 0.9rem;
    }
    
    select.form-control {
        cursor: pointer;
    }
    
    textarea.form-control {
        resize: vertical;
        min-height: 80px;
    }
    
    .transfer-section {
        background: #fff3cd;
        border: 1px solid #ffc107;
        border-radius: 8px;
        padding: 1.5rem;
        margin: 1.5rem 0;
    }
    
    .transfer-section .section-subheading {
        font-size: 0.95rem;
        font-weight: 600;
        color: #856404;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .file-upload-section {
        background: #f8f9fa;
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        padding: 1.5rem;
        margin: 1.5rem 0;
        text-align: center;
    }
    
    .btn-create-file {
        background: #007bff;
        color: white;
        border: none;
        padding: 0.75rem 2rem;
        border-radius: 6px;
        font-weight: 600;
        font-size: 1rem;
        transition: all 0.2s;
    }
    
    .btn-create-file:hover {
        background: #0056b3;
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0, 123, 255, 0.3);
    }
    
    .btn-clear {
        background: #6c757d;
        color: white;
        border: none;
        padding: 0.75rem 2rem;
        border-radius: 6px;
        font-weight: 600;
        font-size: 1rem;
        transition: all 0.2s;
    }
    
    .btn-clear:hover {
        background: #5a6268;
        color: white;
    }
    
    .button-group {
        display: flex;
        gap: 1rem;
        padding-top: 1rem;
        margin-top: 1.5rem;
        border-top: 1px solid #e0e0e0;
    }
    
    .info-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.3rem 0.6rem;
        background: #e3f2fd;
        border: 1px solid #90caf9;
        border-radius: 4px;
        font-size: 0.75rem;
        color: #1976d2;
        margin-left: 0.5rem;
    }

    /* Tooltip container */
    .info-tooltip {
        position: relative;
        cursor: pointer;
    }

    /* Tooltip text */
    .info-tooltip::after {
        content: "PDF, DOC, DOCX, JPG, PNG";
        position: absolute;
        bottom: 130%;
        left: 50%;
        transform: translateX(-50%);
        background: #333;
        color: #fff;
        padding: 6px 10px;
        border-radius: 4px;
        font-size: 0.75rem;
        white-space: nowrap;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.2s ease-in-out;
        z-index: 999;
    }

    /* Tooltip arrow */
    .info-tooltip::before {
        content: "";
        position: absolute;
        bottom: 115%;
        left: 50%;
        transform: translateX(-50%);
        border-width: 5px;
        border-style: solid;
        border-color: #333 transparent transparent transparent;
        opacity: 0;
        transition: opacity 0.2s ease-in-out;
    }

    /* Show on hover */
    .info-tooltip:hover::after,
    .info-tooltip:hover::before {
        opacity: 1;
    }

    
    .priority-badge {
        display: inline-block;
        padding: 0.25rem 0.6rem;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 600;
        margin-left: 0.5rem;
    }
    
    .priority-low { background: #d4edda; color: #155724; }
    .priority-medium { background: #fff3cd; color: #856404; }
    .priority-high { background: #f8d7da; color: #721c24; }
    .priority-urgent { background: #f5c6cb; color: #721c24; }
    
    .dropify-wrapper {
        border: 2px dashed #dee2e6;
        border-radius: 6px;
        transition: all 0.2s;
    }
    
    .dropify-wrapper:hover {
        border-color: #007bff;
        background: #f8f9fa;
    }
</style>
@endpush

@section('content')
@include('messages')

<div class="card">
    <div class="card-body">
        
        <div class="page-header">
            <h5>File Creation</h5>
        </div>

        <div class="row">
            <div class="col-lg-8 col-xl-7">
                <form action="{{ route('filestore') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    {{-- Basic Information --}}
                    <div class="form-section">
                        <div class="section-heading">Basic Information</div>

                        <div class="form-group row">
                            <label class="col-sm-4 control-label">
                                Start Date <span class="text-required">*</span>
                            </label>
                            <div class="col-sm-8">
                                <input type="date"
                                    name="start_date"
                                    class="form-control form-control-sm"
                                    value="{{ old('start_date', date('Y-m-d')) }}"
                                    required>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-4 control-label">
                                Due Date <span class="text-required">*</span>
                            </label>
                            <div class="col-sm-8">
                                <input type="date"
                                    name="due_date"
                                    class="form-control form-control-sm"
                                    value="{{ old('due_date', date('Y-m-d', strtotime('+1 day'))) }}"
                                    required>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-4 control-label">
                                Official File Number <span class="text-required">*</span>
                            </label>
                            <div class="col-sm-8">
                                <input type="text"
                                    name="official_file_number"
                                    class="form-control form-control-sm"
                                    placeholder="Enter official file number"
                                    value="{{ old('official_file_number') }}"
                                    required>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-4 control-label">
                                RFID Code <span class="text-required">*</span>
                            </label>
                            <div class="col-sm-8">
                                <input type="text"
                                    name="rfid_code"
                                    id="rfid_code"
                                    class="form-control form-control-sm"
                                    placeholder="Scan RFID tag"
                                    autofocus
                                    {{-- value="{{ old('official_file_number') }}" --}}
                                    required>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-4 control-label">
                                File Name <span class="text-required">*</span>
                            </label>
                            <div class="col-sm-8">
                                <input type="text"
                                    name="file_name"
                                    class="form-control form-control-sm"
                                    placeholder="Enter file name"
                                    value="{{ old('file_name') }}"
                                    required>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-4 control-label">
                                Subject <span class="text-required">*</span>
                            </label>
                            <div class="col-sm-8">
                                <input type="text"
                                    name="subject"
                                    class="form-control form-control-sm"
                                    placeholder="Enter subject"
                                    value="{{ old('subject') }}"
                                    required>
                            </div>
                        </div>
                    </div>

                    {{-- Classification --}}
                    <div class="form-section">
                        <div class="section-heading">Classification</div>

                        <div class="form-group row">
                            <label class="col-sm-4 control-label">
                                Priority <span class="text-required">*</span>
                            </label>
                            <div class="col-sm-8">
                                <select name="priority" class="form-control form-control-sm" required>
                                    <option value="">Select Priority</option>
                                    <option value="Low" {{ old('priority') == 'Low' ? 'selected' : '' }}>Low</option>
                                    <option value="Medium" {{ old('priority') == 'Medium' ? 'selected' : '' }}>Medium</option>
                                    <option value="High" {{ old('priority') == 'High' ? 'selected' : '' }}>High</option>
                                    <option value="Urgent" {{ old('priority') == 'Urgent' ? 'selected' : '' }}>Urgent</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-4 control-label">
                                Category <span class="text-required">*</span>
                            </label>
                            <div class="col-sm-8">
                                <select name="category_id" id="category_id" class="form-control form-control-sm" required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-4 control-label">
                                Sub Category <span class="text-required">*</span>
                            </label>
                            <div class="col-sm-8">
                                <select name="sub_category_id" id="sub_category_id" class="form-control form-control-sm" required>
                                    <option value="">Select Sub Category</option>
                                    @foreach($subCategories as $sub)
                                        <option value="{{ $sub->id }}"
                                                data-category="{{ $sub->category_id }}">
                                            {{ $sub->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Transfer Details --}}
                    <div class="form-section">
                        <div class="section-heading">Transfer Details</div>
                        
                        <div class="transfer-section">
                            <div class="section-subheading">
                                <i class="fa fa-exchange"></i>
                                Assign File To
                            </div>

                            <div class="form-group row">
                                <label class="col-sm-4 control-label">
                                    Branch <span class="text-required">*</span>
                                </label>
                                <div class="col-sm-8">
                                    <select name="branch_id" id="branch_id" class="form-control form-control-sm" required>
                                        <option value="">Select Branch</option>
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                                {{ $branch->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-sm-4 control-label">
                                    Department <span class="text-required">*</span>
                                </label>
                                <div class="col-sm-8">
                                    <select name="department_id" id="department_id" class="form-control form-control-sm" required>
                                        <option value="">Select Department</option>
                                        @foreach($departments as $dept)
                                            <option value="{{ $dept->id }}" 
                                                data-branch="{{ $dept->branch_id }}"
                                                {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                                {{ $dept->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-sm-4 control-label">
                                    Employee <span class="text-required">*</span>
                                </label>
                                <div class="col-sm-8">
                                    <select name="employee_id" id="employee_id" class="form-control form-control-sm" required>
                                        <option value="">Select Employee</option>
                                        @foreach($employees as $emp)
                                            <option value="{{ $emp->id }}" 
                                                data-dept="{{ $emp->department_id }}"
                                                {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                                                {{ $emp->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Additional Information --}}
                    <div class="form-section">
                        <div class="section-heading">Additional Information</div>

                        <div class="form-group row">
                            <label class="col-sm-4 control-label">
                                Upload File
                                <span class="info-badge info-tooltip">
                                    <i class="fa fa-info-circle"></i>
                                    {{-- PDF, DOC, DOCX, JPG, PNG --}}
                                </span>
                            </label>
                            <div class="col-sm-8">
                                <input type="file"
                                    name="upload_file"
                                    class="dropify"
                                    data-height="120"
                                    data-allowed-file-extensions="pdf doc docx jpg png">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-4 control-label">
                                Print Type
                            </label>
                            <div class="col-sm-8 d-flex align-items-center">
                                <div style="margin-top: 6px;">
                                    <label class="mb-0">
                                        <input type="checkbox" id="print_label_checkbox" name="use_text_prn" value="1" checked>
                                        PRN
                                    </label>
                                </div>
                            </div>
                        </div>


                        <div class="form-group row">
                            <label class="col-sm-4 control-label">
                                Details
                            </label>
                            <div class="col-sm-8">
                                <textarea name="details"
                                    class="form-control form-control-sm"
                                    rows="3"
                                    placeholder="Enter additional details...">{{ old('details') }}</textarea>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-4 control-label">
                                Remarks
                            </label>
                            <div class="col-sm-8">
                                <textarea name="remarks"
                                    class="form-control form-control-sm"
                                    rows="2"
                                    placeholder="Enter remarks...">{{ old('remarks') }}</textarea>
                            </div>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="button-group">
                        <button type="submit" class="btn btn-create-file">
                            <i class="fa fa-plus-circle"></i> Create File
                        </button>
                        <button type="reset" class="btn btn-clear">
                            <i class="fa fa-eraser"></i> Clear
                        </button>
                    </div>

                </form>
            </div>
        </div>

    </div>
</div>

{{-- PRN File Print Script --}}
@if(session('print_prn_file'))
<script>
document.addEventListener("DOMContentLoaded", function () {
    var fileNo = "{{ session('print_prn_file') }}";
    var useTextPrn = {{ session('use_text_prn', 'false') ? 'true' : 'false' }};
    
    var w = window.open('', 'PRN_PRINT', 'width=400,height=500,scrollbars=no');

    if (useTextPrn) {
        // CHECKBOX CHECKED = ONLY LABEL.PRN WITH FILE NUMBER
        var prnUrl = "{{ asset('prn') }}/file_" + fileNo + ".prn";

        fetch(prnUrl)
            .then(res => res.text())
            .then(prnText => {
                w.document.open();
                w.document.write(`
                    <html>
                    <head><title>Label - ${fileNo}</title></head>
                    <body>
                        <pre>${prnText}</pre>
                    </body>
                    </html>
                `);
                w.document.close();
                
                setTimeout(function() {
                    w.print();
                }, 500);
            })
            .catch(err => {
                w.document.write('Error: ' + err);
            });

    } else {
        // CHECKBOX UNCHECKED = COMPACT STICKER SIZE QR CODE
        var qrUrl = "{{ asset('qrcodes') }}/file_" + fileNo + ".svg";

        fetch(qrUrl)
            .then(res => res.text())
            .then(qrSvg => {
                w.document.open();
                w.document.write(`
                    <html>
                    <head>
                        <title>QR - ${fileNo}</title>
                        <style>
                            * {
                                margin: 0;
                                padding: 0;
                                box-sizing: border-box;
                            }
                            body {
                                display: flex;
                                justify-content: center;
                                align-items: center;
                                min-height: 100vh;
                                font-family: Arial, sans-serif;
                            }
                            .sticker {
                                width: 2.5in;
                                height: 3in;
                                border: 1px dashed #ccc;
                                display: flex;
                                flex-direction: column;
                                align-items: center;
                                justify-content: center;
                                padding: 10px;
                                background: white;
                            }
                            .file-number {
                                font-size: 14px;
                                font-weight: bold;
                                margin-bottom: 8px;
                                text-align: center;
                            }
                            .qr-container {
                                width: 2in;
                                height: 2in;
                            }
                            .qr-container svg {
                                width: 100%;
                                height: 100%;
                            }
                            @media print {
                                body {
                                    background: white;
                                }
                                .sticker {
                                    border: none;
                                    page-break-after: avoid;
                                }
                            }
                        </style>
                    </head>
                    <body>
                        <div class="sticker">
                            <div class="file-number">${fileNo}</div>
                            <div class="qr-container">${qrSvg}</div>
                        </div>
                    </body>
                    </html>
                `);
                w.document.close();
                
                setTimeout(function() {
                    w.print();
                }, 500);
            })
            .catch(err => {
                w.document.write('Error: ' + err);
            });
    }
});
</script>
@endif
@endsection


@push('plugin-scripts')
<script src="{{ asset('assets/plugins/dropify/js/dropify.min.js') }}"></script>
<script src="{{ asset('vendor/sweetalert/sweetalert.all.js') }}"></script>
@endpush

@push('custom-scripts')
<script>
$(document).ready(function() {

    console.log('File Creation script loaded');

    // Success message from session
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: '{{ session('success') }}',
            timer: 3000,
            showConfirmButton: true
        });
    @endif
    
    // Initialize Dropify
    $('.dropify').dropify({
        messages: {
            default: 'Drag and drop a file here or click',
            replace: 'Drag and drop or click to replace',
            remove: 'Remove',
            error: 'Sorry, the file is too large or wrong format'
        }
    });

    // Store all original options
    const allDepartments = [];
    const allEmployees = [];

    // Save original department options
    $('#department_id option').each(function() {
        if ($(this).val() !== '') {
            allDepartments.push({
                value: $(this).val(),
                text: $(this).text(),
                branch: $(this).data('branch')
            });
        }
    });

    // Save original employee options
    $('#employee_id option').each(function() {
        if ($(this).val() !== '') {
            allEmployees.push({
                value: $(this).val(),
                text: $(this).text(),
                dept: $(this).data('dept')
            });
        }
    });

    /* ===============================
       BRANCH → DEPARTMENT FILTER
    =============================== */
    $('#branch_id').on('change', function () {
        const branchId = $(this).val();
        
        console.log('Branch selected:', branchId);

        // Reset department and employee
        $('#department_id').val('').html('<option value="">Select Department</option>');
        $('#employee_id').val('').html('<option value="">Select Employee</option>');

        if (!branchId) {
            // Show all departments if no branch selected
            allDepartments.forEach(dept => {
                $('#department_id').append(
                    `<option value="${dept.value}" data-branch="${dept.branch}">${dept.text}</option>`
                );
            });
            return;
        }

        // Add only departments that belong to selected branch
        const filteredDepts = allDepartments.filter(dept => dept.branch == branchId);
        
        if (filteredDepts.length === 0) {
            $('#department_id').html('<option value="">No departments available</option>');
        } else {
            filteredDepts.forEach(dept => {
                $('#department_id').append(
                    `<option value="${dept.value}" data-branch="${dept.branch}">${dept.text}</option>`
                );
            });
        }

        console.log('Filtered departments:', filteredDepts.length);
    });

    /* ===============================
       DEPARTMENT → EMPLOYEE FILTER
    =============================== */
    $('#department_id').on('change', function () {
        const deptId = $(this).val();
        
        console.log('Department selected:', deptId);

        // Reset employee
        $('#employee_id').val('').html('<option value="">Select Employee</option>');

        if (!deptId) {
            return;
        }

        // Add only employees that belong to selected department
        const filteredEmps = allEmployees.filter(emp => emp.dept == deptId);
        
        if (filteredEmps.length === 0) {
            $('#employee_id').html('<option value="">No employees available</option>');
        } else {
            filteredEmps.forEach(emp => {
                $('#employee_id').append(
                    `<option value="${emp.value}" data-dept="${emp.dept}">${emp.text}</option>`
                );
            });
        }

        console.log('Filtered employees:', filteredEmps.length);
    });

    // Category
    // Store all subcategories
    const allSubCategories = [];

    $('#sub_category_id option').each(function () {
        if ($(this).val() !== '') {
            allSubCategories.push({
                value: $(this).val(),
                text: $(this).text(),
                category: $(this).data('category')
            });
        }
    });

    $('#category_id').on('change', function () {
    const categoryId = $(this).val();

    console.log('Category selected:', categoryId);

        // Reset subcategory
        $('#sub_category_id').val('')
            .html('<option value="">Select Sub Category</option>');

        if (!categoryId) {
            // Show all subcategories if no category selected
            allSubCategories.forEach(sub => {
                $('#sub_category_id').append(
                    `<option value="${sub.value}" data-category="${sub.category}">
                        ${sub.text}
                    </option>`
                );
            });
            return;
        }

        // Filter subcategories by category
        const filteredSubs = allSubCategories.filter(
            sub => sub.category == categoryId
        );

        if (filteredSubs.length === 0) {
            $('#sub_category_id')
                .html('<option value="">No subcategories available</option>');
        } else {
            filteredSubs.forEach(sub => {
                $('#sub_category_id').append(
                    `<option value="${sub.value}" data-category="${sub.category}">
                        ${sub.text}
                    </option>`
                );
            });
        }

        console.log('Filtered subcategories:', filteredSubs.length);
    });



    /* ===============================
       FORM VALIDATION ON SUBMIT
    =============================== */
    $('form').on('submit', function(e) {
        const branchId = $('#branch_id').val();
        const deptId = $('#department_id').val();
        const empId = $('#employee_id').val();

        if (!branchId || !deptId || !empId) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Missing Information',
                text: 'Please select Branch, Department and Employee to transfer the file',
                confirmButtonColor: '#007bff'
            });
            return false;
        }
    });

    // Trigger on page load if old values exist (for validation errors)
    @if(old('branch_id'))
        $('#branch_id').trigger('change');
        @if(old('department_id'))
            setTimeout(function() {
                $('#department_id').val('{{ old("department_id") }}').trigger('change');
                @if(old('employee_id'))
                    setTimeout(function() {
                        $('#employee_id').val('{{ old("employee_id") }}');
                    }, 100);
                @endif
            }, 100);
        @endif
    @endif

    @if(old('category_id'))
        $('#category_id').val('{{ old("category_id") }}').trigger('change');
        @if(old('sub_category_id'))
            setTimeout(function () {
                $('#sub_category_id').val('{{ old("sub_category_id") }}');
            }, 100);
        @endif
    @endif

});
</script>
@endpush