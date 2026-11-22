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
    }
</style>
@endpush

@section('content')
@include('messages')

<div class="card">
    <div class="card-body">

        {{-- PAGE HEADER --}}
        <div class="page-header">
            <h5>{{ isset($subcategory) ? 'Update Sub Category' : 'Add Sub Category' }}</h5>
        </div>

        {{-- FORM --}}
        <div class="row">
            <div class="col-lg-6">

                <form
                    action="{{ isset($subcategory)
                        ? route('subcategory.update', $subcategory->id)
                        : route('subcategory.store') }}"
                    method="POST">

                    @csrf
                    @if(isset($subcategory))
                        @method('PUT')
                    @endif

                    <div class="form-section">
                        <div class="section-heading">Sub Category Details</div>

                        {{-- Category --}}
                        <div class="form-group">
                            <label>
                                Category <span class="text-danger">*</span>
                            </label>
                            <select name="category_id"
                                    class="form-control form-control-sm"
                                    required>
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ old('category_id', $subcategory->category_id ?? '') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Sub Category Name --}}
                        <div class="form-group">
                            <label>
                                Sub Category <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   name="name"
                                   class="form-control form-control-sm"
                                   value="{{ old('name', $subcategory->name ?? '') }}"
                                   required>
                        </div>

                        {{-- Description --}}
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description"
                                      class="form-control form-control-sm"
                                      rows="3">{{ old('description', $subcategory->description ?? '') }}</textarea>
                        </div>

                        {{-- ACTION BUTTONS --}}
                        <div class="button-group">
                            <button type="submit" class="btn btn-primary">
                                {{ isset($subcategory) ? 'Update' : 'Save' }}
                            </button>

                            <a href="{{ route('subcategory.index') }}"
                               class="btn btn-secondary">
                                Back
                            </a>
                        </div>

                    </div>
                </form>

            </div>
        </div>

    </div>
</div>
@endsection
