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
            <h5>Branch Master</h5>

            <div class="action-buttons">
                <a href="{{ route('branch.create') }}" class="btn btn-primary">
                    <i data-feather="plus-circle"></i> Add Branch
                </a>

                <button class="btn btn-outline-primary"
                        data-toggle="modal"
                        data-target="#importBranchModal">
                    <i data-feather="upload"></i> Upload
                </button>

                <a href="{{ route('branch.export') }}"
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
                        <th style="width:60px">SI.No</th>
                        <th>Branch Name</th>
                        <th>Branch Code</th>
                        <th>Company</th>
                        <th>Address</th>
                        <th>GST Number</th>
                        <th style="width:120px">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @php $i = 1; @endphp
                    @foreach($master as $branches)
                        <tr>
                            <td class="text-center">{{ $i++ }}</td>
                            <td>{{ $branches->name }}</td>
                            <td>{{ $branches->Branch_Code }}</td>
                            <td>{{ $branches->company->name ?? '-' }}</td>
                            <td>{{ $branches->Address }}</td>
                            <td>{{ $branches->GST_No }}</td>
                            <td class="text-center">

                                {{-- Edit --}}
                                <a href="{{ route('branch.edit', $branches->id) }}"
                                   title="Edit"
                                   class="mr-2">
                                    <i data-feather="edit" class="text-primary"></i>
                                </a>

                                {{-- Delete --}}
                                {{ Form::open([
                                    'method'=>'delete',
                                    'route'=>['branch.destroy', $branches->id],
                                    'style'=>'display:inline'
                                ]) }}
                                    <button type="submit"
                                            class="btn btn-link p-0"
                                            title="Delete"
                                            onclick="return confirm('Are you sure you want to delete this Branch?')">
                                        <i data-feather="trash" class="text-danger"></i>
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
@endsection

{{-- IMPORT MODAL --}}
<div class="modal fade" id="importBranchModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST"
              action="{{ route('branch.import') }}"
              enctype="multipart/form-data">
            @csrf
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Import Branch</h5>
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
                        <a href="{{ route('branch.downloadFormat') }}">
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
