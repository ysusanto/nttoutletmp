<div class="modal-dialog modal-md">
    <div class="modal-content">
    @if(isset($courier))
    {!! Form::model($courier, ['method' => 'PUT', 'route' => ['admin.shipping.shippingCourier.update', $courier->id], 'files' => true, 'id' => 'form', 'data-toggle' => 'validator']) !!}
    @else
    {!! Form::open(['route' => 'admin.shipping.shippingCourier.store', 'files' => true, 'id' => 'form', 'data-toggle' => 'validator']) !!}
@endif
       
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            {{ trans('app.form.form') }}
        </div>
        <div class="modal-body">
            @include('admin.shipping_courier._form')
        </div>
        <div class="modal-footer">
            {!! Form::submit(trans('app.form.save'), ['class' => 'btn btn-flat btn-new']) !!}
        </div>
        {!! Form::close() !!}
    </div> <!-- / .modal-content -->
</div> <!-- / .modal-dialog -->