<?php
if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.


function wbrm_check_seat_available_or_not( $passed, $product_id, $quantity, $variation_id = '', $variations= '' ) {
$seat_name        = wptm_array_strip($_POST['seat_name']);
$journey_date     = sanitize_text_field($_POST['journey_date']);
$bus_id           = sanitize_text_field($_POST['bus_id']);
$start_stops      = sanitize_text_field($_POST['start_stops']);
$check_before_order = wbtm_get_seat_cehck_before_order($seat_name,$journey_date,$bus_id,$start_stops);
    if ( $check_before_order > 0 ){
        $passed = false;
        wc_add_notice( __( 'Sorry, Your Selected Seat Already Booked by another user','bus-ticket-booking-with-seat-reservation' ), 'error' );
    }
    return $passed;
}
add_filter( 'woocommerce_add_to_cart_validation', 'wbrm_check_seat_available_or_not', 10, 5 );

function wbtm_add_custom_fields_text_to_cart_item( $cart_item_data, $product_id, $variation_id ) {
  $tp               = get_post_meta($product_id,'_price',true);
  $price_arr        = get_post_meta($product_id,'wbtm_bus_prices',true);

  

  $new = array();
  $user = array();

  $start_stops      = sanitize_text_field($_POST['start_stops']);
  $end_stops        = sanitize_text_field($_POST['end_stops']);
  $journey_date     = sanitize_text_field($_POST['journey_date']);
  $seat_name        = wptm_array_strip($_POST['seat_name']);
  $total_seat       = sanitize_text_field($_POST['total_seat']);
  $count_seat       = count($seat_name);
  $main_fare        = wbtm_get_bus_price($start_stops,$end_stops, $price_arr);
  $total_fare       = $main_fare*$count_seat;
  $user_start_time  = sanitize_text_field($_POST['user_start_time']);
  $bus_start_time   = sanitize_text_field($_POST['bus_start_time']);
  $bus_id           = sanitize_text_field($_POST['bus_id']);
$ext_bag = 0;
$extra_bag_price         = get_post_meta($product_id,'wbtm_extra_bag_price',true) ? get_post_meta($product_id,'wbtm_extra_bag_price',true) : 0;

if(isset($_POST['wbtm_user_name']) || isset($_POST['wbtm_user_email']) || isset($_POST['wbtm_user_phone']) || isset($_POST['wbtm_user_address']) || isset($_POST['wbtm_user_gender'])){

  
  $wbtm_user_name          = $_POST['wbtm_user_name'];
  $wbtm_user_email         = $_POST['wbtm_user_email'];
  $wbtm_user_phone         = $_POST['wbtm_user_phone'];
  $wbtm_user_address       = $_POST['wbtm_user_address'];
  $wbtm_user_gender        = $_POST['wbtm_user_gender'];
  $extra_bag_quantity      = $_POST['extra_bag_quantity'];







$count_user = count($wbtm_user_name);
  for ( $iu = 0; $iu < $count_user; $iu++ ) {
    
    if ( $wbtm_user_name[$iu] != '' ) :
      $user[$iu]['wbtm_user_name'] = stripslashes( strip_tags( $wbtm_user_name[$iu] ) );
      endif;

    if ( $wbtm_user_email[$iu] != '' ) :
      $user[$iu]['wbtm_user_email'] = stripslashes( strip_tags( $wbtm_user_email[$iu] ) );
      endif;

    if ( $wbtm_user_phone[$iu] != '' ) :
      $user[$iu]['wbtm_user_phone'] = stripslashes( strip_tags( $wbtm_user_phone[$iu] ) );
      endif;

    if ( $wbtm_user_address[$iu] != '' ) :
      $user[$iu]['wbtm_user_address'] = stripslashes( strip_tags( $wbtm_user_address[$iu] ) );
      endif;

    if ( $wbtm_user_gender[$iu] != '' ) :
      $user[$iu]['wbtm_user_gender'] = stripslashes( strip_tags( $wbtm_user_gender[$iu] ) );
      endif;

    if ( $extra_bag_quantity[$iu] != '' ) :
      $user[$iu]['wbtm_extra_bag_qty'] = stripslashes( strip_tags( $extra_bag_quantity[$iu] ) );
      $ext_bag = $ext_bag + $extra_bag_quantity[$iu];
      endif;

    $wbtm_form_builder_data = get_post_meta($product_id, 'wbtm_form_builder_data', true);

    if ( $wbtm_form_builder_data ) {
      foreach ( $wbtm_form_builder_data as $_field ) {
          if ( $wbtm_user_ticket_type[$iu] != '' ) :
            $user[$iu][$_field['wbtm_fbc_id']] = stripslashes( strip_tags( $_POST[$_field['wbtm_fbc_id']][$iu] ) );
            endif; 
      }
    }

}



  $cart_item_data['extra_bag_quantity'] = $ext_bag;

}else{
  $user ="";
  $cart_item_data['extra_bag_quantity'] = 0;
}




  $count = count( $seat_name );
  for ( $i = 0; $i < $count; $i++ ) {
    
    if ( $seat_name[$i] != '' ) :
      $new[$i]['wbtm_seat_name'] = stripslashes( strip_tags( $seat_name[$i] ) );
      endif;
  }

  // $extra_bag_price         = 100;
  $extra_bag_price         = ($extra_bag_price * $ext_bag);
  $total_fare              = $total_fare + $extra_bag_price;
  // $total_fare              = 349;




  $cart_item_data['wbtm_seats'] = $new;
  $cart_item_data['wbtm_start_stops'] = $start_stops;
  $cart_item_data['wbtm_end_stops'] = $end_stops;
  $cart_item_data['wbtm_journey_date'] = $journey_date;
  $cart_item_data['wbtm_journey_time'] = $user_start_time;
  $cart_item_data['wbtm_bus_time'] = $bus_start_time;
  $cart_item_data['wbtm_total_seats'] = $total_seat;
  $cart_item_data['wbtm_passenger_info'] = $user;
  $cart_item_data['wbtm_tp'] = $total_fare;
  $cart_item_data['wbtm_bus_id'] = $bus_id;
  $cart_item_data['bus_id'] = $product_id;
  $cart_item_data['line_total'] = $total_fare;
  $cart_item_data['line_subtotal'] = $total_fare;
return $cart_item_data;

}
add_filter( 'woocommerce_add_cart_item_data', 'wbtm_add_custom_fields_text_to_cart_item', 10, 3 );



add_action( 'woocommerce_before_calculate_totals', 'wbtm_add_custom_price' );
function wbtm_add_custom_price( $cart_object ) {

    foreach ( $cart_object->cart_contents as $key => $value ) {
$eid = $value['bus_id'];
if (get_post_type($eid) == 'wbtm_bus') {      
            $cp = $value['wbtm_tp'];
            $value['data']->set_price($cp);
            $new_price = $value['data']->get_price();
    }
}
}





function wbtm_display_custom_fields_text_cart( $item_data, $cart_item ) {
$wbtm_events_extra_prices = $cart_item['wbtm_seats'];
$extra_bag_quantity = $cart_item['extra_bag_quantity'];
if($wbtm_events_extra_prices){ 
$extra_bag_price         = get_post_meta($cart_item['bus_id'],'wbtm_extra_bag_price',true);
  ?>
<ul class='event-custom-price'>
   <li> 
     <?php echo bus_get_option('wbtm_seat_list_text', 'label_setting_sec') ? bus_get_option('wbtm_seat_list_text', 'label_setting_sec') : _e('Seat List:','bus-ticket-booking-with-seat-reservation'); 
      foreach ( $wbtm_events_extra_prices as $field ) { 
       echo esc_attr( $field['wbtm_seat_name'] ).","; 
      }
      ?>
</li>
<li><?php echo bus_get_option('wbtm_select_journey_date_text', 'label_setting_sec') ? bus_get_option('wbtm_select_journey_date_text', 'label_setting_sec') : _e('Journey Date:','bus-ticket-booking-with-seat-reservation');
 ?> <?php echo $cart_item['wbtm_journey_date']; ?></li>
<li><?php echo bus_get_option('wbtm_starting_text', 'label_setting_sec') ? bus_get_option('wbtm_starting_text', 'label_setting_sec') : _e('Journey Time:','bus-ticket-booking-with-seat-reservation');
 ?> <?php echo $cart_item['wbtm_journey_time']; ?></li>
<li><?php echo bus_get_option('wbtm_boarding_points_text', 'label_setting_sec') ? bus_get_option('wbtm_boarding_points_text', 'label_setting_sec') : _e('Boarding Point:','bus-ticket-booking-with-seat-reservation');
 ?> <?php echo $cart_item['wbtm_start_stops']; ?></li>
<li><?php echo bus_get_option('wbtm_dropping_points_text', 'label_setting_sec') ? bus_get_option('wbtm_dropping_points_text', 'label_setting_sec') : _e('Dropping Point:','bus-ticket-booking-with-seat-reservation'); ?> <?php echo $cart_item['wbtm_end_stops']; ?></li>


<?php if($extra_bag_quantity>0){ ?>
<li><?php _e('Extra Bag: ','bus-ticket-booking-with-seat-reservation'); echo '('.$cart_item['extra_bag_quantity'].' x '.$extra_bag_price.') = '.wc_price($cart_item['extra_bag_quantity']*$extra_bag_price); ?></li>
<?php } ?>


</ul>
<?php
  return $item_data;
}
}

add_filter( 'woocommerce_get_item_data', 'wbtm_display_custom_fields_text_cart', 10, 2 );



add_action('woocommerce_after_checkout_validation', 'rei_after_checkout_validation');
function rei_after_checkout_validation( $posted ) {
global $woocommerce;
$items    = $woocommerce->cart->get_cart();
$eid      = $values['bus_id'];
foreach($items as $item => $values) { 
$wbtm_seats              = $values['wbtm_seats'];
$wbtm_passenger_info     = $values['wbtm_passenger_info'];
$wbtm_start_stops        = $values['wbtm_start_stops'];
$wbtm_end_stops          = $values['wbtm_end_stops'];
$wbtm_journey_date       = $values['wbtm_journey_date'];
$wbtm_journey_time       = $values['wbtm_journey_time'];
$wbtm_bus_start_time     = $values['wbtm_bus_time'];
$wbtm_bus_id             = $values['wbtm_bus_id'];
}

// echo $se = $wbtm_seats[0]['w/btm_seat_name'];

$check_before_order = wbtm_get_seat_cehck_before_place_order($wbtm_seats,$wbtm_journey_date,$wbtm_bus_id,$wbtm_start_stops);


    if ($check_before_order > 0) {
        WC()->cart->empty_cart();
         wc_add_notice( __( "Sorry, Your Selected Seat Already Booked by another user", 'woocommerce' ), 'error' );

    }


// }
}







function wbtm_add_custom_fields_text_to_order_items( $item, $cart_item_key, $values, $order ) {
$eid = $values['bus_id'];
if (get_post_type($eid) == 'wbtm_bus') { 
$wbtm_seats              = $values['wbtm_seats'];
$wbtm_passenger_info     = $values['wbtm_passenger_info'];
$wbtm_start_stops        = $values['wbtm_start_stops'];
$wbtm_end_stops          = $values['wbtm_end_stops'];
$wbtm_journey_date       = $values['wbtm_journey_date'];
$wbtm_journey_time       = $values['wbtm_journey_time'];
$wbtm_bus_start_time     = $values['wbtm_bus_time'];
$wbtm_bus_id             = $values['wbtm_bus_id'];
$extra_bag_quantity      = $values['extra_bag_quantity'];
$wbtm_tp      = $values['wbtm_tp'];

// $check_before_order = wbtm_get_seat_cehck_before_order($wbtm_seats,$wbtm_journey_date,$wbtm_bus_id,$wbtm_start_stops);

// if($check_before_order==0){

$seat ="";
foreach ( $wbtm_seats as $field ) {
      // $item->add_meta_data( __( esc_attr($field['wbtm_seat_name'])));
  $seat .= $field['wbtm_seat_name'].",";
} 
// .$seat =0;
$item->add_meta_data('Seats',$seat);
$item->add_meta_data('Start',$wbtm_start_stops);
$item->add_meta_data('End',$wbtm_end_stops);
$item->add_meta_data('Date',$wbtm_journey_date);
$item->add_meta_data('Time',$wbtm_journey_time);
$item->add_meta_data('Extra Bag',$extra_bag_quantity);
$item->add_meta_data('_wbtm_tp',$wbtm_tp);
$item->add_meta_data('_bus_id',$wbtm_bus_id);
$item->add_meta_data('_btime',$wbtm_bus_start_time);
$item->add_meta_data('_wbtm_passenger_info',$wbtm_passenger_info);


}

$item->add_meta_data('_wbtm_bus_id',$eid);

// }

}
add_action( 'woocommerce_checkout_create_order_line_item', 'wbtm_add_custom_fields_text_to_order_items', 10, 4 );