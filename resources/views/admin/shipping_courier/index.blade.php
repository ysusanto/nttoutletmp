@extends('admin.layouts.master')

@section('content')
<div class="box">
    <div class="box-header with-border">
        <h3 class="box-title">{{ trans('app.model.shipping_courier') }}</h3>
        <div class="box-tools pull-right">

            <a href="javascript:void(0)" data-link="{{ route('admin.shipping.shippingCourier.create') }}" class="ajax-modal-btn btn btn-new btn-flat">Add</a>

        </div>
    </div> <!-- /.box-header -->
    <div class="box-body">
  
        <table class="table table-hover table-no-sort">
            <thead>
                <tr>
                    <th class="massActionWrapper">
                        <!-- Check all button -->
                        <div class="btn-group ">
                            <button type="button" class="btn btn-xs btn-default checkbox-toggle">
                                <i class="fa fa-square-o" data-toggle="tooltip" data-placement="top" title="{{ trans('app.select_all') }}"></i>
                            </button>
                            <button type="button" class="btn btn-xs btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                <span class="caret"></span>
                                <span class="sr-only">{{ trans('app.toggle_dropdown') }}</span>
                            </button>
                            <ul class="dropdown-menu" role="menu">
                                <li><a href="javascript:void(0)" data-link="{{ route('admin.setting.role.massTrash') }}" class="massAction " data-doafter="reload"><i class="fa fa-trash"></i> {{ trans('app.trash') }}</a></li>
                                <li><a href="javascript:void(0)" data-link="{{ route('admin.setting.role.massDestroy') }}" class="massAction " data-doafter="reload"><i class="fa fa-times"></i> {{ trans('app.delete_permanently') }}</a></li>
                            </ul>
                        </div>
                    </th>
                    <th>Courier</th>
                    <th>Name</th>
                    <th>Action</th>


                    <!-- <th>{{ trans('app.option') }}</th> -->
                </tr>
            </thead>
            <tbody id="massSelectArea">
                @foreach($shipping_courier as $courier )
                <tr>
                    <td>

                    </td>
                    <td>
                        {{$courier['parent']}}
                    </td>
                    <td>
                        {{$courier['name']}}
                    </td>

                    <td>
                        <input id="{{ $courier['id']}}" name="courier_id[]" type="checkbox" class="massCheck" value="{{$courier['id']}}">
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div> <!-- /.box-body -->
</div> <!-- /.box -->


@endsection