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

        $this->model = trans('app.model.shipping_courier');
        $this->rajaongkir = new Rajaongkir(env('RAJAONGKIR_APIKEY'), 'pro');
        $this->shippingCourier = $shipping_courier;
    }
    public function index()
    {
        //
        $shipping_courier = $this->shippingCourier->all();
        // $shipping_courier=json_decode(json_encode($shipping_courier));
        // print_r($shipping_courier);die();
        return view('admin.shipping_courier.index', compact('shipping_courier'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $listcouriero=  $this->rajaongkir->getCouriersList();
        // echo json_encode($listcouriero);die();
// dd("dada");
        //
        // return "dadad";
        return view('admin.shipping_courier._create',compact('listcouriero'));
    }
    private function getcodeCourier(){
        $listcouriero=  $this->rajaongkir->getCouriersList();
        $newarraycourier=array();
        foreach ($listcouriero as $key => $value) {
            # code...
            array_push($newarraycourier,array(
                'id'=>$key,
                "value"=>$value
            ));
        }
        return $newarraycourier;
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
        $checkparent=CourierRo::where([
            ["code","=",$request->courier_name],
            ["parent_id","=","0"]
            ])->first();
            if($checkparent){
                $courierRo=new CourierRo();
                $courierRo->parent_id=$checkparent->id;
                $courierRo->name=$request->servicetype;
                $courierRo->code=strtolower($request->servicetype);
                $courierRo->is_active=$request->status;
                $courierRo->created_at=date('Y-m-d H:i:s');
                $courierRo->save();
            }else{
                 $listcouriero=  $this->rajaongkir->getCouriersList();;
                $courierRoparent=new CourierRo();
                $courierRoparent->parent_id="0";
                $courierRoparent->name=$listcouriero[$request->courier_name];
                $courierRoparent->code=strtolower($request->courier_name);
                $courierRoparent->is_active=$request->status;
                $courierRoparent->created_at=date('Y-m-d H:i:s');
                $courierRoparent->save();


                $courierRo=new CourierRo();
                $courierRo->parent_id= $courierRoparent->id;
                $courierRo->name=$request->servicetype;
                $courierRo->code=strtolower($request->servicetype);
                $courierRo->is_active=$request->status;
                $courierRo->created_at=date('Y-m-d H:i:s');
                $courierRo->parent_code=strtolower( $courierRoparent->code);

                $courierRo->save();
            }
            return back()->with('success', trans('messages.created', ['model' => $this->model]));
        // print_r($request->courier_name);die();

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
    public function edit($id)
    {
        $listcouriero=  $this->rajaongkir->getCouriersList();
        $courierdata=$this->shippingCourier->courier($id);
        $courier=array(
            "id"=>$courierdata->id,
            "status" => $courierdata->is_active,
            "servicetype"=>$courierdata->name,
            "courier_name"=>$courierdata->parentcode
        );
        //
        // $datacourier=CourierRo
        return view('admin.shipping_courier._edit',compact('listcouriero'))->with("courier",$courier);
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
    public function remove($id){

    
    $courier=CourierRo::find($id)->delete();
    return back()->with('success', trans('messages.deleted', ['model' => $this->model]));
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
