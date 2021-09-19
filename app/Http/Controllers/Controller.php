<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
  use AuthorizesRequests, DispatchesJobs, ValidatesRequests;


  /**
   * constructor
   */
  public function __construct()
  {
    // Global actions. Dont remove this constructor
  }
  protected function InitMidtransPayment()
  {
    // Set your Merchant Server Key
    \Midtrans\Config::$serverKey = env('MIDTRANS_SERVER_KEY');
    // Set to Development/Sandbox Environment (default). Set to true for Production Environment (accept real transaction).
    \Midtrans\Config::$isProduction = env('MIDTRANS_ISPRODUCTION');
    // Set sanitization on (default)
    \Midtrans\Config::$isSanitized = env('MIDTRANS_ISSANITIZED');
    // Set 3DS transaction for credit card to true
    \Midtrans\Config::$is3ds = env('MIDTRANS_IS3DS');
    \Midtrans\Config::$paymentIdempotencyKey = env('MIDTRANS_IS3DS');
    \Midtrans\Config::$apikey=env("MIDTRANS_API_KEY_IRIS_PAYOUT");
    \Midtrans\Config::$iris_key_app=env("MIDTRANS_API_KEY_IRIS_APPROVAL");
  }
}
