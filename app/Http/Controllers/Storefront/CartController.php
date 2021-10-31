<?php

namespace App\Http\Controllers\Storefront;

use Auth;
use App\Shop;
use App\Cart;
use App\CartParent;
use App\Order;
use App\State;
use App\Coupon;
use App\Country;
use App\Inventory;
use App\Packaging;
use App\ShippingRate;
use App\SystemConfig;
use App\PaymentMethod;
use App\Helpers\ListHelper;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Validations\DirectCheckoutRequest;
use Carbon\Carbon;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $expressId = Null)
    {
        $carts = Cart::whereNull('customer_id')->where('ip_address', $request->ip());
        $cartParent = CartParent::whereNull('customer_id')->where('ip_address', $request->ip());

        if (Auth::guard('customer')->check()) {
            $carts = $carts->orWhere('customer_id', Auth::guard('customer')->user()->id);
            $cartParent = $cartParent->orWhere('customer_id', Auth::guard('customer')->user()->id);
        }

        $carts = $carts->get();
        $cartParent = $cartParent->first();

        // Load related models
        $carts->load(['shop' => function ($q) {
            $q->with(['config', 'packagings' => function ($query) {
                $query->active();
            }])->active();
        }, 'state:id,name', 'country:id,name', 'inventories.image', 'shippingPackage']);

        $platformDefaultPackaging = getPlatformDefaultPackaging(); // Get platform's default packaging

        $business_areas = Country::select('id', 'name', 'iso_code')->orderBy('name', 'asc')->get();

        $geoip = geoip(request()->ip());

        $geoip_country = $business_areas->where('iso_code', $geoip->iso_code)->first();

        $geoip_state = State::select('id', 'name', 'iso_code', 'country_id')
            ->where('iso_code', $geoip->state)->where('country_id', $geoip_country->id)->first();

        return view('theme::cart', compact('carts', 'business_areas', 'geoip_country', 'geoip_state', 'platformDefaultPackaging', 'expressId', 'cartParent'));
    }

    /**
     * Add given item to cart
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function addToCart2(Request $request, $slug)
    {
        $item = Inventory::where('slug', $slug)->first();

        if (!$item) {
            return response()->json(trans('theme.item_not_available'), 404);
        }

        $customer_id = Auth::guard('customer')->check() ? Auth::guard('customer')->user()->id : Null;

        if ($customer_id) {
            $old_cart = Cart::where('shop_id', $item->shop_id)->where(function ($query) use ($customer_id) {
                $query->where('customer_id', $customer_id)->orWhere(function ($q) {
                    $q->whereNull('customer_id')->where('ip_address', request()->ip());
                });
            })->first();
        } else {
            $old_cart = Cart::where('shop_id', $item->shop_id)->whereNull('customer_id')
                ->where('ip_address', $request->ip())->first();
        }

        // Check if the item is alrealy in the cart
        if ($old_cart) {
            $item_in_cart = \DB::table('cart_items')->where('cart_id', $old_cart->id)
                ->where('inventory_id', $item->id)->first();

            if ($item_in_cart) {
                return response()->json(['cart_id' => $item_in_cart->cart_id], 444);  // Item alrealy in cart
            }
        }

        $qtt = $request->quantity ?? $item->min_order_quantity;
        $unit_price = $item->current_sale_price();

        // Instantiate new cart if old cart not found for the shop and customer
        $cart = $old_cart ?? new Cart;
        $cart->shop_id = $item->shop_id;
        $cart->customer_id = $customer_id;
        $cart->ip_address = $request->ip();
        $cart->item_count = $old_cart ? ($old_cart->item_count + 1) : 1;
        $cart->quantity = $old_cart ? ($old_cart->quantity + $qtt) : $qtt;

        if ($request->shipTo) {
            $cart->ship_to = $request->shipTo;
        }

        if ($request->shipToCountryId) {
            $cart->ship_to_country_id = $request->shipToCountryId;
        }

        if ($request->shipToStateId) {
            $cart->ship_to_state_id = $request->shipToStateId;
        }

        //Reset if the old cart exist, bcoz shipping rate will change after adding new item
        $cart->shipping_zone_id = $old_cart ? Null : $request->shippingZoneId;
        $cart->shipping_rate_id = $old_cart ? Null : $request->shippingRateId == 'Null' ? Null : $request->shippingRateId;

        $cart->handling = $old_cart ? $old_cart->handling : getShopConfig($item->shop_id, 'order_handling_cost');
        $cart->total = $old_cart ? ($old_cart->total + ($qtt * $unit_price)) : $unit_price;
        // $cart->packaging_id = $old_cart ? $old_cart->packaging_id : 1;

        // All items need to have shipping_weight to calculate shipping
        // If any one the item missing shipping_weight set null to cart shipping_weight
        if ($item->shipping_weight == Null || ($old_cart && $old_cart->shipping_weight == Null)) {
            $cart->shipping_weight = Null;
        } else {
            $cart->shipping_weight = $old_cart ? ($old_cart->shipping_weight + $item->shipping_weight) : $item->shipping_weight;
        }

        $cart->save();

        // Makes item_description field
        $attributes = implode(' - ', $item->attributeValues->pluck('value')->toArray());
        // Prepare pivot data
        $cart_item_pivot_data = [];
        $cart_item_pivot_data[$item->id] = [
            'inventory_id' => $item->id,
            'item_description' => $item->sku . ': ' . $item->title . ' - ' . $attributes . ' - ' . $item->condition,
            'quantity' => $qtt,
            'unit_price' => $unit_price,
        ];

        // Save cart items into pivot
        if (!empty($cart_item_pivot_data)) {
            $cart->inventories()->syncWithoutDetaching($cart_item_pivot_data);
        }

        return response()->json($cart->toArray(), 200);
    }

    public function addToCart(Request $request, $slug)
    {
        $item = Inventory::where('slug', $slug)->first();

        if (!$item) {
            return response()->json(trans('theme.item_not_available'), 404);
        }

        $customer_id = Auth::guard('customer')->check() ? Auth::guard('customer')->user()->id : Null;

        if ($customer_id) {
            $old_cart = Cart::where('shop_id', $item->shop_id)->where(function ($query) use ($customer_id) {
                $query->where('customer_id', $customer_id)->orWhere(function ($q) {
                    $q->whereNull('customer_id')->where('ip_address', request()->ip());
                });
            })->first();
        } else {
            $old_cart = Cart::where('shop_id', $item->shop_id)->whereNull('customer_id')
                ->where('ip_address', $request->ip())->first();
        }

        // Check if the item is alrealy in the cart
        if ($old_cart) {
            $item_in_cart = \DB::table('cart_items')->where('cart_id', $old_cart->id)
                ->where('inventory_id', $item->id)->first();

            if ($item_in_cart) {
                return response()->json(['cart_id' => $item_in_cart->cart_id], 444);  // Item alrealy in cart
            }
        }

        $qtt = $request->quantity ?? $item->min_order_quantity;
        $unit_price = $item->current_sale_price();
        $cart_parent_id = 0;
        $checkcartparent = CartParent::where("customer_id", $customer_id)->Where("ip_address", $request->ip())->first();
        if ($checkcartparent) {
            $cart_parent_id = $checkcartparent->id;
            $checkcartparent->total = $old_cart ? ($checkcartparent->total + ($qtt * $unit_price)) : $checkcartparent->total + $unit_price;
            $checkcartparent->updated_at = Carbon::now();
            $checkcartparent->save();
        } else {
            $cartparent = new CartParent();
            $cartparent->code = "CP" . date("YmdHis");
            $cartparent->total = $old_cart ? ($old_cart->total + ($qtt * $unit_price)) : $unit_price;
            $cartparent->order_status_id = "0";
            $cartparent->payment_method_id = "0";
            $cartparent->customer_id = $customer_id;
            $cartparent->ip_address = $request->ip();
            $cartparent->save();
            $cart_parent_id = $cartparent->id;
        }

        // Instantiate new cart if old cart not found for the shop and customer
        $cart = $old_cart ?? new Cart;
        $cart->shop_id = $item->shop_id;
        $cart->customer_id = $customer_id;
        $cart->ip_address = $request->ip();
        $cart->item_count = $old_cart ? ($old_cart->item_count + 1) : 1;
        $cart->quantity = $old_cart ? ($old_cart->quantity + $qtt) : $qtt;

        if ($request->shipTo) {
            $cart->ship_to = $request->shipTo;
        }

        if ($request->shipToCountryId) {
            $cart->ship_to_country_id = $request->shipToCountryId;
        }

        if ($request->shipToStateId) {
            $cart->ship_to_state_id = $request->shipToStateId;
        }

        //Reset if the old cart exist, bcoz shipping rate will change after adding new item
        $cart->shipping_zone_id = $old_cart ? Null : $request->shippingZoneId;
        $cart->shipping_rate_id = $old_cart ? Null : $request->shippingRateId == 'Null' ? Null : $request->shippingRateId;

        $cart->handling = $old_cart ? $old_cart->handling : getShopConfig($item->shop_id, 'order_handling_cost');
        $cart->total = $old_cart ? ($old_cart->total + ($qtt * $unit_price)) : $unit_price;
        // $cart->packaging_id = $old_cart ? $old_cart->packaging_id : 1;

        // All items need to have shipping_weight to calculate shipping
        // If any one the item missing shipping_weight set null to cart shipping_weight
        if ($item->shipping_weight == Null || ($old_cart && $old_cart->shipping_weight == Null)) {
            $cart->shipping_weight = Null;
        } else {
            $cart->shipping_weight = $old_cart ? ($old_cart->shipping_weight + $item->shipping_weight) : $item->shipping_weight;
        }
        $cart->cp_id = $cart_parent_id;
        $cart->save();

        // Makes item_description field
        $attributes = implode(' - ', $item->attributeValues->pluck('value')->toArray());
        // Prepare pivot data
        $cart_item_pivot_data = [];
        $cart_item_pivot_data[$item->id] = [
            'inventory_id' => $item->id,
            'item_description' => $item->sku . ': ' . $item->title . ' - ' . $attributes . ' - ' . $item->condition,
            'quantity' => $qtt,
            'unit_price' => $unit_price,
        ];

        // Save cart items into pivot
        if (!empty($cart_item_pivot_data)) {
            $cart->inventories()->syncWithoutDetaching($cart_item_pivot_data);
        }

        return response()->json($cart->toArray(), 200);
    }
    /**
     * Update the cart and redirected to checkout page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Cart    $cart
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $checkcart = Cart::where("cp_id", $request->cart_parent_id)->get();
        if ($checkcart) {
            // dd($request->zone_id);die();
            $x = 0;
            foreach ($checkcart as $cart) {

                $cart->ip_address = $request->ip();
                if (Auth::guard('customer')->check()) {
                    $cart->customer_id = Auth::guard('customer')->user()->id;
                }
                $r = new Request();
                $r->quantity = $request->qty[$cart->id];
                $r->zone_id = $request->zone_id[$x];
                $r->tax_id = $request->tax_id[$x];
                $r->taxrate = $request->taxrate[$x];
                $r->ship_to = $request->ship_to[$x];
                $r->packaging_id = $request->packaging_id[$x];
                $r->shipping_rate_id = $request->shipping_rate_id[$x];
                $r->ship_to_country_id = $request->ship_to_country_id[$x];
                $r->ship_to_state_id = $request->ship_to_state_id[$x];
              

                if (!crosscheckCartOwnership($r, $cart)) {
                    return redirect()->route('cart.index')->with('warning', trans('theme.notify.please_login_to_checkout'));
                }
                //      
                $cart = crosscheckAndUpdateOldCartInfo($r, $cart);
                $x++;
            }
        }


        // if (!crosscheckCartOwnership($request, $cart)) {
        //     return redirect()->route('cart.index')->with('warning', trans('theme.notify.please_login_to_checkout'));
        // }

        // $cart = crosscheckAndUpdateOldCartInfo($request, $cart);

        return redirect()->route('cart.checkout', $request->cart_parent_id);
    }

    /**
     * Checkout the specified cart.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function checkout2(Request $request, Cart $cart)
    {
        if (!crosscheckCartOwnership($request, $cart)) {
            return redirect()->route('cart.index')->with('warning', trans('theme.notify.please_login_to_checkout'));
        }

        $cart = crosscheckAndUpdateOldCartInfo($request, $cart);

        $shop = Shop::where('id', $cart->shop_id)->active()->with(['paymentMethods' => function ($q) {
            $q->active();
        }, 'config'])->first();

        // print_r($shop->config);
        // die();
        // Abort if the shop is not exist or inactive
        abort_unless($shop, 406, trans('theme.notify.seller_has_no_payment_method'));

        $customer = Auth::guard('customer')->check() ? Auth::guard('customer')->user() : Null;
        $business_areas = Country::select('id', 'name', 'iso_code')->orderBy('name', 'asc')->get();
        $states = $cart->ship_to_state_id ? ListHelper::states($cart->ship_to_country_id) : []; // Sate list of the country for ship_to dropdown
        $platformDefaultPackaging = getPlatformDefaultPackaging(); // Get platform's default packaging

        if (vendor_get_paid_directly()) {
            $paymentMethods = $shop->paymentMethods;
        } else {
            $paymentMethods = PaymentMethod::active()->get();
        }
        // dd($paymentMethods[0]);
        // die();
        return view('theme::checkout', compact('cart', 'customer', 'shop', 'business_areas', 'states', 'paymentMethods', 'platformDefaultPackaging'));
    }
    public function checkout(Request $request, $id)
    {
        // dd($id);die();
        $cartparent = CartParent::where("id", $id)->first();

        $carts = Cart::where("cp_id", $id)->get();
        if ($carts) {
            // dd($request->zone_id);die();
            $x = 0;
            $arrayypayment = array();
            foreach ($carts as $cart) {

                $cart->ip_address = $request->ip();
                if (Auth::guard('customer')->check()) {
                    $cart->customer_id = Auth::guard('customer')->user()->id;
                }
                $r = new Request();
                $r->quantity = $request->qty[$cart->id];
                $r->zone_id = $request->zone_id[$x];
                $r->tax_id = $request->tax_id[$x];
                $r->taxrate = $request->taxrate[$x];
                $r->ship_to = $request->ship_to[$x];
                $r->packaging_id = $request->packaging_id[$x];
                $r->shipping_rate_id = $request->shipping_rate_id[$x];
                $r->ship_to_country_id = $request->ship_to_country_id[$x];
                $r->ship_to_state_id = $request->ship_to_state_id[$x];


                if (!crosscheckCartOwnership($r, $cart)) {
                    return redirect()->route('cart.index')->with('warning', trans('theme.notify.please_login_to_checkout'));
                }
                //      
                $cart = crosscheckAndUpdateOldCartInfo($r, $cart);
                $shop = Shop::where('id', $cart->shop_id)->active()->with(['paymentMethods' => function ($q) {
                    $q->active();
                }, 'config'])->first();
                if (vendor_get_paid_directly()) {
                    $paymentMethods = $shop->paymentMethods;
                } else {
                    $paymentMethods = PaymentMethod::active()->get();
                }
             
                if (sizeof($paymentMethods) > 0) {
                    $paymentme=array();
                    foreach ($paymentMethods as $value) {
                        # code...\
                        // if($paymentMethods)
                        if(sizeof($paymentme)>0){
                            // print_r($value->id); 
                            // print_r(array_column($paymentme, 'id'));
                            // print_r(array_search($value->id, array_column($paymentme, 'id')));
                            // die() ;//array_search($value->id, array_column($paymentme, 'id')));die();
                            if(!in_array($value->id, array_column($paymentme, 'id'))){
                                array_push($paymentme,array(
                                    'id'=>$value->id,
                                    'code'=>$value->code,
                                    'type'=>$value->type,
                                    'name'=>$value->name
                                ));
                            }
                        }else{
                            array_push($paymentme,array(
                                'id'=>$value->id,
                                'code'=>$value->code,
                                'type'=>$value->type,
                                'name'=>$value->name
                            ));
                        }
                      
                    
                    }
                   if (sizeof($paymentme) > 0) {
                        $pay['shop']=$paymentme;
                        array_push($arrayypayment, $pay);
                   }
                }

                // dd($paymentMethods);die();
                $x++;
            }
        }
        $outputpayment=array();
        // echo json_encode($paymentme);die();
if(count($carts)>1){
        foreach ($arrayypayment as $key => $value) {
            # code...
            $shop=$value['shop'];
            foreach ($arrayypayment as $k => $v) {
                # code...
                $shop1=$v['shop'];
                foreach ($shop as  $s) {
                    # code...
                    foreach ($shop1 as $s1) {
                        if ( ! isset($outputpayment[$s['id']])) {
                            $outputpayment[$s['id']] = NULL ;
                        }
                        # code...
                        if (($s['id'] == $s1['id']) && ($key != $k))
                        {
                            if(is_array($outputpayment[$s['id']]) )
                            {
                                if(!in_array($s['id'],$outputpayment[$s['id']],true))
                                   {
                                  $outputpayment[$s['id']] = $s;
                                }
                            }
                            elseif ( $outputpayment[$s['id']] == NULL )
                            {
                                $outputpayment[$s['id']] = $s;
                               // $temp_array[$metode['id']] = array_replace($$temp_array[$metode['id']], NULL, $metode);
                            }
                          
                            
                         }
                    }
                }
            }
        }
    
        $outputpayment=array_values( $outputpayment);
        $outputpayment = array_filter($outputpayment);
    }else{
        $outputpayment = $paymentme;
    }
        // echo json_encode($outputpayment);die();
        // $checkcart = Cart::where('cp_id', $request->cart_parent_id)->get();
        $carts->load(['shop' => function ($q) {
            $q->with(['config', 'packagings' => function ($query) {
                $query->active();
            }])->active();
        }, 'state:id,name', 'country:id,name', 'inventories.image', 'shippingPackage']);
        $customer = Auth::guard('customer')->check() ? Auth::guard('customer')->user() : Null;
        $platformDefaultPackaging = getPlatformDefaultPackaging(); // Get platform's default packaging

        $business_areas = Country::select('id', 'name', 'iso_code')->orderBy('name', 'asc')->get();
        $states = [];
        // echo json_encode($carts);
        // die();
        return view('theme::checkout', compact('carts', 'customer', 'business_areas', 'states', 'platformDefaultPackaging', 'cartparent', 'outputpayment'));
    }

    /**
     * Direct checkout with the item/cart
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  str $slug
     *
     * @return \Illuminate\Http\Response
     */
    public function directCheckout(DirectCheckoutRequest $request, $slug)
    {
        $cart = $this->addToCart($request, $slug);

        if (200 == $cart->status()) {
            return redirect()->route('cart.index', $cart->getdata()->id);
        } elseif (444 == $cart->status()) {
            return redirect()->route('cart.index', $cart->getdata()->cart_id);
        }

        return redirect()->back()->with('warning', trans('theme.notify.failed'));
    }

    /**
     * Remove item from cart.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function remove(Request $request)
    {
        $cart = Cart::findOrFail($request->cart);

        $result = \DB::table('cart_items')->where([
            ['cart_id', $request->cart],
            ['inventory_id', $request->item],
        ])->delete();

        if ($result) {
            if (!$cart->inventories()->count()) {
                $cart->forceDelete();
            }

            return response('Item removed', 200);
        }

        return response('Item remove failed!', 404);
    }

    /**
     * validate coupon.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function validateCoupon(Request $request)
    {
        $coupon = Coupon::active()->where([
            ['code', $request->coupon],
            ['shop_id', $request->shop],
        ])->withCount(['orders', 'customerOrders'])->first();

        if (!$coupon) {
            return response('Coupon not found', 404);
        }

        if (!$coupon->isLive() || !$coupon->isValidCustomer()) {
            return response('Coupon not valid', 403);
        }

        if (!$coupon->isValidZone($request->zone)) {
            return response('Coupon not valid for shipping area', 443);
        }

        if (!$coupon->hasQtt()) {
            return response('Coupon qtt limit exit', 444);
        }

        return response()->json($coupon->toArray());
    }
}
