<div class="row">
  <div class="col-md-8 nopadding-right">
    <div class="form-group">
   
      {!! Form::label('name', 'Courier Name'.'*', ['class' => 'with-help']) !!}
      {!! Form::select('courier_name', $listcouriero,null, ['id' => 'courier_name', 'class' => 'form-control select2']) !!}
      <div class="help-block with-errors"></div>
    </div>
  </div>
  <div class="col-md-4 nopadding-left">
    <div class="form-group">
      {!! Form::label('active', 'Status'.'*', ['class' => 'with-help']) !!}
      {!! Form::select('status',array("1"=>"Active","0"=>"Inactive"), null, ['id' => 'courier_name', 'class' => 'form-control select2']) !!}
      <!-- <select class="form-control" name="status" id="statuscourier">
        <option value="1">Active</option>
        <option value="0">Inactive</option>
      </select> -->
    
      <div class="help-block with-errors"></div>
    </div>
  </div>
</div>
<div class="row">
  <div class="col-md-8 nopadding-right">
    <div class="form-group">
      {!! Form::label('name', 'Service Type Name'.'*', ['class' => 'with-help']) !!}
      {!! Form::text('servicetype',null, ['class' => 'form-control', 'placeholder' => "Service Type",'required']) !!}
      <div class="help-block with-errors"></div>
    </div>
  </div>
</div>

<p class="help-block">* {{ trans('app.form.required_fields') }}</p>
