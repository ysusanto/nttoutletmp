@extends('admin.layouts.master')

@section('content')
<div class="box">
	<div class="box-header with-border">
		<h3 class="box-title"><i class="fa fa-truck"></i> {{ trans('app.carriers') }}</h3>
<!-- edit by ari 03062021 -->
	</div> <!-- /.box-header -->
	<div class="box-body">
		<div class="row">
			<div class="spacer10"></div>
			{!! Form::open(['route' => 'admin.shipping.shippingZone.store', 'files' => true, 'id' => 'form', 'data-toggle' => 'validator']) !!}
			<!-- {!! Form::hidden('name', null,) !!} -->
			<div class="col-md-12 col-sm-12">

				<table class="table table-hover table-no-sort">
					<thead>
						<tr>

							<th>Courier</th>
							<th>Type Name</th>
							<th>Action</th>


							<!-- <th>{{ trans('app.option') }}</th> -->
						</tr>
					</thead>
					<tbody id="massSelectArea">
						@foreach($listcourier as $courier )
						<tr>

							<td>
								{{$courier['parent']}}
							</td>
							<td>
								{{$courier['name']}}
							</td>
							<td>

								@if($courier['checked']=="1")
								<input id="{{ $courier['id']}}" name="courier_id[]" type="checkbox" class="massCheck" checked="checked" value="{{$courier['id']}}">
								@else
								<input id="{{ $courier['id']}}" name="courier_id[]" type="checkbox" class="massCheck" value="{{$courier['id']}}">
								@endif


							</td>
						</tr>
						@endforeach
					</tbody>
				</table>

				<div class="row">
					<div class="col-md-12">
						{!! Form::submit(trans('app.form.save'), ['class' => 'btn btn-flat btn-new center']) !!}
					</div>
				</div>
			</div>
			{!! Form::close() !!}

		</div>
	</div> <!-- /.box-body -->
</div> <!-- /.box -->
@endsection