<?php
	Route::post('order/{cartParent}', 'OrderController@create')->name('order.create');
	Route::get('paymentFailed/{order}', 'OrderController@paymentFailed')->name('payment.failed');
	Route::get('paymentSuccess/{order}/{gateway}', 'OrderController@paymentGatewaySuccessResponse')->name('payment.success');
	Route::post('orders/trackshipping', 'OrderController@get_shippingTrack')->name('order.trackship'); // modify by ari 06062021
	Route::post('orders/checkoutmidtrans/{cart}','OrderController@checkoutMidtrans')->name("order.orderbymidtrans")->middleware('ajax');
	Route::post('orders/finishorder', 'OrderController@finishorder')->name('order.finishorder'); // modify by ari 06062021

	Route::post('payments/notification','PaymentMidtransController@notification')->name("paymetmidtrans.notification");
	Route::get('payments/complete', 'PaymentMidtransController@complete')->name('paymentmidtrans.complete');
	Route::get('payments/failed', 'PaymentMidtransController@failed')->name('paymetmidtrans.failed');
	Route::get('payments/unfinish', 'PaymentMidtransController@unfinish')->name('paymentmidtrans.unfinish');
	Route::middleware(['auth:customer'])->group(function () {
		Route::get('order/{order}', 'OrderController@detail')->name('order.detail');
		Route::get('order/invoice/{order}', 'OrderController@invoice')->name('order.invoice');
		Route::get('order/track/{order}', 'OrderController@track')->name('order.track');
		Route::put('order/goodsReceived/{order}', 'OrderController@goods_received')->name('goods.received');
		Route::get('order/again/{order}', 'OrderController@again')->name('order.again');
		
		// Order cancel
		Route::get('order/cancel/{order}/{action?}', 'OrderCancelController@showForm')->name('cancellation.form');
		Route::put('order/cancel/{order}', 'OrderCancelController@cancel')->name('order.cancel');
		Route::post('order/cancel/{order}', 'OrderCancelController@saveCancelRequest')->name('order.submitCancelRequest');


		// Conversations
		Route::post('order/conversation/{order}', 'ConversationController@order_conversation')->name('order.conversation');

		// Disputes
		Route::get('order/dispute/{order}', 'DisputeController@show_dispute_form')->name('dispute.open');
		Route::post('order/dispute/{order}', 'DisputeController@open_dispute')->name('dispute.save');
		Route::post('dispute/{dispute}', 'DisputeController@response')->name('dispute.response');
		Route::post('dispute/{dispute}/appeal', 'DisputeController@appeal')->name('dispute.appeal');
		Route::post('dispute/{dispute}/markAsSolved', 'DisputeController@markAsSolved')->name('dispute.markAsSolved');

		// Refunds
		// Route::post('order/refund/{order}', 'DisputeController@refund_request')->name('refund.request');
	});