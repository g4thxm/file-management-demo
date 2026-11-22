@extends('layout.master')

@push('plugin-styles')
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

    #branch_code {
        text-transform: uppercase;
    }
</style>
@endpush

@section('content')
@include('messages')

<div class="card">
    <div class="card-body">

        {{-- PAGE HEADER --}}
        <div class="page-header">
            <h5>
                {{ isset($branch) ? 'Update Branch' : 'Add Branch' }}
            </h5>
        </div>

        <div class="row">
            <div class="col-lg-6">

                {{-- FORM --}}
                @if (isset($branch))
                    {{ Form::model($branch, [
                        'route' => ['branch.update', $branch->id],
                        'method' => 'PATCH',
                        'class' => 'form-horizontal validate',
                        'autocomplete' => 'off'
                    ]) }}
                @else
                    {!! Form::open([
                        'route' => 'branch.store',
                        'method' => 'POST',
                        'enctype' => 'multipart/form-data'
                    ]) !!}
                @endif

                <div class="form-section">
                    <div class="section-heading">Branch Details</div>

                    {{-- Branch Code --}}
                    <div class="form-group">
                        {{ Form::label('Branch_Code', 'Branch Code') }}
                        <span class="text-danger">*</span>
                        {{ Form::text('Branch_Code', null, [
                            'class' => 'form-control form-control-sm',
                            'required',
                            'maxlength' => 2,
                            'id' => 'branch_code'
                        ]) }}
                    </div>

                    {{-- Branch Name --}}
                    <div class="form-group">
                        {{ Form::label('name', 'Branch Name') }}
                        <span class="text-danger">*</span>
                        {{ Form::text('name', null, [
                            'class' => 'form-control form-control-sm',
                            'required'
                        ]) }}
                    </div>

                    {{-- Company --}}
                    <div class="form-group">
                        {{ Form::label('company_id', 'Company') }}
                        <span class="text-danger">*</span>
                        <select name="company_id"
                                class="form-control form-control-sm"
                                required>
                            <option value="">-- Select Company --</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}"
                                    @if(isset($branch) && $branch->company_id == $company->id) selected @endif>
                                    {{ $company->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Address --}}
                    <div class="form-group">
                        {{ Form::label('Address', 'Address') }}
                        <span class="text-danger">*</span>
                        {{ Form::textarea('Address', null, [
                            'class' => 'form-control form-control-sm',
                            'rows' => 4,
                            'required'
                        ]) }}
                    </div>

                    {{-- GST Number --}}
                    <div class="form-group">
                        {{ Form::label('GST_No', 'GST Number') }}
                        <span class="text-danger">*</span>
                        {{ Form::text('GST_No', null, [
                            'class' => 'form-control form-control-sm',
                            'required'
                        ]) }}
                    </div>

                    {{-- BUTTONS --}}
                    <div class="button-group">
                        <button type="submit" class="btn btn-primary">
                            {{ isset($branch) ? 'Update' : 'Add' }}
                        </button>

                        <a href="{{ route('branch.index') }}"
                           class="btn btn-secondary">
                            Cancel
                        </a>
                    </div>

                </div>

                {{ Form::close() }}

            </div>
        </div>

    </div>
</div>
@endsection
