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
        background: #f8f9fa;
        font-weight: 600;
        color: #555;
        padding: 0.75rem 0.5rem;
        border: 1px solid #e0e0e0;
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
        background: #fff9e6;
        border: 1px solid #ffe066;
        border-radius: 8px;
        padding: 1.5rem;
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
        border-color: #f59e0b;
        box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
    }
    
    .btn-reopen {
        background: #f59e0b;
        color: white;
        border: none;
        padding: 0.65rem 1.5rem;
        border-radius: 6px;
        font-weight: 600;
    }
    
    .btn-reopen:hover {
        background: #d97706;
        color: white;
    }
    
    .text-required {
        color: #dc3545;
    }
</style>
@endpush

@section('content')
@include('messages')

<div class="card">
    <div class="card-body">
        
        <div class="page-header">
            <h5>File Reopen</h5>
        </div>

        {{-- File Selection --}}
        <div class="filter-box">
            <div class="row">
                <div class="col-md-4 mb-3 mb-md-0">
                    <label>File Number <span class="text-required">*</span></label>
                    <select id="file_id" class="form-control select2">
                        <option value="">Select File Number</option>
                        @foreach($files as $file)
                            <option value="{{ $file->id }}">
                                {{ $file->official_file_number }}
                            </option>
                        @endforeach
                    </select>
                    @if($files->isEmpty())
                        <small class="text-muted d-block mt-2">
                            <i class="fa fa-info-circle"></i> No closed files available to reopen
                        </small>
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
                        <textarea class="form-control" id="file_subject" rows="2" readonly></textarea>
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
                            <th>Time</th>
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

        {{-- Reopen Section --}}
        <div id="reopenSection" style="display:none;">
            <div class="section-heading">Reopen & Transfer File</div>
            <div class="action-box">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Transfer to Branch <span class="text-required">*</span></label>
                        <select id="to_branch_id" class="form-control">
                            <option value="">Select Branch</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Transfer to Department <span class="text-required">*</span></label>
                        <select id="to_department_id" class="form-control">
                            <option value="">Select Department</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Transfer to Employee <span class="text-required">*</span></label>
                        <select id="to_user_id" class="form-control">
                            <option value="">Select Employee</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8">
                        <label class="form-label">Reopen Remarks</label>
                        <textarea id="remarks" class="form-control" rows="3" placeholder="Enter reason for reopening..."></textarea>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button id="reopenBtn" class="btn btn-reopen w-100">
                            <i class="fa fa-folder-open"></i> Reopen File
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
$(function () {

    $('.select2').select2({
        width: '100%',
        placeholder: 'Select File Number'
    });

    // Load file details
    $('#file_id').change(function () {
        let fileId = $(this).val();

        if (!fileId) {
            $('#fileDetails,#movementSection,#reopenSection').hide();
            return;
        }

        Swal.fire({
            title: 'Loading...',
            text: 'Please wait',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        fetch("{{ url('filereopen') }}/" + fileId)
            .then(res => res.json())
            .then(data => {

                Swal.close();

                $('#fileDetails,#movementSection,#reopenSection').show();

                $('#official_no').text(data.file.official_file_number || '-');
                $('#start_date').text(formatDateDMY(data.file.start_date));
                $('#due_date').text(formatDateDMY(data.file.due_date));
                $('#file_subject').val(data.file.subject || '-');

                let tbody = $('#movementBody').empty();

                if (!data.movements || data.movements.length === 0) {
                    tbody.append('<tr><td colspan="12" class="text-center text-muted">No movement history</td></tr>');
                    return;
                }

                data.movements.forEach((m, i) => {
                    let d = m.created_at ? new Date(m.created_at) : null;

                    tbody.append(`
                        <tr>
                            <td>${i+1}</td>
                            <td>${formatDateDMY(m.created_at)}</td>
                            <td>${formatTime(m.created_at)}</td>
                            <td>${m.entry_branch?.name || '-'}</td>
                            <td>${m.entry_department?.name || '-'}</td>
                            <td>${m.entry_user?.name || '-'}</td>
                            <td>${m.accessibility == 1 ? 'Assigned' : 'Creator'}</td>
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
                Swal.close();
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load file details'
                });
            });
    });

    //RFID Search
    $('#scan_file').on('keypress', function (e) {
        if (e.which === 13) {
            e.preventDefault();

            const value = $(this).val().trim();
            if (!value) return;

            smartSearch(value);
        }
    });
    
    //RFID Search Function
    function smartSearch(value) {
        Swal.fire({
            title: 'Searching...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        fetch(`{{ url('file/search') }}/${encodeURIComponent(value)}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(async res => {
            const data = await res.json();
            Swal.close();

            // ❌ File not found
            if (res.status === 404) {
                Swal.fire({
                    icon: 'error',
                    title: 'File Not Found',
                    text: data.message
                });
                resetUI();
                return;
            }

            // ⚠️ File still OPEN → ONLY FOR REOPEN PAGE
            if (data.file.status != 1) {
                Swal.fire({
                    icon: 'warning',
                    title: 'File Still Open',
                    text: 'This file is still open'
                });
                resetUI();
                return;
            }

            // ✅ File is CLOSED → proceed
            $('#file_id').val(data.file.id).trigger('change');
            $('#scan_file').val('').focus();
        })
        .catch(() => {
            Swal.close();
            Swal.fire({
                icon: 'error',
                title: 'Server Error',
                text: 'Unable to search file'
            });
            resetUI();
        });
    }

    function resetUI() {
        $('#scan_file').val('').focus();
        $('#file_id').val('').trigger('change');
        $('#fileDetails,#movementSection,#reopenSection').hide();
    }

    // Branch → Department
    $('#to_branch_id').change(function () {
        const branchId = $(this).val();
        
        $('#to_department_id').html('<option value="">Select Department</option>').val('');
        $('#to_user_id').html('<option value="">Select Employee</option>').val('');
        
        if (!branchId) return;
        
        $('#to_department_id').html('<option value="">Loading...</option>');
        
        fetch("{{ url('filemovement/departments') }}/" + branchId)
            .then(res => res.json())
            .then(data => {
                let options = '<option value="">Select Department</option>';
                data.forEach(d => {
                    options += `<option value="${d.id}">${d.name}</option>`;
                });
                $('#to_department_id').html(options);
            })
            .catch(() => {
                $('#to_department_id').html('<option value="">Error loading</option>');
            });
    });

    // Department → Employee
    $('#to_department_id').change(function () {
        const deptId = $(this).val();
        
        $('#to_user_id').html('<option value="">Select Employee</option>').val('');
        
        if (!deptId) return;
        
        $('#to_user_id').html('<option value="">Loading...</option>');
        
        fetch("{{ url('filemovement/employees') }}/" + deptId)
            .then(res => res.json())
            .then(data => {
                let options = '<option value="">Select Employee</option>';
                data.forEach(e => {
                    options += `<option value="${e.id}">${e.name}</option>`;
                });
                $('#to_user_id').html(options);
            })
            .catch(() => {
                $('#to_user_id').html('<option value="">Error loading</option>');
            });
    });

    // Reopen
    $('#reopenBtn').click(function () {
        
        const fileId = $('#file_id').val();
        const toBranchId = $('#to_branch_id').val();
        const toDeptId = $('#to_department_id').val();
        const toUserId = $('#to_user_id').val();
        const remarks = $('#remarks').val();
        
        if (!fileId || !toBranchId || !toDeptId || !toUserId) {
            Swal.fire({
                icon: 'warning',
                title: 'Missing Information',
                text: 'Please fill all required fields'
            });
            return;
        }

        Swal.fire({
            title: 'Confirm Reopen?',
            text: 'This will reopen the closed file and transfer it',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f59e0b',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Reopen',
            cancelButtonText: 'Cancel'
        }).then(result => {

            if (!result.isConfirmed) return;
            
            Swal.fire({
                title: 'Reopening...',
                text: 'Please wait',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch("{{ route('file.reopen.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    file_id: fileId,
                    to_branch_id: toBranchId,
                    to_department_id: toDeptId,
                    to_user_id: toUserId,
                    remarks: remarks
                })
            })
            .then(res => res.json())
            .then(resp => {
                if (resp.status) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: resp.message,
                        showConfirmButton: true
                    }).then(() => location.reload());
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: resp.message
                    });
                }
            })
            .catch(() => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to reopen file'
                });
            });
        });
    });

});

function formatDateDMY(dateStr) {
    if (!dateStr) return '-';

    const d = new Date(dateStr);
    if (isNaN(d)) return '-';

        return d.toLocaleDateString('en-GB', {
            day: '2-digit',
            month: 'short',
            year: 'numeric'
        });
    }

    function formatTime(dateStr) {
        if (!dateStr) return '-';

        const d = new Date(dateStr);
        if (isNaN(d)) return '-';

        return d.toLocaleTimeString('en-GB');
    }


function statusText(s) {
    return {
        0: 'Open',
        1: 'Received',
        2: 'On Hold',
        3: 'In Progress',
        4: 'Transferred',
        5: 'Closed'
    }[s] || '-';
}
</script>
@endpush