@extends('layout.master')

@push('plugin-styles')
<link href="{{ asset('assets/plugins/select2/select2.min.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/datatables-net/dataTables.bootstrap4.css') }}" rel="stylesheet" />
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
    
    .report-header .date-range {
        color: #666;
        font-size: 0.95rem;
    }
    
    .btn-back {
        background: white;
        border: 1px solid #ddd;
        color: #555;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        font-size: 0.9rem;
        margin-bottom: 1.5rem;
    }
    
    .btn-back:hover {
        background: #f5f5f5;
        color: #333;
    }
    
    .btn-back i {
        margin-right: 0.4rem;
    }
    
    .results-table {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 6px;
        overflow: hidden;
        font-size: 0.9rem;
    }
    
    .results-table thead th {
        background: #f8f9fa;
        font-weight: 600;
        color: #555;
        padding: 0.75rem 0.5rem;
        border: 1px solid #e0e0e0;
        font-size: 0.85rem;
        white-space: nowrap;
    }
    
    .results-table tbody td {
        padding: 0.65rem 0.5rem;
        border: 1px solid #e0e0e0;
        color: #333;
        vertical-align: middle;
    }
    
    .results-table tbody tr:hover {
        background: #f8f9fa;
    }
    
    .badge {
        padding: 0.35rem 0.65rem;
        font-size: 0.8rem;
        font-weight: 500;
    }
    
    .text-empty {
        color: #999;
        font-style: italic;
        padding: 2rem !important;
    }
    
    .table-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        padding: 0.75rem;
        background: #f8f9fa;
        border-radius: 6px;
    }
    
    .table-info .count {
        font-weight: 600;
        color: #333;
    }

    .file-link {
    color: #333;
    text-decoration: none;
    font-weight: 500;
    }

    .file-link:hover {
        color: #0d6efd;
        text-decoration: none;
    }

    .file-link i {
        font-size: 0.75rem;
        opacity: 0;
        transition: opacity 0.2s ease;
    }

    .file-link:hover i {
        opacity: 1;
    }

</style>
@endpush

@section('content')
@include('messages')

<div class="card">
    <div class="card-body">
        
        <div class="report-header">
            <div>
                <h5>File Report Results</h5>
                <div class="date-range">
                    Report from 
                    <strong>{{ request('from_date') ? \Carbon\Carbon::parse(request('from_date'))->format('d M Y') : 'N/A' }}</strong> 
                    to 
                    <strong>{{ request('to_date') ? \Carbon\Carbon::parse(request('to_date'))->format('d M Y') : 'N/A' }}</strong>
                </div>
            </div>
        </div>

        <form action="{{ route('file_report.form') }}" method="GET">
            <button type="submit" class="btn btn-back">
                <i class="fa fa-arrow-left"></i> Back to Filter
            </button>
        </form>

        @if($files->isNotEmpty())
        <div class="table-info">
            <div class="count">
                <i class="fa fa-file-text-o"></i> Total Files: {{ $files->count() }}
            </div>
        </div>
        @endif

        <div class="table-responsive">
            <table class="results-table table table-sm mb-0" id="reportTable">
                <thead>
                    <tr>
                        <th style="width: 40px;">#</th>
                        <th>File No</th>
                        <th>File Name</th>
                        <th>Subject</th>
                        <th>Priority</th>
                        <th>Category</th>
                        <th>Department</th>
                        <th>Assigned To</th>
                        <th>Created By</th>
                        <th>Start Date</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Document</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($files as $file)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $file->official_file_number }}</td>
                       <td>
                            <a href="{{ route('report.file_history.view') }}"
                            onclick="event.preventDefault(); submitFileHistory({{ $file->id }});"
                            class="file-link">
                                {{ $file->file_name }}
                                <i class="fa fa-angle-right ms-1"></i>
                            </a>
                        </td>
                        <td>{{ $file->subject ?? '-' }}</td>
                        <td>{{ ucfirst($file->priority) }}</td>
                        <td>{{ $file->category->name ?? '-' }}</td>
                        <td>{{ $file->department->name ?? '-' }}</td>
                        <td>{{ $file->employee->name ?? '-' }}</td>
                        <td>{{ $file->createdBy->name ?? '-' }}</td>
                        <td>{{ $file->start_date?->format('d M Y') ?? '-' }}</td>
                        <td>{{ $file->due_date?->format('d M Y') ?? '-' }}</td>
                        <td>
                            <span class="badge badge-{{ $file->status_badge }}">
                                {{ $file->status_label }}
                            </span>
                        </td>
                        <td>
                            @if($file->upload_file)
                               <a href="{{ route('file.report.view', $file->id) }}" target="_blank"
                                class="btn btn-sm btn-primary">
                                    View
                                </a>

                                <a href="{{ route('file.report.download', $file->id) }}"
                                class="btn btn-sm btn-secondary">
                                    Download
                                </a>
                            @else
                                <span class="text-muted">No File</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="13" class="text-center text-empty">
                            No records found for the selected date range
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            <form id="fileHistoryForm"
                method="POST"
                action="{{ route('report.file_history.view') }}"
                style="display:none;">
                @csrf
                <input type="hidden" name="file_id" id="history_file_id">
            </form>
        </div>

    </div>
</div>

@endsection

@push('plugin-scripts')
<script src="{{ asset('assets/plugins/datatables-net/jquery.dataTables.js') }}"></script>
<script src="{{ asset('assets/plugins/datatables-net-bs4/dataTables.bootstrap4.js') }}"></script>
<script>
    $(document).ready(function() {
        @if($files->isNotEmpty())
        $('#reportTable').DataTable({
            "pageLength": 25,
            "ordering": true,
            "searching": true,
            "info": true,
            "responsive": true,
            "order": [[0, 'asc']]
        });
        @endif
    });

    function submitFileHistory(fileId) {
        document.getElementById('history_file_id').value = fileId;
        document.getElementById('fileHistoryForm').submit();
    }

</script>
@endpush