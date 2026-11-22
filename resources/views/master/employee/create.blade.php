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
</style>
@endpush

@section('content')
@include('messages')

<div class="card">
    <div class="card-body">

        {{-- PAGE HEADER --}}
        <div class="page-header">
            <h5>{{ isset($employee) ? 'Update Employee' : 'Add Employee' }}</h5>
        </div>

        <div class="row">
            <div class="col-lg-6">

                {{-- FORM --}}
                @if(isset($employee))
                    {{ Form::model($employee, [
                        'route' => ['employee.update', $employee->id],
                        'method' => 'PATCH',
                        'autocomplete' => 'off'
                    ]) }}
                @else
                    {{ Form::open([
                        'route' => 'employee.store',
                        'autocomplete' => 'off'
                    ]) }}
                @endif

                <div class="form-section">
                    <div class="section-heading">Employee Details</div>

                    {{-- Branch --}}
                    <div class="form-group">
                        {{ Form::label('branch_id','Branch') }}
                        {{ Form::select(
                            'branch_id',
                            ['' => '-- Select Branch --'] + $branches->pluck('name','id')->toArray(),
                            null,
                            ['class'=>'form-control form-control-sm','id'=>'branch_id','required']
                        ) }}
                    </div>

                    {{-- Department --}}
                    <div class="form-group">
                        {{ Form::label('department_id','Department') }}
                        {{ Form::select(
                            'department_id',
                            ['' => '-- Select Department --'],
                            null,
                            ['class'=>'form-control form-control-sm','id'=>'department_id','required']
                        ) }}
                    </div>

                    {{-- Employee Name --}}
                    <div class="form-group">
                        {{ Form::label('name','Employee Name') }}
                        {{ Form::text('name',null,['class'=>'form-control form-control-sm','required']) }}
                    </div>

                    {{-- Designation --}}
                    <div class="form-group">
                        {{ Form::label('designation','Designation') }}
                        {{ Form::text('designation',null,['class'=>'form-control form-control-sm','required']) }}
                    </div>

                    {{-- Email --}}
                    <div class="form-group">
                        {{ Form::label('email','Email') }}
                        {{ Form::email('email',null,['class'=>'form-control form-control-sm','required']) }}
                    </div>

                    {{-- Description --}}
                    <div class="form-group">
                        {{ Form::label('description','Description') }}
                        {{ Form::textarea('description',null,[
                            'class'=>'form-control form-control-sm',
                            'rows'=>3
                        ]) }}
                    </div>

                    {{-- ACTION BUTTONS --}}
                    <div class="button-group">
                        <button type="submit" class="btn btn-primary">
                            {{ isset($employee) ? 'Update' : 'Save' }}
                        </button>

                        <a href="{{ route('employee.index') }}"
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

@push('custom-scripts')
<script>
const BASE_URL = "{{ url('/') }}";

$(document).ready(function () {

    if (!$('#branch_id').length) return;

    $('#branch_id').on('change', function () {
        let branchId = $(this).val();

        $('#department_id').html('<option value="">Loading...</option>');

        if (!branchId) {
            $('#department_id').html('<option value="">-- Select Department --</option>');
            return;
        }

        $.get(BASE_URL + '/departments/by-branch/' + branchId, function (data) {

            let options = '<option value="">-- Select Department --</option>';

            if ($.isEmptyObject(data)) {
                options += '<option value="">No departments found</option>';
            }

            $.each(data, function (id, name) {
                options += `<option value="${id}">${name}</option>`;
            });

            $('#department_id').html(options);
        });
    });

});
</script>
@endpush
