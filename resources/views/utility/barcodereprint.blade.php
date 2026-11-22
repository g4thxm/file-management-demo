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
            <h6 class="card-title">Barcode Reprint</h6>
            {!! Form::open(['route' => 'barcodereprint.store', 'method' => 'POST', 'enctype' => 'multipart/form-data']) !!}
            <br>
            <div class="row">
                      
                <div class="col-md-6 mb-3">
                    <div class="form-group">
                   

                            {{Form::label('doc_type', 'Document Type')}}
                            <font color="#FF0000" size="">*</font>
                      

                        {{Form::select('doc_type',[''=>'--select--' ,
                        '1'=> 'GRN',
                        '2'=> 'Production',
                        ],null,array('class' => 'js-example-basic-single  ' ,'id' =>'doc_type' ,'onchange' => 'grn_no()','required'  ))}}

                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <div class="form-group">

                       

                            {{Form::label('doc_no', 'Document Number')}}
                            <font color="#FF0000" size="">*</font>
                     

                     

                            {{Form::select('doc_no', [''=>'--select--' ,
                           
                            ],null,array('class'=>'js-example-basic-single ', 'id' => 'grn_id','required'))}}

                       
                    </div>
                </div>
           
            </div>
            <div class="row">
                      
                <div class="col-md-6 mb-3">
                    <div class="form-group">
                   

                        {{ Form::label('from_barcode', 'From Barcode') }}
                        {{ Form::text('from_barcode', null, ['class' => 'form-control mandatory']) }}

                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <div class="form-group">

                       

                            {{ Form::label('to_barcode', 'To Barcode') }}
                        {{ Form::text('to_barcode', null, ['class' => 'form-control mandatory']) }}

                       
                    </div>
                </div>
           
            </div>

            <div class="row">
                <div class="col-md-2">
                    <div class="form-group row">

                        <label for="Scrap Value" class="col-sm-4 control-label">

                            {{Form::label('prn', 'PRN Print')}} </label>

                        </label>

                        <div class="col-sm-8">

                            {{-- {{Form::checkbox('prn',false, ['class' => 'form-control',"checked" =>"true"])}} --}}
                            {{ Form::checkbox('prn', 'true', false, ['class' => 'custom-class', 'id' => 'prn']) }}
                        </div>

                    </div>
                </div>
            </div>
                 
            <div class="row mt-4">
                <div class="col-md-12">
                    {{ Form::button('Print',["class"=>"btn btn-primary","name"=>"Print","type"=>"submit","value"=>"1","target"=>"_blank"]) }}
                </div>
            </div>

            
          

            {!! Form::close() !!}
        </div>
    </div>
<script>
    function grn_no()
{
    
    var qc_type = document.getElementById('doc_type').value;

    $.ajax({
        headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            },            
            type: 'POST',
            url: "{{url('/ajax/get_doc')}}",
            dataType: 'json',
            data: {
                qc_type: qc_type
            },
            success: function(response) {

                // console.log(response);

                    $('#grn_id').html(response);

            }
        });
}
</script>

 @includeWhen(!is_null(session()->get('printable_content1')), 'print.barcodeprintpopup', ['printable_content1'=>session()->get('printable_content1')])

@if (!is_null(session()->get('printable_content')))
    <script>
        window.open( "{{ route('reprintdata.print',session()->get('printable_content')) }}");
    </script>
@endif
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