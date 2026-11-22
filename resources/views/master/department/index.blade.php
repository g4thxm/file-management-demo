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

        {{-- PAGE HEADER --}}
        <div class="page-header">
            <h5>Department Master</h5>

            <div class="action-buttons">
                <a href="{{ route('department.create') }}" class="btn btn-primary">
                    <i data-feather="plus-circle"></i> Add Department
                </a>

                <button class="btn btn-outline-primary"
                        data-toggle="modal"
                        data-target="#importDepartmentModal">
                    <i data-feather="upload"></i> Upload
                </button>

                <a href="{{ route('department.export') }}"
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
                        <th>Department</th>
                        <th>Prefix</th>
                        <th>Branch</th>
                        <th style="width:120px">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($departments as $dept)
                        <tr>
                            <td class="text-center">{{ ++$i }}</td>
                            <td>{{ $dept->name }}</td>
                            <td>{{ $dept->prefix }}</td>
                            <td>{{ $dept->branch->name ?? '-' }}</td>
                            <td class="text-center">

                                {{-- Edit --}}
                                <a href="{{ route('department.edit', $dept->id) }}"
                                   title="Edit"
                                   class="mr-2">
                                    <i data-feather="edit" class="text-primary"></i>
                                </a>

                                {{-- Delete --}}
                                {{ Form::open([
                                    'method' => 'delete',
                                    'route' => ['department.destroy', $dept->id],
                                    'style' => 'display:inline'
                                ]) }}
                                    <button type="submit"
                                            class="btn btn-link p-0"
                                            title="Delete"
                                            onclick="return confirm('Delete this department?')">
                                        <i data-feather="trash" class="text-danger"></i>
                                    </button>
                                {{ Form::close() }}

                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-danger">
                                No Record Found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        <div class="d-flex justify-content-end mt-3">
            {{ $departments->links() }}
        </div>

    </div>
</div>
@endsection

{{-- IMPORT MODAL --}}
<div class="modal fade" id="importDepartmentModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST"
              action="{{ route('department.import') }}"
              enctype="multipart/form-data">
            @csrf
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Import Department</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <input type="file"
                           name="file"
                           class="form-control mb-2"
                           accept=".csv"
                           required>

                    <small>
                        Download predefined format
                        <a href="{{ route('department.downloadFormat') }}">
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
