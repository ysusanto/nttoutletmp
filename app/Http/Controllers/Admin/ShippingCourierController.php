<?php

namespace App\Http\Controllers\Admin;

use App\CityRo;
use App\CourierRo;
use Illuminate\Http\Request;
use App\Common\Authorizable;
use App\Http\Controllers\Controller;
use App\Repositories\ShippingCourier\ShippingCourierRepository;
use App\SubdistrictRo;
use Steevenz\Rajaongkir;

class ShippingCourierController extends Controller
{
    use Authorizable;
    private $model;

    private $shippingCourier;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct(ShippingCourierRepository $shipping_courier)
    {
        parent::__construct();

        $this->model_name = trans('app.model.shipping_courier');
        $this->rajaongkir = new Rajaongkir(env('RAJAONGKIR_APIKEY'), 'pro');
        $this->shippingCourier = $shipping_courier;
    }
    public function index()
    {
        //
        $shipping_courier = $this->shippingCourier->all();
        // echo json_encode($shipping_courier);die();
        return view('admin.shipping_courier.index', compact('shipping_courier'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        // return "dadad";
        return view('admin.shipping_courier._create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\CourierRo  $courierRo
     * @return \Illuminate\Http\Response
     */
    public function show(CourierRo $courierRo)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\CourierRo  $courierRo
     * @return \Illuminate\Http\Response
     */
    public function edit(CourierRo $courierRo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\CourierRo  $courierRo
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CourierRo $courierRo)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\CourierRo  $courierRo
     * @return \Illuminate\Http\Response
     */
    public function destroy(CourierRo $courierRo)
    {
        //
    }
   
    public function getsubdistrict()
    {
      
        $getcityro = \DB::table("city_ros")->whereIn('id_city_ro', array(105,113,164,178,195,208,455,154,296))->get();
     
        if ($getcityro != null) {
            foreach ($getcityro as $city) {
              
                $data =  $this->rajaongkir->getSubdistricts($city->id_city_ro);
             
              
                foreach ($data as $subdistrict) {

                    $substrict_ro = new SubdistrictRo();
                    $substrict_ro->id_city_ro = $city->id_city_ro;
                    $substrict_ro->id_subdistrict_ro = $subdistrict['subdistrict_id'];
                    $substrict_ro->subdistrict = $subdistrict['subdistrict_name'];
                    $substrict_ro->type = $subdistrict['type'];
                    $substrict_ro->save();
                }
              
               
            }
        }
        return view('admin.shipping_courier.index');
    }
}
