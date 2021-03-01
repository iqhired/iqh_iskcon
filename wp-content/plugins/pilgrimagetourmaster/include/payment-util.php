<?php
/*
	*	Tourmaster Plugin
	*	---------------------------------------------------------------------
	*	for payment page
	*	---------------------------------------------------------------------
	*/

//payment_id=MOJO0202205A63339489&payment_status=Credit&payment_request_id=9dc0e9f778ed44f59b25ad905a5e9df0

if (!function_exists('tourmaster_get_payment_page')) {
    function tourmaster_get_payment_page($booking_detail, $is_single = false)
    {
//            $tour_option = tourmaster_get_post_meta($booking_detail['tour-id'], 'tourmaster-tour-option');


        $steps = 5;


        // initiate the variable
        if (!empty($booking_detail['tour-id']) && !empty($booking_detail['tour-date'])) {
            $tour_option = tourmaster_get_post_meta($booking_detail['tour-id'], 'tourmaster-tour-option');
//            if ($tour_option['require-bus-booking'] == 'enable') {
//                $steps = 6;
//            }

            if (!empty($booking_detail['step']) && $booking_detail['step'] == $steps) {
                $booking_detail = tourmaster_set_mandatory_service($tour_option, $booking_detail);
            }

            $date_price = tourmaster_get_tour_date_price($tour_option, $booking_detail['tour-id'], $booking_detail['tour-date']);
            $date_price = tourmaster_get_tour_date_price_package($date_price, $booking_detail);
//            $booking_detail['tour-seva'] = ;
            if(null != (filter_input(INPUT_GET,'payment_id')) && null != (filter_input(INPUT_GET,'payment_status')) && null != (filter_input(INPUT_GET,'payment_request_id'))){
                $live_mode = tourmaster_get_option('payment', 'instamojo-live-mode', 'disable');
                $instamojo_api_key = tourmaster_get_option('payment', 'instamojo-api-key', '');
                $instamojo_auth_token = tourmaster_get_option('payment', 'instamojo-auth-token', '');
                $instamojo_salt = tourmaster_get_option('payment', 'instamojo-salt', '');
                $user_email = tourmaster_get_option('payment', 'user_email_id', '');
                $api_key = "X-Api-Key:" . $instamojo_api_key;
                $auth_token = "X-Auth-Token:" . $instamojo_auth_token;

                $mode = 'test';
                $url = '';
                if( empty($live_mode) || $live_mode == 'disable' ){
                    $url = 'https://test.instamojo.com/api/1.1/payment-requests/';
                    $mode = 'test';
                }else{
                    $url = 'https://www.instamojo.com/api/1.1/payment-requests/';
                    $mode = 'www';
                }
                $ch = curl_init();

                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_HEADER, FALSE);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
                curl_setopt($ch, CURLOPT_HTTPHEADER,array($api_key,$auth_token));

                $response = curl_exec($ch);
                curl_close($ch);
                $data = json_decode($response, true);
//                echo $response;

//                $payment_info['transaction_id'] = $booking_detail['tid'];
                if($_GET['payment_status'] == "Failed"){
//                    $payment_info['amount'] = $data['payment']['amount'];
                    $payment_info['payment_mode'] = "Payment Failed";
//                    foreach ( $data['payment_requests'] as $payment_info_data){
//                        if(filter_input(INPUT_GET,'payment_request_id') == $payment_info_data['id']){
//                            $payment_info['amount'] = $payment_info_data['amount'];
//                            break;
//                        }
//                    }
                    $tid = $booking_detail['tid'];
                    $tdata = tourmaster_get_booking_data(array('tid'=> $tid), array('single'=>true));
                    $pricing_info = json_decode($tdata->pricing_info, true);
                    $order_status = 'pending';
                    tourmaster_update_booking_data(
                        array(
                            'payment_info' => json_encode($payment_info),
//                            'payment_date' => current_time('mysql'),
                            'order_status' => $order_status,
                        ),
                        array('id' => $tid),
                        array('%s', '%s', '%s', '%s', '%s'),
                        array('%d')
                    );

                }else{
                    $payment_info['amount'] = $data['payment']['amount'];
                    $payment_info['payment_mode'] = "Paid Online";
//                $pay_req = array();
//                $pay_req['req'] = ‌‌$data['payment_requests'];
                    foreach ( $data['payment_requests'] as $payment_info_data){
                        if(filter_input(INPUT_GET,'payment_request_id') == $payment_info_data['id']){
                            $payment_info['amount'] = $payment_info_data['amount'];
                            break;
                        }
                    }
                    $tid = $booking_detail['tid'];
//                $tid = $booking_detail['tour-id'];
                    $tdata = tourmaster_get_booking_data(array('tid'=> $tid), array('single'=>true));
                    $pricing_info = json_decode($tdata->pricing_info, true);
                    if( !empty($pricing_info['deposit-price']) && tourmaster_compare_price($pricing_info['deposit-price'], $payment_info['amount']) ){
                        $order_status = 'approved';
                        if( !empty($pricing_info['deposit-price-raw']) ){
                            $payment_info['deposit_amount'] = $pricing_info['deposit-price-raw'];
                        }
                    }else if( tourmaster_compare_price($pricing_info['total-price'], $payment_info['amount']) ){
                        $order_status = 'approved';
                    }else{
                        $order_status = 'approved';
                    }
                    tourmaster_update_booking_data(
                        array(
                            'payment_info' => json_encode($payment_info),
                            'payment_date' => current_time('mysql'),
                            'order_status' => $order_status,
                        ),
                        array('id' => $tid),
                        array('%s', '%s', '%s', '%s', '%s'),
                        array('%d')
                    );

                    tourmaster_mail_notification('payment-made-mail', $tid);
                    tourmaster_mail_notification('admin-online-payment-made-mail', $tid);
                    tourmaster_send_email_invoice($tid);

                }
                $booking_detail['step'] = 5;
                $_COOKIE["tourmaster-booking-detail"] = $booking_detail;
            }
        }

        // if booking data is invalid
        if (empty($date_price)) {
            $ret = '<div class="tourmaster-tour-booking-error" >';
            $ret .= esc_html__('An error occurred while processing your request.', 'tourmaster');
            if (!empty($booking_detail['tour-id'])) {
                $ret .= '<br><br><a href="' . get_permalink($booking_detail['tour-id']) . '" >' . esc_html__('Back to Tour Page', 'tourmaster') . '</a>';
            } else {
                $ret .= '<br><br><a href="' . home_url('/') . '" >' . esc_html__('Back to Home Page', 'tourmaster') . '</a>';
            }
            $ret .= '</div>';

            return array('content' => $ret, 'sidebar' => '');
        }

        // booking step 2
        if (empty($booking_detail['step']) || $booking_detail['step'] == '2') {
            return array(
                'content' => tourmaster_payment_traveller_form_data($tour_option, $date_price, $booking_detail, 3),
                'sidebar' => ''
            );
            // booking step 3
        } else if ($booking_detail['step'] == '3') {
            $editable = false;
            if(empty($booking_detail['voucher-codes'])){
                $editable = true;
            }
//            unset($booking_detail['payment_method']);
            if(!empty($booking_detail["updated_seva"]) && $booking_detail["updated_seva"] == "true"){
                $booking_detail["updated_seva"] = false;
                return array(
//                    'content' => tourmaster_payment_contact_form_data($tour_option, $date_price, $booking_detail, 4),
                    'sidebar' => tourmaster_get_booking_bar_summary($tour_option, $date_price, $booking_detail, $editable, 4,false)
                );
            }else{
                return array(
                    'content' => tourmaster_payment_contact_form_data($tour_option, $date_price, $booking_detail, 4),
                    'sidebar' => tourmaster_get_booking_bar_summary($tour_option, $date_price, $booking_detail, $editable, 4,false)
                );
            }

            // booking step 4
        }
        else if ($booking_detail['step'] == '4') {
//            unset($booking_detail['payment_method']);
            $booking_detail["updated_seva"] = false;
            $pilgrim_price = tourmaster_get_tour_price($tour_option, $date_price, $booking_detail);
            $t_price = $pilgrim_price["total-price"];
            if(array_key_exists('deposit-price', $pilgrim_price)){
                $t_price = $pilgrim_price["deposit-price"];
            }
            return array(
                'content' => tourmaster_payment_service_form($tour_option, $booking_detail) .
                    tourmaster_payment_contact_detail($booking_detail) .
                    tourmaster_payment_traveller_detail($tour_option, $booking_detail) .
                    tourmaster_instamojo_payment_method($booking_detail , $t_price),
                'sidebar' => tourmaster_get_booking_bar_summary($tour_option, $date_price, $booking_detail, false, 5,false)
            );

            // booking step 5
        } else if ($booking_detail['step'] == '5') {

            if ($booking_detail['payment-method'] == 'instamojo') {
                $tour_price = tourmaster_get_tour_price($tour_option, $date_price, $booking_detail);
                if ($date_price['pricing-method'] == 'group') {
                    $traveller_amount = 1;
                } else {
                    $traveller_amount = tourmaster_get_tour_people_amount($tour_option, $date_price, $booking_detail, 'all');
                }
                $package_group_slug = empty($date_price['group-slug']) ? '' : $date_price['group-slug'];

                if ($tid = tourmaster_insert_booking_data($booking_detail, $tour_price, $traveller_amount, $package_group_slug)) {

                    if (is_user_logged_in()) {
                        tourmaster_mail_notification('booking-made-mail', $tid);
                        tourmaster_mail_notification('admin-booking-made-mail', $tid);
                    } else {
                        tourmaster_mail_notification('guest-booking-made-mail', $tid);
                        tourmaster_mail_notification('admin-guest-booking-made-mail', $tid);
                    }
                    if($_GET["payment_status"] == "Failed"){
                        return array(
                            'content' => tourmaster_payment_failed(),
                            'sidebar' => tourmaster_get_booking_bar_summary($tour_option, $date_price, $booking_detail, false, 5,false),
                            'cookie' => ''
                        );
                    }else{
                        return array(
                            'content' => tourmaster_payment_complete(),
                            'sidebar' => tourmaster_get_booking_bar_summary($tour_option, $date_price, $booking_detail, false, 5,false),
                            'cookie' => ''
                        );
                    }
                } else {
                    // cannot insert to database
                }

            }else if ($booking_detail['payment-method'] == 'booking') {
                $tour_price = tourmaster_get_tour_price($tour_option, $date_price, $booking_detail);
                    if ($date_price['pricing-method'] == 'group') {
                        $traveller_amount = 1;
                    } else {
                        $traveller_amount = tourmaster_get_tour_people_amount($tour_option, $date_price, $booking_detail, 'all');
                    }
                    $package_group_slug = empty($date_price['group-slug']) ? '' : $date_price['group-slug'];

                    if ($tid = tourmaster_insert_booking_data($booking_detail, $tour_price, $traveller_amount, $package_group_slug)) {

                        if (is_user_logged_in()) {
                            tourmaster_mail_notification('booking-made-mail', $tid);
                            tourmaster_mail_notification('admin-booking-made-mail', $tid);
                        } else {
                            tourmaster_mail_notification('guest-booking-made-mail', $tid);
                            tourmaster_mail_notification('admin-guest-booking-made-mail', $tid);
                        }

                        return array(
                            'content' => tourmaster_payment_complete(),
                            'sidebar' => tourmaster_get_booking_bar_summary($tour_option, $date_price, $booking_detail, false, 5,false),
                            'cookie' => ''
                        );
                    } else {
                        // cannot insert to database
                    }

            }else if ($is_single) {
                return array(
                    'content' => tourmaster_payment_complete_delay(),
                    'sidebar' => tourmaster_get_booking_bar_summary($tour_option, $date_price, $booking_detail, false, 5,false),
                    'cookie' => ''
                );
            }

        }

        return array();

    } // tourmaster_get_payment_page
}

// get booking bar summary Step 3
if (!function_exists('tourmaster_get_booking_bar_summary')) {
    function tourmaster_get_booking_bar_summary($tour_option, $date_price, $booking_detail, $editable, $steps , $next_Step_req = true)
    {
        $ret = '<div class="tourmaster-tour-booking-bar-summary" >';
        $ret .= '<h3 class="tourmaster-tour-booking-bar-summary-title" >' . get_the_title($booking_detail['tour-id']) . '</h3>';

        $ret .= '<div class="tourmaster-tour-booking-bar-summary-info tourmaster-summary-travel-date" >';
        $ret .= '<span class="tourmaster-head" >' . esc_html__('Travel Date', 'tourmaster') . ' : </span>';
        $ret .= '<span class="tourmaster-tail" >';
        $ret .= tourmaster_date_format($booking_detail['tour-date']);
//        if ($editable) {
//            $ret .= ' ( <span class="tourmaster-tour-booking-bar-date-edit" >' . esc_html__('edit', 'tourmaster') . '</span> )';
//            $ret .= '<form class="tourmaster-tour-booking-temp" action="' . get_permalink($booking_detail['tour-id']) . '" method="post" ></form>';
//        }
        $ret .= '</span>';
        $ret .= '</div>';

        if ($tour_option['tour-type'] == 'multiple' && !empty($tour_option['multiple-duration'])) {
            $tour_duration = intval($tour_option['multiple-duration']);
            $end_date = strtotime('+ ' . ($tour_duration - 1) . ' day', strtotime($booking_detail['tour-date']));

            $ret .= '<div class="tourmaster-tour-booking-bar-summary-info tourmaster-summary-end-date" >';
            $ret .= '<span class="tourmaster-head" >' . esc_html__('End Date', 'tourmaster') . ' : </span>';
            $ret .= '<span class="tourmaster-tail" >' . tourmaster_date_format($end_date) . '</span>';
            $ret .= '</div>';

        }
        if (!empty($booking_detail['package'])) {
            $ret .= '<div class="tourmaster-tour-booking-bar-summary-info tourmaster-summary-package" >';
            $ret .= '<span class="tourmaster-head" >' . esc_html__('Package', 'tourmaster') . ' : </span>';
            $ret .= '<span class="tourmaster-tail" >' . $booking_detail['package'] . '</span>';
            $ret .= '</div>';
        }

        if ($tour_option['tour-type'] == 'multiple' && !empty($tour_option['multiple-duration'])) {

            $ret .= '<div class="tourmaster-tour-booking-bar-summary-info tourmaster-summary-period" >';
            $ret .= '<span class="tourmaster-head" >' . esc_html__('Period', 'tourmaster') . ' : </span>';
            $ret .= '<span class="tourmaster-tail" >' . $tour_duration . ' ';
            $ret .= ($tour_option['multiple-duration'] > 1) ? esc_html__('Days', 'tourmaster') : esc_html__('Day', 'tourmaster');
            $ret .= '</span>';
            $ret .= '</div>';
        }

        // group price
        if ($date_price['pricing-method'] == 'group') {

            // no room based
        } else if ($tour_option['tour-type'] == 'single' || $date_price['pricing-room-base'] == 'disable') {

            $ret .= '<div class="tourmaster-tour-booking-bar-summary-people-wrap" >';

            // fixed price
            if ($date_price['pricing-method'] == 'fixed') {
                $ret .= '<div class="tourmaster-tour-booking-bar-summary-people-amount" >';
                $ret .= '<span class="tourmaster-head" >' . esc_html__('Pilgrim', 'tourmaster') . ' : </span>';
                $ret .= '<span class="tourmaster-tail" >' . $booking_detail['tour-people'] . '</span>';
                $ret .= '</div>';

                // variable price
            } else {
                $ret .= '<div class="tourmaster-tour-booking-bar-summary-people tourmaster-variable clearfix" >';
                if (!empty($date_price['adult-price'])) {
                    $ret .= '<div class="tourmaster-tour-booking-bar-summary-people-amount tourmaster-adult" >';
                    $ret .= '<span class="tourmaster-head" >' . esc_html__('Adult', 'tourmaster') . ' : </span>';
                    $ret .= '<span class="tourmaster-tail" >' . (empty($booking_detail['tour-adult']) ? '0' : $booking_detail['tour-adult']) . '</span>';
                    $ret .= '</div>'; // tourmaster-tour-booking-bar-summary-people-amount
                }
                if (!empty($date_price['sr-citizen-price'])) {
                    $ret .= '<div class="tourmaster-tour-booking-bar-summary-people-amount tourmaster-sr-citizen" >';
                    $ret .= '<span class="tourmaster-head" >' . esc_html__('Sr. Citizen', 'tourmaster') . ' : </span>';
                    $ret .= '<span class="tourmaster-tail" >' . (empty($booking_detail['tour-sr-citizen']) ? '0' : $booking_detail['tour-sr-citizen']) . '</span>';
                    $ret .= '</div>'; // tourmaster-tour-booking-bar-summary-people-amount
                }
                if (!empty($date_price['male-price'])) {
                    $ret .= '<div class="tourmaster-tour-booking-bar-summary-people-amount tourmaster-male" >';
                    $ret .= '<span class="tourmaster-head" >' . esc_html__('Male', 'tourmaster') . ' : </span>';
                    $ret .= '<span class="tourmaster-tail" >' . (empty($booking_detail['tour-male']) ? '0' : $booking_detail['tour-male']) . '</span>';
                    $ret .= '</div>'; // tourmaster-tour-booking-bar-summary-people-amount
                }
                if (!empty($date_price['female-price'])) {
                    $ret .= '<div class="tourmaster-tour-booking-bar-summary-people-amount tourmaster-female" >';
                    $ret .= '<span class="tourmaster-head" >' . esc_html__('Female', 'tourmaster') . ' : </span>';
                    $ret .= '<span class="tourmaster-tail" >' . (empty($booking_detail['tour-female']) ? '0' : $booking_detail['tour-female']) . '</span>';
                    $ret .= '</div>'; // tourmaster-tour-booking-bar-summary-people-amount
                }
                if (!empty($date_price['children-price'])) {
                    $ret .= '<div class="tourmaster-tour-booking-bar-summary-people-amount tourmaster-children" >';
                    $ret .= '<span class="tourmaster-head" >' . esc_html__('Children', 'tourmaster') . ' : </span>';
                    $ret .= '<span class="tourmaster-tail" >' . (empty($booking_detail['tour-children']) ? '0' : $booking_detail['tour-children']) . '</span>';
                    $ret .= '</div>'; // tourmaster-tour-booking-bar-summary-people-amount
                }
                if (!empty($date_price['student-price'])) {
                    $ret .= '<div class="tourmaster-tour-booking-bar-summary-people-amount tourmaster-student" >';
                    $ret .= '<span class="tourmaster-head" >' . esc_html__('Student', 'tourmaster') . ' : </span>';
                    $ret .= '<span class="tourmaster-tail" >' . (empty($booking_detail['tour-student']) ? '0' : $booking_detail['tour-student']) . '</span>';
                    $ret .= '</div>'; // tourmaster-tour-booking-bar-summary-people-amount
                }
                if (!empty($date_price['infant-price'])) {
                    $ret .= '<div class="tourmaster-tour-booking-bar-summary-people-amount tourmaster-infant" >';
                    $ret .= '<span class="tourmaster-head" >' . esc_html__('Infant', 'tourmaster') . ' : </span>';
                    $ret .= '<span class="tourmaster-tail" >' . (empty($booking_detail['tour-infant']) ? '0' : $booking_detail['tour-infant']) . '</span>';
                    $ret .= '</div>'; // tourmaster-tour-booking-bar-summary-people-amount
                }
                $ret .= '</div>'; // tourmaster-tour-booking-bar-summary-people
            }
            $ret .= '</div>'; // tourmaster-tour-booking-bar-summary-people-wrap

            // room based
        } else {

            $ret .= '<div class="tourmaster-tour-booking-bar-summary-room-wrap clearfix" >';

            for ($i = 0; $i < $booking_detail['tour-room']; $i++) {
                $ret .= '<div class="tourmaster-tour-booking-bar-summary-room" >';
                $ret .= '<div class="tourmaster-tour-booking-bar-summary-room-text" >' . esc_html__('Room', 'tourmaster') . ' ' . ($i + 1) . '</div>';
                // fixed price
                if ($date_price['pricing-method'] == 'fixed') {
                    $ret .= '<div class="tourmaster-tour-booking-bar-summary-people-amount" >';
                    $ret .= '<span class="tourmaster-head" >' . esc_html__('Pilgrim', 'tourmaster') . ' : </span>';
                    $ret .= '<span class="tourmaster-tail" >' . $booking_detail['tour-people'][$i] . '</span>';
                    $ret .= '</div>';

                    // variable price
                } else {
                    $ret .= '<div class="tourmaster-tour-booking-bar-summary-people tourmaster-variable clearfix" >';
                    if (!empty($date_price['adult-price'])) {
                        $ret .= '<div class="tourmaster-tour-booking-bar-summary-people-amount tourmaster-adult" >';
                        $ret .= '<span class="tourmaster-head" >' . esc_html__('Adult', 'tourmaster') . ' : </span>';
                        $ret .= '<span class="tourmaster-tail" >' . (empty($booking_detail['tour-adult'][$i]) ? '0' : $booking_detail['tour-adult'][$i]) . '</span>';
                        $ret .= '</div>'; // tourmaster-tour-booking-bar-summary-people-amount
                    }
                    if (!empty($date_price['sr-citizen-price'])) {
                        $ret .= '<div class="tourmaster-tour-booking-bar-summary-people-amount tourmaster-sr-citizen" >';
                        $ret .= '<span class="tourmaster-head" >' . esc_html__('Sr. Citizen', 'tourmaster') . ' : </span>';
                        $ret .= '<span class="tourmaster-tail" >' . (empty($booking_detail['tour-sr-citizen'][$i]) ? '0' : $booking_detail['tour-sr-citizen'][$i]) . '</span>';
                        $ret .= '</div>'; // tourmaster-tour-booking-bar-summary-people-amount
                    }
                    if (!empty($date_price['male-price'])) {
                        $ret .= '<div class="tourmaster-tour-booking-bar-summary-people-amount tourmaster-male" >';
                        $ret .= '<span class="tourmaster-head" >' . esc_html__('Male', 'tourmaster') . ' : </span>';
                        $ret .= '<span class="tourmaster-tail" >' . (empty($booking_detail['tour-male'][$i]) ? '0' : $booking_detail['tour-male'][$i]) . '</span>';
                        $ret .= '</div>'; // tourmaster-tour-booking-bar-summary-people-amount
                    }
                    if (!empty($date_price['female-price'])) {
                        $ret .= '<div class="tourmaster-tour-booking-bar-summary-people-amount tourmaster-female" >';
                        $ret .= '<span class="tourmaster-head" >' . esc_html__('Female', 'tourmaster') . ' : </span>';
                        $ret .= '<span class="tourmaster-tail" >' . (empty($booking_detail['tour-female'][$i]) ? '0' : $booking_detail['tour-female'][$i]) . '</span>';
                        $ret .= '</div>'; // tourmaster-tour-booking-bar-summary-people-amount
                    }
                    if (!empty($date_price['children-price'])) {
                        $ret .= '<div class="tourmaster-tour-booking-bar-summary-people-amount tourmaster-children" >';
                        $ret .= '<span class="tourmaster-head" >' . esc_html__('Children', 'tourmaster') . ' : </span>';
                        $ret .= '<span class="tourmaster-tail" >' . (empty($booking_detail['tour-children'][$i]) ? '0' : $booking_detail['tour-children'][$i]) . '</span>';
                        $ret .= '</div>'; // tourmaster-tour-booking-bar-summary-people-amount
                    }
                    if (!empty($date_price['student-price'])) {
                        $ret .= '<div class="tourmaster-tour-booking-bar-summary-people-amount tourmaster-student" >';
                        $ret .= '<span class="tourmaster-head" >' . esc_html__('Student', 'tourmaster') . ' : </span>';
                        $ret .= '<span class="tourmaster-tail" >' . (empty($booking_detail['tour-student'][$i]) ? '0' : $booking_detail['tour-student'][$i]) . '</span>';
                        $ret .= '</div>'; // tourmaster-tour-booking-bar-summary-people-amount
                    }
                    if (!empty($date_price['infant-price'])) {
                        $ret .= '<div class="tourmaster-tour-booking-bar-summary-people-amount tourmaster-infant" >';
                        $ret .= '<span class="tourmaster-head" >' . esc_html__('Infant', 'tourmaster') . ' : </span>';
                        $ret .= '<span class="tourmaster-tail" >' . (empty($booking_detail['tour-infant'][$i]) ? '0' : $booking_detail['tour-infant'][$i]) . '</span>';
                        $ret .= '</div>'; // tourmaster-tour-booking-bar-summary-people-amount
                    }
                    $ret .= '</div>'; // tourmaster-tour-booking-bar-summary-people
                }
                $ret .= '</div>'; // tourmaster-tour-booking-bar-summary-room
            }
            $ret .= '</div>'; // tourmaster-tour-booking-bar-summary-room-wrap
        }

        //Temporarily removed coupon
        $editable = false;
        if ($editable) {
            $ret .= '<div class="tourmaster-tour-booking-bar-coupon-wrap" >';
            $ret .= '<input type="text" class="tourmaster-tour-booking-bar-coupon" name="coupon-code" placeholder="' . esc_html__('Coupon Code', 'tourmaster') . '" ';
            $ret .= ' value="' . (empty($booking_detail['coupon-code']) ? '' : esc_attr($booking_detail['coupon-code'])) . '" ';
            $ret .= ' />';
            $ret .= '<a class="tourmaster-tour-booking-bar-coupon-validate" ';
            $ret .= ' data-ajax-url="' . esc_url(TOURMASTER_AJAX_URL) . '" ';
            $ret .= ' data-tour-id="' . esc_attr($booking_detail['tour-id']) . '" ';
            $ret .= ' >' . esc_html__('Apply', 'tourmaster') . '</a>';
            $ret .= '<div class="tourmaster-tour-booking-coupon-message" ></div>';
            $ret .= '</div>';
        }

        $req_sevas = tourmaster_get_post_meta($booking_detail['tour-id'], 'tour-settings');
        $seva_types = apply_filters('tourmaster_tour_seva_types', $req_sevas['tour-seva']);

        $r = 0;

        if(($steps - 1) < 4){
            $ret .= '<hr/><div class="tourmaster-tour-booking-bar-coupon-wrap " >';
            foreach ($seva_types as $seva_slug => $seva_type) {
                if($r == 0){
                    $ret .= '<h5>Select Seva (Optional) : </h5>';
                }

                $seva_id = 'seva-'. $seva_types[$r];
                $ret .= '<div style="width:50%; float:left;"><label style="margin-right: 10px"><input type="checkbox"  ' . ((in_array($seva_types[$r],$booking_detail['seva-ids'])) ? 'checked' : '') . ' class="tour_seva" data= ' . $seva_types[$r] . '  id = '.($seva_id).'  value = ' . json_encode($booking_detail) . ' ></input>';
                $ret .= '<span class="" >' . get_the_title($seva_types[$r]) . '</span>';
                $ret .= '</label></div>';
                $r++;
            }
            $ret .= '</div>';
        }

        $tour_price = tourmaster_get_tour_price($tour_option, $date_price, $booking_detail);
        $ret .= '<div class="tourmaster-tour-booking-bar-price-breakdown-wrap" >';
        $ret .= '<span class="tourmaster-tour-booking-bar-price-breakdown-link" id="tourmaster-tour-booking-bar-price-breakdown-link" >' . esc_html__('View Price Breakdown', 'tourmaster') . '</span>';
        $ret .= tourmaster_get_tour_price_breakdown($tour_price['price-breakdown']);
        $ret .= '</div>'; // tourmaster-tour-booking-bar-price-breakdown-wrap

        $ret .= '</div>'; // tourmaster-tour-booking-bar-summary

        // payment option
        $enable_full_payment = tourmaster_get_option('payment', 'enable-full-payment', 'enable');

        if (empty($tour_option['deposit-booking']) || $tour_option['deposit-booking'] == 'default') {
            $enable_deposit_payment = tourmaster_get_option('payment', 'enable-deposit-payment', 'disable');
            $deposit_amount = tourmaster_get_option('payment', 'deposit-payment-amount', '0');
        } else {
            $enable_deposit_payment = $tour_option['deposit-booking'];
            $deposit_amount = empty($tour_option['deposit-amount']) ? 0 : $tour_option['deposit-amount'];
        }

        if (!is_user_logged_in()) {
            $enable_deposit_payment = 'disable';
        }

        if ($enable_deposit_payment == 'enable') {
            $current_date = strtotime(current_time('Y-m-d'));
            $deposit_before_days = intval(tourmaster_get_option('payment', 'display-deposit-payment-day', '0'));
            $travel_date = strtotime($booking_detail['tour-date']);
            if ($current_date + ($deposit_before_days * 86400) > $travel_date) {
                $payment_type = 'full';
            } else {
                $payment_type = empty($booking_detail['payment-type']) ? 'full' : $booking_detail['payment-type'];
            }
        } else {
            $payment_type = 'full';
        }

        if ($payment_type == 'full' && $enable_full_payment == 'disable') {
            $payment_type = 'partial';
        }

        $ret .= '<div class="tourmaster-tour-booking-bar-total-price-wrap ' . ($payment_type == 'partial' ? 'tourmaster-deposit' : '') . '" >';

        if ($enable_full_payment == 'enable' && $enable_deposit_payment == 'enable' && !empty($deposit_amount)) {
            $dis_dep_edit = false;
            if($booking_detail['step'] == "4"){
                $dis_dep_edit = true;
            }
            if($dis_dep_edit){
                $ret .= '<div style="pointer-events: none" class="tourmaster-tour-booking-bar-deposit-option" >';
            }else{
                $ret .= '<div class="tourmaster-tour-booking-bar-deposit-option" >';
            }

            $ret .= '<label class="tourmaster-deposit-payment-full" >';
            $ret .= '<input type="radio" name="payment-type" value="full" ' . ($payment_type == 'full' ? 'checked' : '') . ' />';
            $ret .= '<span class="tourmaster-content" >';
            $ret .= '<i class="icon_check_alt2" ></i>';
            $ret .= esc_html__('Pay Full Amount', 'tourmaster');
            $ret .= '</span>';
            $ret .= '</label>';

            $ret .= '<label class="tourmaster-deposit-payment-partial" >';
            $ret .= '<input type="radio" name="payment-type" value="partial" ' . ($payment_type == 'partial' ? 'checked' : '') . ' />';
            $ret .= '<span class="tourmaster-content" >';
            $ret .= '<i class="icon_check_alt2" ></i>';
            $ret .= sprintf(esc_html__('Pay %d%% Deposit', 'tourmaster'), $deposit_amount);
            $ret .= '</span>';
            $ret .= '</label>';
            $ret .= '</div>';
        } else {
            $ret .= '<input type="hidden" name="payment-type" value="' . esc_attr($payment_type) . '" />';
        }
        $booking_detail["pilgrim_total_price"]=$tour_price['total-price'];
        $ret .= '<i class="icon_tag_alt" ></i>';
        $ret .= '<span class="tourmaster-tour-booking-bar-total-price-title" >' . esc_html__('Total Price', 'tourmaster') . '</span>';
        $ret .= '<span class="tourmaster-tour-booking-bar-total-price" >' . tourmaster_money_format($tour_price['total-price']) . '</span>';
        $ret .= '</div>';

        // deposit display
        if ($enable_deposit_payment == 'enable' && !empty($deposit_amount)) {

            // for price with paypal service fee
            if ($editable) {
                $deposit_price = ($tour_price['total-price'] * floatval($deposit_amount)) / 100;
            } else if (!empty($tour_price['deposit-price'])) {
                $deposit_price = $tour_price['deposit-price'];
            }

            if (!empty($deposit_price)) {
                $display_rate = true;

                $ret .= '<div class="tourmaster-tour-booking-bar-deposit-text ' . ($payment_type == 'partial' ? 'tourmaster-active' : '') . '" >';

                if (!empty($tour_price['deposit-price-raw'])) {
                    $ret .= '<div class="tourmaster-tour-booking-bar-deposit-info clearfix" >';
                    $ret .= '<span class="tourmaster-head" >' . sprintf(esc_html__('Deposit Amount (%s%%)', 'tourmaster'), $deposit_amount) . '</span>';
                    $ret .= '<span class="tourmaster-tail" >' . tourmaster_money_format($tour_price['deposit-price-raw']) . '</span>';
                    $ret .= '</div>';

                    $display_rate = false;
                }

                if (!empty($tour_price['deposit-paypal-service-rate']) && !empty($tour_price['deposit-paypal-service-fee'])) {
                    $ret .= '<div class="tourmaster-tour-booking-bar-deposit-info clearfix" >';
                    $ret .= '<span class="tourmaster-head" >' . sprintf(esc_html__('%d%% Paypal Service Fee', 'tourmaster'), $tour_price['deposit-paypal-service-rate']) . '</span>';
                    $ret .= '<span class="tourmaster-tail" >' . tourmaster_money_format($tour_price['deposit-paypal-service-fee']) . '</span>';
                    $ret .= '</div>';
                } else if (!empty($tour_price['deposit-credit-card-service-rate']) && !empty($tour_price['deposit-credit-card-service-fee'])) {
                    $ret .= '<div class="tourmaster-tour-booking-bar-deposit-info clearfix" >';
                    $ret .= '<span class="tourmaster-head" >' . sprintf(esc_html__('%d%% Credit Card Service Fee', 'tourmaster'), $tour_price['deposit-credit-card-service-rate']) . '</span>';
                    $ret .= '<span class="tourmaster-tail" >' . tourmaster_money_format($tour_price['deposit-credit-card-service-fee']) . '</span>';
                    $ret .= '</div>';
                }

                if ($display_rate) {
                    $ret .= '<span class="tourmaster-tour-booking-bar-deposit-title" >' . sprintf(esc_html__('%s%% Deposit ', 'tourmaster'), $deposit_amount) . '</span>';
                } else {
                    $ret .= '<span class="tourmaster-tour-booking-bar-deposit-title" >' . esc_html__('Deposit Price', 'tourmaster') . '</span>';
                }
                $ret .= '<span class="tourmaster-tour-booking-bar-deposit-price" >' . tourmaster_money_format($deposit_price) . '</span>';
                $ret .= '<span class="tourmaster-tour-booking-bar-deposit-caption" >' . esc_html__('*Pay the rest later', 'tourmaster') . '</span>';
                $ret .= '</div>';
            }
        }

        if ($next_Step_req) {
            $ret .= '<a class="tourmaster-tour-booking-continue tourmaster-button tourmaster-payment-step" data-step=' . $steps . ' >' . esc_html__('Next Step', 'tourmaster') . '</a>';
        }

        return $ret;
    }
}
// service form
if (!function_exists('tourmaster_set_mandatory_service')) {
    function tourmaster_set_mandatory_service($tour_option, $booking_detail)
    {
        if (!empty($tour_option['tour-service'])) {

            $booking_detail['service'] = empty($booking_detail['service']) ? array() : $booking_detail['service'];
            $booking_detail['service-amount'] = empty($booking_detail['service-amount']) ? array() : $booking_detail['service-amount'];

            foreach ($tour_option['tour-service'] as $service_id) {
                $service_option = get_post_meta($service_id, 'tourmaster-service-option', true);

                if (!empty($service_option['mandatory']) && $service_option['mandatory'] == 'enable') {
                    $booking_detail['service'][] = $service_id;
                    $booking_detail['service-amount'][] = 1;
                }
            }
        }

        return $booking_detail;
    } // tourmaster_set_mandatory_service
}

if (!function_exists('tourmaster_payment_service_form')) {
    function tourmaster_payment_service_form($tour_option, $booking_detail)
    {

        $ret = '';

        if (!empty($tour_option['tour-service'])) {
            if (!empty($booking_detail['service']) && !empty($booking_detail['service-amount'])) {
                $services = tourmaster_process_service_data($booking_detail['service'], $booking_detail['service-amount']);
            }

            $ret .= '<div class="tourmaster-payment-service-form-wrap" >';
            $ret .= '<h3 class="tourmaster-payment-service-form-title" >' . esc_html__('Please select your preferred additional services.', 'tourmaster') . '</h3>';

            $ret .= '<div class="tourmaster-payment-service-form-item-wrap" >';
            foreach ($tour_option['tour-service'] as $service_id) {
                $service_option = get_post_meta($service_id, 'tourmaster-service-option', true);
                if (empty($service_option)) continue;

                $ret .= '<div class="tourmaster-payment-service-form-item" >';
                $ret .= '<input type="checkbox" name="service[]" value="' . esc_attr($service_id) . '" ';
                if (!empty($service_option['mandatory']) && $service_option['mandatory'] == 'enable') {
                    $ret .= 'checked onclick="return false;" ';
                } else {
                    $ret .= (empty($services[$service_id])) ? '' : 'checked';
                }
                $ret .= ' />';
                $ret .= '<span class="tourmaster-payment-service-form-item-title" >' . get_the_title($service_id) . '</span>';

                $ret .= '<span class="tourmaster-payment-service-form-price-wrap" >';
                $ret .= '<span class="tourmaster-head" >' . tourmaster_money_format($service_option['price'], -2) . '</span>';
                $ret .= '<span class="tourmaster-tail tourmaster-type-' . esc_attr($service_option['per']) . '" >';
                if ($service_option['per'] == 'person') {
                    $ret .= '<span class="tourmaster-sep" >/</span>' . esc_html__('Person', 'tourmaster');
                    $ret .= '<input type="hidden" name="service-amount[]" value="1" />';
                } else if ($service_option['per'] == 'group') {
                    $ret .= '<span class="tourmaster-sep" >/</span>' . esc_html__('Group', 'tourmaster');
                    $ret .= '<input type="hidden" name="service-amount[]" value="1" />';
                } else if ($service_option['per'] == 'room') {
                    $ret .= '<span class="tourmaster-sep" >/</span>' . esc_html__('Room', 'tourmaster');
                    $ret .= '<input type="hidden" name="service-amount[]" value="1" />';
                } else if ($service_option['per'] == 'unit') {
                    $ret .= '<span class="tourmaster-sep" >x</span>' . '<input type="text" name="service-amount[]" ';
                    $ret .= ' value="' . (empty($services[$service_id]) ? '1' : esc_attr($services[$service_id])) . '" ';
                    $ret .= ' />';
                }
                $ret .= '</span>';
                $ret .= '</span>';
                $ret .= '</div>';
            }
            $ret .= '</div>';

            $ret .= '</div>';
        }

        return $ret;
    }
}

if (!function_exists('tourmaster_process_service_data')) {
    function tourmaster_process_service_data($services, $services_amount)
    {
        $ret = array();

        if (!empty($services)) {
            foreach ($services as $service_key => $service) {
                if (!empty($service) && !empty($services_amount[$service_key])) {
                    $ret[$service] = $services_amount[$service_key];
                }
            }
        }

        return $ret;
    }
}
// traveller form
if (!function_exists('tourmaster_payment_traveller_title')) {
    function tourmaster_payment_traveller_title()
    {
        return apply_filters('tourmaster_traveller_title_types', array(
            'mr' => esc_html__('Mr', 'tourmaster'),
            'mrs' => esc_html__('Mrs', 'tourmaster'),
            'ms' => esc_html__('Ms', 'tourmaster'),
            'miss' => esc_html__('Miss', 'tourmaster'),
            'master' => esc_html__('Master', 'tourmaster'),
        ));
    }
}

if (!function_exists('tourmaster_traveller_emergency_contact_input')) {
    function tourmaster_traveller_emergency_contact_input($tour_option, $booking_detail,$required =true)
    {

        $data_required = 'data-required';
        $first_name = empty($booking_detail['emergency_contact_name']) ? '' : $booking_detail['emergency_contact_name'];
        $phone_no = empty($booking_detail['emergency_contact_phone_no']) ? '' : $booking_detail['emergency_contact_phone_no'];
        $pilgrim_total_price = empty($booking_detail['pilgrim_total_price']) ? '0' : $booking_detail['pilgrim_total_price'];
        $ret = '<div style="width: 100%;float: left; margin-bottom: 5px;"><div style="float: left;width: fit-content"><h3 class="tourmaster-payment-traveller-info-title" ><i class="fa fa-suitcase" ></i>';
        $ret .= esc_html__('Emergency Contact Details', 'tourmaster');
        $ret .= '</h3></div><div style="vertical-align: middle;display: inline-block;"><h5 style="margin-bottom:0px !important; margin-left: 15px;">[ For Contacting during Pilgrimage ]</h5></div></div>';
        $ret .= '<hr style="clear: both"/>';
        $ret .= '<input type="text" class="tourmaster-traveller-info-input" name="emergency_contact_name" value="' . esc_attr($first_name) . '" placeholder="' . esc_html__('Name', 'tourmaster') . ($required ? ' *' : '') . '" ' . $data_required . ' />';
        $ret .= '<input type="text" class="tourmaster-traveller-info-input" name="emergency_contact_phone_no"  data-ajax-url="' . esc_attr(TOURMASTER_AJAX_URL) . '" validate-econtact-phone value="' . esc_attr($phone_no) . '" placeholder="' . esc_html__('Contact Number', 'tourmaster')  . ($required ? ' *' : '') . '" ' . $data_required . '" />';
        $ret .= '<input type="text" hidden class="tourmaster-traveller-info-input" name="pilgrim_total_price" value=' . esc_attr($pilgrim_total_price) . ' />';
//


        return $ret;

    }
}

if (!function_exists('tourmaster_payment_traveller_input')) {
    function tourmaster_payment_traveller_input($tour_option, $booking_detail, $i, $member_type,$member_type_url, $required = true)
    {

        $extra_class = '';

        $title = empty($booking_detail['traveller_title'][$i]) ? '' : $booking_detail['traveller_title'][$i];
        $data_required = $required ? 'data-required' : '';
        $title_html = '';
        $req_id_html = '';
        $dob_html = '';
        $member_type_seq = "";
        $member_id = "";

//        if ($member_type == 'Adult') {
//
//            $dob_html .= '<input type="text" name="DOB[]"  class="minimal tourmaster-adult-dob-datepicker tourmaster-traveller-info-input" readonly ' . ($required ? ' *' : '') . '" ' . $data_required ;
//            //$dob_html .=  'value="' . esc_attr($dob) . '" ';
//            $dob_html .= ' value="DOB" ';
//            $dob_html .= 'data-date-format="' . esc_attr(tourmaster_get_option('general', 'datepicker-date-format', 'd M yy')) . '" >';
//            $dob_html .= '<input type="hidden" name="traveller-dob[]" class="tourmaster-adult-dob-datepicker-alt"  required  />';
//            $dob_html .= '<input type="hidden" name="age[]"  />';
//           // array_push($member_type_seq,'A');
//            $member_type_seq = '<input type="hidden" name="mem-type-seq[]" value="A" />';
//            $member_id = '<input type="hidden" name="mem-id[]" value="' . $i . '" />';
//            //$dob_html .=  '</div>';
//
//
//        } elseif ($member_type == 'Sr. Citizen') {
//
//            $dob_html .= '<input type="text" name="DOB[]" class="minimal  tourmaster-sr-citizen-dob-datepicker tourmaster-traveller-info-input" readonly ' . ($required ? ' *' : '') . '" ' . $data_required ;
//            //$dob_html .=  'value="' . esc_attr($dob) . '" ';
//            $dob_html .= ' value="DOB" ';
//            $dob_html .= 'data-date-format="' . esc_attr(tourmaster_get_option('general', 'datepicker-date-format', 'd M yy')) . '" >';
//            $dob_html .= '<input type="hidden" name="traveller-dob[]" class="tourmaster-sr-citizen-dob-datepicker-alt"  required  />';
//            $member_type_seq = '<input type="hidden" name="mem-type-seq[]" value="S" />';
//            $member_id = '<input type="hidden" name="mem-id[]" value="' . $i . '" />';
//
//            //$dob_html .=  '</div>';
//
//
//        } elseif ($member_type == 'Child') {
//
//            $dob_html .= '<input type="text" name="DOB[]" class="minimal tourmaster-child-dob-datepicker tourmaster-traveller-info-input" readonly '  . ($required ? ' *' : '') . '" ' . $data_required ;
//            //$dob_html .=  'value="' . esc_attr($dob) . '" ';
//            $dob_html .= ' value="DOB" ';
//            $dob_html .= 'data-date-format="' . esc_attr(tourmaster_get_option('general', 'datepicker-date-format', 'd M yy')) . '" >';
//            $dob_html .= '<input type="hidden" name="traveller-dob[]" class="tourmaster-child-dob-datepicker-alt"  required />';
//            $member_type_seq = '<input type="hidden" name="mem-type-seq[]" value="C" />';
//            $member_id = '<input type="hidden" name="mem-id[]" value="' . $i . '" />';
//
//            //$dob_html .=  '</div>';
//
//
//        } else {

            $dob_html .= '<input type="text" name="DOB[]" class="minimal tourmaster-dob-datepicker tourmaster-traveller-info-input" readonly ' . ($required ? ' *' : '') . '" ' . $data_required ;
            //$dob_html .=  'value="' . esc_attr($dob) . '" ';
            $dob_html .= ' value="';
            $dob_html .= empty($booking_detail['DOB'][$i]) ? 'DOB' : $booking_detail['DOB'][$i];
            $dob_html .= '" data-date-format="' . esc_attr(tourmaster_get_option('general', 'datepicker-date-format', 'd M yy')) . '"  >';
            $dob_html .= '<input type="hidden" name="traveller-dob[]" class="tourmaster-dob-datepicker-alt"  required  />';
            $member_type_seq = '<input type="hidden" name="mem-type-seq[]" value="G" />';
            $member_id = '<input type="hidden" name="mem-id[]" value="' . $i . '" />';
            $tour_seva = '<input type="hidden" name="tour_seva" value="" />';

            //$dob_html .=  '</div>';

//        }

        $gender_html = '<select name="gender[]"  class="minimal tourmaster-traveller-info-input" readonly'  . ($required ? ' *' : '') . '" ' . $data_required;
        $gender_html .= '><option value="-1"';
        $gender_html .= empty($booking_detail['gender'][$i]) ? 'selected' : '';
        $gender_html .= '>Gender</option>';
        $gender_html .= '<option value="male"' ;
        $gender_html .= (!empty($booking_detail['gender'][$i]) && $booking_detail['gender'][$i] == "male")? 'selected' : '' ;
        $gender_html .= '>Male</option>';
        $gender_html .= '<option value="female"';
        $gender_html .= (!empty($booking_detail['gender'][$i]) && $booking_detail['gender'][$i] == "female")? 'selected' : '' ;
        $gender_html .= '>Female</option></select>';

        $traveller_id_no = empty($booking_detail['traveller_id_no'][$i]) ? '' : $booking_detail['traveller_id_no'][$i];
//        if ($tour_option['require-traveller-info-title'] == 'enable') {
//            $extra_class .= ' tourmaster-with-info-title';
//
//            $title_html .= '<div class="tourmaster-combobox-wrap tourmaster-traveller-info-title" >';
//            $title_html .= '<select name="traveller_title[]" >';
//            $title_types = tourmaster_payment_traveller_title();
//            foreach ($title_types as $title_slug => $title_type) {
//                $title_html .= '<option value="' . esc_attr($title_slug) . '" ' . ($title_slug == $title ? 'selected' : '') . ' >' . $title_type . '</option>';
//            }
//            $title_html .= '</select>';
//            $title_html .= '</div>';
//        }
        $title = "ID Type";

//        $extra_class .= ' tourmaster-with-info-title';
        $req_traveller_ID = tourmaster_get_post_meta($booking_detail['tour-id'], 'tour-settings');
        // $req_id_html .= '<div class="tourmaster-combobox-wrap tourmaster-traveller-info-title" style="width: auto;padding-right: 10px;">';
        $req_id_html .= '<select class="required minimal tourmaster-traveller-info-input" name="tour_travellers_id[]"' . ($required ? ' *' : '') . '" ' . $data_required . '>';
        $id_types = apply_filters('tourmaster_traveller_id_types', $req_traveller_ID['tour-travellers-id']);
        $req_id_html .= '<option value="-1" ';
        $req_id_html .= empty($booking_detail['tour_travellers_id'][$i]) ? 'selected' : '';
        $req_id_html .= '>' . $title . '</option>';

        foreach ($id_types as $id_slug => $id_type) {
            $req_id_html .= '<option value="' . get_the_title($id_type) . '"';
            $req_id_html .= (!empty($booking_detail['tour_travellers_id'][$i]) && $booking_detail['tour_travellers_id'][$i] == get_the_title($id_type))? ' selected' : '' ;
            $req_id_html .= ' name="' . esc_attr($id_type) . '" >' . get_the_title($id_type) . '</option>';
        }
        $req_id_html .= '</select>';
        $req_id_html .= '<input type="text" class="tourmaster-traveller-info-input tourmaster-col2" name="traveller_id_no[]" value="' . esc_attr($traveller_id_no) . '" placeholder="' . esc_html__('ID No.', 'tourmaster') . ($required ? ' *' : '') . '" ' . $data_required . ' />';

        // $req_id_html .= '</div>';

        $first_name = empty($booking_detail['traveller_first_name'][$i]) ? '' : $booking_detail['traveller_first_name'][$i];
        //$last_name = empty($booking_detail['traveller_last_name'][$i])? '': $booking_detail['traveller_last_name'][$i];
        $voucher = empty($booking_detail['traveller_voucher'][$i]) ? '' : $booking_detail['traveller_voucher'][$i];
        $gender = empty($booking_detail['gender'][$i]) ? '' : $booking_detail['gender'][$i];
        $phone_no = empty($booking_detail['traveller_phone'][$i]) ? '' : $booking_detail['traveller_phone'][$i];

        $req_traveller_ID[] = tourmaster_get_post_meta($booking_detail['tour-id'], 'tour-travellers-id');

        $passport = empty($booking_detail['traveller_passport'][$i]) ? '' : $booking_detail['traveller_passport'][$i];

        $ret = '<div class="tourmaster-traveller-info-field clearfix ' . esc_attr($extra_class) . '">';
        //$ret .= '<span class="tourmaster-head">' . esc_html__('Traveller', 'tourmaster') . ' ' . ($i + 1) . '</span>';
       // $ret .= '<span class="">' . esc_html__($member_type, 'tourmaster') . ' - ' . ($i + 1) . '</span>';
        $ret .= '<span class="tourmaster-tail clearfix"><div class="traveller-info">';
        //$ret .= '<span class="tourmaster-traveller-info-input-icon">' . esc_html__($member_type, 'tourmaster') . ' - ' . ($i + 1) . '</span>';

//        $ret .= '<div class="traveller-info">'.$title_html;
        $ret .= $title_html;
        $ret .= '<input type="text" class="tourmaster-traveller-info-input" name="traveller_first_name[]" value="' . esc_attr($first_name) . '" placeholder="' . esc_html__('Name', 'tourmaster') . ($required ? ' *' : '') . '" ' . $data_required . ' />';
        $ret .= $gender_html;
        $ret .= '<input type="text" class="tourmaster-traveller-info-input" name="traveller_phone[]"  data-ajax-url="' . esc_attr(TOURMASTER_AJAX_URL) . '" validate-phone value="' . esc_attr($phone_no) . '" placeholder="' . esc_html__('Phone Number', 'tourmaster')  . '" />';
//        $ret .= '<input type="text" class="tourmaster-traveller-info-input" name="traveller_last_name[]" value="' . esc_attr($last_name) . '" placeholder="' . esc_html__('Last Name', 'tourmaster') . ($required? ' *': '') . '" ' . $data_required . ' />';
        //$ret .= '</div>';
        // TODO Check the additional Details
        // $ret .= '<div class="traveller-info">'.$dob_html;

        // Adding Date of birth
        $ret .= $dob_html;
        $ret .= $member_type_seq;
        $ret .= $member_id;
        $ret .= $tour_seva;
        //$ret .= '</div>';
        $ret .= $req_id_html;
        $ret .= '<input type="text"  class="tourmaster-traveller-info-input tourmaster-col2" name="traveller_voucher[]" data-ajax-url="' . esc_attr(TOURMASTER_AJAX_URL) . '" validate-coupon value="' . esc_attr($voucher) . '" placeholder="' . esc_html__('Concession Code', 'tourmaster') . '" />';
        $ret .= '<input type="hidden" name="voucher-codes"/>';
        $ret .= '<input type="hidden" name="seva-ids"/>';

        //$ret .= '<div class="traveller-info">'. '<input type="file" class="tourmaster-traveller-info-input tourmaster-col2" name="fileToUpload" id="fileToUpload">';
        //$ret .= '<div class="tourmaster-payment-receipt-field tourmaster-payment-receipt-field-receipt tourmaster-type-file clearfix"><div class="tourmaster-head">Upload ID</div><div class="tourmaster-tail clearfix"><label class="tourmaster-file-label"><span class="tourmaster-file-label-text" data-default="Click to select a file">Click to select a file</span><input type="file" name="fileToUpload"></label></div>';
        if(empty($booking_detail['fileToUpload'][$i])){
            $file_id = 'id-upload-'. $i;
            $file_up_nm = 'fp-'. $i;
            $ret .= '<div class="id-upload-btn"><label  class="tourmaster-file-label"><span id="id-upload-text" class="tourmaster-button tourmaster-file-label-text" data-default="Upload ID"><p id= ' . $file_up_nm . '>Upload ID</p></span><input type="file" accept="image/*" id=' . $file_id . ' name="fileToUpload[]" data-ajax-url="' . esc_attr(TOURMASTER_AJAX_URL) . '" validate-file ></label></div>';

        }else{
            $file_id = 'id-upload-'. $i;
            $file_up_nm = 'fp-'. $i;
            $file_path = $_SERVER["DOCUMENT_ROOT"] . "/wp-content/uploads/documents/" . $booking_detail['fileToUpload'][$i];
            //Convert the bytes into KB.
            $fileSizeKB = round(filesize($file_path) / 1024);
            $file_value = 'ID File Size - ' . $fileSizeKB . 'KB';
            $ret .= '<div class="id-upload-btn"><label  class="tourmaster-file-label"><span id="id-upload-text" class="tourmaster-button tourmaster-file-label-text" data-default="Upload ID"><p id= ' . $file_up_nm . '>' . $file_value . '</p></span><input type="file" accept="image/*" id=' . $file_id . ' name="fileToUpload[]"  data-ajax-url="' . esc_attr(TOURMASTER_AJAX_URL) . '" validate-file  value= '. $file_value .' ></label></div>';

        }

        //$ret .= $req_id_html;
        $ret .= '</div>';

        if (!empty($tour_option['require-traveller-passport']) && $tour_option['require-traveller-passport'] == 'enable') {
            $ret .= '<input type="text" class="tourmaster-traveller-info-passport" name="traveller_passport[]" value="' . esc_attr($passport) . '" placeholder="' . esc_html__('Passport Number', 'tourmaster') . ($required ? ' *' : '') . '" ' . $data_required . ' />';
        }

        // additional traveller fields
        if (!empty($tour_option['additional-traveller-fields'])) {
            foreach ($tour_option['additional-traveller-fields'] as $field) {
                $field_value = empty($booking_detail['traveller_' . $field['slug']][$i]) ? '' : $booking_detail['traveller_' . $field['slug']][$i];

                if (!empty($field['width'])) {
                    $ret .= '<div style="float: left; width: ' . esc_attr($field['width']) . '" >';
                }
                $ret .= '<div class="tourmaster-traveller-info-custom" >';
                if ($field['type'] == 'combobox') {
                    $ret .= '<div class="tourmaster-combobox-wrap" >';
                    $ret .= '<select name="traveller_' . esc_attr($field['slug']) . '[]" >';
                    foreach ($field['options'] as $option_val => $option_title) {
                        $ret .= '<option value="' . esc_attr($option_val) . '" ' . ($field_value == $option_val ? 'selected' : '') . ' >' . $option_title . '</option>';
                    }
                    $ret .= '</select>';
                    $ret .= '</div>';
                } else {
                    $ret .= '<input type="text" ';
                    $ret .= 'name="traveller_' . esc_attr($field['slug']) . '[]" ';
                    $ret .= 'value="' . esc_attr($field_value) . '" ';
                    $ret .= 'placeholder="' . esc_attr($field['title']) . ((!empty($field['required']) && $field['required'] == 'true') ? ' *' : '') . '" ';
                    $ret .= (!empty($field['required']) && $field['required'] == 'true') ? 'data-required ' : '';
                    $ret .= ' />';
                }
                $ret .= '</div>';
                if (!empty($field['width'])) {
                    $ret .= '</div>';
                }
            }
        }

        $ret .= '</span>';
        $ret .= '</div>';

        return $ret;
    }
}

if (!function_exists('tourmaster_payment_traveller_input_form')) {
    function tourmaster_payment_traveller_input_form($tour_option, $booking_detail, $i, $member_type, $traveller_type, $required = true)
    {
        $ret = '';

        if ($traveller_type == 'normal') {

            $ret .= '<h3 class="tourmaster-payment-traveller-info-title" ><i class="fa fa-suitcase" ></i>';
            $ret .= esc_html__('Main Pilgrim Details', 'tourmaster');
            $ret .= '</h3>';


            $tour_adult_count = empty($booking_detail['tour-adult']) ? 0 : intval($booking_detail['tour-adult']);
            $tour_sr_citizen_count = empty($booking_detail['tour-sr-citizen']) ? 0 : intval($booking_detail['tour-sr-citizen']);
            $tour_male_count = empty($booking_detail['tour-male']) ? 0 : intval($booking_detail['tour-male']);
            $tour_female_count = empty($booking_detail['tour-female']) ? 0 : intval($booking_detail['tour-female']);
            $tour_child_count = empty($booking_detail['tour-children']) ? 0 : intval($booking_detail['tour-children']);
            $tour_student_count = empty($booking_detail['tour-student']) ? 0 : intval($booking_detail['tour-student']);
            $tour_infant_count = empty($booking_detail['tour-infant']) ? 0 : intval($booking_detail['tour-infant']);


            for ($i = 0; $i < $tour_adult_count; $i++) {
                $ret .= tourmaster_payment_traveller_input($tour_option, $booking_detail, $i, 'Adult',"wp-content/uploads/2019/08/Adult_A_icon.png");
            }
            for ($i = 0; $i < $tour_sr_citizen_count; $i++) {
                $ret .= tourmaster_payment_traveller_input($tour_option, $booking_detail, $i, 'Sr. Citizen',"wp-content/uploads/2019/08/SeniorCit_S_icon.png");
            }
            for ($i = 0; $i < $tour_male_count; $i++) {
                $ret .= tourmaster_payment_traveller_input($tour_option, $booking_detail, $i, 'Male',"");
            }
            for ($i = 0; $i < $tour_female_count; $i++) {
                $ret .= tourmaster_payment_traveller_input($tour_option, $booking_detail, $i, 'Fe-male',"");
            }
            for ($i = 0; $i < $tour_child_count; $i++) {
                $ret .= tourmaster_payment_traveller_input($tour_option, $booking_detail, $i, 'Child',"wp-content/uploads/2019/08/Child_C_icon.png");
            }
            for ($i = 0; $i < $tour_student_count; $i++) {
                $ret .= tourmaster_payment_traveller_input($tour_option, $booking_detail, $i, 'Student',"");
            }
            for ($i = 0; $i < $tour_infant_count; $i++) {
                $ret .= tourmaster_payment_traveller_input($tour_option, $booking_detail, $i, 'Infant',"");
            }
        }

        $extra_class = '';

        $title = empty($booking_detail['traveller_title'][$i]) ? '' : $booking_detail['traveller_title'][$i];
        $data_required = $required ? 'data-required' : '';
        $title_html = '';
        $req_id_html = '';
        $dob_html = '';

//        if ($member_type == 'Adult') {
//
//            $dob_html .= '<input type="text" class="tourmaster-adult-dob-datepicker tourmaster-traveller-info-input" readonly required ';
//            //$dob_html .=  'value="' . esc_attr($dob) . '" ';
//            $dob_html .= 'value=" Select DOB" ';
//            $dob_html .= 'data-date-format="' . esc_attr(tourmaster_get_option('general', 'datepicker-date-format', 'd M yy')) . '" ';
//            $dob_html .= '<input type="hidden" name="traveller-dob[]" class="tourmaster-adult-dob-datepicker-alt"  required  />';
//            //$dob_html .=  '</div>';
//
//
//        } elseif ($member_type == 'Sr. Citizen') {
//
//            $dob_html .= '<input type="text" class="tourmaster-sr-citizen-dob-datepicker tourmaster-traveller-info-input" readonly required ';
//            //$dob_html .=  'value="' . esc_attr($dob) . '" ';
//            $dob_html .= 'value=" Select DOB" ';
//            $dob_html .= 'data-date-format="' . esc_attr(tourmaster_get_option('general', 'datepicker-date-format', 'd M yy')) . '" ';
//            $dob_html .= '<input type="hidden" name="traveller-dob[]" class="tourmaster-sr-citizen-dob-datepicker-alt"  required  />';
//            //$dob_html .=  '</div>';
//
//
//        } elseif ($member_type == 'Child') {
//
//            $dob_html .= '<input type="text" class="tourmaster-child-dob-datepicker tourmaster-traveller-info-input" readonly  required ';
//            //$dob_html .=  'value="' . esc_attr($dob) . '" ';
//            $dob_html .= 'value=" Select DOB" ';
//            $dob_html .= 'data-date-format="' . esc_attr(tourmaster_get_option('general', 'datepicker-date-format', 'd M yy')) . '" ';
//            $dob_html .= '<input type="hidden" name="traveller-dob[]" class="tourmaster-child-dob-datepicker-alt"  required />';
//            //$dob_html .=  '</div>';
//
//
//        } else {

            $dob_html .= '<input type="text" class=" minimal tourmaster-dob-datepicker tourmaster-traveller-info-input" readonly required ';
            //$dob_html .=  'value="' . esc_attr($dob) . '" ';
            $dob_html .= 'value=" Select DOB" ';
            $dob_html .= 'data-date-format="' . esc_attr(tourmaster_get_option('general', 'datepicker-date-format', 'd M yy')) . '"  ';
            $dob_html .= '<input type="hidden" name="traveller-dob[]" class="tourmaster-dob-datepicker-alt"  required  />';
            //$dob_html .=  '</div>';

//        }


        $traveller_id_no = empty($booking_detail['$traveller_id_no'][$i]) ? '' : $booking_detail['$traveller_id_no'][$i];
//        if ($tour_option['require-traveller-info-title'] == 'enable') {
//            $extra_class .= ' tourmaster-with-info-title';
//
//            $title_html .= '<div class="tourmaster-combobox-wrap tourmaster-traveller-info-title" >';
//            $title_html .= '<select name="traveller_title[]" >';
//            $title_types = tourmaster_payment_traveller_title();
//            foreach ($title_types as $title_slug => $title_type) {
//                $title_html .= '<option value="' . esc_attr($title_slug) . '" ' . ($title_slug == $title ? 'selected' : '') . ' >' . $title_type . '</option>';
//            }
//            $title_html .= '</select>';
//            $title_html .= '</div>';
//        }
        $title = "Select ID Type";

        $extra_class .= ' tourmaster-with-info-title';
        $req_traveller_ID = tourmaster_get_post_meta($booking_detail['tour-id'], 'tour-settings');
        // $req_id_html .= '<div class="tourmaster-combobox-wrap tourmaster-traveller-info-title" style="width: auto;padding-right: 10px;">';
        $req_id_html .= '<select class="minimal tourmaster-traveller-info-input" name="tour_travellers_id[]" required>';
        $id_types = apply_filters('tourmaster_traveller_id_types', $req_traveller_ID['tour-travellers-id']);
        $req_id_html .= '<option value="' . esc_attr($title) . '" ' . ($title == $title ? 'selected' : '') . ' >' . $title . '</option>';

        foreach ($id_types as $id_slug => $id_type) {
            $req_id_html .= '<option value="' . esc_attr($id_slug) . '" >' . get_the_title($id_type) . '</option>';
        }
        $req_id_html .= '</select>';
        $req_id_html .= '<input type="text" class="tourmaster-traveller-info-input tourmaster-col2" name="traveller_id_no[]" value="' . esc_attr($traveller_id_no) . '" placeholder="' . esc_html__('ID No.', 'tourmaster') . ($required ? ' *' : '') . '" ' . $data_required . ' />';

        // $req_id_html .= '</div>';

        $first_name = empty($booking_detail['traveller_first_name'][$i]) ? '' : $booking_detail['traveller_first_name'][$i];
        //$last_name = empty($booking_detail['traveller_last_name'][$i])? '': $booking_detail['traveller_last_name'][$i];
        $voucher = empty($booking_detail['traveller_voucher'][$i]) ? '' : $booking_detail['traveller_voucher'][$i];

        $req_traveller_ID[] = tourmaster_get_post_meta($booking_detail['tour-id'], 'tour-travellers-id');

        $passport = empty($booking_detail['traveller_passport'][$i]) ? '' : $booking_detail['traveller_passport'][$i];

        $ret = '<div class="tourmaster-traveller-info-field clearfix ' . esc_attr($extra_class) . '">';
        //$ret .= '<span class="tourmaster-head">' . esc_html__('Traveller', 'tourmaster') . ' ' . ($i + 1) . '</span>';
        $ret .= '<span class="tourmaster-head">' . esc_html__($member_type, 'tourmaster') . ' - ' . ($i + 1) . '</span>';
        $ret .= '<span class="tourmaster-tail clearfix"><div class="traveller-info">';
        //$ret .= '<div class="traveller-info">'.$title_html;
        $ret .= '<input type="text" class="tourmaster-traveller-info-input" name="traveller_first_name[]" value="' . esc_attr($first_name) . '" placeholder="' . esc_html__('Name', 'tourmaster') . ($required ? ' *' : '') . '" ' . $data_required . ' />';
        //$ret .= '<input type="text" class="tourmaster-traveller-info-input" name="traveller_last_name[]" value="' . esc_attr($last_name) . '" placeholder="' . esc_html__('Last Name', 'tourmaster') . ($required? ' *': '') . '" ' . $data_required . ' />';
        //$ret .= '</div>';
        // TODO Check the additional Details
        // $ret .= '<div class="traveller-info">'.$dob_html;
        $ret .= $dob_html;
        $ret .= '<input type="text" class="tourmaster-traveller-info-input tourmaster-col2" name="traveller_voucher[]" value="' . esc_attr($voucher) . '" placeholder="' . esc_html__('Voucher No.', 'tourmaster') . '" />';
        //$ret .= '</div>';
        $ret .= $req_id_html . '</div>';
        //$ret .= '<div class="traveller-info">'. '<input type="file" class="tourmaster-traveller-info-input tourmaster-col2" name="fileToUpload" id="fileToUpload">';
        //$ret .= '<div class="tourmaster-payment-receipt-field tourmaster-payment-receipt-field-receipt tourmaster-type-file clearfix"><div class="tourmaster-head">Upload ID</div><div class="tourmaster-tail clearfix"><label class="tourmaster-file-label"><span class="tourmaster-file-label-text" data-default="Click to select a file">Click to select a file</span><input type="file" name="fileToUpload"></label></div>';
        $ret .= '<div class="tourmaster-payment-receipt-field tourmaster-payment-receipt-field-receipt tourmaster-type-file clearfix"><div class="tourmaster-head">Upload ID</div><div class="tourmaster-tail clearfix"><label class="tourmaster-file-label"><span class="tourmaster-file-label-text" data-default="Click to select a file">Click to select a file</span><input type="file" name="fileToUpload"></label></div>';

        //$ret .= $req_id_html;
        $ret .= '</div>';

        if (!empty($tour_option['require-traveller-passport']) && $tour_option['require-traveller-passport'] == 'enable') {
            $ret .= '<input type="text" class="tourmaster-traveller-info-passport" name="traveller_passport[]" value="' . esc_attr($passport) . '" placeholder="' . esc_html__('Passport Number', 'tourmaster') . ($required ? ' *' : '') . '" ' . $data_required . ' />';
        }

        // additional traveller fields
        if (!empty($tour_option['additional-traveller-fields'])) {
            foreach ($tour_option['additional-traveller-fields'] as $field) {
                $field_value = empty($booking_detail['traveller_' . $field['slug']][$i]) ? '' : $booking_detail['traveller_' . $field['slug']][$i];

                if (!empty($field['width'])) {
                    $ret .= '<div style="float: left; width: ' . esc_attr($field['width']) . '" >';
                }
                $ret .= '<div class="tourmaster-traveller-info-custom" >';
                if ($field['type'] == 'combobox') {
                    $ret .= '<div class="tourmaster-combobox-wrap" >';
                    $ret .= '<select name="traveller_' . esc_attr($field['slug']) . '[]" >';
                    foreach ($field['options'] as $option_val => $option_title) {
                        $ret .= '<option value="' . esc_attr($option_val) . '" ' . ($field_value == $option_val ? 'selected' : '') . ' >' . $option_title . '</option>';
                    }
                    $ret .= '</select>';
                    $ret .= '</div>';
                } else {
                    $ret .= '<input type="text" ';
                    $ret .= 'name="traveller_' . esc_attr($field['slug']) . '[]" ';
                    $ret .= 'value="' . esc_attr($field_value) . '" ';
                    $ret .= 'placeholder="' . esc_attr($field['title']) . ((!empty($field['required']) && $field['required'] == 'true') ? ' *' : '') . '" ';
                    $ret .= (!empty($field['required']) && $field['required'] == 'true') ? 'data-required' : '';
                    $ret .= ' />';
                }
                $ret .= '</div>';
                if (!empty($field['width'])) {
                    $ret .= '</div>';
                }
            }
        }

        $ret .= '</span>';
        $ret .= '</div>';

        return $ret;
    }
}

if (!function_exists('tourmaster_payment_contact_form_data')) {
    function tourmaster_payment_contact_form_data($tour_option, $date_price, $booking_detail, $step)
    {
        $ret = '';
        $traveller_amount = tourmaster_get_tour_people_amount($tour_option, $date_price, $booking_detail);

        //$ret .= tourmaster_payment_traveller_input_form($tour_option, $booking_detail, $traveller_amount, 'normal', true);

        $ret .= '<div  class="gdlr-core-pbf-element"><div class="gdlr-core-accordion-item gdlr-core-item-pdlr gdlr-core-item-pdb  gdlr-core-accordion-style-background-title gdlr-core-left-align">';
        $ret .= '<div id="gdlr-accordion"> ';
        //Tab 1
        $ret .= '<div class="gdlr-core-accordion-item-tab clearfix  gdlr-core-active">
                        <div class="gdlr-core-accordion-item-icon gdlr-core-js gdlr-core-skin-icon  gdlr-core-skin-e-background gdlr-core-skin-border"></div>
                        <div class="gdlr-core-accordion-item-content-wrapper">';
        $ret .= '<h3 id="pilgrim-contact-details-title" class="tourmaster-payment-contact-title gdlr-core-accordion-item-title gdlr-core-js " ><i class="fa fa-file-text-o" ></i>';
        $ret .= esc_html__('Pilgrim Contact Details (Single point of Contact)', 'tourmaster');
        $ret .= '</h3>';
        // <h4 class="gdlr-core-accordion-item-title gdlr-core-js ">Accordion Item 1</h4>
        $ret .= '<div class="gdlr-core-accordion-item-content" style="">';
        $ret .= '<div class="tourmaster-payment-contact-wrap tourmaster-form-field tourmaster-with-border" >';
        $contact_fields = tourmaster_get_payment_contact_form_fields();
        foreach ($contact_fields as $field_slug => $contact_field) {
            $contact_field['echo'] = false;
            $contact_field['slug'] = $field_slug;

            $value = empty($booking_detail[$field_slug]) ? '' : $booking_detail[$field_slug];

            $ret .= tourmaster_get_form_field($contact_field, 'contact', $value);
        }

        $ret .= '<div class="tourmaster-payment-billing-copy-wrap" >';
        $ret .= '<label><span class="tourmaster-payment-billing-copy-text" >' . esc_html__('Note : The specified person will be contacted for confirmation and tour updates.', 'tourmaster') . '</span>';
        $ret .= '</label></div>'; // tourmaster-payment-billing-copy-wrap


        $ret .= '<div class="tourmaster-tour-booking-required-error-2 tourmaster-notification-box tourmaster-failure" ';
        $ret .= 'data-default="' . esc_html__('Please fill all required fields.', 'tourmaster') . '" ';
        $ret .= 'data-email="' . esc_html__('Invalid E-Mail, please try again.', 'tourmaster') . '" ';
        $ret .= 'data-phone="' . esc_html__('Invalid phone number, please enter 10 digit Mobile Number or Landline number with area code.', 'tourmaster') . '" ';
        $ret .= '></div>';
        $ret .= '<a id="gdlr-core-accordion-next-button-2" class="tourmaster-button tourmaster-traveldet-nextstep gdlr-core-accordion-next-button"  >' . esc_html__('Proceed', 'tourmaster') . '</a>';
        $ret .= '<a id="go-back-button" data-step="2" class="tourmaster-button go-back-button gdlr-core-accordion-back-button" >Go Back</a>';

        $ret .= '</div></div></div></div>';  // End of Tab 2

        //Tab 3
        $ret .= '<div class="gdlr-core-accordion-item-tab clearfix gdlr-core-inactive">
                        <div class="gdlr-core-accordion-item-icon gdlr-core-js gdlr-core-skin-icon  gdlr-core-skin-e-background gdlr-core-skin-border"></div>
                        <div class="gdlr-core-accordion-item-content-wrapper">';

        $ret .= '<h3 id="payee-details-title" class="tourmaster-payment-billing-title gdlr-core-accordion-item-title gdlr-core-js " ><i class="fa fa-file-text-o" ></i>';
        $ret .= esc_html__('Payee Details', 'tourmaster');
        $ret .= '</h3>';
        // <h4 class="gdlr-core-accordion-item-title gdlr-core-js ">Accordion Item 1</h4>
        $ret .= '<div class="gdlr-core-accordion-item-content" style="">';

        $ret .= '<div class="tourmaster-payment-billing-wrap tourmaster-form-field tourmaster-with-border" >';

        $ret .= '<div class="tourmaster-payment-billing-copy-wrap" >';
        $ret .= '<label><input type="checkbox" class="tourmaster-payment-billing-copy" id="tourmaster-payment-billing-copy" ></i>';
        $ret .= '<span class="tourmaster-payment-billing-copy-text" >' . esc_html__('The same as pilgrim contact details', 'tourmaster') . '</span>';
        $ret .= '</label></div>'; // tourmaster-payment-billing-copy-wrap

        foreach ($contact_fields as $field_slug => $contact_field) {

            $contact_field['echo'] = false;
            $contact_field['slug'] = 'billing_' . $field_slug;
            $contact_field['data'] = array(
                'slug' => 'contact-detail',
                'value' => $field_slug
            );

            $value = empty($booking_detail['billing_' . $field_slug]) ? '' : $booking_detail['billing_' . $field_slug];

            $ret .= tourmaster_get_form_field($contact_field, 'billing', $value);
        }

        $ret .= '<div class="tourmaster-tour-booking-required-error-3 tourmaster-notification-box tourmaster-failure" ';
        $ret .= 'data-default="' . esc_html__('Please fill all required fields.', 'tourmaster') . '" ';
        $ret .= 'data-email="' . esc_html__('Invalid E-Mail, please try again.', 'tourmaster') . '" ';
        $ret .= 'data-phone="' . esc_html__('Invalid phone number, please enter 10 digit Mobile Number or Landline number with area code.', 'tourmaster') . '" ';
        $ret .= '></div>';
        $ret .= '<a id="gdlr-core-accordion-next-button-3" class="tourmaster-button  tourmaster-traveldet-nextstep gdlr-core-accordion-next-button"  >' . esc_html__('Proceed', 'tourmaster') . '</a>';
        $ret .= '<a  id="second-step-backbutton2" data-step="2" class="tourmaster-button tourmaster-payment-step-item gdlr-core-accordion-back-button"  >Go Back</a>';

        $ret .= '</div></div></div></div>';  // End of Tab 3

        //Tab 4

        $ret .= '<div class="gdlr-core-accordion-item-tab clearfix gdlr-core-inactive"><div class="gdlr-core-accordion-item-icon gdlr-core-js gdlr-core-skin-icon  gdlr-core-skin-e-background gdlr-core-skin-border"></div>';
        $ret .= '<div class="gdlr-core-accordion-item-content-wrapper">';
        $ret .= '<h3 class="gdlr-core-accordion-item-title gdlr-core-js " ><i class="fa fa-file-text-o" ></i>';
        $ret .= esc_html__('Additional Notes / Comments / Requests', 'tourmaster');
        $ret .= '</h3>';
        $ret .= '<div class="gdlr-core-accordion-item-content">';

        // additional notes

        $additional_notes = empty($booking_detail['additional_notes']) ? '' : $booking_detail['additional_notes'];
        $ret .= '<div class="tourmaster-payment-additional-note-wrap tourmaster-form-field tourmaster-with-border" >';
        $ret .= '<div class="tourmaster-additional-note-field">';
//        $ret .= '<span class="tourmaster-head">' . esc_html__('Remarks / Comments / Requests', 'tourmaster') . '</span>';
        $ret .= '<span class="tourmaster-tail ">';
        $ret .= '<textarea name="additional_notes" >' . esc_textarea($additional_notes) . '</textarea>';
        $ret .= '</span>';
        $ret .= '</div>'; // additional-note-field
        $ret .= '</div>'; // tourmasster-payment-additional-note-wrap
        $ret .= '<div class="tourmaster-tour-booking-required-error tourmaster-notification-box tourmaster-failure" ';
        $ret .= 'data-default="' . esc_html__('Please fill all required fields.', 'tourmaster') . '" ';
        $ret .= 'data-email="' . esc_html__('Invalid E-Mail, please try again.', 'tourmaster') . '" ';
        $ret .= 'data-phone="' . esc_html__('Invalid phone number, please try again.', 'tourmaster') . '" ';
        $ret .= '></div>';
        $ret .= '<a class="tourmaster-button tourmaster-traveldet-nextstep tourmaster-payment-step" data-step= ' . $step . '  >' . esc_html__('Next Step', 'tourmaster') . '</a>';
        $ret .= '<a id="second-step-backbutton3" data-step="2" class="tourmaster-button tourmaster-payment-step-item margin-left-back-button" >Go Back</a>';
        $ret .= '</div></div></div>';  // End of Tab 4
        $ret .= '</div></div></div>';// End of Accordion
        return $ret;
    }
}

if (!function_exists('tourmaster_payment_traveller_form_data')) {
    function tourmaster_payment_traveller_form_data($tour_option, $date_price, $booking_detail, $step)
    {
        $ret = '';
        // get additonal traveller fields
        if (empty($tour_option['additional-traveller-fields'])) {
            $tour_option['additional-traveller-fields'] = tourmaster_get_option('general', 'additional-traveller-fields', '');
        }
        if (!empty($tour_option['additional-traveller-fields'])) {
            $tour_option['additional-traveller-fields'] = tourmaster_read_custom_fields($tour_option['additional-traveller-fields']);
        }

        // traveller detail
        if (!empty($tour_option['require-each-traveller-info']) && $tour_option['require-each-traveller-info'] == 'enable') {
            $tour_option['require-traveller-info-title'] = empty($tour_option['require-traveller-info-title']) ? 'enable' : $tour_option['require-traveller-info-title'];

            $ret .= '<div class="tourmaster-payment-traveller-info-wrap tourmaster-form-field tourmaster-with-border" >';
            // group
            if ($date_price['pricing-method'] == 'group') {
                $traveller_amount = $date_price['max-group-people'];

                if ($traveller_amount > 0) {
                    $required = true;
                    //$ret .= tourmaster_payment_traveller_input_form($tour_option, $booking_detail, $traveller_amount , $required ,  'group');

                    $ret .= '<h3 class="tourmaster-payment-traveller-info-title" ><i class="fa fa-suitcase" ></i>';
                    $ret .= esc_html__('Main Pilgrim Details', 'tourmaster');
                    $ret .= '</h3>';

                    $required = true;
                    for ($i = 0; $i < $traveller_amount; $i++) {
                        $ret .= tourmaster_payment_traveller_input($tour_option, $booking_detail, $i,"","", $required);
                        $required = false;
                    }
                }

                // normal
            } else {
                $traveller_amount = tourmaster_get_tour_people_amount($tour_option, $date_price, $booking_detail);

                //$ret .= tourmaster_payment_traveller_input_form($tour_option, $booking_detail, $traveller_amount, 'normal', true);

//                $ret .= '<div  class="gdlr-core-pbf-element"><div class="gdlr-core-accordion-item gdlr-core-item-pdlr gdlr-core-item-pdb  gdlr-core-accordion-style-background-title gdlr-core-left-align">';
//                $ret .= '<div id="gdlr-accordion"> ';
//                //Tab 1
//                $ret .= '<div class="gdlr-core-accordion-item-tab clearfix  gdlr-core-active">
//                        <div class="gdlr-core-accordion-item-icon gdlr-core-js gdlr-core-skin-icon  gdlr-core-skin-e-background gdlr-core-skin-border"></div>
//                        <div class="gdlr-core-accordion-item-content-wrapper">';
                $ret .= '<div class="tourmaster-traveller-info-field clearfix  tourmaster-with-info-title"><span class="tourmaster-traveller-info-title-st2"><h3 class="tourmaster-payment-traveller-info-title gdlr-core-accordion-item-title gdlr-core-js " ><i class="fa fa-suitcase" ></i>';
                $ret .= esc_html__('Main Pilgrim Details', 'tourmaster');
                $ret .= '</h3><hr/>';
//                $ret .= '</h3></span><div style="padding-top: 6px;"><span><img class="tourmaster-traveller-info-icon" src="wp-content/uploads/2019/08/Adult_A_icon.png"></img><span class="tourmaster-traveller-info-title-st"> - Adult  ,  </span></span>';
//                $ret .= '<span><img class="tourmaster-traveller-info-icon" src="wp-content/uploads/2019/08/SeniorCit_S_icon.png"></img><span class="tourmaster-traveller-info-title-st"> - Senior Citizen  ,  </span></span>';
//                $ret .= '<span><img class="tourmaster-traveller-info-icon" src="wp-content/uploads/2019/08/Child_C_icon.png"></img><span class="tourmaster-traveller-info-title-st"> - Child </span></span></div></div>';
                           // <h4 class="gdlr-core-accordion-item-title gdlr-core-js ">Accordion Item 1</h4>
//                $ret .= '<div class="gdlr-core-accordion-item-content" style="">';
//console_log($booking_detail);
                    //Travellers Details
                    $tour_adult_count = empty($booking_detail['tour-adult']) ? 0 : intval($booking_detail['tour-adult']);
                    $tour_sr_citizen_count = empty($booking_detail['tour-sr-citizen']) ? 0 : intval($booking_detail['tour-sr-citizen']);
                    $tour_male_count = empty($booking_detail['tour-male']) ? 0 : intval($booking_detail['tour-male']);
                    $tour_female_count = empty($booking_detail['tour-female']) ? 0 : intval($booking_detail['tour-female']);
                    $tour_child_count = empty($booking_detail['tour-children']) ? 0 : intval($booking_detail['tour-children']);
                    $tour_student_count = empty($booking_detail['tour-student']) ? 0 : intval($booking_detail['tour-student']);
                    $tour_infant_count = empty($booking_detail['tour-infant']) ? 0 : intval($booking_detail['tour-infant']);
                    $tour_people = empty($booking_detail['tour-people']) ? 0 : intval($booking_detail['tour-people']);


                    for ($i = 0; $i < $tour_adult_count; $i++) {
                        $ret .= tourmaster_payment_traveller_input($tour_option, $booking_detail, $i, 'Adult',"");
                    }
                    for ($i = 0; $i < $tour_sr_citizen_count; $i++) {
                        $ret .= tourmaster_payment_traveller_input($tour_option, $booking_detail, $i, 'Sr. Citizen',"");
                    }
                    for ($i = 0; $i < $tour_male_count; $i++) {
                        $ret .= tourmaster_payment_traveller_input($tour_option, $booking_detail, $i, 'Male',"");
                    }
                    for ($i = 0; $i < $tour_female_count; $i++) {
                        $ret .= tourmaster_payment_traveller_input($tour_option, $booking_detail, $i, 'Fe-male',"");
                    }
                    for ($i = 0; $i < $tour_child_count; $i++) {
                        $ret .= tourmaster_payment_traveller_input($tour_option, $booking_detail, $i, 'Child',"");
                    }
                    for ($i = 0; $i < $tour_student_count; $i++) {
                        $ret .= tourmaster_payment_traveller_input($tour_option, $booking_detail, $i, 'Student',"");
                    }
                    for ($i = 0; $i < $tour_infant_count; $i++) {
                        $ret .= tourmaster_payment_traveller_input($tour_option, $booking_detail, $i, 'Infant',"");
                    }
                    for ($i = 0; $i < $tour_people; $i++) {
                        $ret .= tourmaster_payment_traveller_input($tour_option, $booking_detail, $i, 'People',"");
                    }

                      // $ret .= '<a class="tourmaster-button tourmaster-payment-step" data-step= ' . $step . '  >' . esc_html__('Next Step', 'tourmaster') . '</a>';

//                $ret .= '</div></div></div>'; // End of Tab 1
//                $ret .= '</div></div></div>';// End of Accordion
                $ret .= '<div style="margin-bottom: 40px;"></div>';
                $ret .= tourmaster_traveller_emergency_contact_input($tour_option, $booking_detail ,true);


                $ret .= '<div class="tourmaster-tour-booking-required-error tourmaster-notification-box tourmaster-failure" ';
                $ret .= 'data-default="' . esc_html__('Please fill all required fields.', 'tourmaster') . '" ';
                $ret .= 'data-aadhaar="' . esc_html__('-- Invalid Aadhaar Number, please enter 12 digit Aadhaar Number.', 'tourmaster') . '" ';
                $ret .= 'data-phone-no="' . esc_html__('-- Invalid Phone Number, please check the Number.', 'tourmaster') . '" ';
                $ret .= 'data-firstname="' . esc_html__('-- Please enter valid Name.', 'tourmaster') . '" ';
                $ret .= 'data-dob="' . esc_html__('-- Please enter / select Date of Birth.', 'tourmaster') . '" ';
                $ret .= 'data-idtype="' . esc_html__('-- Please enter / select ID Type.', 'tourmaster') . '" ';
                $ret .= 'data-file="' . esc_html__('-- Please upload your ID.', 'tourmaster') . '" ';
                $ret .= 'data-gender="' . esc_html__('-- Please Gender.', 'tourmaster') . '" ';
                $ret .= 'data-passport="' . esc_html__('-- Invalid Passport Number, please enter valid Passport Number.', 'tourmaster') . '" ';
                $ret .= 'data-voucher="' . esc_html__('-- Invalid Concession Code, Please enter valid Concession Code.', 'tourmaster') . '" ';
                $ret .= 'data-voucher-duplicate="' . esc_html__('-- Duplicate Concession Code, please enter valid Unique Concession Code.', 'tourmaster') . '" ';
                $ret .= 'data-user-id="' . esc_html__('-- Invalid User ID Attachment.', 'tourmaster') . '" ';
                $ret .= 'data-invalid-ext="' . esc_html__('-- Invalid File Extention. Allowed formats are jpg , jpeg , png and gif .', 'tourmaster') . '" ';
                $ret .= 'data-file-exceeds-limit="' . esc_html__('-- Invalid User ID Attachment. File Exceeds Limit. Max file size allowed (2 MB)', 'tourmaster') . '" ';
                $ret .= 'data-invalidchar="' . esc_html__('-- Invalid Character entered. Please enter valid ID Number', 'tourmaster') . '" ';
                $ret .= 'data-econtact-phone-no="' . esc_html__('-- Invalid Emergency Contact Phone Number, please check the Number.', 'tourmaster') . '" ';
                $ret .= 'data-econtact-name="' . esc_html__('-- Please enter valid Emergency Contact Name.', 'tourmaster') . '" ';

                $ret .= '></div>';

                $ret .= '<div style="margin-bottom: 40px;"></div>';

                $ret .= '<a class="tourmaster-button tourmaster-payment-step tourmaster-traveldet-nextstep" data-step= ' . $step . '  >' . esc_html__('Next Step', 'tourmaster') . '</a>';

                //$ret .= '<a class="tourmaster-tour-booking-continue tourmaster-button tourmaster-payment-step" data-step=3 >' . esc_html__('Next Step', 'tourmaster') . '</a>';

            }
            $ret .= '</div>';
        }
        return $ret;
    }
}

if (!function_exists('tourmaster_payment_traveller_form')) {
    function tourmaster_payment_traveller_form($tour_option, $date_price, $booking_detail)
    {

        $ret = '';

        // get additonal traveller fields
        if (empty($tour_option['additional-traveller-fields'])) {
            $tour_option['additional-traveller-fields'] = tourmaster_get_option('general', 'additional-traveller-fields', '');
        }
        if (!empty($tour_option['additional-traveller-fields'])) {
            $tour_option['additional-traveller-fields'] = tourmaster_read_custom_fields($tour_option['additional-traveller-fields']);
        }

        // traveller detail
        if (!empty($tour_option['require-each-traveller-info']) && $tour_option['require-each-traveller-info'] == 'enable') {
            $tour_option['require-traveller-info-title'] = empty($tour_option['require-traveller-info-title']) ? 'enable' : $tour_option['require-traveller-info-title'];

            $ret .= '<div class="tourmaster-payment-traveller-info-wrap tourmaster-form-field tourmaster-with-border" >';
            // group
            if ($date_price['pricing-method'] == 'group') {
                $traveller_amount = $date_price['max-group-people'];

                if ($traveller_amount > 0) {
                    $required = true;
                    //$ret .= tourmaster_payment_traveller_input_form($tour_option, $booking_detail, $traveller_amount , $required ,  'group');

                    $ret .= '<h3 class="tourmaster-payment-traveller-info-title" ><i class="fa fa-suitcase" ></i>';
                    $ret .= esc_html__('Main Pilgrim Details', 'tourmaster');
                    $ret .= '</h3>';

                    $required = true;
                    for ($i = 0; $i < $traveller_amount; $i++) {
                        $ret .= tourmaster_payment_traveller_input($tour_option, $booking_detail, $i,"","", $required);
                        $required = false;
                    }
                }

                // normal
            } else {
                $traveller_amount = tourmaster_get_tour_people_amount($tour_option, $date_price, $booking_detail);

                //$ret .= tourmaster_payment_traveller_input_form($tour_option, $booking_detail, $traveller_amount, 'normal', true);

                $ret .= '<div class="gdlr-core-pbf-element"><div class="gdlr-core-accordion-item gdlr-core-item-pdlr gdlr-core-item-pdb  gdlr-core-accordion-style-box-icon">
		<div class="gdlr-core-accordion-item-tab clearfix  gdlr-core-active">
			<div class="gdlr-core-accordion-item-icon gdlr-core-js gdlr-core-skin-icon  gdlr-core-skin-e-background gdlr-core-skin-border"></div>
			<div class="gdlr-core-accordion-item-content-wrapper">
				<h4 class="gdlr-core-accordion-item-title gdlr-core-js ">Accordion Item 1</h4>
				<div class="gdlr-core-accordion-item-content" style="">';

                $ret .= '<h3 class="tourmaster-payment-traveller-info-title" ><i class="fa fa-suitcase" ></i>';
                $ret .= esc_html__('Main Pilgrim Details', 'tourmaster');
                $ret .= '</h3>';


                $tour_adult_count = empty($booking_detail['tour-adult']) ? 0 : intval($booking_detail['tour-adult']);
                $tour_sr_citizen_count = empty($booking_detail['tour-sr-citizen']) ? 0 : intval($booking_detail['tour-sr-citizen']);
                $tour_male_count = empty($booking_detail['tour-male']) ? 0 : intval($booking_detail['tour-male']);
                $tour_female_count = empty($booking_detail['tour-female']) ? 0 : intval($booking_detail['tour-female']);
                $tour_child_count = empty($booking_detail['tour-children']) ? 0 : intval($booking_detail['tour-children']);
                $tour_student_count = empty($booking_detail['tour-student']) ? 0 : intval($booking_detail['tour-student']);
                $tour_infant_count = empty($booking_detail['tour-infant']) ? 0 : intval($booking_detail['tour-infant']);


                for ($i = 0; $i < $tour_adult_count; $i++) {
                    $ret .= tourmaster_payment_traveller_input($tour_option, $booking_detail, $i, 'Adult',"wp-content/uploads/2019/08/SeniorCit_S_icon.png");
                }
                for ($i = 0; $i < $tour_sr_citizen_count; $i++) {
                    $ret .= tourmaster_payment_traveller_input($tour_option, $booking_detail, $i, 'Sr. Citizen',"wp-content/uploads/2019/08/SeniorCit_S_icon.png");
                }
                for ($i = 0; $i < $tour_male_count; $i++) {
                    $ret .= tourmaster_payment_traveller_input($tour_option, $booking_detail, $i, 'Male',"");
                }
                for ($i = 0; $i < $tour_female_count; $i++) {
                    $ret .= tourmaster_payment_traveller_input($tour_option, $booking_detail, $i, 'Fe-male',"");
                }
                for ($i = 0; $i < $tour_child_count; $i++) {
                    $ret .= tourmaster_payment_traveller_input($tour_option, $booking_detail, $i, 'Child',"wp-content/uploads/2019/08/SeniorCit_S_icon.png");
                }
                for ($i = 0; $i < $tour_student_count; $i++) {
                    $ret .= tourmaster_payment_traveller_input($tour_option, $booking_detail, $i, 'Student',"");
                }
                for ($i = 0; $i < $tour_infant_count; $i++) {
                    $ret .= tourmaster_payment_traveller_input($tour_option, $booking_detail, $i, 'Infant',"");
                }


            }
            $ret .= '</div>';
            $ret .= '</div></div></div></div>';
        }

        return $ret;

    } // tourmaster_payment_traveller_form
}

if (!function_exists('tourmaster_payment_traveller_detail')) {
    function tourmaster_payment_traveller_detail($tour_option, $booking_detail)
    {
        $tour_option['require-traveller-info-title'] = empty($tour_option['require-traveller-info-title']) ? 'enable' : $tour_option['require-traveller-info-title'];
//        if ($tour_option['require-traveller-info-title'] == 'enable') {
//            $title_types = tourmaster_payment_traveller_title();
//        }

        // get additonal traveller fields
        if (empty($tour_option['additional-traveller-fields'])) {
            $tour_option['additional-traveller-fields'] = tourmaster_get_option('general', 'additional-traveller-fields', '');
        }
        if (!empty($tour_option['additional-traveller-fields'])) {
            $tour_option['additional-traveller-fields'] = tourmaster_read_custom_fields($tour_option['additional-traveller-fields']);
        }

        $ret = '';

        if (!empty($tour_option['require-each-traveller-info']) && $tour_option['require-each-traveller-info'] == 'enable' && !empty($booking_detail['traveller_first_name'])) {
            $ret = '<div class="tourmaster-payment-traveller-detail" >';
            $ret .= '<h3 class="tourmaster-payment-detail-title" ><i class="fa fa-file-text-o" ></i>';
            $ret .= esc_html__('Pilgrim Participants', 'tourmaster');
            $ret .= '</h3>';
            for ($i = 0; $i < sizeof($booking_detail['traveller_first_name']); $i++) {
                if (!empty($booking_detail['traveller_first_name'][$i]) || !empty($booking_detail['traveller_last_name'][$i])) {
                    $ret .= '<div class="tourmaster-payment-detail clearfix" >';
                    $ret .= '<span class="tourmaster-head" >' . esc_html__('Pilgrim', 'tourmaster') . ' ' . ($i + 1) . ' :</span>';
                    $ret .= '<span class="tourmaster-tail" >';
//                    if ($tour_option['require-traveller-info-title'] == 'enable') {
//                        if (!empty($title_types[$booking_detail['traveller_title'][$i]])) {
//                            $ret .= $title_types[$booking_detail['traveller_title'][$i]] . ' ';
//                        }
//                    }
                    $ret .= ($booking_detail['traveller_first_name'][$i] . ' ' . $booking_detail['traveller_last_name'][$i]);
                    if (!empty($booking_detail['traveller_passport'][$i])) {
                        $ret .= '<br>' . esc_html__('Passport ID :', 'tourmaster') . ' ' . $booking_detail['traveller_passport'][$i];
                    }

                    if (!empty($tour_option['additional-traveller-fields'])) {
                        foreach ($tour_option['additional-traveller-fields'] as $field) {
                            if (!empty($booking_detail['traveller_' . $field['slug']][$i])) {
                                $ret .= '<br>' . $field['title'] . ' ' . $booking_detail['traveller_' . $field['slug']][$i];
                            }
                        }
                    }

                    $ret .= '</span>';
                    $ret .= '</div>';
                }
            }
            $ret .= '</div>'; // tourmaster-payment-traveller- detail-wrap
        }

        return $ret;
    } // tourmaster_payment_traveller_detail
}
// contact form
if (!function_exists('tourmaster_get_payment_contact_form_fields')) {
    function tourmaster_get_payment_contact_form_fields()
    {

        $custom_fields = tourmaster_get_option('general', 'contact-detail-fields', '');
        if (empty($custom_fields)) {
            return array(
                'first_name' => array(
                    'title' => esc_html__('Name', 'tourmaster'),
                    'type' => 'text',
                    'required' => true
                ),
//                'last_name' => array(
//                    'title' => esc_html__('Last Name', 'tourmaster'),
//                    'type' => 'text',
//                    'required' => false
//                ),
                'email' => array(
                    'title' => esc_html__('Email', 'tourmaster'),
                    'type' => 'email',
                    'name' => 'email',
                    'pattern' => '/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/',
//                    'pattern' => '/[^\s]*@[a-z0-9.-]*/i',
                    'required' => true,
                    'default' => tourmaster_get_option('general', 'user-default-email', '')
                ),
                'phone' => array(
                    'title' => esc_html__('Phone', 'tourmaster'),
                    'type' => 'text',
                    'name' => 'phone',
                    'pattern' => '((\+*)((0[ -]+)*|(91 )*)(\d{12}+|\d{10}+))|\d{5}([- ]*)\d{6}',
                    'required' => true,
                    'default' => tourmaster_get_option('general', 'user-default-phone', '')
                ),
                'country' => array(
                    'title' => esc_html__('Country', 'tourmaster'),
                    'type' => 'combobox',
                    'required' => true,
                    'options' => tourmaster_get_country_list(),
                    'default' => tourmaster_get_option('general', 'user-default-country', '')
                ),
                'contact_address' => array(
                    'title' => esc_html__('Address', 'tourmaster'),
                    'type' => 'textarea'
                ),
            );
        } else {
            return tourmaster_read_custom_fields($custom_fields);
        }


    } // tourmaster_get_payment_contact_form_fields
}

if (!function_exists('tourmaster_set_contact_form_data')) {
    function tourmaster_set_contact_form_data($content, $data, $type = '')
    {
        foreach ($data as $slug => $value) {
            $content = str_replace('{' . $slug . '}', $value, $content);
        }

        return $content;
    }
}

if (!function_exists('tourmaster_payment_contact_form')) {
    function tourmaster_payment_contact_form($booking_detail, $step)
    {

        // form field
        $contact_fields = tourmaster_get_payment_contact_form_fields();
        $ret = '<div class="gdlr-core-pbf-element"><div class="gdlr-core-accordion-item gdlr-core-item-pdlr gdlr-core-item-pdb  gdlr-core-accordion-style-box-icon">
		<div class="gdlr-core-accordion-item-tab clearfix  gdlr-core-active">
			<div class="gdlr-core-accordion-item-icon gdlr-core-js gdlr-core-skin-icon  gdlr-core-skin-e-background gdlr-core-skin-border"></div>
			<div class="gdlr-core-accordion-item-content-wrapper">
				<h4 class="gdlr-core-accordion-item-title gdlr-core-js tourmaster-payment-contact-title"><i class="fa fa-file-text-o" ></i>';
        $ret .= esc_html__('Main Pilgrim Details', 'tourmaster');
        $ret .= '</h4>
				<div class="gdlr-core-accordion-item-content" style="">';
        $ret .= '<div class="tourmaster-payment-contact-wrap tourmaster-form-field tourmaster-with-border" >';
//        $ret .= '<h3 class="tourmaster-payment-contact-title" ><i class="fa fa-file-text-o" ></i>';
//        $ret .= esc_html__('Contact Details', 'tourmaster');
        //$ret .= '</h3>';
        foreach ($contact_fields as $field_slug => $contact_field) {
            $contact_field['echo'] = false;
            $contact_field['slug'] = $field_slug;

            $value = empty($booking_detail[$field_slug]) ? '' : $booking_detail[$field_slug];

            $ret .= tourmaster_get_form_field($contact_field, 'contact', $value);
        }
        $ret .= '</div>';
        $ret .= '</div></div></div></div>';


        // billing address

        $ret = '<div class="gdlr-core-pbf-element"><div class="gdlr-core-accordion-item gdlr-core-item-pdlr gdlr-core-item-pdb  gdlr-core-accordion-style-box-icon">
		<div class="gdlr-core-accordion-item-tab clearfix  gdlr-core-active">
			<div class="gdlr-core-accordion-item-icon gdlr-core-js gdlr-core-skin-icon  gdlr-core-skin-e-background gdlr-core-skin-border"></div>
			<div class="gdlr-core-accordion-item-content-wrapper">
				<h4 class="gdlr-core-accordion-item-title gdlr-core-js ">Accordion Item 1</h4>
				<div class="gdlr-core-accordion-item-content" style="">';
        $ret .= '<div class="tourmaster-payment-billing-wrap tourmaster-form-field tourmaster-with-border" >';
        $ret .= '<h3 class="tourmaster-payment-billing-title" ><i class="fa fa-file-text-o" ></i>';
        $ret .= esc_html__('Payee Details', 'tourmaster');
        $ret .= '</h3>';

        $ret .= '<div class="tourmaster-payment-billing-copy-wrap" >';
        $ret .= '<label><input type="checkbox" class="tourmaster-payment-billing-copy" id="tourmaster-payment-billing-copy" ></i>';
        $ret .= '<span class="tourmaster-payment-billing-copy-text" >' . esc_html__('The same as contact details', 'tourmaster') . '</span>';
        $ret .= '</label></div>'; // tourmaster-payment-billing-copy-wrap

        foreach ($contact_fields as $field_slug => $contact_field) {

            $contact_field['echo'] = false;
            $contact_field['slug'] = 'billing_' . $field_slug;
            $contact_field['data'] = array(
                'slug' => 'contact-detail',
                'value' => $field_slug
            );

            $value = empty($booking_detail['billing_' . $field_slug]) ? '' : $booking_detail['billing_' . $field_slug];

            $ret .= tourmaster_get_form_field($contact_field, 'billing', $value);
        }
        $ret .= '</div>'; // tourmaster-payment-billing-wrap
        $ret .= '</div></div></div></div>';

        // additional notes
        $additional_notes = empty($booking_detail['additional_notes']) ? '' : $booking_detail['additional_notes'];
        $ret .= '<div class="tourmaster-payment-additional-note-wrap tourmaster-form-field tourmaster-with-border" >';
        $ret .= '<h3 class="tourmaster-payment-additional-note-title" ><i class="fa fa-file-text-o" ></i>';
        $ret .= esc_html__('Notes', 'tourmaster');
        $ret .= '</h3>';
        $ret .= '<div class="tourmaster-additional-note-field clearfix">';
        $ret .= '<span class="tourmaster-head">' . esc_html__('Additional Notes', 'tourmaster') . '</span>';
        $ret .= '<span class="tourmaster-tail clearfix">';
        $ret .= '<textarea name="additional_notes" >' . esc_textarea($additional_notes) . '</textarea>';
        $ret .= '</span>';
        $ret .= '</div>'; // additional-note-field
        $ret .= '</div>'; // tourmasster-payment-additional-note-wrap

        $ret .= '<div class="tourmaster-tour-booking-required-error tourmaster-notification-box tourmaster-failure" ';
        $ret .= 'data-default="' . esc_html__('Please fill all required fields.', 'tourmaster') . '" ';
        $ret .= 'data-email="' . esc_html__('Invalid E-Mail, please try again.', 'tourmaster') . '" ';
        $ret .= 'data-phone="' . esc_html__('Invalid phone number, please try again.', 'tourmaster') . '" ';
        $ret .= '></div>';
        $ret .= '<a class="tourmaster-tour-booking-continue tourmaster-button tourmaster-payment-step" data-step= ' . $step . '  >' . esc_html__('Next Step', 'tourmaster') . '</a>';

        return $ret;

    } // tourmaster_payment_contact_form
}

if (!function_exists('tourmaster_payment_contact_detail')) {
    function tourmaster_payment_contact_detail($booking_detail)
    {

        // form field
        $contact_fields = tourmaster_get_payment_contact_form_fields();

        // contact detail
        $ret = '<div class="tourmaster-payment-contact-detail-wrap clearfix tourmaster-item-rvpdlr" >';
        $ret .= '<div class="tourmaster-payment-detail-wrap tourmaster-payment-contact-detail tourmaster-item-pdlr" >';
        $ret .= '<h3 class="tourmaster-payment-detail-title" ><i class="fa fa-file-text-o" ></i>';
        $ret .= esc_html__('Main Pilgrim Details', 'tourmaster');
        $ret .= '</h3>';
        foreach ($contact_fields as $slug => $contact_field) {
            $ret .= '<div class="tourmaster-payment-detail" >';
            $ret .= '<span class="tourmaster-head" >' . $contact_field['title'] . ' :</span>';
            $ret .= '<span class="tourmaster-tail" >' . (empty($booking_detail[$slug]) ? '-' : $booking_detail[$slug]) . '</span>';
            $ret .= '</div>';
        }
        $ret .= '</div>'; // tourmaster-payment-detail-wrap

        // billing detail
        $ret .= '<div class="tourmaster-payment-detail-wrap tourmaster-payment-billing-detail tourmaster-item-pdlr" >';
        $ret .= '<h3 class="tourmaster-payment-detail-title" ><i class="fa fa-file-text-o" ></i>';
        $ret .= esc_html__('Payee Details', 'tourmaster');
        $ret .= '</h3>';
        foreach ($contact_fields as $slug => $contact_field) {
            $ret .= '<div class="tourmaster-payment-detail" >';
            $ret .= '<span class="tourmaster-head" >' . $contact_field['title'] . ' :</span>';
            $ret .= '<span class="tourmaster-tail" >' . (empty($booking_detail['billing_' . $slug]) ? '-' : $booking_detail['billing_' . $slug]) . '</span>';
            $ret .= '</div>';
        }
        $ret .= '</div>'; // tourmaster-payment-detail-wrap
        $ret .= '</div>'; // tourmaster-payment-contact-detail-wrap

        // additional note
        if (!empty($booking_detail['additional_notes'])) {
            $ret .= '<div class="tourmaster-payment-detail-notes-wrap" >';
            $ret .= '<h3 class="tourmaster-payment-detail-title" ><i class="fa fa-file-text-o" ></i>';
            $ret .= esc_html__('Notes', 'tourmaster');
            $ret .= '</h3>';
            $ret .= '<div class="tourmaster-payment-detail" >';
            $ret .= '<span class="tourmaster-head" >' . esc_html__('Additional Notes', 'tourmaster') . ' :</span>';
            $ret .= '<span class="tourmaster-tail" >' . esc_html($booking_detail['additional_notes']) . '</span>';
            $ret .= '</div>'; // tourmaster-payment-detail
            $ret .= '</div>'; // tourmaster-payment-detail-wrap
            $ret .= '<div class="clear" ></div>';
        }

        return $ret;

    } // tourmaster_payment_contact_detail
}

if (!function_exists('tourmaster_payment_seat_select_form')) {
    function tourmaster_payment_seat_select_form($booking_detail)
    {

        $ret = '<div class="tourmaster-payment-billing-copy-wrap" ><label>';
        $ret .= '<input type="checkbox" class="tourmaster-payment-billing-copy" id="tourmaster-payment-billing-copy" ></i>';
        $ret .= '<span class="tourmaster-payment-billing-copy-text" >' . esc_html__('Skip Bus Seat Selection', 'tourmaster') . '</span>';
        $ret .= '</label></div>'; // tourmaster-payment-billing-copy-wrap

        return $ret;

    } // tourmaster_payment_contact_form
}

//Instamojo Payment
if (!function_exists('tourmaster_instamojo_payment_method')) {
    function tourmaster_instamojo_payment_method($booking_detail , $amount)
    {


        $ret = array();

//        if (!empty($_POST['booking_detail'])) {
            //$booking_detail = tourmaster_process_post_data($_POST['booking_detail']);

            if (!empty($booking_detail['tour-id']) && !empty($booking_detail['tour-date'])) {
                $tour_option = tourmaster_get_post_meta($booking_detail['tour-id'], 'tourmaster-tour-option');
                $date_price = tourmaster_get_tour_date_price($tour_option, $booking_detail['tour-id'], $booking_detail['tour-date']);
                $date_price = tourmaster_get_tour_date_price_package($date_price, $booking_detail);

                $booking_detail['payment-method'] =  "instamojo";

                $tour_price = tourmaster_get_tour_price($tour_option, $date_price, $booking_detail);

                if ($date_price['pricing-method'] == 'group') {
                    $traveller_amount = 1;
                } else {
                    $traveller_amount = tourmaster_get_tour_people_amount($tour_option, $date_price, $booking_detail, 'all');
                }

                $package_group_slug = empty($date_price['group-slug']) ? '' : $date_price['group-slug'];
                $tid = tourmaster_insert_booking_data($booking_detail, $tour_price, $traveller_amount, $package_group_slug);

                if ($tour_price['total-price'] <= 0) {
                    $ret['content'] = tourmaster_payment_complete();
                    $ret['cookie'] = $booking_detail;

                } else {
                    $booking_detail['tid'] = $tid;

//                    $ret['content'] = apply_filters('iqhired_' . $_POST['type'] . '_payment_form', '', $tid);
//                    $ret['cookie'] = $booking_detail;
//
//                    // recalculate the fee
//                    $ret['sidebar'] = tourmaster_get_booking_bar_summary($tour_option, $date_price, $booking_detail,false,5);
                }
            }


        $payment_method = tourmaster_get_option('payment', 'payment-method', array('booking','instamojo'));
        $instamojo_enable = in_array('instamojo', $payment_method);

        $extra_class = '';
        if ($instamojo_enable) {
            $extra_class .= ' tourmaster-both-online-payment';
        }
        $ret = '<div class="tourmaster-payment-method-wrap ' . esc_attr($extra_class) . '" >';
        

        $our_term = tourmaster_get_option('payment', 'term-of-service-page', '#');
        $our_term = is_numeric($our_term) ? get_permalink($our_term) : $our_term;
        $privacy = tourmaster_get_option('payment', 'privacy-statement-page', '#');
        $privacy = is_numeric($privacy) ? get_permalink($privacy) : $privacy;

        $ret .= '<div class="tourmaster-payment-terms" name="tourmaster-payment-terms" id="tourmaster-payment-terms" >';
        $ret .= '<span class="headline">We request the pilgrims to Please read Terms & Conditions carefully to avoid any unpalatable experiences.</span><br/><br/>';
        $ret .= '* I/We agree, <br/>';

        $ret .= '<br><input type="checkbox" class="term-and-service" name="term-and-service" />';
        $ret .= sprintf(wp_kses(
            __('<a href="/terms-and-conditions/" target="popup" class="tands_btn" onclick="window.open(\'/terms-and-conditions/\',\'popup\',\'width=600,height=600,scrollbars=no,resizable=no\'); return false;">Terms and Conditions</a><br/>', 'tourmaster'),
            array('a' => array('href' => array(), 'target' => array(), 'class' => array(), 'onclick' => array()))
        ), $our_term, $privacy);

        $ret .= '<br><input type="checkbox" class="term-and-service" name="term-and-service" />';
        $ret .= sprintf(wp_kses(
            __('<a href="/other-terms-and-conditions/" target="_blank" class="tands_btn" onclick="window.open(\'/other-terms-and-conditions/\',\'popup\',\'width=600,height=600,scrollbars=no,resizable=no\'); return false;">Other Terms and Conditions</a><br/>', 'tourmaster'),
            array('a' => array('href' => array(), 'target' => array(), 'class' => array(), 'onclick' => array()))
        ), $our_term, $privacy);

        $ret .= '<br><input type="checkbox" class="term-and-service" name="term-and-service" />';
        $ret .= sprintf(wp_kses(
            __('<a href="/covid-related-terms-conditions/" target="_blank" class="tands_btn" onclick="window.open(\'/covid-related-terms-conditions/\',\'popup\',\'width=600,height=600,scrollbars=no,resizable=no\'); return false;">COVID Related Terms and Conditions</a><br/>', 'tourmaster'),
            array('a' => array('href' => array(), 'target' => array(), 'class' => array(), 'onclick' => array()))
        ), $our_term, $privacy);

        $ret .= '<br><input type="checkbox" class="term-and-service" name="term-and-service" />';
        $ret .= sprintf(wp_kses(
            __('<a href="/declaration/" target="_blank" class="tands_btn" onclick="window.open(\'/declaration/\',\'popup\',\'width=600,height=600,scrollbars=no,resizable=no\'); return false;">Declaration</a><br/>', 'tourmaster'),
            array('a' => array('href' => array(), 'target' => array(), 'class' => array(), 'onclick' => array()))
        ), $our_term, $privacy);

        $ret .= '<br><input type="checkbox" class="term-and-service" name="term-and-service" />';
        $ret .= sprintf(wp_kses(
            __('<a href="/rules-and-regulations/" target="_blank" class="tands_btn" onclick="window.open(\'/rules-and-regulations/\',\'popup\',\'width=600,height=600,scrollbars=no,resizable=no\'); return false;">Rules and Regulations</a>.<br>', 'tourmaster'),
            array('a' => array('href' => array(), 'target' => array(), 'class' => array(), 'onclick' => array()))
        ), $our_term, $privacy);

        $ret .= '<br><input type="checkbox" class="term-and-service" name="term-and-service" />';
        $ret .= sprintf(wp_kses(
            __('<a href="/tour-extension-policy/" target="_blank" class="tands_btn" onclick="window.open(\'/tour-extension-policy/\',\'popup\',\'width=600,height=600,scrollbars=no,resizable=no\'); return false;">Tour Extension Policy</a>.<br>', 'tourmaster'),
            array('a' => array('href' => array(), 'target' => array(), 'class' => array(), 'onclick' => array()))
        ), $our_term, $privacy);

        $ret .= '<br><input type="checkbox" class="term-and-service" name="term-and-service" />';
        $ret .= sprintf(wp_kses(
            __('<a href="/cancellation-and-refund-policy/" target="_blank" class="tands_btn" onclick="window.open(\'/cancellation-and-refund-policy/\',\'popup\',\'width=600,height=600,scrollbars=no,resizable=no\'); return false;">Cancellation and Refund Policy</a>.<br>', 'tourmaster'),
            array('a' => array('href' => array(), 'target' => array(), 'class' => array(), 'onclick' => array()))
        ), $our_term, $privacy);

        $ret .= '<div class="tourmaster-tour-booking-required-error tourmaster-notification-box tourmaster-failure" ';
        $ret .= 'data-default="' . esc_attr(esc_html__('Please click the link and agree to all the terms and conditions before proceeding to the next step.', 'tourmaster')) . '" ';
        $ret .= '></div>';
        $ret .= '</div>'; // tourmaster-payment-terms
//        console_log("book" . $booking_detail);
        
        $ret .= '<h3 class="tourmaster-payment-method-title" >' . esc_html__('Please confirm the payment details', 'tourmaster') . '</h3>';

        if ($instamojo_enable){
            $ret .= '<div class="tourmaster-payment-gateway clearfix" >';
            $name = $booking_detail["billing_first_name"];
            $email = $booking_detail["billing_email"];
            $number = $booking_detail["billing_phone"];
            $booking_detail["pilgrim_amount"] = $amount;
            $booking_detail["amount"] = $amount;

            $action = './instamojo/Instamojo_Payment.php';
            $ret .= '<div class="tourmaster-online-payment-method" id="takeme-to-payment-gateway">  <form class="w3-container" method=\'POST\' action="' . esc_attr($action). '" ';
//            Username: <input type="text" name="Username"><br>
//            Password: <input type="password" name="Password"><br>

            $ret .= '><div class="form-group">
              <label class="control-label col-sm-3" for="name">Name : </label>
              <div class="col-sm-9">
                <input type="text" name="name" class="form-control" value=' . esc_attr($name) . ' readonly=\"readonly\" />
              </div>
            </div> ' ;
            $ret .= '<div class="form-group">
              <label class="control-label col-sm-3" for="number">Number : </label>
              <div class="col-sm-9">
                <input type="text" name="number"  class="form-control" value= ' . esc_attr($number) . ' readonly=\"readonly\" />
              </div>
            </div> ' ;
            $ret .= '<div class="form-group">
              <label class="control-label col-sm-3" for="email">Email : </label>
              <div class="col-sm-9">
                <input type="text" name="email" class="form-control" value= ' . esc_attr($email) . ' readonly=\"readonly\" />
              </div>
            </div> ' ;
            $ret .= '<div class="form-group">
              <label class="control-label col-sm-3" for="amount">Amount to Pay : </label>
              <div class="col-sm-9">
                <input type="text" name="amount" class="form-control" value= ' . esc_attr($amount) . ' readonly=\"readonly\" />
              </div>
            </div> ' ;
//            $ret .= '<p><label class="w3-text-black"><b>Name : </b></label><input type="text" name="name" class="w3-text-black" value=' . esc_attr($name) . ' disabled=\"disabled\" />';
//            $ret .= '<p><label class="w3-text-black"><b>Number : </b></label><input type="text" name="number"  class="w3-text-black" value= ' . esc_attr($number) . ' disabled=\"disabled\"/>';
//            $ret .= '<p><label class="w3-text-black"><b>email : </b></label><input type="text" name="email" class="w3-text-black" value= ' . esc_attr($email) . ' disabled=\"disabled\" />';
//            $ret .= '<p><label class="w3-text-black"><b>amount : </b></label><input type="text" name="amount" class="w3-text-black" value= ' . esc_attr($amount) . ' disabled=\"disabled\" />';
            $ret .= '<input type="hidden" name="tid"  value= ' . esc_attr($booking_detail["tid"]) . ' disabled="disabled"></input>';
//            $ret .= '<input type="hidden" name="tid" >' . esc_attr($booking_detail['tour-id']) . '</input>';
//            $ret .= '<p><input name=\'submit\' class="w3-btn w3-blue tourmaster-payment-step" value=\'Make Payment!\' data-step="6"  data-action=' . esc_attr($action) . ' data-name="payment-method" data-value="instamojo" data-price=' . esc_attr($amount) . ' ';
//            $ret .= '<br><p class="payment-final-step-p"><input name="submit" id="submit-payment" type="Submit" class="tourmaster-button gdlr-core-accordion-back-button" value="Make Payment" disabled="disabled" /><span><a id="go-back-button" class="tourmaster-button gdlr-core-accordion-back-button" data-step="3">Go Back</a></span></p>' ;
            $ret .= '<div id="payment-final-step-p-makepayment"><br><p class="payment-final-step-p"><input name="submit" id="submit-payment" type="Submit" class="tourmaster-button gdlr-core-accordion-back-button" value="Make Payment" disabled="disabled" /><span><input type="button" id="go-back-button" class="tourmaster-button gdlr-core-accordion-back-button" data-step="3" value="Go Back" /></span></p>' ;

            $ret .= '<br><p id="t-and-c-warning" ><label class="w3-text-red"><b>Please check all the terms and conditions before you could make payment.</b></label></p>';
//            $ret .= '<a href="https://test.instamojo.com/@ashams001/" rel="im-checkout" data-text="Pay" data-css-style="color:#ffffff; background:#004dcf; width:180px; border-radius:30px"   data-layout="vertical"></a>
//<script src="https://js.instamojo.com/v1/button.js"></script>';

//            if (!empty($payment_attr['type'])) {
//                    $ret .= ' data-action-type="' . esc_attr($payment_attr['type']) . '" ';
//                }
            $ret .= '</div>';
//            $ret .= '</p>';
            $ret .= '</form></div>';

            $ret .= '</div>'; // tourmaster-payment-gateway

        }

        if (in_array('booking', $payment_method)) {

            if (sizeof($payment_method) > 1) {
                $ret .= '<div class="tourmaster-payment-method-or" id="tourmaster-payment-method-or" >';
                $ret .= '<span class="tourmaster-left" ></span>';
                $ret .= '<span class="tourmaster-middle" >' . esc_html__('OR', 'tourmaster') . '</span>';
                $ret .= '<span class="tourmaster-right" ></span>';
                $ret .= '</div>'; // tourmaster-payment-method-or
            }

            $ret .= '<div class="tourmaster-payment-method-booking" >';
            if (is_user_logged_in()) {
                $ret .= '<a class="tourmaster-button tourmaster-payment-method-booking-button tourmaster-payment-step" data-name="payment-method" data-value="booking" data-step="5" >';
                $ret .= esc_html__('Book and pay later', 'tourmaster');
                $ret .= '</a>';
            } else {
                $book_by_email = tourmaster_get_option('general', 'enable-booking-via-email', 'enable');

                if ($book_by_email == 'enable') {
                    $ret .= '<a class="tourmaster-button tourmaster-payment-method-booking-button tourmaster-payment-step" data-name="payment-method" data-value="booking" data-step="5" >';
                    $ret .= esc_html__('Book now via email', 'tourmaster');
                    $ret .= '</a>';
                } else {
                    $ret .= '<a class="tourmaster-button tourmaster-payment-method-booking-button" data-tmlb="book-and-pay-later-login" >';
                    $ret .= esc_html__('Book and pay later', 'tourmaster');
                    $ret .= '</a>';
                    $ret .= tourmaster_lightbox_content(array(
                        'id' => 'book-and-pay-later-login',
                        'title' => esc_html__('To book and pay later requires an account', 'tourmaster'),
                        'content' => tourmaster_get_login_form2(false, array(
                            'redirect' => 'payment'
                        ))
                    ));
                }
            }
            $ret .= '</div>'; // tourmaster-payment-method-booking
        }

//        if (in_array('booking', $payment_method)) {
//
//            if (sizeof($payment_method) > 1) {
//                $ret .= '<div class="tourmaster-payment-method-or" id="tourmaster-payment-method-or" >';
//                $ret .= '<span class="tourmaster-left" ></span>';
//                $ret .= '<span class="tourmaster-middle" >' . esc_html__('OR', 'tourmaster') . '</span>';
//                $ret .= '<span class="tourmaster-right" ></span>';
//                $ret .= '</div>'; // tourmaster-payment-method-or
//            }
//
//            $ret .= '<div class="tourmaster-payment-method-booking" >';
//            if (is_user_logged_in()) {
//                $ret .= '<a class="tourmaster-button tourmaster-payment-method-booking-button tourmaster-payment-step" data-name="payment-method" data-value="booking" data-step="6" >';
//                $ret .= esc_html__('Book and pay later', 'tourmaster');
//                $ret .= '</a>';
//            }
//            $ret .= '</div>'; // tourmaster-payment-method-booking
//        }

        $ret .= '</div>'; // tourmaster-payment-method-wrap
        $_COOKIE['tourmaster-booking-detail'] = $booking_detail;

        return $ret;
    }
}

if (!function_exists('tourmaster_payment_method')) {
    function tourmaster_payment_method()
    {

        $payment_method = tourmaster_get_option('payment', 'payment-method', array('booking','instamojo'));
        $paypal_enable = in_array('paypal', $payment_method);
        $credit_card_enable = in_array('credit-card', $payment_method);
        $hipayprofessional_enable = in_array('hipayprofessional', $payment_method);
        $instamojo_enable = in_array('instamojo', $payment_method);

        $extra_class = '';
        if ($instamojo_enable) {
            $extra_class .= ' tourmaster-both-online-payment';
        }
//        if ($paypal_enable && $credit_card_enable) {
//            $extra_class .= ' tourmaster-both-online-payment';
//        } elseif ($paypal_enable && $hipayprofessional_enable) {
//            $extra_class .= ' tourmaster-both-online-payment';
//        } elseif ($credit_card_enable && $hipayprofessional_enable) {
//            $extra_class .= ' tourmaster-both-online-payment';
//        } elseif (!$paypal_enable && !$credit_card_enable && !$hipayprofessional_enable) {
//            $extra_class .= ' tourmaster-none-online-payment';
//        }
        $ret = '<div class="tourmaster-payment-method-wrap ' . esc_attr($extra_class) . '" >';
        $ret .= '<h3 class="tourmaster-payment-method-title" >' . esc_html__('Please select a payment method', 'tourmaster') . '</h3>';

        if (in_array('booking', $payment_method)) {
            if (is_user_logged_in()) {
                $ret .= '<div class="tourmaster-payment-method-description" >';
                $ret .= esc_html__('* If you wish to do a bank transfer, please select "Book and pay later" button.', 'tourmaster');
                $ret .= '<br>' . esc_html__('You will have an option to submit payment receipt on your dashboard page.', 'tourmaster');
                $ret .= '</div>';
            }
        }

        $our_term = tourmaster_get_option('payment', 'term-of-service-page', '#');
        $our_term = is_numeric($our_term) ? get_permalink($our_term) : $our_term;
        $privacy = tourmaster_get_option('payment', 'privacy-statement-page', '#');
        $privacy = is_numeric($privacy) ? get_permalink($privacy) : $privacy;
        $ret .= '<div class="tourmaster-payment-terms" name="tourmaster-payment-terms" id="tourmaster-payment-terms" >';
        $ret .= '<input type="checkbox" name="term-and-service" />';
        $ret .= sprintf(wp_kses(
            __('* I agree with <a href="%s" target="_blank" class="tands_btn">Terms of Service and Privacy Statement</a>.', 'tourmaster'),
            array('a' => array('href' => array(), 'target' => array(), 'class' => array()))
        ), $our_term, $privacy);
        $ret .= '<div class="tourmaster-tour-booking-required-error tourmaster-notification-box tourmaster-failure" ';
        $ret .= 'data-default="' . esc_attr(esc_html__('Please click the link and agree to all the terms and conditions before proceeding to the next step.', 'tourmaster')) . '" ';
        $ret .= '></div>';
        $ret .= '</div>'; // tourmaster-payment-terms

        if ($instamojo_enable){
            $ret .= '<div class="tourmaster-payment-gateway clearfix" >';
            $payment_attr = apply_filters('iqhired_plugin_payment_attribute', array());
            $ret .= '<div class="tourmaster-online-payment-method tourmaster-payment-credit-card" >';
            $ret .= '<img src="' . esc_attr(TOURMASTER_URL) . '/images/credit-card.png" alt="credit-card" width="170" height="76" ';
            if (!empty($payment_attr['method']) && $payment_attr['method'] == 'ajax') {
                $ret .= 'data-method="ajax" data-action="tourmaster_payment_selected" data-ajax="' . esc_url(TOURMASTER_AJAX_URL) . '" ';
                if (!empty($payment_attr['type'])) {
                    $ret .= 'data-action-type="' . esc_attr($payment_attr['type']) . '" ';
                }
            }
            $ret .= ' />';

            // service fee
            $credit_card_service_fee = tourmaster_get_option('payment', 'credit-card-service-fee', '');
            if (!empty($credit_card_service_fee)) {
                $ret .= '<div class="tourmaster-payment-credit-card-service-fee-text" >';
                $ret .= sprintf(esc_html__('Additional %s%% is charged for payment via credit card.', 'tourmaster'), $credit_card_service_fee);
                $ret .= '</div>';
            }

            // image display
            $credit_card_types = tourmaster_get_option('payment', 'accepted-credit-card-type', array());
            if (!empty($credit_card_types)) {
                $ret .= '<div class="tourmaster-payment-credit-card-type" >';
                foreach ($credit_card_types as $type) {
                    $ret .= '<img src="' . esc_attr(TOURMASTER_URL) . '/images/' . esc_attr($type) . '.png" alt="' . esc_attr($type) . '" />';

                }
                $ret .= '</div>';
            }

            $ret .= '</div>';
            $ret .= '</div>'; // tourmaster-payment-gateway
        }else if ($paypal_enable || $credit_card_enable || $hipayprofessional_enable) {
            $ret .= '<div class="tourmaster-payment-gateway clearfix" >';
            if ($paypal_enable) {
                $paypal_button_atts = apply_filters('tourmaster_paypal_button_atts', array());
                $ret .= '<div class="tourmaster-online-payment-method tourmaster-payment-paypal" >';
                $ret .= '<img src="' . esc_attr(TOURMASTER_URL) . '/images/paypal.png" alt="paypal" width="170" height="76" ';
                if (!empty($paypal_button_atts['method']) && $paypal_button_atts['method'] == 'ajax') {
                    $ret .= 'data-method="ajax" data-action="tourmaster_payment_selected" data-ajax="' . esc_url(TOURMASTER_AJAX_URL) . '" ';
                    if (!empty($paypal_button_atts['type'])) {
                        $ret .= 'data-action-type="' . esc_attr($paypal_button_atts['type']) . '" ';
                    }
                }
                $ret .= ' />';

                if (!empty($paypal_button_atts['service-fee'])) {
                    $ret .= '<div class="tourmaster-payment-paypal-service-fee-text" >';
                    $ret .= sprintf(esc_html__('Additional %s%% is charged for PayPal payment.', 'tourmaster'), $paypal_button_atts['service-fee']);
                    $ret .= '</div>';
                }
                $ret .= '</div>';
            }

            if ($credit_card_enable) {
                $payment_attr = apply_filters('iqhired_plugin_payment_attribute', array());
                $ret .= '<div class="tourmaster-online-payment-method tourmaster-payment-credit-card" >';
                $ret .= '<img src="' . esc_attr(TOURMASTER_URL) . '/images/credit-card.png" alt="credit-card" width="170" height="76" ';
                if (!empty($payment_attr['method']) && $payment_attr['method'] == 'ajax') {
                    $ret .= 'data-method="ajax" data-action="tourmaster_payment_selected" data-ajax="' . esc_url(TOURMASTER_AJAX_URL) . '" ';
                    if (!empty($payment_attr['type'])) {
                        $ret .= 'data-action-type="' . esc_attr($payment_attr['type']) . '" ';
                    }
                }
                $ret .= ' />';

                // service fee
                $credit_card_service_fee = tourmaster_get_option('payment', 'credit-card-service-fee', '');
                if (!empty($credit_card_service_fee)) {
                    $ret .= '<div class="tourmaster-payment-credit-card-service-fee-text" >';
                    $ret .= sprintf(esc_html__('Additional %s%% is charged for payment via credit card.', 'tourmaster'), $credit_card_service_fee);
                    $ret .= '</div>';
                }

                // image display
                $credit_card_types = tourmaster_get_option('payment', 'accepted-credit-card-type', array());
                if (!empty($credit_card_types)) {
                    $ret .= '<div class="tourmaster-payment-credit-card-type" >';
                    foreach ($credit_card_types as $type) {
                        $ret .= '<img src="' . esc_attr(TOURMASTER_URL) . '/images/' . esc_attr($type) . '.png" alt="' . esc_attr($type) . '" />';

                    }
                    $ret .= '</div>';
                }

                $ret .= '</div>';
            }

            if ($hipayprofessional_enable) {
                $hipayprofessional_button_atts = apply_filters('tourmaster_hipayprofessional_button_atts', array());

                $ret .= '<div class="tourmaster-online-payment-method" >';
                $ret .= '<img src="' . esc_attr(TOURMASTER_URL) . '/images/hipay.png" alt="hipayprofessional" ';
                if (!empty($hipayprofessional_button_atts['method']) && $hipayprofessional_button_atts['method'] == 'ajax') {
                    $ret .= 'data-method="ajax" data-action="tourmaster_payment_selected" data-ajax="' . esc_url(TOURMASTER_AJAX_URL) . '" ';
                    if (!empty($hipayprofessional_button_atts['type'])) {
                        $ret .= 'data-action-type="' . esc_attr($hipayprofessional_button_atts['type']) . '" ';
                    }
                }
                $ret .= ' />';
                $ret .= '</div>';

            }
            $ret .= '</div>'; // tourmaster-payment-gateway
        }

        if (in_array('booking', $payment_method)) {

            if (sizeof($payment_method) > 1) {
                $ret .= '<div class="tourmaster-payment-method-or" id="tourmaster-payment-method-or" >';
                $ret .= '<span class="tourmaster-left" ></span>';
                $ret .= '<span class="tourmaster-middle" >' . esc_html__('OR', 'tourmaster') . '</span>';
                $ret .= '<span class="tourmaster-right" ></span>';
                $ret .= '</div>'; // tourmaster-payment-method-or
            }

            $ret .= '<div class="tourmaster-payment-method-booking" >';
            if (is_user_logged_in()) {
                $ret .= '<a class="tourmaster-button tourmaster-payment-method-booking-button tourmaster-payment-step" data-name="payment-method" data-value="booking" data-step="4" >';
                $ret .= esc_html__('Book and pay later', 'tourmaster');
                $ret .= '</a>';
            } else {
                $book_by_email = tourmaster_get_option('general', 'enable-booking-via-email', 'enable');

                if ($book_by_email == 'enable') {
                    $ret .= '<a class="tourmaster-button tourmaster-payment-method-booking-button tourmaster-payment-step" data-name="payment-method" data-value="booking" data-step="4" >';
                    $ret .= esc_html__('Book now via email', 'tourmaster');
                    $ret .= '</a>';
                } else {
                    $ret .= '<a class="tourmaster-button tourmaster-payment-method-booking-button" data-tmlb="book-and-pay-later-login" >';
                    $ret .= esc_html__('Book and pay later', 'tourmaster');
                    $ret .= '</a>';
                    $ret .= tourmaster_lightbox_content(array(
                        'id' => 'book-and-pay-later-login',
                        'title' => esc_html__('To book and pay later requires an account', 'tourmaster'),
                        'content' => tourmaster_get_login_form2(false, array(
                            'redirect' => 'payment'
                        ))
                    ));
                }
            }
            $ret .= '</div>'; // tourmaster-payment-method-booking
        }

        $ret .= '</div>'; // tourmaster-payment-method-wrap

        return $ret;
    }
}

if (!function_exists('tourmaster_payment_complete')) {
    function tourmaster_payment_complete()
    {

        $ret = '<div class="tourmaster-payment-complete-wrap" >';
        $ret .= '<div class="tourmaster-payment-complete-head" >' . esc_html__('Booking Completed!', 'tourmaster') . '</div>';
        $ret .= '<div class="tourmaster-payment-complete-content-wrap" >';
        $ret .= '<i class=" icon_check_alt2 tourmaster-payment-complete-icon" ></i>';
        $ret .= '<div class="tourmaster-payment-complete-thank-you" >' . esc_html__('Thank you!', 'tourmaster') . '</div>';
        $ret .= '<div class="tourmaster-payment-complete-content" >';
        $ret .= wp_kses(__('Your booking detail has been sent to your email. <br> You can check the payment status from your dashboard.', 'tourmaster'), array('br' => array()));
        $ret .= '</div>'; // tourmaster-payment-complete-content

        if (is_user_logged_in()) {
            $ret .= '<a class="tourmaster-payment-complete-button tourmaster-button" href="' . tourmaster_get_template_url('user') . '" >' . esc_html__('Go to my dashboard', 'tourmaster') . '</a>';
        } else {
            $ret .= '<a class="tourmaster-payment-complete-button tourmaster-button" href="' . esc_url(home_url("/")) . '" >' . esc_html__('Go to homepage', 'tourmaster') . '</a>';
        }

        $bottom_text = tourmaster_get_option('general', 'payment-complete-bottom-text', '');
        if (!empty($bottom_text)) {
            $ret .= '<div class="tourmaster-payment-complete-bottom-text" >';
            $ret .= tourmaster_content_filter($bottom_text);
            $ret .= '</div>';
        }
        $ret .= '</div>'; // tourmaster-payment-complete-content-wrap
        $ret .= '</div>'; // tourmaster-payment-complete-wrap

        return $ret;
    }
}

if (!function_exists('tourmaster_payment_complete_delay')) {
    function tourmaster_payment_complete_delay()
    {
        $ret = '<div class="tourmaster-payment-complete-wrap" >';
        $ret .= '<div class="tourmaster-payment-complete-head" >' . esc_html__('Booking Completed!', 'tourmaster') . '</div>';
        $ret .= '<div class="tourmaster-payment-complete-content-wrap" >';
        $ret .= '<i class=" icon_check_alt2 tourmaster-payment-complete-icon" ></i>';
        $ret .= '<div class="tourmaster-payment-complete-thank-you" >' . esc_html__('Thank you!', 'tourmaster') . '</div>';
        $ret .= '<div class="tourmaster-payment-complete-content" >';
        // $ret .= wp_kses(__('Your booking detail will be sent to your email shortly. <br> You can check the payment status from your dashboard.<br>', 'tourmaster'), array('br' => array()));

        $ret .= wp_kses(__('Your Booking is under approval. You will receive a confirmation email once it is approved. In case of non-approval, amount will be refunded to same source of payment. <br> You can check the payment status from your dashboard.<br>', 'tourmaster'), array('br' => array()));

        $ret .= '</div>'; // tourmaster-payment-complete-content

        if (is_user_logged_in()) {
            $ret .= '<a class="tourmaster-payment-complete-button tourmaster-button" href="' . tourmaster_get_template_url('user') . '" >' . esc_html__('Go to my dashboard', 'tourmaster') . '</a>';
        } else {
            $ret .= '<a class="tourmaster-payment-complete-button tourmaster-button" href="' . esc_url(home_url("/")) . '" >' . esc_html__('Go to homepage', 'tourmaster') . '</a>';
        }

        $bottom_text = tourmaster_get_option('general', 'payment-complete-bottom-text', '');
        if (!empty($bottom_text)) {
            $ret .= '<div class="tourmaster-payment-complete-bottom-text" >';
            $ret .= tourmaster_content_filter($bottom_text);
            $ret .= '</div>';
        }
        $ret .= '</div>'; // tourmaster-payment-complete-content-wrap
        $ret .= '</div>'; // tourmaster-payment-complete-wrap

        return $ret;
    }
}

if (!function_exists('tourmaster_payment_failed')) {
    function tourmaster_payment_failed()
    {
        $ret = '<div class="tourmaster-payment-complete-wrap" >';
        $ret .= '<div class="tourmaster-payment-complete-head" >' . esc_html__('Payment failed. Booking is pending!', 'tourmaster') . '</div>';
        $ret .= '<div class="tourmaster-payment-complete-content-wrap" >';
        $ret .= '<i class=" icon_check_alt2 tourmaster-payment-complete-icon" ></i>';
//        $ret .= '<div class="tourmaster-payment-complete-thank-you" >' . esc_html__('Thank you!', 'tourmaster') . '</div>';
        $ret .= '<div class="tourmaster-payment-complete-content" >';
        // $ret .= wp_kses(__('Your booking detail will be sent to your email shortly. <br> You can check the payment details from your dashboard.<br>', 'tourmaster'), array('br' => array()));

        $ret .= wp_kses(__('Your Booking is pending as the payment failed . <br> You can try to process the payment again from your dashboard.<br>', 'tourmaster'), array('br' => array()));
        $ret .= wp_kses(__('Once the payment is processed the booking will be processed by the admin accordingly.<br>', 'tourmaster'), array('br' => array()));

        $ret .= '</div>'; // tourmaster-payment-complete-content

        if (is_user_logged_in()) {
            $ret .= '<a class="tourmaster-payment-complete-button tourmaster-button" href="' . tourmaster_get_template_url('user') . '" >' . esc_html__('Go to my dashboard', 'tourmaster') . '</a>';
        } else {
            $ret .= '<a class="tourmaster-payment-complete-button tourmaster-button" href="' . esc_url(home_url("/")) . '" >' . esc_html__('Go to homepage', 'tourmaster') . '</a>';
        }

        $bottom_text = tourmaster_get_option('general', 'payment-complete-bottom-text', '');
        if (!empty($bottom_text)) {
            $ret .= '<div class="tourmaster-payment-complete-bottom-text" >';
            $ret .= tourmaster_content_filter($bottom_text);
            $ret .= '</div>';
        }
        $ret .= '</div>'; // tourmaster-payment-complete-content-wrap
        $ret .= '</div>'; // tourmaster-payment-complete-wrap

        return $ret;
    }
}

//////////////////////////////////////////////////////////////////
/////////////////            lightbox             ////////////////
//////////////////////////////////////////////////////////////////
if (!function_exists('tourmaster_lb_payment_receipt')) {
    function tourmaster_lb_payment_receipt($transaction_id)
    {
        $form_fields = array(
            'receipt' => array(
                'title' => esc_html__('Select Image', 'tourmaster'),
                'type' => 'file',
            ),
            'transaction-id' => array(
                'title' => esc_html__('Transaction ID ( from the receipt )', 'tourmaster'),
                'type' => 'text',
                'required' => true
            )
        );

        $ret = '<form class="tourmaster-payment-receipt-form tourmaster-form-field tourmaster-with-border" ';
        $ret .= 'method="post" enctype="multipart/form-data" ';
        $ret .= 'action="' . remove_query_arg(array('error_code')) . '" ';
        $ret .= '>';
        // deposit payment
        $result = tourmaster_get_booking_data(array('id' => $transaction_id), array('single' => true));
        $tour_option = tourmaster_get_post_meta($result->tour_id, 'tourmaster-tour-option');
        $enable_full_payment = tourmaster_get_option('payment', 'enable-full-payment', 'enable');

        if (empty($tour_option['deposit-booking']) || $tour_option['deposit-booking'] == 'default') {
            $enable_deposit_payment = tourmaster_get_option('payment', 'enable-deposit-payment', 'disable');
            $deposit_amount = tourmaster_get_option('payment', 'deposit-payment-amount', '0');
        } else {
            $enable_deposit_payment = $tour_option['deposit-booking'];
            $deposit_amount = empty($tour_option['deposit-amount']) ? 0 : $tour_option['deposit-amount'];
        }

        if ($enable_deposit_payment == 'enable') {
            $current_date = strtotime(current_time('Y-m-d'));
            $deposit_before_days = intval(tourmaster_get_option('payment', 'display-deposit-payment-day', '0'));
            if ($current_date + ($deposit_before_days * 86400) > $result->travel_date) {
                $enable_deposit_payment == 'disable';
            }
        }

        $pricing_info = json_decode($result->pricing_info, true);
        $payment_type_checked = false;
        $ret .= '<div class="tourmaster-payment-receipt-field tourmaster-payment-receipt-field-payment-type clearfix" >';
        $ret .= '<div class="tourmaster-head" >' . esc_html__('Select Payment Type', 'tourmaster') . '</div>';
        $ret .= '<div class="tourmaster-tail clearfix" >';
        $ret .= '<div class="tourmaster-payment-receipt-deposit-option" >';
        if ($enable_full_payment == 'enable') {
            $ret .= '<label class="tourmaster-deposit-payment-full" >';
            $ret .= '<input type="radio" name="payment-type" value="full" ' . ($payment_type_checked ? '' : 'checked') . ' />';
            $ret .= '<span class="tourmaster-content" >';
            $ret .= '<i class="icon_check_alt2" ></i>';
            $ret .= sprintf(esc_html__('Pay Full Amount : %s', 'tourmaster'), tourmaster_money_format($pricing_info['total-price']));
            $ret .= '</span>';
            $ret .= '</label>';
            $payment_type_checked = true;
        }

        if ($enable_deposit_payment == 'enable' && !empty($deposit_amount)) {
            $deposit_price = ($pricing_info['total-price'] * intval($deposit_amount)) / 100;
            $ret .= '<label class="tourmaster-deposit-payment-partial" >';
            $ret .= '<input type="radio" name="payment-type" value="partial" ' . ($payment_type_checked ? '' : 'checked') . ' />';
            $ret .= '<span class="tourmaster-content" >';
            $ret .= '<i class="icon_check_alt2" ></i>';
            $ret .= sprintf(esc_html__('Pay %d%% Deposit : %s', 'tourmaster'), $deposit_amount, tourmaster_money_format($deposit_price));
            $ret .= '<input type="hidden" name="deposit-rate" value="' . esc_attr($deposit_amount) . '" />';
            $ret .= '<input type="hidden" name="deposit-price" value="' . esc_attr($deposit_price) . '" />';
            $ret .= '</span>';
            $ret .= '</label>';
        }

        $ret .= '</div>';
        $ret .= '</div>';
        $ret .= '</div>';

        foreach ($form_fields as $field_slug => $form_field) {
            $form_field['echo'] = false;
            $form_field['slug'] = $field_slug;
            $ret .= tourmaster_get_form_field($form_field, 'payment-receipt');
        }

        $ret .= '<div class="tourmaster-lb-submit-error tourmaster-notification-box tourmaster-failure" >';
        $ret .= esc_html__('Please fill all required fields', 'tourmaster');
        $ret .= '</div>';

        $ret .= '<div class="tourmaster-payment-receipt-field-submit" >';
        $ret .= '<input class="tourmaster-payment-receipt-field-submit-button tourmaster-button" type="submit" value="' . esc_html__('Submit', 'tourmaster') . '" />';
        $ret .= '</div>';

        $ret .= '<div class="tourmaster-payment-receipt-description" >';
        $ret .= esc_html__('* Please wait for the verification process after submitting the receipt. This could take up to couple days. You can check the status of submission from your "Dashboard" or "My Booking" page.', 'tourmaster');
        $ret .= '</div>';

        $ret .= '<input type="hidden" name="action" value="payment-receipt" />';
        $ret .= '<input type="hidden" name="id" value="' . esc_attr($transaction_id) . '" />';

        $ret .= '</form>';

        return $ret;
    }
}

//////////////////////////////////////////////////////////////////
/////////////////            ajax action          ////////////////
//////////////////////////////////////////////////////////////////

add_action('wp_ajax_tourmaster_upload_id', 'tourmaster_ajax_upload_id');
add_action('wp_ajax_nopriv_tourmaster_upload_id', 'tourmaster_ajax_upload_id');
if (!function_exists('tourmaster_ajax_upload_id')) {
    function tourmaster_ajax_upload_id()
    {

        //x$booking_detail = empty($_POST['booking_detail']) ? array() : tourmaster_process_post_data($_POST['booking_detail']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_FILES['files'])) {
                $errors = [];
                $path = 'uploads/';
                $extensions = ['jpg', 'jpeg', 'png', 'gif'];

                $all_files = count($_FILES['files']['tmp_name']);

                for ($i = 0; $i < $all_files; $i++) {
                    $file_name = $_FILES['files']['name'][$i];
                    $file_tmp = $_FILES['files']['tmp_name'][$i];
                    $file_type = $_FILES['files']['type'][$i];
                    $file_size = $_FILES['files']['size'][$i];
                    $file_ext = strtolower(end(explode('.', $_FILES['files']['name'][$i])));

                    $file = $path . $_POST['file_name'];

                    if (!in_array($file_ext, $extensions)) {
                        $errors[] = 'Extension not allowed: ' . $file_name . ' ' . $file_type;
                    }

                    if ($file_size > 2097152) {
                        $errors[] = 'File size exceeds limit: ' . $file_name . ' ' . $file_type;
                    }

                    if (empty($errors)) {
                        move_uploaded_file($file_tmp, $file);
                    }
                }

                if ($errors) print_r($errors);
            }
        }
    } // tourmaster_ajax_payment_template
}

add_action('wp_ajax_tourmaster_payment_template', 'tourmaster_ajax_payment_template');
add_action('wp_ajax_nopriv_tourmaster_payment_template', 'tourmaster_ajax_payment_template');
if (!function_exists('tourmaster_ajax_payment_template')) {
    function tourmaster_ajax_payment_template()
    {

        $booking_detail = empty($_POST['booking_detail']) ? array() : tourmaster_process_post_data($_POST['booking_detail']);

        $ret = tourmaster_get_payment_page($booking_detail);

        if (!empty($_POST['sub_action']) && $_POST['sub_action'] == 'update_sidebar') {
            //unset($ret['content']);
        }

        if ($booking_detail['step'] == 4) {
            $booking_detail['next-step'] = 5;
            $cook_book_det = $_COOKIE['tourmaster-booking-detail'];
            if(is_array($cook_book_det)){
                $booking_detail =  (array_merge ($booking_detail, $cook_book_det));
            }
        }

        $ret['cookie'] = $booking_detail;
        die(json_encode($ret));

    } // tourmaster_ajax_payment_template
}

add_action('wp_ajax_tourmaster_payment_seva_template', 'tourmaster_ajax_payment_seva_template');
add_action('wp_ajax_nopriv_tourmaster_payment_seva_template', 'tourmaster_ajax_payment_seva_template');
if (!function_exists('tourmaster_ajax_payment_seva_template')) {
    function tourmaster_ajax_payment_seva_template()
    {

        $booking_detail = empty($_POST['booking_detail']) ? array() : tourmaster_process_post_data($_POST['booking_detail']);

        $ret = tourmaster_get_payment_page($booking_detail);

        if (!empty($_POST['sub_action']) && $_POST['sub_action'] == 'update_sidebar') {
            unset($ret['content']);
        }

        if ($booking_detail['step'] != 5) {
            $ret['cookie'] = $booking_detail;
        }

        die(json_encode($ret));

    } // tourmaster_ajax_payment_template
}

if (!function_exists('tourmaster_ajax_validate_coupon_code')) {
    function tourmaster_ajax_validate_coupon_code()
    {

        $ret = array();

        if (empty($_POST['coupon_code'])) {
            die(json_encode(array(
                'status' => 'failed',
                'message' => esc_html__('Please fill in the coupon code', 'tourmaster')
            )));
        } else {

            $status = tourmaster_validate_coupon_code($_POST['coupon_code'],$_POST['coupon_type'], $_POST['tour_id']);
            unset($status['data']);

            die(json_encode($status));
        }

    } // tourmaster_ajax_payment_template
}

add_action('wp_ajax_tourmaster_validate_coupon_code', 'tourmaster_ajax_validate_coupon_code');
add_action('wp_ajax_nopriv_tourmaster_validate_coupon_code', 'tourmaster_ajax_validate_coupon_code');
if (!function_exists('tourmaster_validate_coupon_code')) {
    function tourmaster_validate_coupon_code($coupon_code,$coupon_type, $tour_id)
    {
        global $wpdb;

        $coupons = get_posts(array(
            'post_type' => 'tour_coupon',
            'posts_per_page' => 1,
            'meta_key' => 'tourmaster-coupon-code',
            'meta_value' => $coupon_code
        ));

        if (!empty($coupons)) {

            $coupon_status = true;
            $coupon_option = get_post_meta($coupons[0]->ID, 'tourmaster-coupon-option', true);

            // check expiry
            if (!empty($coupon_option['coupon-expiry'])) {
                if (strtotime(date("Y-m-d")) > strtotime($coupon_option['coupon-expiry'])) {
                    return array(
                        'status' => 'failed',
                        'message' => esc_html__('This coupon has been expired, please try again with different coupon', 'tourmaster')
                    );
                }
            }

            // check specific tour
            if (!empty($coupon_option['apply-to-specific-tour'])) {
                $allow_tours = array_map('trim', explode(',', $coupon_option['apply-to-specific-tour']));
                if (!in_array($tour_id, $allow_tours)) {
                    return array(
                        'status' => 'failed',
                        'message' => esc_html__('This coupon is not available for this tour, please try again with different coupon', 'tourmaster')
                    );
                }
            }

            // check the available number
            if (!empty($coupon_option['coupon-amount'])) {
                $used_coupon = tourmaster_get_booking_data(array('coupon_code' => $coupon_code), array(), 'COUNT(*)');

                if ($used_coupon >= $coupon_option['coupon-amount']) {
                    return array(
                        'status' => 'failed',
                        'message' => esc_html__('This coupon has been used up, please try again with different coupon', 'tourmaster')
                    );
                }
            }

            // check the coupon type for voucher and coupon
            if (!empty($coupon_option['coupon-type'])) {

                if (!($coupon_option['coupon-type'] == $coupon_type)) {
                    return array(
                        'status' => 'failed',
                        'message' => esc_html__('Invalid Coupon Type', 'tourmaster')
                    );
                }
            }

            // coupon is valid
            $discount_amount = 0;
            if (!empty($coupon_option['coupon-discount-type'])) {
                if ($coupon_option['coupon-discount-type'] == 'percent') {
                    $discount_amount = $coupon_option['coupon-discount-amount'] . '%';
                } else if ($coupon_option['coupon-discount-type'] == 'amount') {
                    $discount_amount = tourmaster_money_format($coupon_option['coupon-discount-amount']);
                }
            }
            $message = sprintf(__('You got %s discount', 'tourmaster'), $discount_amount);
            return array(
                'status' => 'success',
                'message' => $message,
                'data' => $coupon_option
            );

        } else {
            return array(
                'status' => 'failed',
                'message' => esc_html__('Invalid coupon code, please try again with different coupon', 'tourmaster')
            );
        }
    }
}
if (!function_exists('tourmaster_ajax_validate_coupon_code')) {
    function tourmaster_ajax_validate_coupon_code()
    {

        $ret = array();

        if (empty($_POST['coupon_code'])) {
            die(json_encode(array(
                'status' => 'failed',
                'message' => esc_html__('Please fill in the coupon code', 'tourmaster')
            )));
        } else {

            $status = tourmaster_validate_coupon_code($_POST['coupon_code'],$_POST['coupon_type'], $_POST['tour_id']);
            unset($status['data']);

            die(json_encode($status));
        }

    } // tourmaster_ajax_payment_template
}

//////////////////////////////////////////////////////////////////
/////////////////     payment plugin supported    ////////////////
//////////////////////////////////////////////////////////////////
add_filter('iqhired_payment_get_transaction_data', 'tourmaster_iqhired_payment_get_transaction_data', 10, 3);
if (!function_exists('tourmaster_iqhired_payment_get_transaction_data')) {
    function tourmaster_iqhired_payment_get_transaction_data($ret, $tid, $types)
    {
        $result = tourmaster_get_booking_data(array('id' => $tid), array('single' => true));
        if (!empty($result)) {
            $ret = array();

            foreach ($types as $type) {
                if ($type == 'price') {
                    $ret[$type] = json_decode($result->pricing_info, true);
                } else if ($type == 'email') {
                    $contact_info = json_decode($result->contact_info, true);
                    $ret[$type] = $contact_info[$type];
                } else if ($type == 'tour_id') {
                    $ret[$type] = $result->tour_id;
                }
            }
        }

        return $ret;
    }
}

add_filter('iqhired_payment_get_option', 'tourmaster_iqhired_payment_get_option', 10, 2);
if (!function_exists('tourmaster_iqhired_payment_get_option')) {
    function tourmaster_iqhired_payment_get_option($value, $key)
    {
        return tourmaster_get_option('payment', $key, $value);
    }
}

add_action('iqhired_set_payment_complete', 'tourmaster_iqhired_set_payment_complete', 10, 2);
if (!function_exists('tourmaster_iqhired_set_payment_complete')) {
    function tourmaster_iqhired_set_payment_complete($tid, $payment_info)
    {

        $result = tourmaster_get_booking_trans_data(array('id' => $tid), array('single' => true));
//        console_log("Booking Dta = " . $result->total_price);
        if (empty($payment_info['amount']) || tourmaster_compare_price($result->total_price, $payment_info['amount'])) {
            $order_status = 'online-paid';
        } else {
            $order_status = 'deposit-paid';
        }

        tourmaster_update_booking_data(
            array(
                'payment_info' => json_encode($payment_info),
                'payment_date' => current_time('mysql'),
                'order_status' => $order_status
            ),
            array('id' => $tid),
            array('%s', '%s', '%s'),
            array('%d')
        );
//        console_log("Booking Dta = " . $result->total_price);

        tourmaster_mail_notification('payment-made-mail', $tid);
        tourmaster_mail_notification('admin-online-payment-made-mail', $tid);
        tourmaster_send_email_invoice($tid);

    }
}

add_action('wp_ajax_tourmaster_payment_plugin_complete', 'tourmaster_payment_plugin_complete');
add_action('wp_ajax_nopriv_tourmaster_payment_plugin_complete', 'tourmaster_payment_plugin_complete');
if (!function_exists('tourmaster_payment_plugin_complete')) {
    function tourmaster_payment_plugin_complete()
    {
        die(json_encode(array(
            'cookie' => '',
            'content' => tourmaster_payment_complete()
        )));
    }
}

add_action('wp_ajax_tourmaster_payment_selected', 'tourmaster_ajax_payment_selected');
add_action('wp_ajax_nopriv_tourmaster_payment_selected', 'tourmaster_ajax_payment_selected');
if (!function_exists('tourmaster_ajax_payment_selected')) {
    function tourmaster_ajax_payment_selected()
    {

        $ret = array();

        if (!empty($_POST['booking_detail'])) {
            $booking_detail = tourmaster_process_post_data($_POST['booking_detail']);

            if (!empty($booking_detail['tour-id']) && !empty($booking_detail['tour-date'])) {
                $tour_option = tourmaster_get_post_meta($booking_detail['tour-id'], 'tourmaster-tour-option');
                $date_price = tourmaster_get_tour_date_price($tour_option, $booking_detail['tour-id'], $booking_detail['tour-date']);
                $date_price = tourmaster_get_tour_date_price_package($date_price, $booking_detail);

                $booking_detail['payment_method'] = $_POST['type'];
                $tour_price = tourmaster_get_tour_price($tour_option, $date_price, $booking_detail);

                if ($date_price['pricing-method'] == 'group') {
                    $traveller_amount = 1;
                } else {
                    $traveller_amount = tourmaster_get_tour_people_amount($tour_option, $date_price, $booking_detail, 'all');
                }

                $package_group_slug = empty($date_price['group-slug']) ? '' : $date_price['group-slug'];
                $tid = tourmaster_insert_booking_data($booking_detail, $tour_price, $traveller_amount, $package_group_slug);

                if ($tour_price['total-price'] <= 0) {
                    $ret['content'] = tourmaster_payment_complete();
                    $ret['cookie'] = '';

                } else {
                    $booking_detail['tid'] = $tid;

                    $ret['content'] = apply_filters('iqhired_' . $_POST['type'] . '_payment_form', '', $tid);
                    $ret['cookie'] = $booking_detail;

                    // recalculate the fee
                    $ret['sidebar'] = tourmaster_get_booking_bar_summary($tour_option, $date_price, $booking_detail,false,5);
                }
            }
        }

        die(json_encode($ret));
    } // tourmaster_ajax_payment_selected
}

function console_log( $data ){
    echo '<script>';
    echo 'console.log('. json_encode( $data ) .')';
    echo '</script>';
}

function console_dir( $data ){
    echo '<script>';
    echo 'console.dir('. json_encode( $data ) .')';
    echo '</script>';
}