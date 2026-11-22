@extends('layout.master')
@push('plugin-styles')
<link href="{{ asset('assets/plugins/select2/select2.min.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet" />

@endpush




@section('content')
@include('sweetalert::alert')
<div class="content-header">





    @include('messages')

    <section class="content">

        <div class="container-fluid">

            <div class="card">

                <div class="card-header">

                    <h3 class="card-title"> Configuration

                    </h3>

                </div>

                <div class="col-md-6">

                   
                    {!! Form::open(['route' => 'config.store', 'method' => 'POST', 'enctype' => 'multipart/form-data']) !!}

                    

                    <div class="card-body">



                        <div class="form-group row">

                            <label for="Scrap Value" class="col-sm-4 control-label">

                                {{ Form::label('file', 'Company Logo:') }}
                                <font color="#FF0000" size="">
                                </font>
                            </label>

                            </label>

                            <div class="col-sm-8">

                                {{ Form::file('company_logo', null, ['class' => 'form-control mandatory', "accept" => "image/*", "data-rule-filesize" => "1512"]) }}

                            </div>

                        </div>


                        <div class="form-group row">

                            <label for="Scrap Value" class="col-sm-4 control-label">

                                {{ Form::label('file', 'Login Logo:') }}
                                <font color="#FF0000" size="">
                                </font>
                            </label>

                            </label>

                            <div class="col-sm-8">

                                {{ Form::file('login_logo', null, ['class' => 'form-control mandatory', "accept" => "image/*", "data-rule-filesize" => "1512"]) }}

                            </div>

                        </div>

                        <div class="form-group row">

                            <label for="Scrap Value" class="col-sm-4 control-label">

                                {{ Form::label('file', 'Login Backgrond:') }}
                                <font color="#FF0000" size="">
                                </font>
                            </label>

                            </label>

                            <div class="col-sm-8">

                                {{ Form::file('login_background', null, ['class' => 'form-control mandatory', "accept" => "image/*", "data-rule-filesize" => "1512"]) }}

                            </div>

                        </div>

                        <div class="form-group row">

                            <label for="Scrap Value" class="col-sm-4 control-label">

                                {{ Form::label('file', 'Favicon:') }}
                                <font color="#FF0000" size="">
                                </font>
                            </label>

                            </label>

                            <div class="col-sm-8">

                                {{ Form::file('favicon', null, ['class' => 'form-control mandatory', "accept" => "image/*", "data-rule-filesize" => "1512"]) }}

                            </div>

                        </div>

                       

                     







                        <div class="form-group row">

                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                            <div class="form-check form-check-inline">
                              <label class="form-check-label">
                                <input type="radio" class="form-check-input" name="darkmode" id="darkmode0" value="0" @if($darkmode == 0) checked="" @endif>
                                Light mode
                              </label>
                            </div>
                            <div class="form-check form-check-inline">
                              <label class="form-check-label">
                                <input type="radio" class="form-check-input" name="darkmode" id="darkmode1" value="1" @if($darkmode == 1) checked="" @endif>
                                Dark mode
                              </label>
                            </div>
                            </div>
                            </div>

                        </div>





                    </div>

                    <div class="card-body">

                        {{ Form::button('Save',["class"=>"btn btn-primary","name"=>"Print","type"=>"submit","value"=>"1","target"=>"_blank"]) }}

                        <input type="reset" name="Submit2" class="btn btn-default" value="Clear" />

                    </div>



                    {!! Form::close() !!}

                </div>









            </div>

        </div>

    </section>

</div>
<script>
    $(document).ready(function(){
         $('input[type=radio][name=darkmode]').change(function() {
             if (this.value == 0) {
                 window.location.href = "{{ url('darkmode/0') }}";
                 //alert("Select Light");
             }else if (this.value == 1) {
                 window.location.href = "{{ url('darkmode/1') }}";
                 //alert("Select Dark");
             }
         });
     });
 </script>
@endsection
@push('plugin-scripts')
<script src="{{ asset('assets/plugins/select2/select2.min.js') }}"></script>

@endpush

@push('custom-scripts')
<script src="{{ asset('assets/js/select2.js') }}"></script>

@endpush