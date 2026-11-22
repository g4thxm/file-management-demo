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
            <h5>{{ isset($department) ? 'Update Department' : 'Add Department' }}</h5>
        </div>

        <div class="row">
            <div class="col-lg-6">

                {{-- FORM --}}
                @if(isset($department))
                    {{ Form::model($department, [
                        'route' => ['department.update', $department->id],
                        'method' => 'PATCH',
                        'autocomplete' => 'off'
                    ]) }}
                @else
                    {{ Form::open([
                        'route' => 'department.store',
                        'autocomplete' => 'off'
                    ]) }}
                @endif

                <div class="form-section">
                    <div class="section-heading">Department Details</div>

                    {{-- Branch --}}
                    <div class="form-group">
                        {{ Form::label('branch_id','Branch') }}
                        {{ Form::select(
                            'branch_id',
                            $branches->pluck('name','id')->prepend('-- Select Branch --',''),
                            null,
                            ['class'=>'form-control form-control-sm','required']
                        ) }}
                    </div>

                    {{-- Department Name --}}
                    <div class="form-group">
                        {{ Form::label('name','Department Name') }}
                        {{ Form::text('name', null, [
                            'class'=>'form-control form-control-sm',
                            'required'
                        ]) }}
                    </div>

                    {{-- Prefix --}}
                    <div class="form-group">
                        {{ Form::label('prefix','Prefix (2 characters)') }}
                        {{ Form::text('prefix', null, [
                            'class'=>'form-control form-control-sm',
                            'maxlength'=>2,
                            'required'
                        ]) }}
                    </div>

                    {{-- Description --}}
                    <div class="form-group">
                        {{ Form::label('description','Description') }}
                        {{ Form::textarea('description', null, [
                            'class'=>'form-control form-control-sm',
                            'rows'=>3
                        ]) }}
                    </div>

                    {{-- ACTION BUTTONS --}}
                    <div class="button-group">
                        <button type="submit" class="btn btn-primary">
                            {{ isset($department) ? 'Update' : 'Save' }}
                        </button>

                        <a href="{{ route('department.index') }}"
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
