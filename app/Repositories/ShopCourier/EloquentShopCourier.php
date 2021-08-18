<?php

namespace App\Repositories\ShopCourier;


use Illuminate\Http\Request;
use App\Repositories\BaseRepository;
use App\Repositories\EloquentRepository;
use App\ShopCourierRo;
use App\CourierRo;
use Carbon\Carbon;
class EloquentShopCourier extends EloquentRepository implements BaseRepository, ShopCourierRepository
{
	protected $model;
    protected $modelCourier;

	public function __construct(ShopCourierRo $shopCourier,CourierRo $courierRo)
	{
		$this->model = $shopCourier;
        $this->modelCourier=$courierRo;
	}

    public function all()
    {
        
        return $this->model->orderBy('id', 'asc')->get();
    }
    public function getShopCourier($shop_id){
        $couriers=$this->model->where('shop_id', $shop_id)->get();
      return $couriers;
    }
    public function getShopCourierbycourier($shop_id,$courier_id)
    {
        $couriers = $this->model->where([['shop_id',"=",$shop_id],['id_courier',"=",$courier_id]])->first();
        return $couriers;
    }
    public function storedata($data){
        $deleteshopcourier=$this->deleteshopcourierbyshop($data['shop_id']);
        if(sizeof($data['courier_id'])>0){
            foreach($data['courier_id'] as $couriers){
                $c=new ShopCourierRo();
                $c->shop_id=$data['shop_id'];
                $c->id_courier=$couriers;
                $c->created_at= Carbon::now()->timestamp;
                $c->save();
            }
        }
        return "ok";
    }
    public function allCourier()
    {

        // $shop_id = $request->user()->merchantId(); 
        $listShopCourier=array();
        $courier=$this->model->with('shop')->get();
        // echo json_encode($courier);die();
        if($courier){
            foreach($courier as $val){
                $couriername= $this->modelCourier->where('id', $val->id_courier)->first();
                if($couriername){
                    $parentname = $this->modelCourier->where('id', $couriername->parent_id)->first();
                    $datashopcourier=array(
                        'id'=>$val->id,
                        'courier_name'=>$parentname->name,
                        'type'=>$couriername->name,
                        'code'=>$couriername->code
                    );
                    array_push($listShopCourier,$datashopcourier);
                }
            }
        }
        return $listShopCourier;
    }
    public function destroy($id)
    {
        $slider = parent::find($id);

        $slider->flushImages();

        return $slider->forceDelete();
    }

    public function massDestroy($ids)
    {
        foreach ($ids as $id) {
            $this->destroy($id);
        }
    }
    public function deleteshopcourierbyshop($shop_id){

        $couriers = $this->model->where('shop_id', $shop_id)->get();
        if($couriers){
            foreach($couriers as $val){
                $this->model->findOrFail($val->id)->forceDelete();
            }
        }
        return 'ok';
    }
}