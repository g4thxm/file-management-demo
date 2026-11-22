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

        {{-- PAGE HEADER (same as User / Sub Category list) --}}
        <div class="page-header">
            <h5>Category Master</h5>

            <div class="action-buttons">
                <a href="{{ route('category.create') }}" class="btn btn-primary">
                    <i data-feather="plus-circle"></i> Add Category
                </a>

                <button class="btn btn-outline-primary"
                        data-toggle="modal"
                        data-target="#importCategoryModal">
                    <i data-feather="upload"></i> Upload
                </button>

                <a href="{{ route('category.export') }}"
                   class="btn btn-outline-success">
                    <i data-feather="download"></i> Export
                </a>
            </div>
        </div>

        {{-- TABLE --}}
        <div class="table-responsive">
            <table id="dataTableExample" class="table"> {{-- table-bordered --}}
                <thead>
                    <tr>
                        <th style="width:60px">#</th>
                        <th>Category Name</th>
                        <th>Description</th>
                        <th style="width:120px">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                        <tr>
                            <td class="text-center">{{ ++$i }}</td>
                            <td>{{ $category->name }}</td>
                            <td>{{ $category->description }}</td>
                            <td class="text-center">

                                {{-- Edit --}}
                                <a href="{{ route('category.edit', $category->id) }}"
                                   title="Edit"
                                   class="mr-2">
                                    <i data-feather="edit" class="text-primary"></i>
                                </a>

                                {{-- Delete --}}
                                {{ Form::open([
                                    'method' => 'delete',
                                    'route' => ['category.destroy', $category->id],
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
                            <td colspan="4" class="text-center text-danger">
                                No Record Found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        <div class="d-flex justify-content-end mt-3">
            {{ $categories->links() }}
        </div>

    </div>
</div>

@endsection

{{-- IMPORT MODAL --}}
<div class="modal fade" id="importCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST"
              action="{{ route('category.import') }}"
              enctype="multipart/form-data">
            @csrf
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Import Category</h5>
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
                        <a href="{{ route('category.downloadFormat') }}">
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
