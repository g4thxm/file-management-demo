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
            <h5>Company Master</h5>

            <div class="action-buttons">
                <a href="{{ route('company.create') }}" class="btn btn-primary">
                    <i data-feather="plus-circle"></i> Add Company
                </a>

                <button class="btn btn-outline-primary"
                        data-toggle="modal"
                        data-target="#CompanyUploadModal">
                    <i data-feather="upload"></i> Upload
                </button>

                <a href="{{ route('company.export') }}"
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
                        <th>Company Code</th>
                        <th>Company Name</th>
                        <th style="width:120px">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($companies as $company)
                        <tr>
                            <td class="text-center">{{ ++$i }}</td>
                            <td>{{ $company->code }}</td>
                            <td>{{ $company->name }}</td>
                            <td class="text-center">

                                {{-- Edit --}}
                                <a href="{{ route('company.edit', $company->id) }}"
                                   title="Edit"
                                   class="mr-2">
                                    <i data-feather="edit" class="text-primary"></i>
                                </a>

                                {{-- Delete --}}
                                <form method="POST"
                                      action="{{ route('company.destroy', $company->id) }}"
                                      style="display:inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="btn btn-link p-0"
                                            title="Delete"
                                            onclick="return confirm('Are you sure?')">
                                        <i data-feather="trash" class="text-danger"></i>
                                    </button>
                                </form>

                            </td>
                        </tr>
                    @endforeach

                    @if($i == 0)
                        <tr>
                            <td colspan="4" class="text-center text-danger">
                                No Record Found
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

    </div>
</div>
@endsection

{{-- IMPORT MODAL --}}
<div class="modal fade" id="CompanyUploadModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST"
              action="{{ route('company.import') }}"
              enctype="multipart/form-data">
            @csrf

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Company Excel Upload</h5>
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
                        <a href="{{ route('company.download-format') }}">
                            Clicking Here
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
