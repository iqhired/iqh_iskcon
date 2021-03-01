<?php
if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.


add_shortcode( 'wbtm-bus-list', 'wbtm_bus_list' );
function wbtm_bus_list($atts, $content=null){
        $defaults = array(
            "cat"                   => "0",
            "show"                  => "20",
        );
        $params                     = shortcode_atts($defaults, $atts);
        $cat                        = $params['cat'];
        $show                       = $params['show'];
ob_start();
 

$paged = get_query_var("paged")?get_query_var("paged"):1;
if($cat>0){
     $args_search_qqq = array (
                     'post_type'        => array( 'wbtm_bus' ),
                     'paged'            => $paged,
                     'posts_per_page'   => $show,
                      'tax_query'       => array(
                                array(
                                        'taxonomy'  => 'wbtm_bus_cat',
                                        'field'     => 'term_id',
                                        'terms'     => $cat
                                    )
                        )

                );
 }else{
     $args_search_qqq = array (
                     'post_type'        => array( 'wbtm_bus' ),
                     'paged'             => $paged,
                     'posts_per_page'   => $show

                );  
 }

    $loop = new WP_Query( $args_search_qqq );
?>
<div class="wbtm-bus-list-sec">
    
<?php 
    while ($loop->have_posts()) {
    $loop->the_post(); 
    $bp_arr = get_post_meta(get_the_id(),'wbtm_bus_bp_stops',true); 
    $dp_arr = get_post_meta(get_the_id(),'wbtm_bus_next_stops',true);
    $price_arr = get_post_meta(get_the_id(),'wbtm_bus_prices',true);
    $total_dp = count($dp_arr)-1;
    $term = get_the_terms(get_the_id(),'wbtm_bus_cat');
?>

<div class="wbtm-bus-lists">
    <div class="bus-thumb">
        <?php the_post_thumbnail('full'); ?>
    </div>
    <h2><?php the_title(); ?></h2>
    <ul>
        <li><strong>
        <?php echo bus_get_option('wbtm_type_text', 'label_setting_sec') ? bus_get_option('wbtm_type_text', 'label_setting_sec') : _e('Type:','bus-ticket-booking-with-seat-reservation'); ?> 
            
        </strong> <?php echo $term[0]->name; ?></li>
        <li><strong>
          <?php echo bus_get_option('wbtm_bus_no_text', 'label_setting_sec') ? bus_get_option('wbtm_bus_no_text', 'label_setting_sec') : _e('Bus No:','bus-ticket-booking-with-seat-reservation'); ?>  

        </strong> <?php echo get_post_meta(get_the_id(),'wbtm_bus_no',true); ?></li>
        <li><strong>
          <?php echo bus_get_option('wbtm_from_text', 'label_setting_sec') ? bus_get_option('wbtm_from_text', 'label_setting_sec') : _e('Start From:','bus-ticket-booking-with-seat-reservation'); ?>   
        </strong> <?php echo $start = $bp_arr[0]['wbtm_bus_bp_stops_name'];; ?> </li>
        <li><strong>
        <?php echo bus_get_option('wbtm_end_text', 'label_setting_sec') ? bus_get_option('wbtm_end_text', 'label_setting_sec') : _e('End','bus-ticket-booking-with-seat-reservation'); ?>    
         </strong> <?php echo $end = $dp_arr[$total_dp]['wbtm_bus_next_stops_name'];; ?> 
        </li>
        <li><strong><?php echo bus_get_option('wbtm_fare_text', 'label_setting_sec') ? bus_get_option('wbtm_fare_text', 'label_setting_sec') : _e('Fare','bus-ticket-booking-with-seat-reservation'); ?></strong> <?php echo get_woocommerce_currency_symbol().wbtm_get_bus_price($start,$end, $price_arr); ?> 
        </li>
    </ul>
    <a href="<?php the_permalink(); ?>" class='btn wbtm-bus-list-btn'>
    <?php echo bus_get_option('wbtm_book_now_text', 'label_setting_sec') ? bus_get_option('wbtm_book_now_text', 'label_setting_sec') : _e('Book Now','bus-ticket-booking-with-seat-reservation'); ?>    
    </a>
</div>
<?php
}
?>
<div class="row">
    <div class="col-md-12"><?php
    $pargs = array(
        "current"=>$paged,
        "total"=>$loop->max_num_pages
    );
    echo "<div class='pagination-sec'>".paginate_links($pargs)."</div>";
    ?>  
    </div>
</div>
</div>
<?php
$content = ob_get_clean();
return $content;
}




add_shortcode( 'wbtm-bus-search', 'wbtm_bus_search' );
function wbtm_bus_search($atts, $content=null){
        $defaults = array(
            "cat"                   => "0"
        );
        $params                     = shortcode_atts($defaults, $atts);
        $cat                        = $params['cat'];
ob_start();

$start  = isset( $_GET['bus_start_route'] ) ? strip_tags($_GET['bus_start_route']) : '';
$end    = isset( $_GET['bus_end_route'] ) ? strip_tags($_GET['bus_end_route']) : '';
$date   = isset( $_GET['j_date'] ) ? strip_tags($_GET['j_date']) : date('Y-m-d');
$rdate  = isset( $_GET['r_date'] ) ? strip_tags($_GET['r_date']) : date('Y-m-d');
$today = date('Y-m-d');
$the_day = date('D', strtotime($date));
$od_name = 'od_'.$the_day;
?>
<?php do_action( 'woocommerce_before_single_product' ); ?>
<div class="wbtm-search-form-sec">
    <form action="" method="get">
   <?php wbtm_bus_search_fileds($start,$end,$date,$rdate); //do_action('wbtm_search_fields'); ?>
    </form>
    
</div>



<div class="wbtm-search-result-list">
<?php 
if(isset($_GET['bus_start_route']) && ($_GET['bus_end_route']) && ($_GET['j_date'])){
    

$date = isset($_GET['j_date']) ? $_GET['j_date'] : date('Y-m-d');
$tab_date = isset($_GET['tab_date']) ? $_GET['tab_date'] : $date;




// echo $prev_date = date('Y-m-d', strtotime($date .' -1 day'));
$next_date = date('Y-m-d', strtotime($tab_date .' +1 day'));
$day_after_next_date = date('Y-m-d', strtotime($tab_date .' +2 day'));
$day_after_day_after_next_date = date('Y-m-d', strtotime($tab_date .' +3 day'));

?>
<div class='wbtm_next_days_tab'>
<ul>

    <li class=<?php if($date == $tab_date){ echo 'current-tab'; } ?>><a href="<?php echo get_site_url(); ?>/bus-search-list/?bus_start_route=<?php echo strip_tags($_GET['bus_start_route']); ?>&bus_end_route=<?php echo strip_tags($_GET['bus_end_route']); ?>&j_date=<?php echo $tab_date; ?>&r_date=<?php echo strip_tags($_GET['r_date']); ?>&bus-r=<?php echo strip_tags($_GET['bus-r']); ?>&tab_date=<?php echo $tab_date; ?>"><?php echo date('D, d M Y', strtotime($tab_date)) ?></a></li>

    <li class=<?php if($date == $next_date){ echo 'current-tab'; } ?>><a href="<?php echo get_site_url(); ?>/bus-search-list/?bus_start_route=<?php echo strip_tags($_GET['bus_start_route']); ?>&bus_end_route=<?php echo strip_tags($_GET['bus_end_route']); ?>&j_date=<?php echo $next_date; ?>&r_date=<?php echo strip_tags($_GET['r_date']); ?>&bus-r=<?php echo strip_tags($_GET['bus-r']); ?>&tab_date=<?php echo $tab_date; ?>"><?php echo date('D, d M Y', strtotime($next_date)) ?></a></li>

    <li class=<?php if($date == $day_after_next_date){ echo 'current-tab'; } ?>><a href="<?php echo get_site_url(); ?>/bus-search-list/?bus_start_route=<?php echo strip_tags($_GET['bus_start_route']); ?>&bus_end_route=<?php echo strip_tags($_GET['bus_end_route']); ?>&j_date=<?php echo $day_after_next_date; ?>&r_date=<?php echo strip_tags($_GET['r_date']); ?>&bus-r=<?php echo strip_tags($_GET['bus-r']); ?>&tab_date=<?php echo $tab_date; ?>"><?php echo date('D, d M Y', strtotime($day_after_next_date)) ?></a></li>

    <li class=<?php if($date == $day_after_day_after_next_date){ echo 'current-tab'; } ?>><a href="<?php echo get_site_url(); ?>/bus-search-list/?bus_start_route=<?php echo strip_tags($_GET['bus_start_route']); ?>&bus_end_route=<?php echo strip_tags($_GET['bus_end_route']); ?>&j_date=<?php echo $day_after_day_after_next_date; ?>&r_date=<?php echo strip_tags($_GET['r_date']); ?>&bus-r=<?php echo strip_tags($_GET['bus-r']); ?>&tab_date=<?php echo $tab_date; ?>"><?php echo date('D, d M Y', strtotime($day_after_day_after_next_date)) ?></a></li>
</ul>
</div>


 <div class="selected_route">
     <strong>
     <?php echo bus_get_option('wbtm_route_text', 'label_setting_sec') ? bus_get_option('wbtm_route_text', 'label_setting_sec') : _e('Route','bus-ticket-booking-with-seat-reservation'); ?>
    <?php printf( '<span>%s <i class="fa fa-long-arrow-right"></i> %s<span>', $start, $end ); ?> <strong>
     <?php echo bus_get_option('wbtm_date_text', 'label_setting_sec') ? bus_get_option('wbtm_date_text', 'label_setting_sec') : _e('Date:','bus-ticket-booking-with-seat-reservation'); ?>
        
    </strong> <?php echo date('D, d M Y', strtotime($date)); ?> 
</div>
<table class="bus-search-list">
    <thead>
        <tr>
            <th class='wbtm-mobile-hide'></th>
            <th><?php echo bus_get_option('wbtm_bus_name_text', 'label_setting_sec') ? bus_get_option('wbtm_bus_name_text', 'label_setting_sec') : _e('Bus Name','bus-ticket-booking-with-seat-reservation'); ?>  
            </th>
            <th class='wbtm-mobile-hide'><?php echo bus_get_option('wbtm_departing_text', 'label_setting_sec') ? bus_get_option('wbtm_departing_text', 'label_setting_sec') : _e('DEPARTING','bus-ticket-booking-with-seat-reservation'); ?> 
            </th> 
            <th class='wbtm-mobile-hide'><?php echo bus_get_option('wbtm_coach_no_text', 'label_setting_sec') ? bus_get_option('wbtm_coach_no_text', 'label_setting_sec') : _e('COACH NO','bus-ticket-booking-with-seat-reservation'); ?>  
            </th>
            <th class='wbtm-mobile-hide'><?php echo bus_get_option('wbtm_starting_text', 'label_setting_sec') ? bus_get_option('wbtm_starting_text', 'label_setting_sec') : _e('STARTING','bus-ticket-booking-with-seat-reservation'); ?> 
            </th>
            <th class='wbtm-mobile-hide'><?php echo bus_get_option('wbtm_end_text', 'label_setting_sec') ? bus_get_option('wbtm_end_text', 'label_setting_sec') : _e('END','bus-ticket-booking-with-seat-reservation'); ?> 
            </th>
            <th><?php echo bus_get_option('wbtm_fare_text', 'label_setting_sec') ? bus_get_option('wbtm_fare_text', 'label_setting_sec') : _e('FARE','bus-ticket-booking-with-seat-reservation'); ?> 
            </th>
            <th class='wbtm-mobile-hide'><?php echo bus_get_option('wbtm_type_text', 'label_setting_sec') ? bus_get_option('wbtm_type_text', 'label_setting_sec') : _e('TYPE','bus-ticket-booking-with-seat-reservation'); ?> 
            </th>
            <th class='wbtm-mobile-hide'><?php echo bus_get_option('wbtm_arrival_text', 'label_setting_sec') ? bus_get_option('wbtm_arrival_text', 'label_setting_sec') : _e('ARRIVAL','bus-ticket-booking-with-seat-reservation'); ?> 
            </th>
            <th><?php echo bus_get_option('wbtm_seats_available_text', 'label_setting_sec') ? bus_get_option('wbtm_seats_available_text', 'label_setting_sec') : _e('SEATS AVAILABLE','bus-ticket-booking-with-seat-reservation'); ?> 
            </th>
            <th><?php echo bus_get_option('wbtm_view_text', 'label_setting_sec') ? bus_get_option('wbtm_view_text', 'label_setting_sec') : _e('VIEW','bus-ticket-booking-with-seat-reservation'); ?> 
            </th>
        </tr>
    </thead>
    <tbody>
<?php

         $args_search_qqq = array (
                     'post_type'        => array( 'wbtm_bus' ),
                     'posts_per_page'   => -1,
                     'order'             => 'ASC',
                     'orderby'           => 'meta_value', 
                     'meta_key'          => 'wbtm_bus_start_time',                      
                     'meta_query'    => array(
                        'relation' => 'AND',
                        array(
                            'key'       => 'wbtm_bus_bp_stops',
                            'value'     => $start,
                            'compare'   => 'LIKE',
                        ),
                      
                        array(
                            'key'       => 'wbtm_bus_next_stops',
                            'value'     => $end,
                            'compare'   => 'LIKE',
                        ),
                    )                     

                );  
 

    $loop = new WP_Query($args_search_qqq);
    while ($loop->have_posts()) {
    $loop->the_post();
    $values = get_post_custom( get_the_id() );
    $term = get_the_terms(get_the_id(),'wbtm_bus_cat');
    // print_r($term);
    $total_seat = $values['wbtm_total_seat'][0];
    $sold_seat = wbtm_get_available_seat(get_the_id(),$date);
    $available_seat = ($total_seat - $sold_seat);
    $price_arr = get_post_meta(get_the_id(),'wbtm_bus_prices',true);  
    $bus_bp_array = get_post_meta(get_the_id(),'wbtm_bus_bp_stops',true);
    $bus_dp_array = get_post_meta(get_the_id(),'wbtm_bus_next_stops',true); 
    $bp_time = wbtm_get_bus_start_time($start, $bus_bp_array);
    $dp_time = wbtm_get_bus_end_time($end, $bus_dp_array);
    $od_start_date  = get_post_meta(get_the_id(),'wbtm_od_start',true);  
    $od_end_date    = get_post_meta(get_the_id(),'wbtm_od_end',true);
    $od_range                               = wbtm_check_od_in_range($od_start_date, $od_end_date, $date);
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
if(wbtm_buffer_time_check($bp_time,$date) == 'yes'){
  if(in_array($date,$wbtm_bus_on)){      
 ?>
        
        <tr class="<?php echo wbtm_find_product_in_cart(get_the_id()); ?>">
            <td class='wbtm-mobile-hide'><div class="bus-thumb-list"><?php the_post_thumbnail('thumb'); ?></div></td>
            <td><?php the_title();?></td>
            <td class='wbtm-mobile-hide'><?php echo $start; ?></td>
            <td class='wbtm-mobile-hide'><?php echo $values['wbtm_bus_no'][0]; ?></td>
            <td class='wbtm-mobile-hide'><?php echo date('h:i A', strtotime($bp_time)); ?></td>
            <td class='wbtm-mobile-hide'><?php echo $end; ?></td>
            <td ><?php echo get_woocommerce_currency_symbol(); ?><?php echo wbtm_get_bus_price($start,$end, $price_arr); ?></td>
            <td class='wbtm-mobile-hide'><?php  if(!empty($term)){ echo $term[0]->name;} ?></td>
            <td class='wbtm-mobile-hide'><?php echo date('h:i A', strtotime($dp_time)); ?></td>
            <td align="center"><span class='available-seat'><?php echo $available_seat; ?></span></td>
            <td><button id="view_panel_<?php echo get_the_id().wbtm_make_id($date); ?>" class='view-seat-btn'>
            <?php echo bus_get_option('wbtm_view_seats_text', 'label_setting_sec') ? bus_get_option('wbtm_view_seats_text', 'label_setting_sec') : _e('View Seats','bus-ticket-booking-with-seat-reservation'); ?>    
            </button></td>
        </tr>


        <tr style='display: none;' class="admin-bus-details" id="admin-bus-details<?php echo get_the_id().wbtm_make_id($date); ?>">
            <td colspan="11">
        <?php
            $bus_meta           = get_post_custom(get_the_id());
            $seat_col           = $bus_meta['wbtm_seat_col'][0];
            $seat_row           = $bus_meta['wbtm_seat_row'][0];
            $next_stops_arr     =  get_post_meta(get_the_id(), 'wbtm_bus_next_stops', true);
            $wbtm_bus_bp_stops  =  get_post_meta(get_the_id(), 'wbtm_bus_bp_stops', true);
            $seat_col_arr       = explode(",",$seat_col);
            $seat_row_arr       = explode(",",$seat_row);
            $seat_column        = count($seat_col_arr);
            $count              = 1;
            // $fare   = $bus_meta['wbtm_bus_route_fare'][0];

            $start  = isset( $_GET['bus_start_route'] ) ? sanitize_text_field($_GET['bus_start_route']) : '';
            $end    = isset( $_GET['bus_end_route'] ) ? sanitize_text_field($_GET['bus_end_route']) : '';
            $date   = isset( $_GET['j_date'] ) ? sanitize_text_field($_GET['j_date']) : date('Y-m-d');
            $term = get_the_terms(get_the_id(),'wbtm_bus_cat');
            $price_arr = get_post_meta(get_the_id(),'wbtm_bus_prices',true);  

            if($seat_column==4){
                $seat_style = 2;
            }elseif ($seat_column==3) {
                # code...
                $seat_style = 1;
            }else{
                $seat_style = 999;
            }
    ?>
<div class="wbtm-content-wrappers">
    <div>
    <?php wbtm_bus_seat_plan(wbtm_get_this_bus_seat_plan(),$start,$date); ?>
       <div class="bus-info-sec">
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
                        <h6><?php echo bus_get_option('wbtm_date_text', 'label_setting_sec') ? bus_get_option('wbtm_date_text', 'label_setting_sec') : _e('Date','bus-ticket-booking-with-seat-reservation'); ?>
                        </h6>
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
                            <th>
                            <?php echo bus_get_option('wbtm_seat_no_text', 'label_setting_sec') ? bus_get_option('wbtm_seat_no_text', 'label_setting_sec') : _e('Seat No','bus-ticket-booking-with-seat-reservation'); 
                             ?>    
                            </th>
                            <th>
                             <?php echo bus_get_option('wbtm_fare_text', 'label_setting_sec') ? bus_get_option('wbtm_fare_text', 'label_setting_sec') : _e('Fare','bus-ticket-booking-with-seat-reservation'); 
                             ?>       
                            </th>
                            <th>
                              <?php echo bus_get_option('wbtm_remove_text', 'label_setting_sec') ? bus_get_option('wbtm_remove_text', 'label_setting_sec') : _e('Remove','bus-ticket-booking-with-seat-reservation'); 
                             ?>      
                            </th>
                        </tr>
                        <tr>
                            <td align="center">
                             <?php echo bus_get_option('wbtm_total_text', 'label_setting_sec') ? bus_get_option('wbtm_total_text', 'label_setting_sec') : _e('Total','bus-ticket-booking-with-seat-reservation'); 
                             ?>     
                             <span id='total_seat<?php echo get_the_id().wbtm_make_id($date); ?>_booked'></span><input type="hidden" value="" id="tq<?php echo get_the_id().wbtm_make_id($date); ?>" name='total_seat' class="number"/></td>
                            
                            <td align="center"><input type="hidden" value="" id="tfi<?php echo get_the_id().wbtm_make_id($date); ?>" class="number"/><span id="totalFare<?php echo get_the_id().wbtm_make_id($date); ?>"></span></td><td></td>
                        </tr>
                    </table>

                    <div id="divParent<?php echo get_the_id().wbtm_make_id($date); ?>"></div>
                    
                    <input type="hidden" name="bus_id" value="<?php echo get_the_id(); ?>">
                   
                    <button id='bus-booking-btn<?php echo get_the_id().wbtm_make_id($date); ?>' type="submit" name="add-to-cart" value="<?php echo esc_attr(get_the_id()); ?>" class="single_add_to_cart_button button alt btn-mep-event-cart">
                    <?php echo bus_get_option('wbtm_book_now_text', 'label_setting_sec') ? bus_get_option('wbtm_book_now_text', 'label_setting_sec') : _e('Book Now','bus-ticket-booking-with-seat-reservation'); ?>         
                    </button>

                </div>
            </form>
        </div>




    </div>

<?php 
$uid = get_the_id().wbtm_make_id($date);
wbtm_seat_booking_js($uid,$fare);
?>  

</div>
</td>
        </tr>




   <?php 
}else{
    if(empty($wbtm_bus_on_dates)){
    ?>
        <tr class="<?php echo wbtm_find_product_in_cart(get_the_id()); ?>">
            <td class='wbtm-mobile-hide'><div class="bus-thumb-list"><?php the_post_thumbnail('thumb'); ?></div></td>
            <td><?php the_title();?></td>
            <td class='wbtm-mobile-hide'><?php echo $start; ?></td>
            <td class='wbtm-mobile-hide'><?php echo $values['wbtm_bus_no'][0]; ?></td>
            <td class='wbtm-mobile-hide'><?php echo date('h:i A', strtotime($bp_time)); ?></td>
            <td class='wbtm-mobile-hide'><?php echo $end; ?></td>
            <td ><?php echo get_woocommerce_currency_symbol(); ?><?php echo wbtm_get_bus_price($start,$end, $price_arr); ?></td>
            <td class='wbtm-mobile-hide'><?php  if(!empty($term)){ echo $term[0]->name;} ?></td>
            <td class='wbtm-mobile-hide'><?php echo date('h:i A', strtotime($dp_time)); ?></td>
            <td align="center"><span class='available-seat'><?php echo $available_seat; ?></span></td>
            <td><button id="view_panel_<?php echo get_the_id().wbtm_make_id($date); ?>" class='view-seat-btn'>
            <?php echo bus_get_option('wbtm_view_seats_text', 'label_setting_sec') ? bus_get_option('wbtm_view_seats_text', 'label_setting_sec') : _e('View Seats','bus-ticket-booking-with-seat-reservation'); ?>    
            </button></td>
        </tr>


        <tr style='display: none;' class="admin-bus-details" id="admin-bus-details<?php echo get_the_id().wbtm_make_id($date); ?>">
            <td colspan="11">
        <?php
            $bus_meta           = get_post_custom(get_the_id());
            $seat_col           = $bus_meta['wbtm_seat_col'][0];
            $seat_row           = $bus_meta['wbtm_seat_row'][0];
            $next_stops_arr     =  get_post_meta(get_the_id(), 'wbtm_bus_next_stops', true);
            $wbtm_bus_bp_stops  =  get_post_meta(get_the_id(), 'wbtm_bus_bp_stops', true);
            $seat_col_arr       = explode(",",$seat_col);
            $seat_row_arr       = explode(",",$seat_row);
            $seat_column        = count($seat_col_arr);
            $count              = 1;
            // $fare   = $bus_meta['wbtm_bus_route_fare'][0];

            $start  = isset( $_GET['bus_start_route'] ) ? sanitize_text_field($_GET['bus_start_route']) : '';
            $end    = isset( $_GET['bus_end_route'] ) ? sanitize_text_field($_GET['bus_end_route']) : '';
            $date   = isset( $_GET['j_date'] ) ? sanitize_text_field($_GET['j_date']) : date('Y-m-d');
            $term = get_the_terms(get_the_id(),'wbtm_bus_cat');
            $price_arr = get_post_meta(get_the_id(),'wbtm_bus_prices',true);  

            if($seat_column==4){
                $seat_style = 2;
            }elseif ($seat_column==3) {
                # code...
                $seat_style = 1;
            }else{
                $seat_style = 999;
            }
    ?>
<div class="wbtm-content-wrappers">
    <div>
    <?php wbtm_bus_seat_plan(wbtm_get_this_bus_seat_plan(),$start,$date); ?>
       <div class="bus-info-sec">
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
                        <h6><?php echo bus_get_option('wbtm_date_text', 'label_setting_sec') ? bus_get_option('wbtm_date_text', 'label_setting_sec') : _e('Date','bus-ticket-booking-with-seat-reservation'); ?>
                        </h6>
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
                            <th>
                            <?php echo bus_get_option('wbtm_seat_no_text', 'label_setting_sec') ? bus_get_option('wbtm_seat_no_text', 'label_setting_sec') : _e('Seat No','bus-ticket-booking-with-seat-reservation'); 
                             ?>    
                            </th>
                            <th>
                             <?php echo bus_get_option('wbtm_fare_text', 'label_setting_sec') ? bus_get_option('wbtm_fare_text', 'label_setting_sec') : _e('Fare','bus-ticket-booking-with-seat-reservation'); 
                             ?>       
                            </th>
                            <th>
                              <?php echo bus_get_option('wbtm_remove_text', 'label_setting_sec') ? bus_get_option('wbtm_remove_text', 'label_setting_sec') : _e('Remove','bus-ticket-booking-with-seat-reservation'); 
                             ?>      
                            </th>
                        </tr>
                        <tr>
                            <td align="center">
                             <?php echo bus_get_option('wbtm_total_text', 'label_setting_sec') ? bus_get_option('wbtm_total_text', 'label_setting_sec') : _e('Total','bus-ticket-booking-with-seat-reservation'); 
                             ?>     
                             <span id='total_seat<?php echo get_the_id().wbtm_make_id($date); ?>_booked'></span><input type="hidden" value="" id="tq<?php echo get_the_id().wbtm_make_id($date); ?>" name='total_seat' class="number"/></td>
                            
                            <td align="center"><input type="hidden" value="" id="tfi<?php echo get_the_id().wbtm_make_id($date); ?>" class="number"/><span id="totalFare<?php echo get_the_id().wbtm_make_id($date); ?>"></span></td><td></td>
                        </tr>
                    </table>

                    <div id="divParent<?php echo get_the_id().wbtm_make_id($date); ?>"></div>
                    
                    <input type="hidden" name="bus_id" value="<?php echo get_the_id(); ?>">
                   
                    <button id='bus-booking-btn<?php echo get_the_id().wbtm_make_id($date); ?>' type="submit" name="add-to-cart" value="<?php echo esc_attr(get_the_id()); ?>" class="single_add_to_cart_button button alt btn-mep-event-cart">
                    <?php echo bus_get_option('wbtm_book_now_text', 'label_setting_sec') ? bus_get_option('wbtm_book_now_text', 'label_setting_sec') : _e('Book Now','bus-ticket-booking-with-seat-reservation'); ?>         
                    </button>

                </div>
            </form>
        </div>




    </div>

<?php 
$uid = get_the_id().wbtm_make_id($date);
wbtm_seat_booking_js($uid,$fare);
?>  

</div>
</td>
        </tr>



        
    <?php
}
}
}
}
} 
}
// if(!empty($wbtm_bus_on_dates)){ } }

wp_reset_query();

?>
</tbody>
</table>

<?php } 

if(isset($_GET['bus_start_route']) && ($_GET['bus_end_route']) && ($_GET['r_date'])){
    if($rdate>$date){

$the_day = date('D', strtotime($rdate));
$od_name = 'od_'.$the_day;
?>
 <div class="selected_route">
     <strong><?php echo bus_get_option('wbtm_route_text', 'label_setting_sec') ? bus_get_option('wbtm_route_text', 'label_setting_sec') : _e('Route','bus-ticket-booking-with-seat-reservation'); ?></strong>
    <?php printf( '<span>%s <i class="fa fa-long-arrow-right"></i> %s<span>', $end, $start ); ?> <strong><?php echo bus_get_option('wbtm_date_text', 'label_setting_sec') ? bus_get_option('wbtm_date_text', 'label_setting_sec') : _e('Date:','bus-ticket-booking-with-seat-reservation'); ?></strong> <?php echo date('D, d M Y', strtotime($rdate)); ?> 
 </div>
<table class="bus-search-list">
    <thead>
               <tr>
            <th class='wbtm-mobile-hide'></th>
            <th><?php echo bus_get_option('wbtm_bus_name_text', 'label_setting_sec') ? bus_get_option('wbtm_bus_name_text', 'label_setting_sec') : _e('Bus Name','bus-ticket-booking-with-seat-reservation'); ?>  
            </th>
            <th class='wbtm-mobile-hide'><?php echo bus_get_option('wbtm_departing_text', 'label_setting_sec') ? bus_get_option('wbtm_departing_text', 'label_setting_sec') : _e('DEPARTING','bus-ticket-booking-with-seat-reservation'); ?> 
            </th> 
            <th class='wbtm-mobile-hide'><?php echo bus_get_option('wbtm_coach_no_text', 'label_setting_sec') ? bus_get_option('wbtm_coach_no_text', 'label_setting_sec') : _e('COACH NO','bus-ticket-booking-with-seat-reservation'); ?>  
            </th>
            <th class='wbtm-mobile-hide'><?php echo bus_get_option('wbtm_starting_text', 'label_setting_sec') ? bus_get_option('wbtm_starting_text', 'label_setting_sec') : _e('STARTING','bus-ticket-booking-with-seat-reservation'); ?> 
            </th>
            <th class='wbtm-mobile-hide'><?php echo bus_get_option('wbtm_end_text', 'label_setting_sec') ? bus_get_option('wbtm_end_text', 'label_setting_sec') : _e('END','bus-ticket-booking-with-seat-reservation'); ?> 
            </th>
            <th><?php echo bus_get_option('wbtm_fare_text', 'label_setting_sec') ? bus_get_option('wbtm_fare_text', 'label_setting_sec') : _e('FARE','bus-ticket-booking-with-seat-reservation'); ?> 
            </th>
            <th class='wbtm-mobile-hide'><?php echo bus_get_option('wbtm_type_text', 'label_setting_sec') ? bus_get_option('wbtm_type_text', 'label_setting_sec') : _e('TYPE','bus-ticket-booking-with-seat-reservation'); ?> 
            </th>
            <th class='wbtm-mobile-hide'><?php echo bus_get_option('wbtm_arrival_text', 'label_setting_sec') ? bus_get_option('wbtm_arrival_text', 'label_setting_sec') : _e('ARRIVAL','bus-ticket-booking-with-seat-reservation'); ?> 
            </th>
            <th><?php echo bus_get_option('wbtm_seats_available_text', 'label_setting_sec') ? bus_get_option('wbtm_seats_available_text', 'label_setting_sec') : _e('SEATS AVAILABLE','bus-ticket-booking-with-seat-reservation'); ?> 
            </th>
            <th><?php echo bus_get_option('wbtm_view_text', 'label_setting_sec') ? bus_get_option('wbtm_view_text', 'label_setting_sec') : _e('VIEW','bus-ticket-booking-with-seat-reservation'); ?> 
            </th>
        </tr>
    </thead>
    <tbody>
<?php

         $args_search_rrr = array (
                     'post_type'        => array( 'wbtm_bus' ),
                     'posts_per_page'   => -1,
                     'order'             => 'ASC',
                     'orderby'           => 'meta_value', 
                     'meta_key'          => 'wbtm_bus_start_time',                      
                     'meta_query'    => array(
                        'relation' => 'AND',
                        array(
                            'key'       => 'wbtm_bus_bp_stops',
                            'value'     => $end,
                            'compare'   => 'LIKE',
                        ),
                      
                        array(
                            'key'       => 'wbtm_bus_next_stops',
                            'value'     => $start,
                            'compare'   => 'LIKE',
                        ),
                    )                     

                );  
 

    $loopr = new WP_Query($args_search_rrr);
    while ($loopr->have_posts()) {
    $loopr->the_post();
    $values = get_post_custom( get_the_id() );
    $term = get_the_terms(get_the_id(),'wbtm_bus_cat');
    // print_r($term);
    $total_seat = $values['wbtm_total_seat'][0];
    $sold_seat = wbtm_get_available_seat(get_the_id(),$rdate);
    $available_seat = ($total_seat - $sold_seat);

$price_arr = get_post_meta(get_the_id(),'wbtm_bus_prices',true);    
$bus_bp_array = get_post_meta(get_the_id(),'wbtm_bus_bp_stops',true);
$bus_dp_array = get_post_meta(get_the_id(),'wbtm_bus_next_stops',true); 
$bp_time = wbtm_get_bus_start_time($end, $bus_bp_array);
$dp_time = wbtm_get_bus_end_time($start, $bus_dp_array);

$od_start_date  = get_post_meta(get_the_id(),'wbtm_od_start',true);  
$od_end_date    = get_post_meta(get_the_id(),'wbtm_od_end',true);
$od_range = wbtm_check_od_in_range($od_start_date, $od_end_date, $rdate);
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
if(wbtm_buffer_time_check($bp_time,$rdate) == 'yes'){
  if(in_array($rdate,$wbtm_bus_on)){  
?>
        <tr class="<?php echo wbtm_find_product_in_cart(get_the_id()); ?>">
            <td class='wbtm-mobile-hide'><div class="bus-thumb-list"><?php the_post_thumbnail('thumb'); ?></div></td>
            <td><?php the_title(); ?></td>
            <td class='wbtm-mobile-hide'><?php echo $end; ?></td>
            <td class='wbtm-mobile-hide'><?php echo $values['wbtm_bus_no'][0]; ?></td>
            <td class='wbtm-mobile-hide'><?php echo date('h:i A', strtotime($bp_time)); ?></td>
            <td class='wbtm-mobile-hide'><?php echo $start; ?></td>
            <td><?php echo get_woocommerce_currency_symbol(); ?><?php echo wbtm_get_bus_price($end,$start, $price_arr); ?></td>
            <td class='wbtm-mobile-hide'><?php  if(!empty($term)){ echo $term[0]->name;} ?></td>
            <td class='wbtm-mobile-hide'><?php echo date('h:i A', strtotime($dp_time)); ?></td>
            <td align="center"><span class='available-seat'><?php echo $available_seat; ?></span></td>
            <td><button id="view_panel_<?php echo get_the_id().wbtm_make_id($rdate); ?>" class='view-seat-btn'>View Seats</button></td>
        </tr>
        <tr style='display: none;' class="admin-bus-details" id="admin-bus-details<?php echo get_the_id().wbtm_make_id($rdate); ?>">
            <td colspan="11">
                <?php
                    $bus_meta           = get_post_custom(get_the_id());
                    $seat_col           = $bus_meta['wbtm_seat_col'][0];
                    $seat_row           = $bus_meta['wbtm_seat_row'][0];
                    $next_stops_arr     =  get_post_meta(get_the_id(), 'wbtm_bus_next_stops', true);
                    $wbtm_bus_bp_stops  =  get_post_meta(get_the_id(), 'wbtm_bus_bp_stops', true);
                    $seat_col_arr       = explode(",",$seat_col);
                    $seat_row_arr       = explode(",",$seat_row);
                    $seat_column = count($seat_col_arr);
                    $count  = 1;
$term = get_the_terms(get_the_id(),'wbtm_bus_cat');
$price_arr = get_post_meta(get_the_id(),'wbtm_bus_prices',true);  

if($seat_column==4){
    $seat_style = 2;
}elseif ($seat_column==3) {
    # code...
    $seat_style = 1;
}else{
    $seat_style = 999;
}
    ?>
<div class="wbtm-content-wrappers">
    <div >
    <?php wbtm_bus_seat_plan(wbtm_get_this_bus_seat_plan(),$end,$rdate); ?>

       <div class="bus-info-sec">
        <?php 
        $price_arr = get_post_meta(get_the_id(),'wbtm_bus_prices',true);
        $fare = wbtm_get_bus_price($end,$start, $price_arr);
        ?>
            <form action="" method='post'>
                <div class="top-search-section">                    
                    <div class="leaving-list">
                        <input type="hidden"  name='journey_date' class="text" value='<?php echo $rdate; ?>'/>
                        <input type="hidden" name='start_stops' value="<?php echo $end; ?>" class="hidden"/>
                        <input type='hidden' value='<?php echo $start; ?>' name='end_stops'/>
                        <h6><?php echo bus_get_option('wbtm_route_text', 'label_setting_sec') ? bus_get_option('wbtm_route_text', 'label_setting_sec') : _e('Route','bus-ticket-booking-with-seat-reservation'); ?></h6>
                        <div class="selected_route">
                            <?php printf( '<span>%s <i class="fa fa-long-arrow-right"></i> %s<span>', $end, $start ); ?>
                             (<?php echo get_woocommerce_currency_symbol(); ?><?php echo wbtm_get_bus_price($end,$start, $price_arr); ?>)
                        </div>
                    </div>                    
                    <div class="leaving-list">
                        <h6><?php echo bus_get_option('wbtm_date_text', 'label_setting_sec') ? bus_get_option('wbtm_date_text', 'label_setting_sec') : _e('Date:','bus-ticket-booking-with-seat-reservation'); ?></h6>
                        <div class="selected_date">
                            <?php printf( '<span>%s</span>', date( 'jS F, Y', strtotime( $rdate ) ) ); ?>
                        </div>
                    </div>   
                    <div class="leaving-list">
                        <h6><?php _e('Start & Arrival Time','bus-ticket-booking-with-seat-reservation'); ?></h6>
                        <div class="selected_date">
                            <?php  
                                $bus_bp_array = get_post_meta(get_the_id(),'wbtm_bus_bp_stops',true);
                                $bus_dp_array = get_post_meta(get_the_id(),'wbtm_bus_next_stops',true);
                                $bp_time = wbtm_get_bus_start_time($end, $bus_bp_array);
                                $dp_time = wbtm_get_bus_end_time($start, $bus_dp_array);
                                echo date('h:i A', strtotime($bp_time)).' <i class="fa fa-long-arrow-right"></i> '.date('h:i A', strtotime($dp_time));
                            ?>
                        <input type="hidden" value="<?php echo date('h:i A', strtotime($bp_time)); ?>" name="user_start_time" id='user_start_time<?php echo get_the_id().wbtm_make_id($rdate); ?>'>
                        <input type="hidden" name="bus_start_time" value="<?php echo date('h:i A', strtotime($bp_time)); ?>" id='bus_start_time'>                            
                        </div>
                    </div>                                    
                </div>
                <div class="seat-selected-list-fare">
                    <table class="selected-seat-list<?php echo get_the_id().wbtm_make_id($rdate); ?>">
                        <tr class='list_head<?php echo get_the_id().wbtm_make_id($rdate); ?>'>
                            <th><?php echo bus_get_option('wbtm_seat_no_text', 'label_setting_sec') ? bus_get_option('wbtm_seat_no_text', 'label_setting_sec') : _e('Seat No','bus-ticket-booking-with-seat-reservation'); ?></th>
                            <th><?php echo bus_get_option('wbtm_fare_text', 'label_setting_sec') ? bus_get_option('wbtm_fare_text', 'label_setting_sec') : _e('Fare','bus-ticket-booking-with-seat-reservation'); ?></th>
                            <th><?php echo bus_get_option('wbtm_remove_text', 'label_setting_sec') ? bus_get_option('wbtm_remove_text', 'label_setting_sec') : _e('Remove','bus-ticket-booking-with-seat-reservation'); 
                             ?>
                             </th>
                        </tr>
                        <tr>
                            <td align="center"> 
                                <?php echo bus_get_option('wbtm_total_text', 'label_setting_sec') ? bus_get_option('wbtm_total_text', 'label_setting_sec') : _e('Total','bus-ticket-booking-with-seat-reservation'); 
                             ?>
                            <span id='total_seat<?php echo get_the_id().wbtm_make_id($rdate); ?>_booked'></span><input type="hidden" value="" id="tq<?php echo get_the_id().wbtm_make_id($rdate); ?>" name='total_seat' class="number"/></td>
                            
                            <td align="center"><input type="hidden" value="" id="tfi<?php echo get_the_id().wbtm_make_id($rdate); ?>" class="number"/><span id="totalFare<?php echo get_the_id().wbtm_make_id($rdate); ?>"></span></td><td></td>
                        </tr>
                    </table>
                    <div id="divParent<?php echo get_the_id().wbtm_make_id($rdate); ?>"></div>
                    <input type="hidden" name="bus_id" value="<?php echo get_the_id(); ?>">
                    <button id='bus-booking-btn<?php echo get_the_id().wbtm_make_id($rdate); ?>' type="submit" name="add-to-cart" value="<?php echo esc_attr(get_the_id()); ?>" class="single_add_to_cart_button button alt btn-mep-event-cart"> <?php echo bus_get_option('wbtm_book_now_text', 'label_setting_sec') ? bus_get_option('wbtm_book_now_text', 'label_setting_sec') : _e('Book Now','bus-ticket-booking-with-seat-reservation'); 
                             ?>         
                     </button>
                </div>
            </form>
        </div>
    </div>
<?php 
$uid = get_the_id().wbtm_make_id($rdate);
// do_action('wbtm_search_seat_js',$uid,100);  
wbtm_seat_booking_js($uid,$fare);
?>
</div>
</td>
        </tr>        
<?php }else{
    if(empty($wbtm_bus_on_dates)){ ?>

     <tr class="<?php echo wbtm_find_product_in_cart(get_the_id()); ?>">
            <td class='wbtm-mobile-hide'><div class="bus-thumb-list"><?php the_post_thumbnail('thumb'); ?></div></td>
            <td><?php the_title(); ?></td>
            <td class='wbtm-mobile-hide'><?php echo $end; ?></td>
            <td class='wbtm-mobile-hide'><?php echo $values['wbtm_bus_no'][0]; ?></td>
            <td class='wbtm-mobile-hide'><?php echo date('h:i A', strtotime($bp_time)); ?></td>
            <td class='wbtm-mobile-hide'><?php echo $start; ?></td>
            <td><?php echo get_woocommerce_currency_symbol(); ?><?php echo wbtm_get_bus_price($end,$start, $price_arr); ?></td>
            <td class='wbtm-mobile-hide'><?php  if(!empty($term)){ echo $term[0]->name;} ?></td>
            <td class='wbtm-mobile-hide'><?php echo date('h:i A', strtotime($dp_time)); ?></td>
            <td align="center"><span class='available-seat'><?php echo $available_seat; ?></span></td>
            <td><button id="view_panel_<?php echo get_the_id().wbtm_make_id($rdate); ?>" class='view-seat-btn'>View Seats</button></td>
        </tr>
        <tr style='display: none;' class="admin-bus-details" id="admin-bus-details<?php echo get_the_id().wbtm_make_id($rdate); ?>">
            <td colspan="11">
                <?php
                    $bus_meta           = get_post_custom(get_the_id());
                    $seat_col           = $bus_meta['wbtm_seat_col'][0];
                    $seat_row           = $bus_meta['wbtm_seat_row'][0];
                    $next_stops_arr     =  get_post_meta(get_the_id(), 'wbtm_bus_next_stops', true);
                    $wbtm_bus_bp_stops  =  get_post_meta(get_the_id(), 'wbtm_bus_bp_stops', true);
                    $seat_col_arr       = explode(",",$seat_col);
                    $seat_row_arr       = explode(",",$seat_row);
                    $seat_column = count($seat_col_arr);
                    $count  = 1;
$term = get_the_terms(get_the_id(),'wbtm_bus_cat');
$price_arr = get_post_meta(get_the_id(),'wbtm_bus_prices',true);  

if($seat_column==4){
    $seat_style = 2;
}elseif ($seat_column==3) {
    # code...
    $seat_style = 1;
}else{
    $seat_style = 999;
}
    ?>
<div class="wbtm-content-wrappers">
    <div >
    <?php wbtm_bus_seat_plan(wbtm_get_this_bus_seat_plan(),$end,$rdate); ?>

       <div class="bus-info-sec">
        <?php 
        $price_arr = get_post_meta(get_the_id(),'wbtm_bus_prices',true);
        $fare = wbtm_get_bus_price($end,$start, $price_arr);
        ?>
            <form action="" method='post'>
                <div class="top-search-section">                    
                    <div class="leaving-list">
                        <input type="hidden"  name='journey_date' class="text" value='<?php echo $rdate; ?>'/>
                        <input type="hidden" name='start_stops' value="<?php echo $end; ?>" class="hidden"/>
                        <input type='hidden' value='<?php echo $start; ?>' name='end_stops'/>
                        <h6><?php echo bus_get_option('wbtm_route_text', 'label_setting_sec') ? bus_get_option('wbtm_route_text', 'label_setting_sec') : _e('Route','bus-ticket-booking-with-seat-reservation'); ?></h6>
                        <div class="selected_route">
                            <?php printf( '<span>%s <i class="fa fa-long-arrow-right"></i> %s<span>', $end, $start ); ?>
                             (<?php echo get_woocommerce_currency_symbol(); ?><?php echo wbtm_get_bus_price($end,$start, $price_arr); ?>)
                        </div>
                    </div>                    
                    <div class="leaving-list">
                        <h6><?php echo bus_get_option('wbtm_date_text', 'label_setting_sec') ? bus_get_option('wbtm_date_text', 'label_setting_sec') : _e('Date:','bus-ticket-booking-with-seat-reservation'); ?></h6>
                        <div class="selected_date">
                            <?php printf( '<span>%s</span>', date( 'jS F, Y', strtotime( $rdate ) ) ); ?>
                        </div>
                    </div>   
                    <div class="leaving-list">
                        <h6><?php _e('Start & Arrival Time','bus-ticket-booking-with-seat-reservation'); ?></h6>
                        <div class="selected_date">
                            <?php  
                                $bus_bp_array = get_post_meta(get_the_id(),'wbtm_bus_bp_stops',true);
                                $bus_dp_array = get_post_meta(get_the_id(),'wbtm_bus_next_stops',true);
                                $bp_time = wbtm_get_bus_start_time($end, $bus_bp_array);
                                $dp_time = wbtm_get_bus_end_time($start, $bus_dp_array);
                                echo date('h:i A', strtotime($bp_time)).' <i class="fa fa-long-arrow-right"></i> '.date('h:i A', strtotime($dp_time));
                            ?>
                        <input type="hidden" value="<?php echo date('h:i A', strtotime($bp_time)); ?>" name="user_start_time" id='user_start_time<?php echo get_the_id().wbtm_make_id($rdate); ?>'>
                        <input type="hidden" name="bus_start_time" value="<?php echo date('h:i A', strtotime($bp_time)); ?>" id='bus_start_time'>                            
                        </div>
                    </div>                                    
                </div>
                <div class="seat-selected-list-fare">
                    <table class="selected-seat-list<?php echo get_the_id().wbtm_make_id($rdate); ?>">
                        <tr class='list_head<?php echo get_the_id().wbtm_make_id($rdate); ?>'>
                            <th><?php echo bus_get_option('wbtm_seat_no_text', 'label_setting_sec') ? bus_get_option('wbtm_seat_no_text', 'label_setting_sec') : _e('Seat No','bus-ticket-booking-with-seat-reservation'); ?></th>
                            <th><?php echo bus_get_option('wbtm_fare_text', 'label_setting_sec') ? bus_get_option('wbtm_fare_text', 'label_setting_sec') : _e('Fare','bus-ticket-booking-with-seat-reservation'); ?></th>
                            <th><?php echo bus_get_option('wbtm_remove_text', 'label_setting_sec') ? bus_get_option('wbtm_remove_text', 'label_setting_sec') : _e('Remove','bus-ticket-booking-with-seat-reservation'); 
                             ?>
                             </th>
                        </tr>
                        <tr>
                            <td align="center"> 
                                <?php echo bus_get_option('wbtm_total_text', 'label_setting_sec') ? bus_get_option('wbtm_total_text', 'label_setting_sec') : _e('Total','bus-ticket-booking-with-seat-reservation'); 
                             ?>
                            <span id='total_seat<?php echo get_the_id().wbtm_make_id($rdate); ?>_booked'></span><input type="hidden" value="" id="tq<?php echo get_the_id().wbtm_make_id($rdate); ?>" name='total_seat' class="number"/></td>
                            
                            <td align="center"><input type="hidden" value="" id="tfi<?php echo get_the_id().wbtm_make_id($rdate); ?>" class="number"/><span id="totalFare<?php echo get_the_id().wbtm_make_id($rdate); ?>"></span></td><td></td>
                        </tr>
                    </table>
                    <div id="divParent<?php echo get_the_id().wbtm_make_id($rdate); ?>"></div>
                    <input type="hidden" name="bus_id" value="<?php echo get_the_id(); ?>">
                    <button id='bus-booking-btn<?php echo get_the_id().wbtm_make_id($rdate); ?>' type="submit" name="add-to-cart" value="<?php echo esc_attr(get_the_id()); ?>" class="single_add_to_cart_button button alt btn-mep-event-cart"> <?php echo bus_get_option('wbtm_book_now_text', 'label_setting_sec') ? bus_get_option('wbtm_book_now_text', 'label_setting_sec') : _e('Book Now','bus-ticket-booking-with-seat-reservation'); 
                             ?>         
                     </button>
                </div>
            </form>
        </div>
    </div>
<?php 
$uid = get_the_id().wbtm_make_id($rdate);
// do_action('wbtm_search_seat_js',$uid,100);  
wbtm_seat_booking_js($uid,$fare);
?>
</div>
</td>
        </tr>        
<?php 
} 
} 
}
}
}
    }
wp_reset_query();

?>
</tbody>
</table>
<?php } } ?>




</div>
<?php
$content = ob_get_clean();
return $content;
}


add_shortcode( 'wbtm-bus-search-form', 'wbtm_bus_search_form' );
function wbtm_bus_search_form($atts, $content=null){
        $defaults = array(
            "cat"                   => "0"
        );
        $params                     = shortcode_atts($defaults, $atts);
        $cat                        = $params['cat'];
ob_start();
 
$start  = isset( $_GET['bus_start_route'] ) ? strip_tags($_GET['bus_start_route']) : '';
$end    = isset( $_GET['bus_end_route'] ) ? strip_tags($_GET['bus_end_route']) : '';
$date   = isset( $_GET['j_date'] ) ? strip_tags($_GET['j_date']) : date('Y-m-d');
$r_date     = isset( $_GET['r_date'] ) ? strip_tags($_GET['r_date']) : date('Y-m-d');

?>
<div class="wbtm-search-form-fields-sec">
    <h2><?php echo bus_get_option('wbtm_buy_ticket_text', 'label_setting_sec') ? bus_get_option('wbtm_buy_ticket_text', 'label_setting_sec') : _e('BUY TICKET:','bus-ticket-booking-with-seat-reservation'); ?>
    </h2>
    <form action="<?php echo get_site_url(); ?>/bus-search-list/" method="get">
        <?php wbtm_bus_search_fileds($start,$end,$date,$r_date); //do_action('wbtm_search_fields'); ?>
    </form>
</div>
<?php
$content = ob_get_clean();
return $content;
}