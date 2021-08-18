<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Common\Authorizable;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Repositories\ShippingZone\ShippingZoneRepository;
use App\Http\Requests\Validations\CreateShippingZoneRequest;
use App\Http\Requests\Validations\UpdateShippingZoneRequest;
use App\Repositories\ShippingCourier\ShippingCourierRepository;
use App\Repositories\ShopCourier\ShopCourierRepository;
class ShippingZoneController extends Controller
{
    use Authorizable;

    private $model_name;

    private $shipping_zone;
    private $shop_courier;
    private $courier;

    /**
     * construct
     */
    public function __construct(ShippingZoneRepository $shipping_zone,ShopCourierRepository $shop_courier,ShippingCourierRepository $shippingCourierRepository)
    {
        parent::__construct();

        $this->model_name = trans('app.model.carrier');

        // $this->shipping_zone = $shipping_zone;
        $this->shop_courier=$shop_courier;
        $this->courier=$shippingCourierRepository;
    }
//edit by ari 15/05/2021
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $listcourier=array();
        $shop_id=Auth::user()->shop_id;
        // $shipping_zones = $this->shop_courier->allCourier();
        $couriers=$this->courier->all();
        $shopcourier=$this->shop_courier->getShopCourier($shop_id);
       if($couriers){
            foreach($couriers as $c){
                $checked="0";
                $checkshopcourier=$this->shop_courier->getShopCourierbycourier($shop_id,$c['id']);
                if($checkshopcourier){
                $checked="1";
                }
                $datacourier=array(
                    'id'=>$c['id'],
                    "parent"=>$c['parent'],
                    "name"=>$c['name'],
                    'checked'=> $checked
                );
                array_push($listcourier,$datacourier);
            }
       }
        return view('admin.shipping_zone.index', compact('listcourier'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.shipping_zone._create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data['courier_id'] = $request->input('courier_id');
        $data['shop_id'] = Auth::user()->shop_id;
        // echo json_encode($shop_id);die();
        $this->shop_courier->storedata($data);

        return back()->with('success', trans('messages.created', ['model' => $this->model_name]));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $shipping_zone = '';// $this->shipping_zone->find($id);

        return view('admin.shipping_zone._edit', compact('shipping_zone'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateShippingZoneRequest $request, $id)
    {
      //  $this->shipping_zone->update($request, $id);

        return back()->with('success', trans('messages.updated', ['model' => $this->model_name]));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $zone
     * @param  int  $country
     * @return \Illuminate\Http\Response
     */
    public function removeCountry(Request $request, $zone, $country)
    {
        //$this->shipping_zone->removeCountry($request, $zone, $country);

        return back()->with('success',  trans('messages.updated', ['model' => $this->model_name]));
    }

    /**
     * Update the specified resource from storage.
     *
     * @param  int  $zone
     * @param  int  $country
     * @return \Illuminate\Http\Response
     */
    public function editStates($zone, $country)
    {
        $shipping_zone = '';//$this->shipping_zone->find($zone);

        return view('admin.shipping_zone._states', compact('shipping_zone', 'country'));
    }

    /**
     * Update the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $zone
     * @param  int  $country
     * @return \Illuminate\Http\Response
     */
    public function updateStates(Request $request, $zone, $country)
    {
        //$this->shipping_zone->updateStates($request, $zone, $country);

        return back()->with('success',  trans('messages.updated', ['model' => $this->model_name]));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        //$this->shipping_zone->destroy($id);

        return back()->with('success',  trans('messages.deleted', ['model' => $this->model_name]));
    }

    /**
     * Return tax rate
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    public function ajaxGetTaxRate(Request $request)
    {
        // if ($request->ajax()){
        //     $taxrate = getTaxRate($request->input('ID'));

        //     return get_formated_decimal($taxrate, true, 2);
        // }

        return false;
    }
}
