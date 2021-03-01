<?php 
    get_header(); 
    the_post();
    global $post, $woocommerce;
    $bus_meta           = get_post_custom(get_the_id());
    if(array_key_exists('wbtm_seat_col', $bus_meta)){  $seat_col = $bus_meta['wbtm_seat_col'][0]; $seat_col_arr       = explode(",",$seat_col); $seat_column   = count($seat_col_arr); }else{ $seat_col = array(); $seat_column   = 0; }    

    if(array_key_exists('wbtm_seat_row', $bus_meta)){  $seat_row = $bus_meta['wbtm_seat_row'][0]; $seat_row_arr       = explode(",",$seat_row); }else{ $seat_row = array(); }

    $next_stops_arr     = get_post_meta(get_the_id(), 'wbtm_bus_next_stops', true);
    $wbtm_bus_bp_stops  = get_post_meta(get_the_id(), 'wbtm_bus_bp_stops', true);
    
    
    
    $count              = 1;
    // $fare   = $bus_meta['wbtm_bus_route_fare'][0];

    $start     = isset( $_GET['bus_start_route'] ) ? sanitize_text_field($_GET['bus_start_route']) : '';
    $end       = isset( $_GET['bus_end_route'] ) ? sanitize_text_field($_GET['bus_end_route']) : '';
    $date      = isset( $_GET['j_date'] ) ? sanitize_text_field($_GET['j_date']) : date('Y-m-d');
    $term      = get_the_terms(get_the_id(),'wbtm_bus_cat');
    $price_arr = get_post_meta(get_the_id(),'wbtm_bus_prices',true);  


$wbtm_bus_on = array();
$wbtm_bus_on_dates = get_post_meta(get_the_id(),'wbtm_bus_on_dates',true); 



if($seat_column==4){
    $seat_style = 2;
}elseif ($seat_column==3) {
    # code...
    $seat_style = 1;
}else{
    $seat_style = 999;
}
    ?>
    <div class="wbtm-content-wrapper">
        <?php do_action( 'woocommerce_before_single_product' ); ?>
        <div class="bus-details">
            <div class="bus-thumbnail">
                <?php the_post_thumbnail('full'); ?>
            </div>
            <div class="bus-details-info">
                <h2><?php the_title(); ?></h2>
                <h3><?php echo $term[0]->name; ?></h3>
                <?php the_content(); ?>
                <p><strong>
                 <?php echo bus_get_option('wbtm_bus_no_text', 'label_setting_sec') ? bus_get_option('wbtm_bus_no_text', 'label_setting_sec') : _e('Bus No:','bus-ticket-booking-with-seat-reservation'); ?>   
                </strong> <?php echo get_post_meta(get_the_id(),'wbtm_bus_no',true); ?></p>
                <p><strong>
                 <?php echo bus_get_option('wbtm_total_seat_text', 'label_setting_sec') ? bus_get_option('wbtm_total_seat_text', 'label_setting_sec') : _e('Total Seat:','bus-ticket-booking-with-seat-reservation'); ?>     
                </strong><?php echo get_post_meta(get_the_id(),'wbtm_total_seat',true); ?> </p>

                <?php 
                // if(array_key_exists('wbtm_seat_rows', $values) 
                if(array_key_exists('wbtm_seat_rows', $bus_meta) && $bus_meta['show_boarding_points'][0]!='yes'){ ?>
                <div class="bus-route-details">
                    <div class="bus-route-list">
                        <h6><?php echo bus_get_option('wbtm_boarding_points_text', 'label_setting_sec') ? bus_get_option('wbtm_boarding_points_text', 'label_setting_sec') : _e('Boarding Points','bus-ticket-booking-with-seat-reservation'); ?>   

                        </h6>
                        <ul>
                            <?php
                            $start_stops = get_post_meta(get_the_id(),'wbtm_bus_bp_stops',true);
                            // print_r($start_stops);
                            foreach ($start_stops as $_start_stops) {
                                # code...
                                echo "<li>".$_start_stops['wbtm_bus_bp_stops_name']."</li>";
                                $vatija[] = $_start_stops['wbtm_bus_bp_stops_name'];
                            }
                            ?>                            
                        </ul>
                    </div>
                    <div class="bus-route-list">
                        <h6>
                          <?php echo bus_get_option('wbtm_dropping_points_text', 'label_setting_sec') ? bus_get_option('wbtm_dropping_points_text', 'label_setting_sec') : _e('Dropping Points','bus-ticket-booking-with-seat-reservation');?>  
                        </h6>
                        <ul>
                            <?php
                            $end_stops = get_post_meta(get_the_id(),'wbtm_bus_next_stops',true);
                            // print_r($end_stops);
                            foreach ($end_stops as $_end_stops) {
                                # code...
                                echo "<li>".$_end_stops['wbtm_bus_next_stops_name']."</li>";
                            }
                            ?>                            
                        </ul>                        
                    </div>
                </div>

<?php } ?>


            </div>
        </div>
<div class="bus-single-search-form">
<form action="" method="get">
    <?php 
if(isset($_GET['bus_start_route'])){
    $bus_start = strip_tags($_GET['bus_start_route']);
}else{
    $bus_start = "";
}
if(isset($_GET['bus_end_route'])){
    $bus_end = strip_tags($_GET['bus_end_route']);
}else{
    $bus_end = "";
}
    ?>
    <ul class="search-li">
        <li>
            <label for="boarding_point">
                <?php echo bus_get_option('wbtm_boarding_points_text', 'label_setting_sec') ? bus_get_option('wbtm_boarding_points_text', 'label_setting_sec') : _e('Boarding Points','bus-ticket-booking-with-seat-reservation'); ?> 
                    <select name="bus_start_route" id="boarding_point" required>
                            <option value=""><?php _e('Select Boarding Point','bus-ticket-booking-with-seat-reservation'); ?></option>
                        <?php 
                            foreach ($wbtm_bus_bp_stops as $_start_stops) {
                                # code...
                                ?>
                                <option name="<?php echo $brs = $_start_stops['wbtm_bus_bp_stops_name']; ?>" <?php if($brs==$bus_start){ echo 'selected'; } ?>><?php echo $_start_stops['wbtm_bus_bp_stops_name']; ?></option>
                                <?php
                            }
                        ?>
                    </select>
            </label>            
        </li>
        <li> 
            <label for="drp_point">
               <?php echo bus_get_option('wbtm_dropping_points_text', 'label_setting_sec') ? bus_get_option('wbtm_dropping_points_text', 'label_setting_sec') : _e('Dropping Points','bus-ticket-booking-with-seat-reservation'); ?> 
                    <select name="bus_end_route" id="drp_point" required>
                        <option value=""><?php _e('Select Drop Off Point','bus-ticket-booking-with-seat-reservation'); ?></option>
                        <?php 
                            foreach ($next_stops_arr as $_end_stops) {
                                # code...
                                ?>
                                <option name="<?php echo $brd = $_end_stops['wbtm_bus_next_stops_name']; ?>" <?php if($brd==$bus_end){ echo 'selected'; } ?>><?php echo $_end_stops['wbtm_bus_next_stops_name']; ?></option>
                                <?php
                            }
                        ?>
                    </select>
            </label>              
        </li>
        <li>
<?php 
if(!empty($wbtm_bus_on_dates)){
    echo '<span style=font-size:14px;>Departure Date';
    echo '<select name="j_date" id="on_point">';
    foreach ($wbtm_bus_on_dates as $value) {
        $custom_journey_date = date( 'Y-m-d', strtotime( $value['wbtm_on_date_name']));
        $today = date('Y-m-d');
        if($today<$custom_journey_date){
        echo '<option value='.$value['wbtm_on_date_name'].'>'.date_i18n( 'D, jS F, Y', strtotime( $value['wbtm_on_date_name'])).'</option>';
        }
    }
    echo '</select>';
}else{
?>



            <label for="j_date">
                <?php echo bus_get_option('wbtm_select_journey_date_text', 'label_setting_sec') ? bus_get_option('wbtm_select_journey_date_text', 'label_setting_sec') : _e('Select Journey Date','bus-ticket-booking-with-seat-reservation'); ?> 
                    <input type="text" id='j_date' name='j_date' class="text" value='<?php echo $date; ?>' required>
            </label>
<?php } ?>            
        </li>
        <li>
        <button type="submit">
        <?php echo bus_get_option('wbtm_search_text', 'label_setting_sec') ? bus_get_option('wbtm_search_text', 'label_setting_sec') : _e('Search','bus-ticket-booking-with-seat-reservation'); ?>     

        </button>
        </li>
    </ul>
    </form>    
</div>
    <?php 
if( isset( $_GET['j_date'] ) ) { 
$date           = strip_tags($_GET['j_date']);
$the_day        = date('D', strtotime($date));
$od_name        = 'od_'.$the_day;
$od_start_date  = get_post_meta(get_the_id(),'wbtm_od_start',true);  
$od_end_date    = get_post_meta(get_the_id(),'wbtm_od_end',true);
$od_range       = wbtm_check_od_in_range($od_start_date, $od_end_date, $date);
$oday           = get_post_meta(get_the_id(),$od_name,true); 

$wbtm_bus_on = array();
$wbtm_bus_on_dates = get_post_meta(get_the_id(),'wbtm_bus_on_dates',true); 


if(!empty($wbtm_bus_on_dates)){
    foreach ($wbtm_bus_on_dates as $value) {
        $wbtm_bus_on[] = $value['wbtm_on_date_name'];
    }
    $od_range   = 'no';
    $oday       = 'no';
}
if($od_range =='no'){
if($oday !='yes'){

     wbtm_bus_seat_plan(wbtm_get_this_bus_seat_plan(),$start,$date);
     $bus_bp_array = get_post_meta(get_the_id(),'wbtm_bus_bp_stops',true);
     $bus_dp_array = get_post_meta(get_the_id(),'wbtm_bus_next_stops',true);
     $bp_time = wbtm_get_bus_start_time($start, $bus_bp_array);
     $dp_time = wbtm_get_bus_end_time($end, $bus_dp_array);

    if(wbtm_buffer_time_check($bp_time,$date) == 'yes'){ 
  ?>
       <div class="bus-info-sec wbtm-search-result-list ">
            <?php 
            $price_arr = get_post_meta(get_the_id(),'wbtm_bus_prices',true);
            $fare = wbtm_get_bus_price($start,$end, $price_arr);
            ?>
            <form action="" method='post'>
                <div class="top-search-section">                    
                    <div class="leaving-list">
                        <input type="hidden"  name='journey_date' class="text" value='<?php echo $date; ?>'/>
                        <input type="hidden" name='start_stops' value="<?php echo $start; ?>" class="hidden"/>
                        <input type='hidden' value='<?php echo $end; ?>' name='end_stops'/>
                        <h6>
                            <?php echo bus_get_option('wbtm_route_text', 'label_setting_sec') ? bus_get_option('wbtm_route_text', 'label_setting_sec') : _e('Route','bus-ticket-booking-with-seat-reservation'); ?>   
                        </h6>
                        <div class="selected_route">
                            <?php printf( '<span>%s <i class="fa fa-long-arrow-right"></i> %s<span>', $start, $end ); ?>
                             (<?php echo get_woocommerce_currency_symbol(); ?><?php echo wbtm_get_bus_price($start,$end, $price_arr); ?>)
                        </div>
                    </div>                    
                    <div class="leaving-list">
                        <h6><?php echo bus_get_option('wbtm_date_text', 'label_setting_sec') ? bus_get_option('wbtm_date_text', 'label_setting_sec') : _e('Date','bus-ticket-booking-with-seat-reservation'); ?></h6>
                        <div class="selected_date">
                            <?php printf( '<span>%s</span>', date( 'jS F, Y', strtotime( $date ) ) ); ?>
                        </div>
                    </div>   
                    <div class="leaving-list">
                        <h6>
                       <?php echo bus_get_option('wbtm_start_arrival_time_text', 'label_setting_sec') ? bus_get_option('wbtm_start_arrival_time_text', 'label_setting_sec') : _e('Start & Arrival Time','bus-ticket-booking-with-seat-reservation'); ?> 
                        </h6>
                        <div class="selected_date">
                            <?php  
                                $bus_bp_array = get_post_meta(get_the_id(),'wbtm_bus_bp_stops',true);
                                $bus_dp_array = get_post_meta(get_the_id(),'wbtm_bus_next_stops',true);
                                $bp_time = wbtm_get_bus_start_time($start, $bus_bp_array);
                                $dp_time = wbtm_get_bus_end_time($end, $bus_dp_array);
                                echo date('h:i A', strtotime($bp_time)).' <i class="fa fa-long-arrow-right"></i> '.date('h:i A', strtotime($dp_time));
                            ?>
                        <input type="hidden" value="<?php echo date('h:i A', strtotime($bp_time)); ?>" name="user_start_time" id='user_start_time<?php echo get_the_id().wbtm_make_id($date); ?>'>
                        <input type="hidden" name="bus_start_time" value="<?php echo date('h:i A', strtotime($bp_time)); ?>" id='bus_start_time'>                            
                        </div>
                    </div>                                    
                </div>
                <div class="seat-selected-list-fare">
                    <table class="selected-seat-list<?php echo get_the_id().wbtm_make_id($date); ?>">
                        <tr class='list_head<?php echo get_the_id().wbtm_make_id($date); ?>'>
                            <th><?php echo bus_get_option('wbtm_seat_no_text', 'label_setting_sec') ? bus_get_option('wbtm_seat_no_text', 'label_setting_sec') : _e('Seat No','bus-ticket-booking-with-seat-reservation'); ?></th>
                            <th><?php echo bus_get_option('wbtm_fare_text', 'label_setting_sec') ? bus_get_option('wbtm_fare_text', 'label_setting_sec') : _e('Fare','bus-ticket-booking-with-seat-reservation'); 
                             ?></th>
                            <th><?php echo bus_get_option('wbtm_remove_text', 'label_setting_sec') ? bus_get_option('wbtm_remove_text', 'label_setting_sec') : _e('Remove','bus-ticket-booking-with-seat-reservation'); 
                             ?></th>
                        </tr>
                        <tr>
                            <td align="center"><?php echo bus_get_option('wbtm_total_text', 'label_setting_sec') ? bus_get_option('wbtm_total_text', 'label_setting_sec') : _e('Total','bus-ticket-booking-with-seat-reservation'); 
                             ?><span id='total_seat<?php echo get_the_id().wbtm_make_id($date); ?>_booked'></span><input type="hidden" value="" id="tq<?php echo get_the_id().wbtm_make_id($date); ?>" name='total_seat' class="number"/></td>
                            
                            <td align="center"><input type="hidden" value="" id="tfi<?php echo get_the_id().wbtm_make_id($date); ?>" class="number"/><span id="totalFare<?php echo get_the_id().wbtm_make_id($date); ?>"></span></td><td></td>
                        </tr>
                    </table>
                    <div id="divParent<?php echo get_the_id().wbtm_make_id($date); ?>"></div>
                    <input type="hidden" name="bus_id" value="<?php echo get_the_id(); ?>">
                    <button id='bus-booking-btn<?php echo get_the_id().wbtm_make_id($date); ?>' type="submit" name="add-to-cart" value="<?php echo esc_attr(get_the_id()); ?>" class="single_add_to_cart_button button alt btn-mep-event-cart">
                    
                    <?php echo bus_get_option('wbtm_book_now_text', 'label_setting_sec') ? bus_get_option('wbtm_book_now_text', 'label_setting_sec') : _e('Book Now','bus-ticket-booking-with-seat-reservation'); 
                     ?>
                        
                    </button>
                </div>
            </form>
        </div>

    <?php } }} }?>
</div>
</div>
<?php 
$uid = get_the_id().wbtm_make_id($date);
wbtm_seat_booking_js($uid,$fare);
?>  
<?php get_footer(); ?>