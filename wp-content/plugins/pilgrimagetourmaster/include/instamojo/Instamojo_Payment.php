<?php
$ret = array();
if (isset($_POST['name']) && isset($_POST['number'])
    && isset($_POST['email']) && isset($_POST['amount'])
    && isset($_POST['tid'])) {

    $live_mode = tourmaster_get_option('payment', 'instamojo-live-mode', 'disable');
    $instamojo_api_key = tourmaster_get_option('payment', 'instamojo-api-key', '');
    $instamojo_auth_token = tourmaster_get_option('payment', 'instamojo-auth-token', '');
    $instamojo_salt = tourmaster_get_option('payment', 'instamojo-salt', '');
    $user_email = tourmaster_get_option('payment', 'user_email_id', '');

    $mode = 'test';
    $url = '';
    if( empty($live_mode) || $live_mode == 'disable' ){
        $url = 'https://test.instamojo.com/api/1.1/payment-requests/';
        $mode = 'test.instamojo.com/@ashams001/';
    }else{
        $url = 'https://www.instamojo.com/api/1.1/payment-requests/';
//        $mode = 'www';
        $mode = 'www.instamojo.com/@Iskcon_Bangalore/';
    }
    $api_key = "X-Api-Key:" . $instamojo_api_key;
    $auth_token = "X-Auth-Token:" . $instamojo_auth_token;
    //console_log("Inside Payment");

    //$req = $_POST['booking_detail'];
    $pilgrim_amount = $_POST['amount'];
//    $api_key = apply_filters('iqhired_payment_get_option', '', 'instamojo-api-key');
//    $auth_token = trim(apply_filters('iqhired_payment_get_option', '', 'instamojo-auth-token'));
//
//    $live_mode = apply_filters('iqhired_payment_get_option', '', 'instamojo-live-mode');


    $amount = $pilgrim_amount;
    $name = $_POST['name'];
    $number = $_POST['number'];
    $email = $_POST['email'];
    //https://www.instamojo.com/@iskconbangalore_Pilgrimages/06b621314df14031a49fd846eac3562d
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
    curl_setopt($ch, CURLOPT_HTTPHEADER,
        array($api_key,$auth_token));

//    $baseUrl = "http://localhost:8888/";
//     $baseUrl = "https://iskcon.iqdemopro.com/";
//    $liveUrl = "https://iskcon.iqdemopro.com/";
    $current_url = (is_ssl()? "https": "http") . "://" . $_SERVER['HTTP_HOST'];

    $payload = Array(
        'purpose' => 'Pilgrimage Bookings',
        'amount' => $amount,
        'phone' => $number,
        'buyer_name' => $name,
        'redirect_url' => $current_url.'?tourmaster-payment',
        'send_email' => true,
        'webhook' => $current_url.'/wp-content/plugins/pilgrimagetourmaster/include/instamojo/webhook.php',
        'send_sms' => false,
        'email' => $email,
        'allow_repeated_payments' => false
    );
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
    $response = curl_exec($ch);

    //echo $response;

    $data = json_decode($response, true);

    if($data['success'] == 1){
        // for on page payment, use this.
        $payment_id = $data['payment_request']['id'];

        echo '<script src="https://js.instamojo.com/v1/checkout.js"></script>
        <script>
            Instamojo.open("https://'.$mode.$payment_id.'"); 
        </script> ';

        //and for redirect to payment page, use this and uncomment the header() below.

        //header('Location:'.$data['payment_request']['longurl'].'');
    }else{
        $payment_info['error'] = curl_error($ch);
        $tid = $_POST['tid'];
        tourmaster_update_booking_data(
            array(
                'payment_info' => json_encode($payment_info),
            ),
            array('id' => $tid, 'payment_date' => '0000-00-00 00:00:00'),
            array('%s'),
            array('%d', '%s')
        );
        echo '<div class="w3-panel w3-red w3-content"><p>Error Try Again Later! Hello world</p></div>';
    }
    curl_close($ch);
    die(json_encode($ret));
}


add_filter('iqhired_credit_card_payment_gateway_options', 'iqhired_instamojo_payment_gateway_options');
if( !function_exists('iqhired_instamojo_payment_gateway_options') ){
    function iqhired_instamojo_payment_gateway_options( $options ){
        $options['instamojo'] = esc_html__('Instamojo', 'tourmaster');

        return $options;
    }
}

// init the script on payment page head
add_filter('iqhired_plugin_payment_option', 'iqhired_instamojo_payment_option');
if( !function_exists('iqhired_instamojo_payment_option') ){
    function iqhired_instamojo_payment_option( $options ){

        $options['instamojo'] = array(
            'title' => esc_html__('Instamojo', 'tourmaster'),
            'options' => array(
                'instamojo-live-mode' => array(
                    'title' => __('Live Mode ', 'tourmaster'),
                    'type' => 'checkbox',
                    'default' => 'disable',
                    'description' => __('Please turn this option off when you\'re on test mode.','tourmaster')
                ),
                'instamojo-api-key' => array(
                    'title' => __('Instamojo Private API Key', 'tourmaster'),
                    'type' => 'text'
                ),
                'instamojo-auth-token' => array(
                    'title' => __('Instamojo Private Auth Token', 'tourmaster'),
                    'type' => 'text'
                ),
                'instamojo-salt' => array(
                    'title' => __('Instamojo Private Salt', 'tourmaster'),
                    'type' => 'text'
                ),

                'user-email-id' => array(
                    'title' => __('Email ID', 'tourmaster'),
                    'type' => 'text'
                ),
            )
        );

        return $options;
    } // iqhired_instamojo_payment_option
}

if( $current_payment_gateway == 'instamojo' ){

    add_filter('iqhired_plugin_payment_attribute', 'iqhired_instamojo_payment_attribute');
    add_filter('iqhired_instamojo_payment_form', 'iqhired_instamojo_payment_form', 10, 2);

    add_action('wp_ajax_instamojo_payment_charge', 'iqhired_instamojo_payment_charge');
    add_action('wp_ajax_nopriv_instamojo_payment_charge', 'iqhired_instamojo_payment_charge');
}

// add attribute for payment button
if( !function_exists('iqhired_instamojo_payment_attribute') ){
    function iqhired_instamojo_payment_attribute( $attributes ){
        return array('method' => 'ajax', 'type' => 'instamojo');
    }
}

