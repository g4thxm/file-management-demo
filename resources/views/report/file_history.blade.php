@extends('layout.master')

@push('plugin-styles')
<link href="{{ asset('assets/plugins/select2/select2.min.css') }}" rel="stylesheet" />
<style>
    .report-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #e8e8e8;
    }
    
    .report-header h5 {
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
    
    .detail-table {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 6px;
        overflow: hidden;
        margin-bottom: 2rem;
    }
    
    .detail-table th {
        background: #f8f9fa;
        font-weight: 600;
        color: #555;
        font-size: 0.9rem;
        padding: 0.75rem;
        border: 1px solid #e0e0e0;
        width: 180px;
    }
    
    .detail-table td {
        padding: 0.75rem;
        border: 1px solid #e0e0e0;
        color: #333;
    }
    
    .history-table {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 6px;
        overflow: hidden;
        font-size: 0.9rem;
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
    
    .badge {
        padding: 0.35rem 0.65rem;
        font-size: 0.8rem;
        font-weight: 500;
    }
    
    .btn {
        padding: 0.5rem 1.2rem;
        font-size: 0.9rem;
        border-radius: 6px;
    }
    
    .text-empty {
        color: #999;
        font-style: italic;
    }
</style>
@endpush

@section('content')
@include('messages')

<div class="card">
    <div class="card-body">
        
        <div class="report-header">
            <h5>File History Report</h5>
        </div>

       <div class="filter-box">
    <form action="{{ route('report.file_history.view') }}" method="POST" class="row">
        @csrf
        <div class="col-md-5">
            <label>File Number</label>
            <select name="file_id" class="form-control select2">
                <option value="">Select File Number</option>
                @foreach($files as $f)
                    <option value="{{ $f->id }}"
                        {{ isset($file) && $file->id == $f->id ? 'selected' : '' }}>
                        {{ $f->official_file_number }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-4">
            <label>Scan RFID</label>
            <input type="text" id="scan_file" class="form-control"
                placeholder="Scan or type RFID tag">
            <small class="text-muted d-block">Press Enter to search</small>
        </div>

        <div class="col-md-3 d-flex align-items-end">
            <div class="w-100" style="margin-bottom: 20px;">
                <button type="submit" class="btn btn-primary">View Report</button>
                <a href="{{ route('report.file_history') }}" class="btn btn-secondary">Reset</a>
            </div>
        </div>
    </form>
</div>

        @if(isset($file))

        {{-- File Details --}}
        <div class="section-heading">File Details</div>
        <table class="detail-table table table-sm mb-0">
            <tbody>
                <tr>
                    <th>Official File Number</th>
                    <td>{{ $file->official_file_number }}</td>
                    <th>Created By</th>
                    <td>{{ $file->createdBy->name ?? '-' }}</td>
                </tr>
                <tr>
                    <th>File Name</th>
                    <td>{{ $file->file_name }}</td>
                    <th>Subject</th>
                    <td>{{ $file->subject ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Start Date</th>
                    <td>{{ $file->start_date?->format('d M Y') ?? '-' }}</td>
                    <th>Due Date</th>
                    <td>{{ $file->due_date?->format('d M Y') ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td>
                        <span class="badge badge-{{ $file->status_badge }}">
                            {{ $file->status_label }}
                        </span>
                    </td>
                    <th>Priority</th>
                    <td>{{ ucfirst($file->priority) ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Category</th>
                    <td>{{ $file->category->name ?? '-' }}</td>
                    <th>Sub Category</th>
                    <td>{{ $file->subCategory->name ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Creation Branch</th>
                    <td>{{ $file->createdBy?->employee?->branch?->name ?? '-' }}</td>
                    <th>Creation Department</th>
                    <td>{{ $file->createdBy?->employee?->department?->name ?? '-' }}</td>
                </tr>
            </tbody>
        </table>

        {{-- File Movement History --}}
        <div class="section-heading">File Movement History</div>
        <div class="table-responsive">
            <table class="history-table table table-sm mb-0">
                <thead>
                    <tr>
                        <th style="width: 40px;">#</th>
                        <th style="width: 100px;">Date</th>
                        <th style="width: 90px;">Time</th>
                        <th style="width: 110px;">Status</th>
                        <th>Access</th>
                        <th>Remarks</th>
                        <th>From Branch</th>
                        <th>From Dept</th>
                        <th>From User</th>
                        <th>To Branch</th>
                        <th>To Dept</th>
                        <th>To User</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($file->movements as $m)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $m->created_date }}</td>
                        <td>{{ $m->created_time }}</td>
                        <td>{{ $m->status_label }}</td>
                        <td>{{ $m->accessibility_text ?? '-' }}</td>
                        <td>{{ $m->remarks ?? '-' }}</td>
                        <td>{{ $m->entryUser?->branch?->name ?? '-' }}</td>
                        <td>{{ $m->entryUser?->department?->name ?? '-' }}</td>
                        <td>{{ $m->entryUser?->name ?? '-' }}</td>
                        <td>{{ $m->to_branch_display }}</td>
                        <td>{{ $m->to_department_display }}</td>
                        <td>{{ $m->to_user_display }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="12" class="text-center text-empty">No movement records found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- File Closure --}}
        <div class="section-heading">File Closure History</div>
        <div class="table-responsive">
            <table class="history-table table table-sm mb-0">
                <thead>
                    <tr>
                        <th style="width: 40px;">#</th>
                        <th>Closed Date</th>
                        <th>Closed Time</th>
                        <th>Remarks</th>
                        <th>Branch</th>
                        <th>Department</th>
                        <th>Closed By</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($closures as $c)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $c->closed_date }}</td>
                        <td>{{ $c->closed_time }}</td>
                        <td>{{ $c->remarks ?? '-' }}</td>
                        <td>{{ $c->closedBy?->employee?->branch?->name ?? '-' }}</td>
                        <td>{{ $c->closedBy?->employee?->department?->name ?? '-' }}</td>
                        <td>{{ $c->closedBy?->name ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-empty">No closure records found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- File Reopen --}}
        <div class="section-heading">File Reopen History</div>
        <div class="table-responsive">
            <table class="history-table table table-sm mb-0">
                <thead>
                    <tr>
                        <th style="width: 40px;">#</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Remarks</th>
                        <th>To Branch</th>
                        <th>To Department</th>
                        <th>To Employee</th>
                        <th>Reopened By</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reopens as $r)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $r->opened_date }}</td>
                        <td>{{ $r->reopened_time }}</td>
                        <td>{{ $r->movement?->remarks ?? '-' }}</td>
                        <td>{{ $r->movement?->toBranch?->name ?? '-' }}</td>
                        <td>{{ $r->movement?->toDepartment?->name ?? '-' }}</td>
                        <td>{{ $r->movement?->toUser?->name ?? '-' }}</td>
                        <td>{{ $r->reopenedBy?->name ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-empty">No reopen records found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @endif
    </div>
</div>
@endsection

@push('plugin-scripts')
<script src="{{ asset('assets/plugins/select2/select2.min.js') }}"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            width: '100%',
            placeholder: 'Select File Number'
        });

        // SMART SEARCH (RFID / FILE NO)
        $('#scan_file').on('keypress', function (e) {
            if (e.which === 13) {
                e.preventDefault();

                const value = $(this).val().trim();
                if (!value) return;

                smartSearch(value);
            }
        });

        function smartSearch(value) {

            fetch(`{{ url('file/search') }}/${encodeURIComponent(value)}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(async res => {
                const data = await res.json();

                if (!res.ok || !data.success) {
                    Swal.fire({
                        icon: 'error',
                        title: 'File Not Found',
                        text: data.message || 'Invalid RFID / File Number'
                    });
                    return;
                }

                // ✅ Set dropdown & auto submit
                $('select[name="file_id"]').val(data.file.id).trigger('change');

                // Auto submit form
                $('form').submit();
            })
            .catch(() => {
                Swal.fire('Error', 'Search failed', 'error');
            });
        }
    });
</script>
@endpush