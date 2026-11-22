@extends('layout.master')

@push('plugin-styles')

@endpush
@section('content')

<div class="container">
    <div class="row">
    <div class="col-md-12 grid-margin">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">Profile</h5>
            {{Form::open(array('url' => route('profile.store'), 'id' => 'profileForm',"method"=>"POST"))}}
			<div class=" row  ">
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
			
            <div class=" row  ">
                <div class="col-md-4 mb-3">
                  <div class="form-group">
              <label for="password">Password</label>
              <input id="password" class="form-control" name="password" type="password">
            </div>
              </div>
              
              <div class="col-md-4 mb-3">
                     <div class="form-group">
              <label for="confirm_password">Confirm password</label>
              <input id="confirm_password" class="form-control" name="confirm_password" type="password">
            </div>
                </div>
				</div>
				<div class=" row  ">
				<div class="col-md-4 mb-3">
				<div class="form-group">
                     <button class="btn btn-primary" type="submit">Update  </button>
					 </div>
                </div>
				
                </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

@endsection

@push('plugin-scripts')
<script src="{{ asset('assets/plugins/jquery-validation/jquery.validate.min.js') }}"></script>
@endpush

@push('custom-scripts')
<script>
	$(function() {
  'use strict';

  $(function() {
    // validate signup form on keyup and submit
    $("#profileForm").validate({
      rules: {
        password: {
          required: true,
          minlength: 5
        },
        confirm_password: {
          required: true,
          minlength: 5,
          equalTo: "#password"
        }
      },
      messages: {
        password: {
          required: "Please provide a password",
          minlength: "Your password must be at least 5 characters long"
        },
        confirm_password: {
          required: "Please provide a password",
          minlength: "Your password must be at least 5 characters long",
          equalTo: "Please enter the same password as above"
        }
      },
      errorPlacement: function(label, element) {
        label.addClass('mt-2 text-danger');
        label.insertAfter(element);
      },
      highlight: function(element, errorClass) {
        $(element).parent().addClass('has-danger')
        $(element).addClass('form-control-danger')
      }
    });
  });
});


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
@endpush 