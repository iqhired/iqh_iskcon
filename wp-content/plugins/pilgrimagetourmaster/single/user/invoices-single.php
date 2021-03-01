 <?php
	echo '<div class="tourmaster-user-content-inner tourmaster-user-content-inner-invoices-single" >';
	tourmaster_get_user_breadcrumb();

	// booking table block
	tourmaster_user_content_block_start();

	global $current_user;
	$result = tourmaster_get_booking_data(array(
		'id' => $_GET['id'],
		'user_id' => $current_user->data->ID,
		'order_status' => array(
			'condition' => '!=',
			'value' => 'cancel'
		)
	), array('single' => true));

	echo '<div class="tourmaster-invoice-wrap clearfix" id="tourmaster-invoice-wrap" >';

	$invoice_logo = tourmaster_get_option('general', 'invoice-logo');
	$billing_info = empty($result->billing_info)? array(): json_decode($result->billing_info, true);
	
	echo '<div class="tourmaster-invoice-head clearfix" style="margin-bottom: 30px !important;">';
	echo '<div class="tourmaster-invoice-head-left" >';
	echo '<div class="tourmaster-invoice-logo" style="margin-bottom: 15px !important;">';
	if( empty($invoice_logo) ){
		echo tourmaster_get_image(TOURMASTER_URL . '/images/invoice-logo.png');
	}else{
		echo tourmaster_get_image($invoice_logo);
	}
	echo '</div>'; // tourmaster-invoice-logo
	echo '<div class="tourmaster-invoice-id" >' . esc_html__('Confirmation ID :', 'tourmaster') . ' #' . $result->id . '</div>';
//	echo '<div class="tourmaster-invoice-date" >' . esc_html__('Invoice date :', 'tourmaster') . ' ' . tourmaster_date_format($result->booking_date) . '</div>';
//	echo '<div class="tourmaster-invoice-receiver" >';
//	echo '<div class="tourmaster-invoice-receiver-head" >' . esc_html__('Invoice To', 'tourmaster') . '</div>';
//	echo '<div class="tourmaster-invoice-receiver-info" >';
//	$customer_address = tourmaster_get_option('general', 'invoice-customer-address');
//	if( empty($customer_address) ){
//		echo '<span class="tourmaster-invoice-receiver-name" >' . $billing_info['first_name'] . ' ' . $billing_info['last_name'] . '</span>';
//		echo '<span class="tourmaster-invoice-receiver-address" >' . (empty($billing_info['contact_address'])? '': $billing_info['contact_address']) . '</span>';
//	}else{
//		echo tourmaster_content_filter(tourmaster_set_contact_form_data($customer_address, $billing_info));
//	}
//	echo '</div>';
//	echo '</div>';
	echo '</div>'; // tourmaster-invoice-head-left
	
//	$company_name = tourmaster_get_option('general', 'invoice-company-name', '');
//	$company_info = tourmaster_get_option('general', 'invoice-company-info', '');
//	echo '<div class="tourmaster-invoice-head-right" >';
//	echo '<div class="tourmaster-invoice-company-info" >';
//	echo '<div class="tourmaster-invoice-company-name" >' . $company_name . '</div>';
//	echo '<div class="tourmaster-invoice-company-info" >' . tourmaster_content_filter($company_info) . '</div>';
//	echo '</div>';
//	echo '</div>'; // tourmaster-invoice-head-right
	echo '</div>'; // tourmaster-invoice-head

	// price breakdown
	if( !empty($result->pricing_info) ){
        echo '<div class="tourmaster-tail" >' . esc_html__('Hare Krishna', 'tourmaster') . '</div><br/>';
        echo '<div class="tourmaster-tail" >' . esc_html__('Dear ', 'tourmaster') . $billing_info['first_name'] . ' ' . $billing_info['last_name'] . ',' .'</div><br/>';
        echo '<div class="tourmaster-tail" >' . esc_html__('Please accept the blessings of Sri Sri Radha Krishnachandra', 'tourmaster') .'</div><br/>';
        echo '<div class="tourmaster-tail" >' . esc_html__('Thank you for booking pilgrimage yatra with us. The total payment of amount ', 'tourmaster') . tourmaster_money_format($result->total_price) . esc_html__(' needs to be made towards ISKCON Pilgrimages.', 'tourmaster') .' </div><br/>';
//        echo '<div class="tourmaster-tail" >' . esc_html__('Thank you for your payment of amount ', 'tourmaster') . tourmaster_money_format($result->total_price) . esc_html__(' towards ISKCON Pilgrimages.', 'tourmaster') .' </div><br/>';
		$pricing_info = json_decode($result->pricing_info, true);

        echo '<div class="tourmaster-invoice-price-breakdown" >';

        echo '<div class="tourmaster-invoice-price-head" style="width: 100%">';
        echo '<div class="" style="width: 25%;float: left;margin: -10px 0px;" >' . esc_html__('Pilgrimage Name', 'tourmaster') . '</div>';
        echo '<div class="" style="width: 25%;float: left;margin: -10px 0px;">' . esc_html__('Booking Date', 'tourmaster') . '</div>';
        echo '<div class="" style="width: 25%;float: left;margin: -10px 0px;">' . esc_html__('Participant Details', 'tourmaster') . '</div>';
        echo '<div class="" style="width: 20%;float: right;margin: -10px 0px;">' . esc_html__('Amount', 'tourmaster') . '</div>';
        echo '</div>'; // tourmaster-invoice-price-head

        //$bdet = json_decode($result->booking_detail);
        $bdet = (array) json_decode($result->booking_detail);


        echo tourmaster_get_tour_invoice_price_details($bdet,tourmaster_date_format($result->booking_date), $pricing_info['price-breakdown']);

        echo '<div class="tourmaster-invoice-total-price clearfix" >';
        echo '<span class="tourmaster-head">' . esc_html__('Total', 'tourmaster') . '</span> ';
        echo '<span class="tourmaster-tail">' . tourmaster_money_format($result->total_price) . '</span>';
        echo '</div>'; // tourmaster-invoice-total-price
        echo '<div class="tourmaster-invoice-total-price clearfix" >';
        echo '<span class="tourmaster-head">' . esc_html__('Balance Amount', 'tourmaster') . '</span> ';
//        $dep_amount = ($pricing_info['deposit-price'] == null)?0:$pricing_info['deposit-price'];
        if(!empty($result->payment_info)){
            $payment_info = json_decode($result->payment_info, true);
            if( !empty($payment_info['deposit_amount']) ){
                $dep_amount = ($payment_info['deposit_amount'] == null)?0:$payment_info['deposit_amount'];
//                echo '<div class="tourmaster-tail" >' . tourmaster_money_format($payment_info['deposit_amount']) . '</div>';
                echo '<span class="tourmaster-tail">' . tourmaster_money_format($result->total_price - $dep_amount) . '</span>';
            }else{
                $paid_amount = ($payment_info['amount'] == null)?0:$payment_info['amount'];
                echo '<span class="tourmaster-tail">' . tourmaster_money_format($result->total_price - $paid_amount) . '</span>';
            }
        }else{
            echo '<span class="tourmaster-tail">' . tourmaster_money_format($result->total_price) . '</span>';
        }

        echo '</div>'; // tourmaster-invoice-total-price
        echo '</div>'; // tourmaster-invoice-price-breakdown

        echo '<div class="tourmaster-invoice-price-head" style="width: 100%; margin-top: 50px;">';
        echo '<div class="" style="width: 25%;float: left;margin: -10px 0px;" >' . esc_html__('Payee Name', 'tourmaster') . '</div>';
        echo '<div class="" style="width: 25%;float: left;margin: -10px 0px;">' . esc_html__('email ID', 'tourmaster') . '</div>';
        echo '<div class="" style="width: 25%;float: left;margin: -10px 0px;">' . esc_html__('Mobile', 'tourmaster') . '</div>';
        echo '<div class="" style="width: 25%;float: left;margin: -10px 0px;">' . esc_html__('Adress', 'tourmaster') . '</div>';
        echo '</div>'; // tourmaster-payee-details

        echo '<div class="tourmaster-invoice-price-item clearfix" style="width: 100%;">';
        echo '<div class="" style="width: 25%;float: left;margin: -10px 0px;" >' . esc_html__($billing_info['first_name'], 'tourmaster') . '</div>';
        echo '<div class="" style="width: 25%;float: left;margin: -10px 0px;">' . esc_html__($billing_info['email'], 'tourmaster') . '</div>';
        echo '<div class="" style="width: 25%;float: left;margin: -10px 0px;">' . esc_html__($billing_info['phone'], 'tourmaster') . '</div>';
        echo '<div class="" style="width: 25%;float: left;margin: -10px 0px;">' . esc_html__($billing_info['contact_address'], 'tourmaster') . '</div>';
        echo '</div>'; // tourmaster-payee-details

	}

//	if( !empty($result->order_status) ){
        if( !empty($result->order_status) && in_array($result->order_status, array('approve','approved','rejected', 'online-paid', 'departed', 'deposit-paid')) ){

            $payment_date = tourmaster_date_format($result->payment_date);


		echo '<div class="tourmaster-invoice-payment-info clearfix" >';
		echo '<div class="tourmaster-invoice-payment-info-item" >';
		echo '<div class="tourmaster-head" >' . esc_html__('Payment Method', 'tourmaster') . '</div>';
		echo '<div class="tourmaster-tail" >';
		if( !empty($bdet['payment-method']) && $bdet['payment-method'] == 'receipt' ){
			echo esc_html__('Bank Transfer', 'tourmaster');
		}else if( !empty($bdet['payment-method']) ){
			echo esc_html__('Online Payment', 'tourmaster');
		}
		echo '</div>';
		echo '</div>'; // tourmaster-invoice-payment-info-item

		if( !empty($payment_info['amount']) ){
			echo '<div class="tourmaster-invoice-payment-info-item" >';
			echo '<div class="tourmaster-head" >' . esc_html__('Paid Amount', 'tourmaster') . '</div>';
			if( !empty($payment_info['deposit_amount']) ){
				echo '<div class="tourmaster-tail" >' . tourmaster_money_format($payment_info['deposit_amount']) . '</div>';
			}else{
				echo '<div class="tourmaster-tail" >' . tourmaster_money_format($payment_info['amount']) . '</div>';
			}
			echo '</div>'; // tourmaster-invoice-payment-info-item
		}

		echo '<div class="tourmaster-invoice-payment-info-item" >';
		echo '<div class="tourmaster-head" >' . esc_html__('Payment Date', 'tourmaster') . '</div>';
		echo '<div class="tourmaster-tail" >' . $payment_date . '</div>';
		echo '</div>'; // tourmaster-invoice-payment-info-item

		$transaction_id = '';
		if( !empty($payment_info['transaction_id']) ){
			$transaction_id = $payment_info['transaction_id'];
		}else if( !empty($payment_info['transaction-id']) ){
			$transaction_id = $payment_info['transaction-id'];
		}
		if( !empty($transaction_id) ){
			echo '<div class="tourmaster-invoice-payment-info-item" >';
			echo '<div class="tourmaster-head" >' . esc_html__('Transaction ID', 'tourmaster') . '</div>';
			echo '<div class="tourmaster-tail" >' . $transaction_id . '</div>';
			echo '</div>'; // tourmaster-invoice-payment-info-item
		}
		echo '</div>';
	}

	echo '</div>'; // tourmaster-invoice-wrap

	echo '<div class="tourmaster-invoice-button" >';
	if( empty($result->order_status) || !in_array($result->order_status, array('approve','approved', 'online-paid', 'departed', 'deposit-paid','rejected' , 'cancel' , 'cancelled')) ){
		echo '<a href="' . esc_url(add_query_arg(array('page_type'=>'my-booking'))) . '" class="tourmaster-button" >' . esc_html__('Make a Payment', 'tourmaster') . '</a>';
	}
	echo '<a href="' . esc_url(add_query_arg(array('page_type'=>'invoices'))) . '" class="tourmaster-button tourmaster-print" data-id="tourmaster-invoice-wrap" ><i class="fa fa-print" ></i>' . esc_html__('Print', 'tourmaster') . '</a>';
	// echo '<a href="#" class="tourmaster-button tourmaster-pdf-download" data-id="tourmaster-invoice-wrap" ><i class="fa fa-file-pdf-o" ></i>' . esc_html__('Download Pdf', 'tourmaster') . '</a>';
	echo '</div>'; // tourmaster-invoice-button

	tourmaster_user_content_block_end();
	echo '</div>'; // tourmaster-user-content-inner