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
@include('sweetalert::alert')

<div class="card">
    <div class="card-body">

        {{-- PAGE HEADER --}}
        <div class="page-header">
            <h5>Change Password</h5>
        </div>

        <div class="row">
            <div class="col-lg-6">

                {!! Form::open([
                    'route' => 'changepassword.update',
                    'method' => 'POST',
                    'autocomplete' => 'off'
                ]) !!}

                <div class="form-section">
                    <div class="section-heading">Security Details</div>

                    {{-- Old Password --}}
                    <div class="form-group">
                        {{ Form::label('oldpassword', 'Old Password') }}
                        <span class="text-danger">*</span>

                        <div class="input-group">
                            {{ Form::password('oldpassword', [
                                'class' => 'form-control form-control-sm password-field',
                                'required'
                            ]) }}
                            <div class="input-group-append">
                                <span class="input-group-text toggle-password" style="cursor:pointer">
                                    <i class="fa fa-eye"></i>
                                </span>
                            </div>
                        </div>

                        @error('oldpassword')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- New Password --}}
                    <div class="form-group">
                        {{ Form::label('newpassword', 'New Password') }}
                        <span class="text-danger">*</span>

                        <div class="input-group">
                            {{ Form::password('newpassword', [
                                'class' => 'form-control form-control-sm password-field',
                                'required',
                                'minlength' => 6
                            ]) }}
                            <div class="input-group-append">
                                <span class="input-group-text toggle-password" style="cursor:pointer">
                                    <i class="fa fa-eye"></i>
                                </span>
                            </div>
                        </div>

                        <small class="text-muted">Minimum 6 characters</small>

                        @error('newpassword')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- Confirm Password --}}
                    <div class="form-group">
                        {{ Form::label('confirmpassword', 'Confirm Password') }}
                        <span class="text-danger">*</span>

                        <div class="input-group">
                            {{ Form::password('confirmpassword', [
                                'class' => 'form-control form-control-sm password-field',
                                'required'
                            ]) }}
                            <div class="input-group-append">
                                <span class="input-group-text toggle-password" style="cursor:pointer">
                                    <i class="fa fa-eye"></i>
                                </span>
                            </div>
                        </div>

                        @error('confirmpassword')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- ACTION BUTTONS --}}
                    <div class="button-group">
                        <button type="submit" class="btn btn-primary">
                            Update Password
                        </button>

                        <a href="{{ route('home') }}" class="btn btn-secondary">
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
<script>
    $(document).on('click', '.toggle-password', function () {
        let input = $(this).closest('.input-group').find('.password-field');
        let icon  = $(this).find('i');

        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });
</script>
@endpush
