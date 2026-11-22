@extends('layout.master')

@section('content')
@include('messages')

<div class="card">
    <div class="card-body">

        {{-- PAGE HEADER --}}
        <div class="page-header mb-4">
            <h5>Add Company</h5>
        </div>

        <div class="row">
            <div class="col-lg-6">

                {!! Form::open([
                    'route' => 'company.store',
                    'class' => 'form-horizontal',
                    'autocomplete' => 'off'
                ]) !!}

                <div class="form-section">

                    {{-- Company Code --}}
                    <div class="form-group">
                        <label>
                            Company Code <span class="text-danger">*</span>
                        </label>
                        {!! Form::text('code', null, [
                            'class' => 'form-control form-control-sm',
                            'required'
                        ]) !!}
                    </div>

                    {{-- Company Name --}}
                    <div class="form-group">
                        <label>
                            Company Name <span class="text-danger">*</span>
                        </label>
                        {!! Form::text('name', null, [
                            'class' => 'form-control form-control-sm',
                            'required'
                        ]) !!}
                    </div>

                    {{-- BUTTONS --}}
                    <div class="button-group">
                        <button type="submit" class="btn btn-primary">
                            Save
                        </button>

                        <a href="{{ route('company.index') }}"
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
