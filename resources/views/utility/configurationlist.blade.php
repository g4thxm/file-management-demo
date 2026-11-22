@extends('layout.master')

@push('plugin-styles')
<link href="{{ asset('assets/plugins/select2/select2.min.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet" />


@endpush
@section('content')
@include('sweetalert::alert')
<div class="content-header">
  

@include('messages')




<div class="card">
    <div class="card-body">
        <h6 class="card-title">Configuration</h6>
       


        <div class="row">
            <div class="col-md-12 text-right">





                <a class="btn " href="{{route('config.create')}}"> Update Configuration
                    <span class="fa fa-refresh"></span>
                </a>




            </div>
        </div>
        
        
            <div class="row">
                {{-- <div class="col-md-12 d-flex justify-content-between"> --}}
                <?php
                 $branch = $master;
                 ?>
                  @foreach($branch as $branches)
                  <div class="col-md-3">
                    <div class="card" style="width: 18rem; height: 12rem;">
                        <div class="card-body d-flex justify-content-center" >
                        @if (!empty($branches->front_logo))
                        <img align="center" class="card-img-top" src='{{ asset("dist/img/$branches->front_logo") }}' alt="Sidepanel Logo"  >
                        @else
                        <span class="text-muted">Not configured</span>
                        @endif

                        </div>
                        
                      </div>
                      <div class="card-body">
                        <p class="card-text">Sidepanel Logo.</p>

                    </div>
                      
                    </div>
                    
                    <div class="col-md-3">

                      <div class="card" style="width: 18rem; height: 12rem;">
                        <div class="card-body d-flex justify-content-center" >
                        @if (!empty($branches->login_logo))
                        <img class="card-img-top" src='{{ asset("dist/img/$branches->login_logo") }}' alt="Login Logo">
                        @else
                        <span class="text-muted">Not configured</span>
                        @endif
                      </div>
                        
                      </div>
                      <div class="card-body">
                        <p class="card-text">Login Logo</p>
                    </div>
                    </div>

                  <div class="col-md-3">

                      <div class="card" style="width: 18rem; height: 12rem;">
                        <div class="card-body d-flex justify-content-center" >
                        @if (!empty($branches->back_img))
                        <img class="card-img-top" src='{{ asset("dist/img/$branches->back_img") }}' alt="Login Background" >
                        @else
                        <span class="text-muted">Not configured</span>
                        @endif
                      </div>
                        
                      </div>
                      <div class="card-body">
                        <p class="card-text">Login Background</p>
                    </div>
                    </div>

                  <div class="col-md-3">

                      <div class="card" style="width: 18rem; height: 12rem;">
                        <div class="card-body d-flex justify-content-center" >

                        @if (!empty($branches->favi))
                        <img class="card-img-top" src='{{ asset("dist/img/$branches->favi") }}' alt="Favicon" style="width: 3rem; height: 3rem;">
                        @else
                        <span class="text-muted">Not configured</span>
                        @endif
                      </div>
                       
                      </div>
                      <div class="card-body">
                        <p class="card-text">Favicon</p>
                    </div>
                    </div>


            @endforeach
        {{-- </div> --}}
            
        </div>

       
       
         
    




    </div>


</div>

</div>


@endsection
@push('plugin-scripts')
<script src="{{ asset('assets/plugins/select2/select2.min.js') }}"></script>


@endpush

@push('custom-scripts')
<script src="{{ asset('assets/js/select2.js') }}"></script>


@endpush