@extends('layout.master')

@push('plugin-styles')
<link href="{{ asset('assets/plugins/select2/select2.min.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet" />

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
        margin-top: 1.5rem;
        padding-top: 1rem;
        border-top: 1px solid #e0e0e0;
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
            <h5>{{ isset($user) ? 'Update User' : 'Add User' }}</h5>
        </div>

        {{-- FORM --}}
        @if(isset($user))
            {{ Form::model($user, [
                'route' => ['usermaster.update', $user->id],
                'method' => 'PATCH',
                'autocomplete' => 'off'
            ]) }}
        @else
            {{ Form::open([
                'action' => 'App\Http\Controllers\Master\UsermasterController@store',
                'method' => 'POST',
                'autocomplete' => 'off'
            ]) }}
        @endif

        {{-- BASIC INFORMATION --}}
        <div class="form-section">
            <div class="section-heading">Basic Information</div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-sm-4 control-label">
                            User Name <span class="text-danger">*</span>
                        </label>
                        <div class="col-sm-8">
                            {{ Form::text('name', null, ['class'=>'form-control form-control-sm','required']) }}
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-sm-4 control-label">
                            Login User Name <span class="text-danger">*</span>
                        </label>
                        <div class="col-sm-8">
                            {{ Form::text('username', null, ['class'=>'form-control form-control-sm','required']) }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-sm-4 control-label">
                            Email <span class="text-danger">*</span>
                        </label>
                        <div class="col-sm-8">
                            {{ Form::email('email', null, [
                                'class'=>'form-control form-control-sm',
                                'required',
                                'placeholder'=>'reviewer@example.test'
                            ]) }}
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-sm-4 control-label">
                            Employee <span class="text-danger">*</span>
                        </label>
                        <div class="col-sm-8">
                            {{ Form::select(
                                'employee_id',
                                $employees->pluck('name','id')->prepend('-- Select Employee --',''),
                                isset($user) ? $user->employee_id : null,
                                ['class'=>'form-control form-control-sm select2','required']
                            ) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- PASSWORD SECTION --}}
        <div class="form-section">
            <div class="section-heading">Security</div>

            @if(isset($user))
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group row" id="cnfirm_pwd">
                        <label class="col-sm-4 control-label">Change Password</label>
                        <div class="col-sm-8">
                            {{ Form::checkbox('chng_pswd', null, false, [
                                'id'=>'chng_pswd',
                                'onclick'=>'changepassword()'
                            ]) }}
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-sm-4 control-label">Password</label>
                        <div class="col-sm-8">
                            <input type="password" name="password" id="password"
                                   class="form-control form-control-sm"
                                   autocomplete="current-password">
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group row">
                        <label class="col-sm-4 control-label">Confirm Password</label>
                        <div class="col-sm-8">
                            <input type="password" name="confirm_password" id="confirm_password"
                                   class="form-control form-control-sm"
                                   autocomplete="current-password">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ACTION BUTTONS --}}
        <div class="button-group">
            {{ Form::submit(isset($user) ? 'Update' : 'Add', ['class'=>'btn btn-primary']) }}
            <button type="reset" class="btn btn-secondary">Clear</button>
        </div>

        {{ Form::close() }}

    </div>
</div>

{{-- PASSWORD TOGGLE SCRIPT --}}
@if(isset($user))
<script>
    $(document).ready(function () {
        $('#password, #confirm_password').prop('disabled', true);
    });

    function changepassword() {
        const enabled = $('#chng_pswd').is(':checked');
        $('#password, #confirm_password').prop('disabled', !enabled);
    }
</script>
@else
<script>
    $(document).ready(function () {
        $('#cnfirm_pwd').hide();
    });
</script>
@endif

@endsection
