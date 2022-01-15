<?php

namespace App\Http\Controllers;

use App\State;
use App\Address;
use App\Cart;
use App\CityRo;
use App\Helpers\ListHelper;
use Illuminate\Http\Request;
use App\Repositories\Address\AddressRepository;
use App\Http\Requests\Validations\CreateAddressRequest;
use App\Http\Requests\Validations\UpdateAddressRequest;
use App\Repositories\SubdistrictRo\SubdistrictRoRepository;
use App\SubdistrictRo;
use Steevenz\Rajaongkir;

class AddressController extends Controller
{
    private $model_name;

    private $address;
    private $rajaongkir;
    private $subdistrictro;

    /**
     * construct
     */
    public function __construct(AddressRepository $address, SubdistrictRoRepository $subdistrictro)
    {
        parent::__construct();

        $this->model_name = trans('app.model.address');
        $this->address = $address;
        $this->subdistrictro = $subdistrictro;
        $config['api_key'] = env('RAJAONGKIR_APIKEY');
        $config['account_type'] = 'pro';
        $this->rajaongkir = new Rajaongkir(env('RAJAONGKIR_APIKEY'), 'pro');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function addresses($addressable_type, $addressable_id)
    {
        $data = $this->address->addresses($addressable_type, $addressable_id);

        return view('address.show', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($addressable_type, $addressable_id)
    {
        $addressable_type = get_qualified_model($addressable_type);

        return view('address._create', compact(['addressable_type', 'addressable_id']));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateAddressRequest $request)
    {
        $this->address->store($request);

        return back()->with('success', trans('messages.created', ['model' => $this->model_name]));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $address = $this->address->find($id);

        return view('address._edit', compact('address'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAddressRequest $request, $id)
    {
        $this->address->update($request, $id);

        return back()->with('success', trans('messages.updated', ['model' => $this->model_name]));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $this->address->destroy($id);

        return back()->with('success', trans('messages.deleted', ['model' => $this->model_name]));
    }

    /**
     * Response AJAX call to return states of a give country
     */
    public function ajaxCountryStates(Request $request)
    {
        if ($request->ajax()) {
            $states = ListHelper::states($request->input('id'));

            return response($states, 200);
        }

        return response('Not allowed!', 404);
    }
    //add by ari 27052021
    public function ajaxShippingCourier(Request $request)
    {

        if ($request->ajax()) {
            $cart = Cart::findorFail($request->input('cart'));
            $addresShop = \DB::table('addresses')
                ->select(['subdistrict_2', 'id'])->where('addressable_type', 'App\Shop')->where('addressable_id', $cart->shop_id)
                ->first();

            $idSubdistrictShop = $this->subdistrictro->getSubdistrictbyname(strtolower($addresShop->subdistrict_2));

            $addrescustomer = \DB::table('addresses as a')
                ->select(['subdistrict_2', 'id'])->where('addressable_type', 'App\Customer')->where('id', $request->input('address_id'))
                ->first();
            //   
            // print_r($addrescustomer);
            // die();
            $idSubdistrictCustomer = $this->subdistrictro->getSubdistrictbyname(strtolower($addrescustomer->subdistrict_2));
            // print_r($idSubdistrictCustomer);
            // die();
            $shopcourier = \DB::table('shop_courier_ros')
                ->leftJoin('courier_ros', 'shop_courier_ros.id_courier', '=', 'courier_ros.id')
                ->leftJoin('courier_ros as cr', 'courier_ros.parent_id', '=', 'cr.id')
                ->select(['shop_courier_ros.id', 'shop_courier_ros.id_courier', 'courier_ros.name as type_courier', 'cr.name as courier', 'cr.path_logo', 'courier_ros.code', 'cr.code as code_courier'])
                ->where('shop_id', $cart->shop_id)
                ->where("is_active","1")->get();
            $courier = array();
            $couriertype = array();
            if ($shopcourier != null) {
                foreach ($shopcourier as $sc) {
                    array_push($couriertype, $sc->type_courier);
                    if (!in_array($sc->code_courier, $courier)) {
                        array_push($courier, $sc->code_courier);
                    }
                }
            }

            $resultcourier = array();
            if ($idSubdistrictCustomer->id_subdistrict_ro != null && $idSubdistrictShop->id_subdistrict_ro != null && count($courier) > 0) {
                foreach ($courier as $c) {
                    $origintype = ListHelper::getTypeRo($idSubdistrictShop->type);
                    $detinationtype = ListHelper::getTypeRo($idSubdistrictCustomer->type);
                    $datasendro = array(
                        'origin' => $idSubdistrictShop->id_subdistrict_ro,
                        'originType' => $origintype,
                        'destination' => $idSubdistrictCustomer->id_subdistrict_ro,
                        'destinatioType' => $detinationtype,
                        'weight' => (int)  $cart->shipping_weight,
                        'courier' => strtolower($c)
                    );

                    $data =  $this->rajaongkir->getCost(["subdistrict" => (int)$idSubdistrictShop->id_subdistrict_ro], ["subdistrict" => (int) $idSubdistrictCustomer->id_subdistrict_ro], (int)  $cart->shipping_weight, strtolower($c)); //      RajaOngkir::Cost([
                    //     'origin'         => $addresShop->id_city_ro, // id kota asal
                    //     'originType' => $origintype,
                    //     'destination'     => $addrescustomer->id_city_ro, // id kota tujuan
                    //     'destinationType' =>$detinationtype,
                    //     'weight'         => (int)  $cart->shipping_weight, // berat satuan gram
                    //     'courier'         => strtolower($c), // kode kurir pengantar ( jne / tiki / pos )
                    // ])->get();
                    // print_r($shopcourier);die();
                    $courierservice = "";
                    if ($data != null && $shopcourier != null) {
                        foreach ($shopcourier as $row) {

                            $codero = explode("-", $row->code);
                            if (count($codero) > 0) {
                                if (count($codero) == 2) {
                                    $courierservice = $codero[1];
                                } else {
                                    $courierservice = $codero[0];
                                }
                            } else {
                                $courierservice = $codero;
                            }
                            if ($row->code_courier == "pos" && $courierservice == "kilat") {
                                $courierservice = "Paket Kilat Khusus";
                            }
                            if (strtolower($row->code_courier) == strtolower($data['code'])) {
                                foreach ($data['costs'] as $ct) {

                                    if (strtolower($courierservice) == strtolower($ct['service'])) {

                                        $datacourier = array(
                                            'id' => $row->id_courier,
                                            'courier' => $row->courier,
                                            'typeCourier' => $ct['service'],
                                            'cost' => $ct['cost'][0]['value'],
                                            'est' => $ct['cost'][0]['etd']
                                        );
                                        array_push($resultcourier, $datacourier);
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // echo json_encode($data);
            // die();
            // if($data['rajaongkir']['status']['code']==200){
            //     $resultro= $data['rajaongkir']['results'];
            // }

            $states = ListHelper::states($request->input('id'));

            return response($resultcourier, 200);
        }

        return response('Not allowed!', 404);
    }
    function ajaxStatesCity(Request $request)
    {
        if ($request->ajax()) {
            $states =\DB::table('states')->where('id', $request->input('id'))->orderBy('name', 'asc')->first(); 
            
            $provincero =\DB::table('provinces')->where('province_eng',  $states->name)->first(); 
          
            $city=\DB::table('city_ros')->where('id_province_ro',$provincero->province_id_ro)->orderBy('city', 'asc')->pluck("city");
           // dd($city);die();
            return response($city, 200);
        }

        return response('Not allowed!', 404);
    }
    function ajaxCitySubdistrict(Request $request)
    {
        if ($request->ajax()) {
            $city=\DB::table('city_ros')->where('city',$request->input('id'))->first();
            $subdistrik =\DB::table('subdistrict_ros')->where('id_city_ro', $city->id_city_ro)->orderBy('subdistrict', 'asc')->pluck('subdistrict'); 
            
         //   $provincero =\DB::table('provinces')->where('province_eng',  $states->name)->first(); 
          
            
           // dd($city);die();
            return response($subdistrik, 200);
        }

        return response('Not allowed!', 404);
    }
}
