@extends('layout.master')

@push('plugin-styles')
<link href="{{ asset('assets/plugins/select2/select2.min.css') }}" rel="stylesheet" />

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

    .form-section {
        background: #fff;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 2rem;
        margin-bottom: 2rem;
    }

    .section-heading {
        font-size: 1rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 1.5rem;
        padding-bottom: .75rem;
        border-bottom: 2px solid #007bff;
    }

    .button-group {
        display: flex;
        gap: 1rem;
        padding-top: 1rem;
        margin-top: 1.5rem;
        border-top: 1px solid #e0e0e0;
        justify-content: flex-end;
    }
</style>
@endpush

@section('content')
@include('messages')

<div class="card">
    <div class="card-body">

        {{-- PAGE HEADER --}}
        <div class="page-header">
            <h5>{{ isset($category) ? 'Update Category' : 'Add Category' }}</h5>
        </div>

        <div class="row">
            <div class="col-lg-6">

                {{-- FORM --}}
                @if(isset($category))
                    {!! Form::model($category, [
                        'route' => ['category.update', $category->id],
                        'method' => 'PUT',
                        'autocomplete' => 'off'
                    ]) !!}
                @else
                    {!! Form::open([
                        'route' => 'category.store',
                        'method' => 'POST',
                        'autocomplete' => 'off'
                    ]) !!}
                @endif

                <div class="form-section">
                    <div class="section-heading">Category Details</div>

                    {{-- Category Name --}}
                    <div class="form-group">
                        <label>
                            Category Name <span class="text-danger">*</span>
                        </label>
                        {!! Form::text('name', null, [
                            'class' => 'form-control form-control-sm',
                            'required' => true,
                            'maxlength' => 100
                        ]) !!}
                    </div>

                    {{-- Description --}}
                    <div class="form-group">
                        <label>Description</label>
                        {!! Form::textarea('description', null, [
                            'class' => 'form-control form-control-sm',
                            'rows' => 3
                        ]) !!}
                    </div>

                    {{-- ACTION BUTTONS --}}
                    <div class="button-group">
                        {!! Form::submit(
                            isset($category) ? 'Update' : 'Save',
                            ['class' => 'btn btn-primary']
                        ) !!}
                        <a href="{{ route('category.index') }}"
                           class="btn btn-secondary">
                            Cancel
                        </a>
                    </div>
                </div>

                {!! Form::close() !!}

            </div>
        </div>

    </div>
</div>
@endsection

@push('plugin-scripts')
<script src="{{ asset('assets/plugins/select2/select2.min.js') }}"></script>
@endpush
