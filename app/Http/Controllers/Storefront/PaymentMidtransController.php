<?php

namespace App\Http\Controllers\storefront;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Order;
use App\Cart;
use app\MidtransModel;
use App\Coupon;
use Carbon\Carbon;
class PaymentMidtransController extends Controller
{
	public function notification(Request $request)
	{

		$payload = $request->getContent();
		$notification = json_decode($payload);
		// echo json_encode($notification);
		// die();
		$validSignatureKey = hash("sha512", $notification->order_id . $notification->status_code . $notification->gross_amount . env('MIDTRANS_SERVER_KEY'));

		if ($notification->signature_key != $validSignatureKey) {
			return response(['message' => 'Invalid signature'], 403);
		}

		$this->InitMidtransPayment();
		$statusCode = null;

		$paymentNotification = new \Midtrans\Notification();
		// echo json_encode($paymentNotification);
		// die();
		$order = Order::where('order_number', $paymentNotification->order_id)->firstOrFail();
	
		if ($order->isPaid()) {
			return response(['message' => 'The order has been paid before'], 422);
		}

		$transaction = $paymentNotification->transaction_status;
		$type = $paymentNotification->payment_type;
		$orderId = $paymentNotification->order_id;
		$fraud = $paymentNotification->fraud_status;

		$vaNumber = null;
		$vendorName = null;
		if (!empty($paymentNotification->va_numbers[0])) {
			$vaNumber = $paymentNotification->va_numbers[0]->va_number;
			$vendorName = $paymentNotification->va_numbers[0]->bank;
		}

		$paymentStatus = null;
		if ($transaction == 'capture') {
			// For credit card transaction, we need to check whether transaction is challenge by FDS or not
			if ($type == 'credit_card') {
				if ($fraud == 'challenge') {
					// TODO set payment status in merchant's database to 'Challenge by FDS'
					// TODO merchant should decide whether this transaction is authorized or not in MAP
					$paymentStatus =  \App\MidtransModel::CHALLENGE;
				} else {
					// TODO set payment status in merchant's database to 'Success'
					$paymentStatus = \App\MidtransModel::SUCCESS;
				}
			}
		} else if ($transaction == 'settlement') {
			// TODO set payment status in merchant's database to 'Settlement'
			$paymentStatus =  \App\MidtransModel::SETTLEMENT;
		} else if ($transaction == 'pending') {
			// TODO set payment status in merchant's database to 'Pending'
			$paymentStatus = \App\MidtransModel::PENDING;
		} else if ($transaction == 'deny') {
			// TODO set payment status in merchant's database to 'Denied'
			$paymentStatus =  \App\MidtransModel::DENY;
		} else if ($transaction == 'expire') {
			// TODO set payment status in merchant's database to 'expire'
			$paymentStatus =  \App\MidtransModel::EXPIRE;
		} else if ($transaction == 'cancel') {
			// TODO set payment status in merchant's database to 'Denied'
			$paymentStatus =  \App\MidtransModel::CANCEL;
		}

		$paymentParams = [
			'order_id' => $order->id,
			'number' => MidtransModel::generateCode(),
			'amount' => $paymentNotification->gross_amount,
			'method' => 'midtrans',
			'status' => $paymentStatus,
			'token' => $paymentNotification->transaction_id,
			'payloads' => $payload,
			'payment_type' => $paymentNotification->payment_type,
			'va_number' => $vaNumber,
			'vendor_name' => $vendorName,
			'biller_code' => $paymentNotification->biller_code,
			'bill_key' => $paymentNotification->bill_key,
			'created_at' => date('Y-m-d H:i:s')
		];

		$payment = \App\MidtransModel::create($paymentParams);

		if ($paymentStatus && $payment) {
			\DB::transaction(
				function () use ($order, $payment) {
					if (in_array($payment->status, [MidtransModel::SUCCESS, MidtransModel::SETTLEMENT])) {
						$order->payment_status = Order::PAYMENT_STATUS_PAID;

						if ($order->order_status_id < Order::STATUS_CONFIRMED) {
							$order->order_status_id = Order::STATUS_CONFIRMED;
						}

						$order->save();
					}
				}
			);
		}

		$message = 'Payment status is : ' . $paymentStatus;

		$response = [
			'code' => 200,
			'message' => $message,
		];

		return response($response, 200);
	}
	public function complete(Request $request)
	{
		$code = $request->query('order_id');
		$order = Order::where('order_number', $code)->firstOrFail();

		if ($order->payment_status == Order::PAYMENT_STATUS_UNPAID) {
			return redirect('payments/failed?order_id=' . $code);
		}
		return view('theme::order_complete', compact('order'))->with('success', trans('theme.notify.order_placed'));
	}
	public function failed(Request $request)
	{
		$code = $request->query('order_id');
		$order = Order::where('order_number', $code)->firstOrFail();
		$cart =$this->moveAllItemsToCartAgain($order, false);

		return redirect()->route('cart.checkout', $cart)->with('error', trans('theme.notify.payment_failed'))->withInput();
	}
	public function unfinish(Request $request)
	{
	}
	//

	private function moveAllItemsToCartAgain($order, $revert = false)
    {
        if (!$order instanceof Order) {
            $order = Order::find($order);
        }

        if (!$order) return;

        // Set time
        $now = Carbon::now();

        // Save the cart
        $cart = Cart::create([
            'shop_id' => $order->shop_id,
            'customer_id' => $order->customer_id,
            'ship_to' => $order->ship_to,
            'shipping_zone_id' => $order->shipping_zone_id,
            'shipping_rate_id' => $order->shipping_rate_id,
            // 'ship_to_country_id' => $order->ship_to_country_id,
            // 'ship_to_state_id' => $order->ship_to_state_id,
            'packaging_id' => $order->packaging_id,
            'item_count' => $order->item_count,
            'quantity' => $order->quantity,
            'taxrate' => $order->taxrate,
            'shipping_weight' => $order->shipping_weight,
            'total' => $order->total,
            'shipping' => $order->shipping,
            'packaging' => $order->packaging,
            'handling' => $order->handling,
            'taxes' => $order->taxes,
            'grand_total' => $order->grand_total,
            'email' => $order->email,
            'ip_address' => get_visitor_IP(),
            'created_at' => $revert ? $order->created_at : $now,
            'updated_at' => $revert ? $order->updated_at : $now,
        ]);

        // Add order item into cart pivot table
        $cart_items = [];
        $quantity = 0;
        $shipping_weight = 0;
        $total = 0;
        $grand_total = 0;

        foreach ($order->inventories as $item) {
            // Skip if the item is out of stock
            if (!$item->stock_quantity > 0) {
                Session::flash('warning', trans('messages.some_item_out_of_stock'));
                continue;
            }

            // Get current updated price
            $unit_price = $item->current_sale_price();

            // Set qtt after checking availablity
            $item_qtt = $item->stock_quantity >= $item->pivot->quantity ?
                $item->pivot->quantity : $item->stock_quantity;

            $shipping_weight += $item->shipping_weight;
            $quantity += $item_qtt;
            $total += $item_qtt * $unit_price;

            $cart_items[] = [
                'cart_id'           => $cart->id,
                'inventory_id'      => $item->pivot->inventory_id,
                'item_description'  => $item->pivot->item_description,
                'quantity'          => $item_qtt,
                'unit_price'        => $unit_price,
                'created_at'        => $revert ? $item->pivot->created_at : $now,
                'updated_at'        => $revert ? $item->pivot->created_at : $now,
            ];

            // Sync up the inventory. Increase the stock of the order items from the listing
            if ($revert) {
                $item->increment('stock_quantity', $item->pivot->quantity);
            }
        }

        \DB::table('cart_items')->insert($cart_items);

        if ($revert) {
            // Increment the coupone in use
            if ($order->coupon_id) {
                Coupon::find($order->coupon_id)->increment('quantity');
            }

            $order->forceDelete();   // Delete the order
        }

        // Update cart
        $cart->quantity = $quantity;
        $cart->shipping_weight = $shipping_weight;
        $cart->total = $total;
        $cart->grand_total = $cart->calculate_grand_total();
        $cart->updated_at = $cart->updated_at;
        $cart->taxes = $cart->get_tax_amount();
        $cart->save();

        return $cart;
    }


}
