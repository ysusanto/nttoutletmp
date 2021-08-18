<?php

namespace App\Http\Controllers\Admin;

use Auth;
use App\Order;
use App\Common\Authorizable;
use Illuminate\Http\Request;
use App\Events\Order\OrderCreated;
use App\Events\Order\OrderUpdated;
use App\Events\Order\OrderFulfilled;
use App\Http\Controllers\Controller;
use App\Repositories\Order\OrderRepository;
use App\Repositories\ShippingCourier\ShippingCourierRepository;
use App\Http\Requests\Validations\CreateOrderRequest;
use App\Http\Requests\Validations\FulfillOrderRequest;
use App\Http\Requests\Validations\CustomerSearchRequest;
use Steevenz\Rajaongkir;
use Carbon\Carbon;
// use App\Services\PdfInvoice;
// use Konekt\PdfInvoice\InvoicePrinter;

class OrderController extends Controller
{
    use Authorizable;
    private $shippingCourier;
    private $model_name;
    private $rajaongkir;
    private $order;

    /**
     * construct
     */
    public function __construct(OrderRepository $order, ShippingCourierRepository $shipping_courier)
    {
        parent::__construct();
        $this->model_name = trans('app.model.order');
        $this->order = $order;
        $this->shippingCourier = $shipping_courier;
        $this->rajaongkir = new Rajaongkir(env('RAJAONGKIR_APIKEY'), 'pro');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $orders = $this->order->all();

        $archives = $this->order->trashOnly();

        return view('admin.order.index', compact('orders', 'archives'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function searchCutomer()
    {
        return view('admin.order._search_customer');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $data['customer'] = $this->order->getCustomer($request->input('customer_id'));

        $data['cart_lists'] = $this->order->getCartList($request->input('customer_id'));

        if ($request->input('cart_id')) {
            $data['cart'] = $this->order->getCart($request->input('cart_id'));
        }

        return view('admin.order.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateOrderRequest $request)
    {
        $order = $this->order->store($request);

        event(new OrderCreated($order));

        return redirect()->route('admin.order.order.index')
            ->with('success', trans('messages.created', ['model' => $this->model_name]));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        $order = $this->order->find($id);

        $this->authorize('view', $order); // Check permission
        // echo json_encode($order);die();
        $courier = $this->shippingCourier->courier($order->shipping_zone_id);
        // echo json_encode($courier);die();
        // 
        $address = $order->customer->primaryAddress();

        return view('admin.order.show', compact('order', 'address', 'courier'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function invoice($id)
    {
        $order = $this->order->find($id);

        $this->authorize('view', $order); // Check permission

        $order->invoice('D'); // Download the invoice
    }

    /**
     * Show the fulfillment form for the specified order.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function fulfillment($id)
    {
        $order = $this->order->find($id);

        $this->authorize('fulfill', $order); // Check permission

        return view('admin.order._fulfill', compact('order'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $order = $this->order->find($id);
        // echo json_encode($order);die();
        $this->authorize('fulfill', $order); // Check permission

        return view('admin.order._edit', compact('order'));
    }

    /**
     * Fulfill the order
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function fulfill(FulfillOrderRequest $request, $id)
    {
        $order = $this->order->find($id);

        $this->authorize('fulfill', $order); // Check permission

        $this->order->fulfill($request, $order);

        event(new OrderFulfilled($order, $request->filled('notify_customer')));

        if (config('shop_settings.auto_archive_order') && $order->isPaid()) {
            $this->order->trash($id);

            return redirect()->route('admin.order.order.index')
                ->with('success', trans('messages.fulfilled_and_archived'));
        }

        return back()->with('success', trans('messages.updated', ['model' => $this->model_name]));
    }

    /**
     * updateOrderStatus the order
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function updateOrderStatus(Request $request, $id)
    {
        // echo json_encode( $request->input('order_status_id'));die();
        $order = $this->order->find($id);
        if ($request->input('order_status_id') == "5") { // add by ari 05062021
            $order->tracking_id = $request->input('tracking_id');
            $order->shipping_date =Carbon::now()->format('Y-m-d');

            $order->save();
        }

        $this->authorize('fulfill', $order); // Check permission

        $this->order->updateOrderStatus($request, $order);

        event(new OrderUpdated($order, $request->filled('notify_customer')));

        return back()->with('success', trans('messages.updated', ['model' => $this->model_name]));
    }

    public function adminNote($id)
    {
        $order = $this->order->find($id);

        $this->authorize('fulfill', $order); // Check permission

        return view('admin.order._edit_admin_note', compact('order'));
    }

    public function saveAdminNote(Request $request, $id)
    {
        $order = $this->order->find($id);

        // $this->authorize('fulfill', $order); // Check permission

        $this->order->updateAdminNote($request, $order);

        return back()->with('success', trans('messages.updated', ['model' => $this->model_name]));
    }

    /**
     * Trash the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Order  $order
     * @return \Illuminate\Http\Response
     */
    public function archive(Request $request, $id)
    {
        $this->order->trash($id);

        return redirect()->route('admin.order.order.index')
            ->with('success', trans('messages.archived', ['model' => $this->model_name]));
    }

    /**
     * Restore the specified resource from soft delete.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function restore(Request $request, $id)
    {
        $this->order->restore($id);

        return back()->with('success', trans('messages.restored', ['model' => $this->model_name]));
    }

    /**
     * Toggle Payment Status of the given order, Its uses the ajax middleware
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function togglePaymentStatus(Request $request, $id)
    {
        if (Auth::user()->isFromMerchant() && !vendor_get_paid_directly()) {
            return back()->with('warning', trans('messages.failed', ['model' => $this->model_name]));
        }

        $order = $this->order->find($id);

        $this->authorize('fulfill', $order); // Check permission

        if ($order->isPaid()) {
            $order->markAsUnpaid();
        } else {
            $order->markAsPaid();
        }

        return back()->with('success', trans('messages.updated', ['model' => $this->model_name]));
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
        $this->order->destroy($id);

        return back()->with('success',  trans('messages.deleted', ['model' => $this->model_name]));
    }

    /**
     * Empty the Trash the mass resources.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function emptyTrash(Request $request)
    {
        $this->order->emptyTrash($request);

        if ($request->ajax()) {
            return response()->json(['success' => trans('messages.deleted', ['model' => $this->model_name])]);
        }

        return back()->with('success', trans('messages.deleted', ['model' => $this->model_name]));
    }

    public function getTrackshipping(Request $request)
    {
        $id=$request->input("id");
        // echo json_encode($id);die();
        $order = $this->order->find($id);
        // echo json_encode($order);die();
        $courier = $this->shippingCourier->courier($order->shipping_zone_id);
      
        $gettrackro =  $this->rajaongkir->getWaybill($order->tracking_id,strtolower($courier->parent));
        $listTrack = array();
        if ($gettrackro != null) {
            if(isset($gettrackro['delivery_status'])){
                if($gettrackro['delivery_status']['status']=="DELIVERED"){
                  
                    // add by ari 17082021
                        // $order->tracking_id = $request->input('tracking_id');
                        $order->delivery_date =Carbon::now()->format('Y-m-d');
                        $order->order_status_id=Order::STATUS_DELIVERED;
                        $order->save();
                
                }
            }
            if (count($gettrackro['manifest']) > 0) {
                foreach ($gettrackro['manifest'] as $value) {
                    # code...
                    $trackdata = array(
                        'm_id'=>$value['manifest_code'],
                        'desc'=>$value['manifest_description'],
                        'date_output'=>date("d M Y",strtotime($value['manifest_date'])),
                        'time'=>$value['manifest_time'],
                        'city'=>$value['city_name'],
                        'datetime_order'=>$value['manifest_date']." ".$value['manifest_time']
                    );
                    array_push($listTrack,$trackdata);
                }
            }
        }
        usort($listTrack, 'date_compare');
        return response($listTrack, 200);
    }
    function date_compare($a, $b)
    {
        $t1 = strtotime($a['datetime_order']);
        $t2 = strtotime($b['datetime_order']);
        return $t1 - $t2;
    }
}
