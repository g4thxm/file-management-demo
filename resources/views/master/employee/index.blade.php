@extends('layout.master')

@push('plugin-styles')
<link href="{{ asset('assets/plugins/datatables-net/dataTables.bootstrap4.css') }}" rel="stylesheet" />

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

    .action-buttons {
        display: flex;
        gap: .75rem;
    }
</style>
@endpush

@section('content')
@include('sweetalert::alert')
@include('messages')

<div class="card">
    <div class="card-body">

        {{-- PAGE HEADER (same as other masters) --}}
        <div class="page-header">
            <h5>Employee Master</h5>

            <div class="action-buttons">
                <a href="{{ route('employee.create') }}" class="btn btn-primary">
                    <i data-feather="plus-circle"></i> Add Employee
                </a>

                <button class="btn btn-outline-primary"
                        data-toggle="modal"
                        data-target="#employeeUploadModal">
                    <i data-feather="upload"></i> Upload
                </button>

                <a href="{{ route('employee.export') }}"
                   class="btn btn-outline-success">
                    <i data-feather="download"></i> Export
                </a>
            </div>
        </div>

        {{-- TABLE --}}
        <div class="table-responsive">
            <table id="dataTableExample" class="table">
                <thead>
                    <tr>
                        <th style="width:60px">#</th>
                        <th>Name</th>
                        <th>Designation</th>
                        <th>Branch</th>
                        <th>Department</th>
                        <th>Email</th>
                        <th style="width:120px">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $emp)
                        <tr>
                            <td class="text-center">{{ ++$i }}</td>
                            <td>{{ $emp->name }}</td>
                            <td>{{ $emp->designation }}</td>
                            <td>{{ $emp->branch->name ?? '-' }}</td>
                            <td>{{ $emp->department->name ?? '-' }}</td>
                            <td>{{ $emp->email }}</td>
                            <td class="text-center">

                                {{-- Edit --}}
                                <a href="{{ route('employee.edit', $emp->id) }}"
                                   title="Edit"
                                   class="mr-2">
                                    <i data-feather="edit" class="text-primary"></i>
                                </a>

                                {{-- Delete --}}
                                {{ Form::open([
                                    'method' => 'delete',
                                    'route' => ['employee.destroy', $emp->id],
                                    'style' => 'display:inline'
                                ]) }}
                                    <button type="submit"
                                            class="btn btn-link p-0"
                                            title="Delete"
                                            onclick="return confirm('Are you sure?')">
                                        <i data-feather="trash" class="text-danger"></i>
                                    </button>
                                {{ Form::close() }}

                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-danger">
                                No Record Found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        <div class="d-flex justify-content-end mt-3">
            {{ $employees->links() }}
        </div>

    </div>
</div>
@endsection

{{-- EMPLOYEE UPLOAD MODAL --}}
<div class="modal fade" id="employeeUploadModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST"
              action="{{ route('employee.import') }}"
              enctype="multipart/form-data">
            @csrf
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Employee Excel Upload</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <input type="file"
                           name="file"
                           class="form-control mb-2"
                           required>

                    <small class="text-muted">
                        Download predefined format
                        <a href="{{ route('employee.download-format') }}">
                            Click Here
                        </a>
                    </small>
                </div>

                <div class="modal-footer">
                    <button type="button"
                            class="btn btn-secondary"
                            data-dismiss="modal">
                        Close
                    </button>

                    <button type="submit"
                            class="btn btn-primary">
                        Upload
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>

@push('plugin-scripts')
<script src="{{ asset('assets/plugins/datatables-net/jquery.dataTables.js') }}"></script>
<script src="{{ asset('assets/plugins/datatables-net-bs4/dataTables.bootstrap4.js') }}"></script>
@endpush

@push('custom-scripts')
<script src="{{ asset('assets/js/data-table.js') }}"></script>
@endpush
