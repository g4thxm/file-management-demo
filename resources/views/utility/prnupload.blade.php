@extends('layout.master')

@push('plugin-styles')
<link href="{{ asset('assets/plugins/select2/select2.min.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/datatables-net/dataTables.bootstrap4.css') }}" rel="stylesheet" />

@endpush
@section('content')
@include('sweetalert::alert')
@include('messages')


    <div class="card">
        <div class="card-body">
            <h6 class="card-title">Label Settings </h6>
            {!! Form::open(['route' => 'prnupload.store', 'method' => 'POST', 'enctype' => 'multipart/form-data']) !!}
            <div class="row ">
                <div class="col-md-6 mb-3">
                    <div class="form-group">
                   

                            {{Form::label('prn_type', 'Document Type')}}
                            <font color="#FF0000" size="">*</font>
                      

                        {{Form::select('prn_type',[''=>'--select--' ,
                        '1'=> 'GRN',
                        '2'=> 'Production',
                        ],null,array('class' => 'js-example-basic-single  ' ,'id' =>'prn_type' ))}}

                    </div>
                </div>
                <div class="col-md-6 form-group">                   
                       <div class="col-md-12 form-group">  
                        <div>
                        {{ Form::label('file', 'File') }}<font color="#FF0000" size="">*</font>
                            
                        </div>                         
                        {{ Form::file('file', null, ['class' => 'form-control mandatory','accept'=>'text/plain', 'required']) }}
                       </div>                        
                 </div>
            </div>
            <div class="row mt-4">
               
                <div class="col-md-12 ">
                    {{ Form::button('Upload',["class"=>"btn btn-primary","name"=>"Upload","type"=>"submit","value"=>"1"]) }}
                </div>
            </div>

            
          

            {!! Form::close() !!}
            <br><br>
            <div class="box-body">
                <div class="col-md-12">
                    <div class="col-md-3">
                    </div>
                    <div class="col-md-6">
                        <table class="table">
                            <thead>
                            <tr>
                                <th>
                                    Field
                                </th>
                                <th>
                                    Tag
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>Doc No</td>
                                <td>[doc_no]</td>
                            </tr>
                            <tr>
                                <td>Serial Number</td>
                                <td>[barcode]</td>
                            </tr>
                            <tr>
                                <td>Item Name</td>
                                <td>[Item_name]</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection
@push('plugin-scripts')
<script src="{{ asset('assets/plugins/select2/select2.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatables-net/jquery.dataTables.js') }}"></script>
<script src="{{ asset('assets/plugins/datatables-net-bs4/dataTables.bootstrap4.js') }}"></script>

@endpush

@push('custom-scripts')
<script src="{{ asset('assets/js/select2.js') }}"></script>
<script src="{{ asset('assets/js/data-table.js') }}"></script>


@endpush