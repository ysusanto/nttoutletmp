<div class="form-group">
  <div class="input-group">
    <span class="input-group-addon flat"><i class="fas fa-user"></i></span>
    {!! Form::text('address_title', null, ['class' => 'form-control flat', 'placeholder' => trans('theme.placeholder.address_title') . '*', 'required']) !!}
  </div>
  <div class="help-block with-errors"></div>
</div>

<div class="row">
  <div class="col-md-8 nopadding-right">
    <div class="form-group">
      @if (isset($one_checkout_form))
      {!! Form::text('country_id', $cart->country->name, ['class' => 'form-control', 'disabled' => 'true']) !!}
      <div class="help-block with-errors small text-warning">{{ trans('checkout::lang.make_changes_on_cart_page') }}</div>
      @else
      {!! Form::select('country_id', $countries, isset($address) ? null : (isset($cart) ? $cart->ship_to_country_id : config('system_settings.address_default_country')), ['id' => 'address_country_id', 'class' => 'form-control flat', 'placeholder' => trans('theme.country') . '*', 'required']) !!}
      <div class="help-block with-errors"></div>
      @endif
    </div>
  </div>
  <div class="col-md-4 nopadding-left">
    <div class="form-group">
      {!! Form::text('zip_code', null, ['class' => 'form-control flat', 'placeholder' => trans('theme.placeholder.zip_code') . '*', 'required']) !!}
      <div class="help-block with-errors"></div>
    </div>
  </div>
</div>
<div class="form-group">
      @if (isset($one_checkout_form))
      {!! Form::text('state_id', $cart->state->name, ['class' => 'form-control', 'disabled' => 'true']) !!}
      <div class="help-block with-errors small text-warning">{{ trans('checkout::lang.make_changes_on_cart_page') }}</div>
      @else
      {!! Form::select('state_id', isset($states) ? $states : [], isset($cart) ? $cart->ship_to_state_id : (isset($address) ? null : config('system_settings.address_default_state')), ['id' => 'address_state_id', 'class' => 'form-control flat', 'placeholder' => trans('theme.placeholder.state') . '*', empty($states) ? '' : 'required']) !!}
      <div class="help-block with-errors"></div>
      @endif
    </div>
    <div class="form-group">
    {!! Form::select('city', isset($city) ? $city : [], isset($cart) ? $cart->city : config('system_settings.address_default_state'), ['id' => 'city', 'class' => 'form-control flat', 'placeholder' => trans('theme.placeholder.city') . '*', empty($city) ? '' : 'required']) !!}
    
      <div class="help-block with-errors"></div>
    </div>

<div class="form-group">
    {!! Form::select('subdistrict_2', isset($subdistrict_2) ? $subdistrict_2 : [], isset($cart) ? $cart->subdistrict_2 : config('system_settings.address_default_state'), ['id' => 'subdistrict_2', 'class' => 'form-control flat', 'placeholder' => trans('theme.placeholder.subdistrict_1') . '*', empty($subdistrict_2) ? '' : 'required']) !!}
    
      <div class="help-block with-errors"></div>
    </div>
<div class="form-group">
  {!! Form::text('address_line_1', null, ['class' => 'form-control flat', 'placeholder' => trans('theme.placeholder.address_line_1') . '*', 'required']) !!}
  <div class="help-block with-errors"></div>
</div>

<div class="form-group">
  {!! Form::text('address_line_2', null, ['class' => 'form-control flat', 'placeholder' => trans('theme.placeholder.address_line_2')]) !!}
  <div class="help-block with-errors"></div>
</div>

<div class="form-group">
  {!! Form::text('phone', null, ['class' => 'form-control flat', 'placeholder' => trans('theme.placeholder.phone_number')]) !!}
  <div class="help-block with-errors"></div>
</div>