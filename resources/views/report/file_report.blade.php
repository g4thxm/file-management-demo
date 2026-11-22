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
    
    .filter-box {
        background: #f8f9fa;
        padding: 2rem;
        border-radius: 8px;
        margin-bottom: 2rem;
    }
    
    .filter-box label {
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
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    .btn-group-custom {
        display: flex;
        gap: 0.75rem;
        justify-content: center;
        margin-top: 1.5rem;
    }
    
    .btn {
        padding: 0.6rem 1.5rem;
        font-size: 0.95rem;
        border-radius: 6px;
        font-weight: 500;
        border: none;
    }
    
    .btn-primary {
        background: #667eea;
    }
    
    .btn-primary:hover {
        background: #5568d3;
    }
    
    .btn-success {
        background: #48bb78;
    }
    
    .btn-success:hover {
        background: #38a169;
    }
    
    .btn i {
        margin-right: 0.4rem;
    }
</style>
@endpush

@section('content')
@include('messages')

<div class="card">
    <div class="card-body">
        
        <div class="report-header">
            <h5>File Report</h5>
            @if(request('from_date') && request('to_date'))
                <small class="text-muted">
                    Report from {{ request('from_date') ? \Carbon\Carbon::parse(request('from_date'))->format('d M Y') : '' }} 
                    to {{ request('to_date') ? \Carbon\Carbon::parse(request('to_date'))->format('d M Y') : '' }}
                </small>
            @endif
        </div>

        <div class="filter-box">
            <form action="{{ route('file_report.view') }}" method="GET">
                <div class="row">
                    <div class="col-md-6">
                        <label for="from_date">From Date</label>
                        <input
                            type="date"
                            name="from_date"
                            id="from_date"
                            class="form-control"
                            value="{{ request('from_date') }}"
                            required
                        >
                    </div>

                    <div class="col-md-6">
                        <label for="to_date">To Date</label>
                        <input
                            type="date"
                            name="to_date"
                            id="to_date"
                            class="form-control"
                            value="{{ request('to_date') }}"
                            required
                        >
                    </div>
                </div>

                <div class="btn-group-custom">
                    <button
                        type="submit"
                        name="action"
                        value="view"
                        class="btn btn-primary"
                    >
                        <i class="fa fa-eye"></i> View Report
                    </button>

                    <button
                        type="submit"
                        name="action"
                        value="csv"
                        class="btn btn-success"
                    >
                        <i class="fa fa-download"></i> Download CSV
                    </button>

                    <button
                        type="submit"
                        name="action"
                        value="pdf"
                        class="btn btn-danger"
                    >
                        <i class="fa fa-file-pdf-o"></i> Export PDF
                    </button>

                </div>
            </form>
        </div>

        @if(isset($files))
        <div class="section-heading">Report Results</div>
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="dataTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>File Number</th>
                        <th>File Name</th>
                        <th>Subject</th>
                        <th>Status</th>
                        <th>Priority</th>
                        <th>Created Date</th>
                        <th>Branch</th>
                        <th>Department</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($files as $file)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $file->official_file_number }}</td>
                        <td>{{ $file->file_name }}</td>
                        <td>{{ $file->subject ?? '-' }}</td>
                        <td>
                            <span class="badge badge-{{ $file->status_badge }}">
                                {{ $file->status_label }}
                            </span>
                        </td>
                        <td>{{ ucfirst($file->priority) }}</td>
                        <td>{{ $file->created_at?->format('d M Y') }}</td>
                        <td>{{ $file->branch?->name ?? '-' }}</td>
                        <td>{{ $file->department?->name ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted">No files found for the selected date range</td>
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
<script src="{{ asset('assets/plugins/datatables-net/jquery.dataTables.js') }}"></script>
<script src="{{ asset('assets/plugins/datatables-net-bs4/dataTables.bootstrap4.js') }}"></script>
<script>
    $(document).ready(function() {
        @if(isset($files))
        $('#dataTable').DataTable({
            "pageLength": 25,
            "ordering": true,
            "searching": true,
            "info": true
        });
        @endif
    });
</script>
@endpush