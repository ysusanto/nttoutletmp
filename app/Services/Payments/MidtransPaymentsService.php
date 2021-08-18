<?php

namespace App\Services\Payments;

use Auth;
use App\Order;
use Illuminate\Http\Request;
use App\Contracts\PaymentServiceContract;
use Incevio\Cybersource\CybersourceSDK\ApiException;
use Midtrans;

class MidtransPaymentService
{
	public $success;
	public $request;
	public $payee;
	public $receiver;
	public $order;
	public $amount;
	public $fee;
	public $description;
	public $meta;
	public $card;
	public $sandbox;
	public $billingAddress;
	public $amountObj;
	public $refference;

	public function __construct(Request $request)
	{
		$this->request = $request;

		// Get payee model
		if ($this->request->has('payee')) {
			$this->setPayee($this->request->payee);
		} elseif (Auth::guard('customer')->check()) {
			$this->setPayee(Auth::guard('customer')->user());
		} elseif (Auth::guard('web')->check() && Auth::user()->isMerchant()) {
			$this->setPayee(Auth::user()->owns);
		}
	}

	public function charge()
	{
		$this->success = TRUE;
		return $this;
	}

	public function setPayee($payee)
	{
		$this->payee = $payee;

		return $this;
	}

	public function setAmount($amount)
	{
		$this->amount = $amount;

		return $this;
	}

	public function setDescription($description = '')
	{
		$this->description = $description;

		return $this;
	}

	public function setReceiver($receiver = 'platform')
	{
		$this->receiver = $receiver;

		return $this;
	}

	public function setOrderInfo(Order $order)
	{
		$this->order = $order;

		return $this;
	}

	public function setConfig()
	{

		return $this;
	}

	private function setBillingAddress()
	{
		$address = Null;

		if ($this->payee) {
			$address = $this->payee->billingAddress ?? $this->payee->address();
		}

		$country_id = $address ? $address->country_id : $this->request->country_id;
		$state_id = $address && $address->state ? $address->state_id : $this->request->state_id;

		$name = explode(' ', $this->request->cardholder_name);
		$fname = $name[0];
		$lname = count($name) > 1 ? end($name) : $fname;

		$locality = ($address && $address->city) ? $address->city : $this->request->city;

		$this->billingAddress = [
			"firstName"          => $fname,
			"lastName"           => $lname,
			"address1"           => $address ? $address->address_line_1 : $this->request->address_line_1,
			"address2"           => $address ? $address->address_line_2 : $this->request->address_line_2,
			"postalCode"         => $address ? $address->zip_code : $this->request->zip_code,
			"locality"           => $locality ?? get_value_from($state_id, 'states', 'name'),
			"country"            => get_value_from($country_id, 'countries', 'iso_code'),
			"administrativeArea" => $state_id ? get_value_from($state_id, 'states', 'iso_code') : '',
			"phoneNumber"        => $address ? $address->phone : $this->request->phone,
			"email"              => $this->payee ? $this->payee->email : $this->request->email,
		];

		return $this;
	}

	private function setCard()
	{
		$this->card = [
			"number"          => $this->request->cnumber,
			"securityCode"    => $this->request->ccode,
			"expirationMonth" => $this->request->card_expiry_month,
			"expirationYear"  => $this->request->card_expiry_year,
		];

		return $this;
	}
}
