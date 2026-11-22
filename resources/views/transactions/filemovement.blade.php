@extends('layout.master')

@push('plugin-styles')
<link href="{{ asset('assets/plugins/select2/select2.min.css') }}" rel="stylesheet" />
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
    
    .filter-box {
        background: #f8f9fa;
        padding: 1.5rem;
        border-radius: 8px;
        margin-bottom: 2rem;
    }
    
    .filter-box label {
        font-weight: 500;
        color: #555;
        margin-bottom: 0.4rem;
    }
    
    .section-heading {
        font-size: 1rem;
        font-weight: 600;
        color: #333;
        margin: 2rem 0 1rem 0;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid #e0e0e0;
    }
    
    .detail-box {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 6px;
        padding: 1rem;
        margin-bottom: 1.5rem;
    }
    
    .detail-box .detail-row {
        display: flex;
        margin-bottom: 0.5rem;
    }
    
    .detail-box .detail-row:last-child {
        margin-bottom: 0;
    }
    
    .detail-box .detail-label {
        font-weight: 600;
        color: #555;
        min-width: 130px;
    }
    
    .detail-box .detail-value {
        color: #333;
    }
    
    .history-table {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 6px;
        overflow: hidden;
        font-size: 0.9rem;
        margin-bottom: 2rem;
    }
    
    .history-table thead th {
        background: #28a745;
        color: white;
        font-weight: 600;
        padding: 0.75rem 0.5rem;
        border: 1px solid #218838;
        font-size: 0.85rem;
    }
    
    .history-table tbody td {
        padding: 0.65rem 0.5rem;
        border: 1px solid #e0e0e0;
        color: #333;
    }
    
    .history-table tbody tr:hover {
        background: #f8f9fa;
    }
    
    .action-box {
        background: #e8f5e9;
        border: 1px solid #a5d6a7;
        border-radius: 8px;
        padding: 1.5rem;
    }
    
    .transfer-box {
        background: #fff3cd;
        border: 1px solid #ffc107;
        border-radius: 8px;
        padding: 1.5rem;
        margin-top: 1rem;
    }
    
    .transfer-box .section-subheading {
        font-size: 0.9rem;
        font-weight: 600;
        color: #856404;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .form-label {
        font-weight: 500;
        color: #555;
        margin-bottom: 0.4rem;
    }
    
    .form-control {
        border: 2px solid #e0e0e0;
        border-radius: 6px;
        padding: 0.6rem 0.9rem;
    }
    
    .form-control:focus {
        border-color: #28a745;
        box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.1);
    }
    
    .btn-update-file {
        background: #28a745;
        color: white;
        border: none;
        padding: 0.65rem 1.5rem;
        border-radius: 6px;
        font-weight: 600;
    }
    
    .btn-update-file:hover {
        background: #218838;
        color: white;
    }
    
    .text-required {
        color: #dc3545;
    }
    
    .info-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.4rem 0.8rem;
        background: #fff3cd;
        border: 1px solid #ffc107;
        border-radius: 4px;
        font-size: 0.85rem;
        color: #856404;
        margin-top: 0.5rem;
    }
    
    .status-badge {
        display: inline-block;
        padding: 0.25rem 0.6rem;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .status-received { background: #d4edda; color: #155724; }
    .status-onhold { background: #fff3cd; color: #856404; }
    .status-inprogress { background: #cce5ff; color: #004085; }
    .status-transferred { background: #e2e3e5; color: #383d41; }
    .status-completed { background: #d1ecf1; color: #0c5460; }
</style>
@endpush

@section('content')
@include('messages')

<div class="card">
    <div class="card-body">
        
        <div class="page-header">
            <h5>File Movement</h5>
        </div>

        {{-- File Selection --}}
        <div class="filter-box">
            <div class="row">
                <div class="col-md-4 mb-3 mb-md-0">
                    <label>File Number <span class="text-required">*</span></label>
                    <select id="file_id" class="form-control select2">
                        <option value="">Select File Number</option>
                        @foreach($files as $file)
                            <option value="{{ $file->id }}">{{ $file->official_file_number }}</option>
                        @endforeach
                    </select>
                    @if($files->isEmpty())
                        <div class="info-badge">
                            <i class="fa fa-info-circle"></i>
                            <span>No files assigned to you at the moment</span>
                        </div>
                    @endif
                </div>

                <div class="col-md-4">
                    <label>Scan RFID <span class="text-required">*</span></label>
                    <input type="text" id="scan_file" class="form-control" placeholder="Scan or type RFID tag">
                    <small class="text-muted">Press Enter to search</small>
                </div>
            </div>
        </div>

        {{-- File Details --}}
        <div id="fileDetails" style="display:none;">
            <div class="section-heading">File Details</div>
            <div class="detail-box">
                <div class="row">
                    <div class="col-md-4">
                        <div class="detail-row">
                            <div class="detail-label">Official File No:</div>
                            <div class="detail-value" id="official_no">-</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="detail-row">
                            <div class="detail-label">Start Date:</div>
                            <div class="detail-value" id="start_date">-</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="detail-row">
                            <div class="detail-label">Due Date:</div>
                            <div class="detail-value" id="due_date">-</div>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <label class="form-label">File Subject</label>
                        <textarea id="file_subject" class="form-control" rows="2" readonly></textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- Movement History --}}
        <div id="movementSection" style="display:none;">
            <div class="section-heading">Movement History</div>
            <div class="table-responsive">
                <table class="history-table table table-sm mb-0">
                    <thead>
                        <tr>
                            <th style="width: 40px;">#</th>
                            <th>Date</th>
                            <th>Branch</th>
                            <th>Department</th>
                            <th>User</th>
                            <th>Access</th>
                            <th>Status</th>
                            <th>Remarks</th>
                            <th>To Branch</th>
                            <th>To Dept</th>
                            <th>To User</th>
                        </tr>
                    </thead>
                    <tbody id="movementBody"></tbody>
                </table>
            </div>
        </div>

        {{-- Action Section --}}
        <div id="actionSection" style="display:none;">
            <div class="section-heading">Update File Status</div>
            <div class="action-box">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Status <span class="text-required">*</span></label>
                        <select id="status" class="form-control">
                            <option value="">Select Status</option>
                            <option value="1">Received</option>
                            <option value="2">On Hold</option>
                            <option value="3">In Progress</option>
                            <option value="4">Transfer</option>
                            <option value="5">Completed</option>
                        </select>
                    </div>
                </div>

                {{-- Transfer Fields --}}
                <div id="transferFields" style="display:none;">
                    <div class="transfer-box">
                        <div class="section-subheading">
                            <i class="fa fa-exchange"></i>
                            Transfer Details
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">To Branch <span class="text-required">*</span></label>
                                <select id="to_branch_id" class="form-control">
                                    <option value="">Select Branch</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">To Department <span class="text-required">*</span></label>
                                <select id="to_department_id" class="form-control">
                                    <option value="">Select Department</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">To Employee <span class="text-required">*</span></label>
                                <select id="to_user_id" class="form-control">
                                    <option value="">Select Employee</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-8">
                        <label class="form-label">Remarks</label>
                        <textarea id="remarks" class="form-control" rows="3" placeholder="Enter any additional notes..."></textarea>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button id="saveMovement" class="btn btn-update-file w-100">
                            <i class="fa fa-check-circle"></i> Update Movement
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('plugin-scripts')
<script src="{{ asset('assets/plugins/select2/select2.min.js') }}"></script>
<script src="{{ asset('vendor/sweetalert/sweetalert.all.js') }}"></script>
@endpush

@push('custom-scripts')
<script>
$(document).ready(function () {

    console.log('FileMovement script loaded');

    $('.select2').select2({
        width: '100%',
        placeholder: 'Select File Number'
    });

    /* ===============================
       FILE SELECTION (DROPDOWN)
    =============================== */
    $('#file_id').on('change', function () {
        const fileId = $(this).val();
        
        if (!fileId) {
            $('#fileDetails,#movementSection,#actionSection').hide();
            resetActionFields();
            return;
        }

        console.log('File selected:', fileId);
        
        // Clear scan input when dropdown is used
        $('#scan_file').val('');
        
        // Reset action fields
        resetActionFields();

        // Load file details
        loadFileDetails(fileId);
    });

    /* ===============================
       SCAN FILE INPUT (ON ENTER)
    =============================== */
   $('#scan_file').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();

            const value = $(this).val().trim();
            if (!value) return;

            searchFileByNumber(value);
        }
    });


    /* ===============================
       SEARCH FILE BY RFID
    =============================== */
   function searchFileByNumber(value) {

        Swal.fire({
            title: 'Searching...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        fetch("{{ url('filemovement/search') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': "{{ csrf_token() }}",
                'Accept': 'application/json'
            },
            body: JSON.stringify({ file_number: value })
        })
        .then(async res => {
            const data = await res.json();
            Swal.close();

            // NOT FOUND / NOT ASSIGNED TO USER
            if (!res.ok || !data.file_id) {
                Swal.fire({
                    icon: 'error',
                    title: 'File Not Found',
                    text: 'This file is not assigned to you'
                });

                resetUI();
                return;
            }

            // FILE BELONGS TO USER
            $('#file_id').val(data.file_id).trigger('change');
            $('#scan_file').val('').focus();
        })
        .catch(() => {
            Swal.fire('Error', 'Search failed', 'error');
            resetUI();
        });
    }

    function resetUI() {
        $('#scan_file').val('').focus();
        $('#file_id').val('').trigger('change');
        $('#fileDetails,#movementSection,#actionSection').hide();
    }


    /* ===============================
       LOAD FILE DETAILS (COMMON FUNCTION)
    =============================== */
    function loadFileDetails(fileId) {
        Swal.fire({
            title: 'Loading...',
            text: 'Please wait',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        fetch("{{ url('filemovement') }}/" + fileId)
            .then(res => {
                if (!res.ok) throw new Error('HTTP error ' + res.status);
                return res.json();
            })
            .then(data => {
                console.log('Data received:', data);
                
                Swal.close();

                $('#fileDetails,#movementSection').show();

                if (data.file.status == 5) {
                    $('#actionSection').hide();
                } else {
                    $('#actionSection').show();
                }

                $('#official_no').text(data.file.official_file_number || '-');
                $('#start_date').text(data.file.start_date || '-');
                $('#due_date').text(data.file.due_date || '-');
                $('#file_subject').val(data.file.subject || '-');

                const tbody = $('#movementBody').empty();

                if (!data.movements || data.movements.length === 0) {
                    tbody.append('<tr><td colspan="11" class="text-center text-muted">No movement history</td></tr>');
                    return;
                }
                const movements = [...data.movements].sort(
                    (a, b) => new Date(b.created_at) - new Date(a.created_at)
                );

                movements.forEach((m, i) => {
                    tbody.append(`
                        <tr>
                            <td>${i + 1}</td>
                            <td>${formatDateDMY(m.created_at)}</td>
                            <td>${m.entry_branch?.name || m.to_branch?.name || '-'}</td>
                            <td>${m.entry_department?.name || m.to_department?.name || '-'}</td>
                            <td>${m.entry_user?.name || m.to_user?.name || '-'}</td>
                            <td>${m.accessibility == 1 ? 'Assigned' : 'Created'}</td>
                            <td>${statusText(m.status)}</td>
                            <td>${m.remarks || '-'}</td>
                            <td>${m.status == 5 ? 'Closed' : (m.to_branch?.name || '-')}</td>
                            <td>${m.status == 5 ? 'Closed' : (m.to_department?.name || '-')}</td>
                            <td>${m.status == 5 ? 'Closed' : (m.to_user?.name || '-')}</td>
                        </tr>
                    `);
                });

            })
            .catch(err => {
                console.error('Fetch error:', err);
                Swal.close();
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Unable to load file details: ' + err.message
                });
            });
    }

    /* ===============================
       RESET ACTION FIELDS
    =============================== */
    function resetActionFields() {
        $('#status').val('');
        $('#remarks').val('');
        $('#transferFields').hide();
        $('#to_branch_id,#to_department_id,#to_user_id').val('').trigger('change');
    }

    /* ===============================
       STATUS → TRANSFER TOGGLE
    =============================== */
    $('#status').on('change', function () {
        if ($(this).val() == 4) {
            $('#transferFields').slideDown();
        } else {
            $('#transferFields').slideUp();
            $('#to_branch_id,#to_department_id,#to_user_id').val('').trigger('change');
        }
    });

    /* ===============================
       BRANCH → LOAD DEPARTMENTS (AJAX)
    =============================== */
    $('#to_branch_id').on('change', function () {
        const branchId = $(this).val();
    
        console.log('Branch selected:', branchId);

        $('#to_department_id').html('<option value="">Select Department</option>').val('').trigger('change');
        $('#to_user_id').html('<option value="">Select Employee</option>').val('').trigger('change');

        if (!branchId) return;

        $('#to_department_id').html('<option value="">Loading...</option>');

        fetch("{{ url('filemovement/departments') }}/" + branchId)
            .then(res => res.json())
            .then(departments => {
                console.log('Departments loaded:', departments);

                let options = '<option value="">Select Department</option>';
                
                departments.forEach(dept => {
                    options += `<option value="${dept.id}">${dept.name}</option>`;
                });

                $('#to_department_id').html(options).trigger('change');
            })
            .catch(err => {
                console.error('Error loading departments:', err);
                $('#to_department_id').html('<option value="">Select Department</option>');
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load departments'
                });
            });
    });

    /* ===============================
       DEPARTMENT → LOAD EMPLOYEES (AJAX)
    =============================== */
    $('#to_department_id').on('change', function () {
        const deptId = $(this).val();
    
        console.log('Department selected:', deptId);

        $('#to_user_id').html('<option value="">Select Employee</option>').val('').trigger('change');

        if (!deptId) return;

        $('#to_user_id').html('<option value="">Loading...</option>');

        fetch("{{ url('filemovement/employees') }}/" + deptId)
            .then(res => res.json())
            .then(employees => {
                console.log('Employees loaded:', employees);

                let options = '<option value="">Select Employee</option>';
                
                if (employees.length === 0) {
                    options = '<option value="">No employees found</option>';
                } else {
                    employees.forEach(emp => {
                        options += `<option value="${emp.id}">${emp.name}</option>`;
                    });
                }

                $('#to_user_id').html(options).trigger('change');
            })
            .catch(err => {
                console.error('Error loading employees:', err);
                $('#to_user_id').html('<option value="">Select Employee</option>');
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load employees'
                });
            });
    });

    /* ===============================
       SAVE / UPDATE WITH SWEETALERT
    =============================== */
    $('#saveMovement').on('click', function () {

        const payload = {
            file_id: $('#file_id').val(),
            status: $('#status').val(),
            remarks: $('#remarks').val(),
            to_branch_id: $('#to_branch_id').val(),
            to_department_id: $('#to_department_id').val(),
            to_user_id: $('#to_user_id').val()
        };

        if (!payload.file_id) {
            Swal.fire({
                icon: 'warning',
                title: 'Missing Information',
                text: 'Please select a file first'
            });
            return;
        }

        if (!payload.status) {
            Swal.fire({
                icon: 'warning',
                title: 'Missing Information',
                text: 'Please select status'
            });
            return;
        }

        if (payload.status == 4 && (!payload.to_branch_id || !payload.to_department_id || !payload.to_user_id)) {
            Swal.fire({
                icon: 'warning',
                title: 'Missing Information',
                text: 'For Transfer status, please select Branch, Department and Employee'
            });
            return;
        }

        Swal.fire({
            title: 'Confirm Update',
            text: 'Are you sure you want to update this file movement?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Update',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Updating...',
                    text: 'Please wait',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                fetch("{{ url('filemovement/update') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': "{{ csrf_token() }}"
                    },
                    body: JSON.stringify(payload)
                })
                .then(resp => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: resp.message || 'File movement updated successfully',
                        showConfirmButton: true,
                        timer: 3000
                    }).then(() => {
                        window.location.href = "{{ route('filemovement.index') }}";
                    });
                })
                .catch(err => {
                    console.error('Update error:', err);
                    Swal.fire({
                        icon: 'error',
                        title: 'Update Failed',
                        text: 'An error occurred while updating. Please try again.'
                    });
                });
            }
        });
    });

    function statusText(s) {
        const statuses = {
            0: 'Not Received',
            1: 'Received',
            2: 'On Hold',
            3: 'In Progress',
            4: 'Transferred',
            5: 'Completed'
        };
        return statuses[parseInt(s)] || '-';
    }

    function formatDateDMY(dateStr) {
        if (!dateStr) return '-';

        const date = new Date(dateStr);

        if (isNaN(date)) return '-';

        return date.toLocaleDateString('en-GB', {
            day: '2-digit',
            month: 'short',
            year: 'numeric'
        });
    }

});
</script>
@endpush