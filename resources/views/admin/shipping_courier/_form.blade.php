<div class="row">
  <div class="col-md-8 nopadding-right">
    <div class="form-group">
      {!! Form::label('name', 'Courier'.'*', ['class' => 'with-help']) !!}
      {!! Form::select('courier_old', $countries , null, ['id' => 'courier_old', 'class' => 'form-control select2']) !!}
      <br>
      {!! Form::text('courier_new', null, ['class' => 'form-control', 'placeholder' => "courier"]) !!}
      <div class="help-block with-errors"></div>
    </div>
  </div>
  <div class="col-md-4 nopadding-left">
    <div class="form-group">
      {!! Form::label('active', 'type'.'*', ['class' => 'with-help']) !!}
      {!! Form::text('type_new', null, ['class' => 'form-control', 'placeholder' => "Type"]) !!}
      <div class="help-block with-errors"></div>
    </div>
  </div>
</div>

<p class="help-block">* {{ trans('app.form.required_fields') }}</p>