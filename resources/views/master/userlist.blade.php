@extends('layout.master')

@push('plugin-styles')
<link href="{{ asset('assets/plugins/select2/select2.min.css') }}" rel="stylesheet" />
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
        gap: 0.75rem;
    }
</style>
@endpush

@section('content')
@include('sweetalert::alert')
@include('messages')

<div class="card">
    <div class="card-body">

        {{-- PAGE HEADER (same as File Creation) --}}
        <div class="page-header">
            <h5>User Master</h5>

            <div class="action-buttons">
                <a href="{{ route('usermaster.create') }}" class="btn btn-primary">
                    <i data-feather="plus-circle"></i> Add User
                </a>

                

                <button class="btn btn-outline-primary"
                        data-toggle="modal"
                        data-target="#importUserModal">
                    <i data-feather="upload"></i> Upload
                </button>

                <a href="{{ route('usermaster.export') }}"
                   class="btn btn-outline-success">
                    <i data-feather="download"></i> Export
                </a>
            </div>
        </div>

        {{-- TABLE --}}
        <div class="table-responsive">
            <table id="dataTableExample" class="table ">
                <thead>
                    <tr>
                        <th style="width:80px">SI.No</th>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Employee</th>
                        <th style="width:120px">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $usr)
                        <tr>
                            <td class="text-center">{{ ++$i }}</td>
                            <td>{{ $usr->name }}</td>
                            <td>{{ $usr->username }}</td>
                            <td>{{ $usr->employee->name ?? '-' }}</td>
                            <td class="text-center">

                                <a href="{{ route('usermaster.edit', $usr->id) }}" title="Edit">
                                    <i data-feather="edit" class="text-primary"></i>
                                </a>

                                {{ Form::open([
                                    'method' => 'delete',
                                    'action' => ['App\Http\Controllers\Master\UsermasterController@destroy', $usr->id],
                                    'style' => 'display:inline'
                                ]) }}
                                    <button type="submit"
                                            class="btn btn-sm"
                                            onclick="return confirm('Are you sure?')">
                                        <i data-feather="trash-2" class="text-danger"></i>
                                    </button>
                                {{ Form::close() }}

                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>
</div>

{{-- IMPORT MODAL --}}
<div class="modal fade" id="importUserModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST"
              action="{{ route('usermaster.import') }}"
              enctype="multipart/form-data">
            @csrf
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Import Users</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <input type="file"
                           name="file"
                           class="form-control"
                           accept=".csv"
                           required>

                    <small class="d-block mt-2">
                        Download predefined format
                        <a href="{{ route('usermaster.downloadFormat') }}">
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

@endsection

@push('plugin-scripts')
<script src="{{ asset('assets/plugins/datatables-net/jquery.dataTables.js') }}"></script>
<script src="{{ asset('assets/plugins/datatables-net-bs4/dataTables.bootstrap4.js') }}"></script>
@endpush

@push('custom-scripts')
<script src="{{ asset('assets/js/data-table.js') }}"></script>
@endpush
