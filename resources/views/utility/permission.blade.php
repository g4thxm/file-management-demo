@extends('layout.master')

@push('plugin-styles')
<link href="{{ asset('assets/plugins/select2/select2.min.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/datatables-net/dataTables.bootstrap4.css') }}" rel="stylesheet" />

@endpush

@section('content')
@include('sweetalert::alert')
    @include('messages')
    <section class="content">
        {{ Form::open(array("url"=>"permission","method"=>"POST","class"=>"form-horizontal validate","autocomplete"=>"no_auto_complete"))}}
        <div class="container-fluid">

            <div class="card">
                <div class="card-header">
                    <h6 class="card-title">User Permission</h6>
                    <div class="col-sm-6">
                        <div class="card-body">
                            <div class="row">
                                <label for="Scrap Value" class="col-sm-4 control-label">
                                    {{Form::label('username', 'User Name')}} <font color="#FF0000" size="">*</font> </label>
                                </label>
                                @php $permission1=[] @endphp
                                <div class="col-sm-8">
                                    @if(Auth::user()->id==1)
                                        {{Form::select('user_id',\App\Models\User::all()->sortBy('name')->pluck('name','id')->prepend('--select--',''),[$permission1],array('class'=>'form-control select2','id'=>'user_id'))}}
                                    @else
                                        {{Form::select('user_id',\App\Models\User::all()->whereNotIn('id',1)->sortBy('name')->pluck('name','id')->prepend('--select--',''),[$permission1],array('class'=>'form-control select2','id'=>'user_id'))}}
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row m-auto" id="permissioncheck">
                </div>
                <div class="card-body">
                    {{Form::submit('Save', ['class'=>'btn btn-primary'])}}
                    {{Form::reset('Cancel', ['class'=>'btn btn-default','onclick'=>'resetall()'])}}
                </div>
            </div>
        </div>
        {{Form::close()}}
    </section>
    <script>




        $(function () {
            //Initialize Select2 Elements
            $('.select2').select2()

            //Initialize Select2 Elements
            $('.select2bs4').select2({
                theme: 'bootstrap4'
            })


        });



        $(document).ready(function(){
            // on user change permission

            $('#user_id').change(function () {
                var userid=$(this).val();
                $.ajax({
                    type: 'POST',
                    url: "{{url('/ajax/permissionmenu')}}",
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                    },
                    data: {uid:userid},
                    success: function (response) {
                        var response1=response[x].menu;
                        var response2=response[x].submenu;
                        var response3=response[x].permission;
                        var flag=0;
                        if(response3==''){
                            response3=['',''];
                        }
                        for(a in response1){
                            var flag=0;
                            for(b in response2) {
                                if (response1[a].id == response2[b].menu_id) {
                                    for (c in response3) {
                                        if(response2[b].id == response3[c].submenu_id) {
                                            var ab="#"+response2[b].id+response1[a].link;
                                            $(ab).prop("checked", true);
                                            break;
                                        }
                                        else{
                                            var ab="#"+response2[b].id+response1[a].link;
                                            $(ab).prop("checked", false);
                                        }
                                    }
                                }
                            }

                            $.each($('.'+response1[a].link),function(){
                                if($(this).prop('checked')==false){
                                    flag=1;
                                }

                            });
                            if(flag==1){
                                $('#'+response1[a].link+'[name="cbox"').prop("checked", false);
                            }
                            else{
                                $('#'+response1[a].link+'[name="cbox"').prop("checked", true);
                            }
                        }
                    }
                });
            });


            // onload permission
            $.ajax({
                type: 'POST',
                url: "{{url('/ajax/permissionmenu')}}",
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                },
                success: function(response) {
                    var userpermission = '';
                    for (x in response) {
                        var response1 = response[x].menu;
                        var response2 = response[x].submenu;
                        for (a in response1) {
                            userpermission = userpermission +'<div class="col-sm-4  mt-4"><div class="card "><div class="card-header bg-info" color: white;"><h3 class="card-title">'+
                                response1[a].title+'</h3><div class="text-right">' +
                                'Select All&nbsp;&nbsp;&nbsp;<input type="checkbox" name="cbox" style="width: 14px; height: 14px; " onclick="myFunction(this.id,checked)" id="'+response1[a].link+'"> </div></div><div class="card-body"><table class="table table-striped"><tbody>';
                            for (b in response2) {
                                if (response1[a].id == response2[b].menu_id) {
                                    userpermission= userpermission+'<tr><td style="width: 90%">'+response2[b].title+'</td>' +
                                        '<td><input class="checkboxstyle '+response1[a].link+'" type="checkbox" name="'+response2[b].id+'" id="'+response2[b].id+response1[a].link+'" value="1">' +
                                        '</td></tr>';
                                }
                            }
                            // style="background-color: #727cf5; 
                            userpermission = userpermission +'</tbody></table></div></div></div>';
                        }
                        $('#permissioncheck').append(userpermission);
                    }
                }
            });
        });

        function myFunction(a,b) {
            $(document).ready(function () {
                var item=$('#'+a);
                if(b==true) {
                    $('.' + a).prop('checked', true);
                    item.prop('checked',true);
                }
                else{
                    $('.' + a).prop('checked', false);
                    item.prop('checked',false);
                }
            });

        }
    </script>
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