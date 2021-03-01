<?php
/**
 * 2AM Awesome loginbar Settings Controls
 *
 * @version 1.0
 *
 */
if ( !class_exists('MAGE_Events_Setting_Controls' ) ):
class MAGE_Events_Setting_Controls {

    private $settings_api;

    function __construct() {
        $this->settings_api = new MAGE_Setting_API;

        add_action( 'admin_init', array($this, 'admin_init') );
        add_action( 'admin_menu', array($this, 'admin_menu') );
    }

    function admin_init() {

        //set the settings
        $this->settings_api->set_sections( $this->get_settings_sections() );
        $this->settings_api->set_fields( $this->get_settings_fields() );

        //initialize settings
        $this->settings_api->admin_init();
    }

    function admin_menu() {
        add_options_page( 'Event Settings', 'Event Settings', 'delete_posts', 'wbtm_bus_settings_page', array($this, 'plugin_page') );

         add_submenu_page('edit.php?post_type=wbtm_bus', __('Bus General Settings','wbtm_bus'), __('Bus General Settings','wbtm_bus'), 'manage_options', 'wbtm_bus_settings_page', array($this, 'plugin_page'));
    }

    function get_settings_sections() {
        $sections = array(
            array(
                'id' => 'general_setting_sec',
                'title' => __( 'Time Buffer Settings', 'bus-ticket-booking-with-seat-reservation' )
            ),
            array(
                'id' => 'label_setting_sec',
                'title' => __( 'Translation Settings', 'mage-eventpress' )
            ) 
        );
        return $sections;
    }




    /**
     * Returns all the settings fields
     *
     * @return array settings fields
     */
    function get_settings_fields() {
        $settings_fields = array(
            'general_setting_sec' => array(

             array(
                    'name' => 'bus_buffer_time',
                    'label' => __( 'Buffer Time', 'bus-ticket-booking-with-seat-reservation' ),
                    'desc' => __( 'Please enter here car buffer time in Hour. By default is 0', 'bus-ticket-booking-with-seat-reservation' ),
                    'type' => 'text',
                    'default' => ''
                )
            ),
            'label_setting_sec' => array(


            array(
                'name' => 'wbtm_buy_ticket_text',
                'label' => __( 'BUY TICKET', 'bus-ticket-booking-with-seat-reservation' ),
                'desc' => __( 'Enter the text which you want to display as To Search form page.', 'bus-ticket-booking-with-seat-reservation' ),
                'type' => 'text',
                'default' => 'BUY TICKET'
            ),
            array(
                'name' => 'wbtm_from_text',
                'label' => __( 'From', 'bus-ticket-booking-with-seat-reservation' ),
                'desc' => __( 'Enter the text which you want to display as To Search form page.', 'bus-ticket-booking-with-seat-reservation' ),
                'type' => 'text',
                'default' => 'From:'
            ),
          array(
                'name' => 'wbtm_to_text',
                'label' => __( 'To:', 'bus-ticket-booking-with-seat-reservation' ),
                'desc' => __( 'Enter the text which you want to display as To Search form page.', 'bus-ticket-booking-with-seat-reservation' ),
                'type' => 'text',
                'default' => 'To:'
            ),
            
          array(
                'name' => 'wbtm_date_of_journey_text',
                'label' => __( 'Date of Journey:', 'bus-ticket-booking-with-seat-reservation' ),
                'desc' => __( 'Enter the text which you want to display as Date of Journey Search form page.', 'bus-ticket-booking-with-seat-reservation' ),
                'type' => 'text',
                'default' => 'Date of Journey:'
            ),

                array(
                'name' => 'wbtm_return_date_text',
                'label' => __( 'Return Date:', 'bus-ticket-booking-with-seat-reservation' ),
                'desc' => __( 'Enter the text which you want to display as Date of Journey Search form page.', 'bus-ticket-booking-with-seat-reservation' ),
                'type' => 'text',
                'default' => 'Return Date:'
            ),

          array(
                'name' => 'wbtm_one_way_text',
                'label' => __( 'One Way', 'bus-ticket-booking-with-seat-reservation' ),
                'desc' => __( 'Enter the text which you want to display as One Way Search form page.', 'bus-ticket-booking-with-seat-reservation' ),
                'type' => 'text',
                'default' => 'One Way'
            ),

          array(
                'name' => 'wbtm_return_text',
                'label' => __( 'Return', 'bus-ticket-booking-with-seat-reservation' ),
                'desc' => __( 'Enter the text which you want to display as Return Search form page.', 'bus-ticket-booking-with-seat-reservation' ),
                'type' => 'text',
                'default' => 'Return'
            ),

          array(
                'name' => 'wbtm_search_buses_text',
                'label' => __( 'SEARCH BUSES', 'bus-ticket-booking-with-seat-reservation' ),
                'desc' => __( 'Enter the text which you want to display as SEARCH BUSES button form page.', 'bus-ticket-booking-with-seat-reservation' ),
                'type' => 'text',
                'default' => 'SEARCH BUSES'
            ),
            array(
                'name' => 'wbtm_route_text',
                'label' => __( 'Route', 'bus-ticket-booking-with-seat-reservation' ),
                'desc' => __( 'Enter the text which you want to display as Route Search Result Page.', 'bus-ticket-booking-with-seat-reservation' ),
                'type' => 'text',
                'default' => 'Route'
            ),
            array(
                'name' => 'wbtm_date_text',
                'label' => __( 'Date:', 'bus-ticket-booking-with-seat-reservation' ),
                'desc' => __( 'Enter the text which you want to display as Date Search Result Page.', 'bus-ticket-booking-with-seat-reservation' ),
                'type' => 'text',
                'default' => 'Date:'
            ),
            array(
                'name' => 'wbtm_bus_name_text',
                'label' => __( 'Bus Name:', 'bus-ticket-booking-with-seat-reservation' ),
                'desc' => __( 'Enter the text which you want to display as Bus Name Search Result Page.', 'bus-ticket-booking-with-seat-reservation' ),
                'type' => 'text',
                'default' => 'Bus Name:'
            ),
             array(
                'name' => 'wbtm_departing_text',
                'label' => __( 'DEPARTING', 'bus-ticket-booking-with-seat-reservation' ),
                'desc' => __( 'Enter the text which you want to display as DEPARTING Search Result Page.', 'bus-ticket-booking-with-seat-reservation' ),
                'type' => 'text',
                'default' => 'DEPARTING'
            ),
             array(
                'name' => 'wbtm_coach_no_text',
                'label' => __( 'COACH NO', 'bus-ticket-booking-with-seat-reservation' ),
                'desc' => __( 'Enter the text which you want to display as COACH NO Search Result Page.', 'bus-ticket-booking-with-seat-reservation' ),
                'type' => 'text',
                'default' => 'COACH NO'
            ),
             array(
                'name' => 'wbtm_starting_text',
                'label' => __( 'STARTING', 'bus-ticket-booking-with-seat-reservation' ),
                'desc' => __( 'Enter the text which you want to display as STARTING Search Result Page.', 'bus-ticket-booking-with-seat-reservation' ),
                'type' => 'text',
                'default' => 'STARTING'
            ),
             array(
                'name' => 'wbtm_end_text',
                'label' => __( 'END', 'bus-ticket-booking-with-seat-reservation' ),
                'desc' => __( 'Enter the text which you want to display as END Search Result Page.', 'bus-ticket-booking-with-seat-reservation' ),
                'type' => 'text',
                'default' => 'END'
            ),
             array(
                'name' => 'wbtm_fare_text',
                'label' => __( 'FARE', 'bus-ticket-booking-with-seat-reservation' ),
                'desc' => __( 'Enter the text which you want to display as FARE Search Result Page.', 'bus-ticket-booking-with-seat-reservation' ),
                'type' => 'text',
                'default' => 'FARE'
            ),
             array(
                'name' => 'wbtm_type_text',
                'label' => __( 'TYPE', 'bus-ticket-booking-with-seat-reservation' ),
                'desc' => __( 'Enter the text which you want to display as TYPE Search Result Page.', 'bus-ticket-booking-with-seat-reservation' ),
                'type' => 'text',
                'default' => 'TYPE'
            ),
             array(
                'name' => 'wbtm_arrival_text',
                'label' => __( 'ARRIVAL', 'bus-ticket-booking-with-seat-reservation' ),
                'desc' => __( 'Enter the text which you want to display as ARRIVAL Search Result Page.', 'bus-ticket-booking-with-seat-reservation' ),
                'type' => 'text',
                'default' => 'ARRIVAL'
            ),
             array(
                'name' => 'wbtm_seats_available_text',
                'label' => __( 'SEATS AVAILABLE ', 'bus-ticket-booking-with-seat-reservation' ),
                'desc' => __( 'Enter the text which you want to display as SEATS AVAILABLE Search Result Page.', 'bus-ticket-booking-with-seat-reservation' ),
                'type' => 'text',
                'default' => 'SEATS AVAILABLE'
            ),
             array(
                'name' => 'wbtm_view_text',
                'label' => __( 'VIEW', 'bus-ticket-booking-with-seat-reservation' ),
                'desc' => __( 'Enter the text which you want to display as VIEW Search Result Page.', 'bus-ticket-booking-with-seat-reservation' ),
                'type' => 'text',
                'default' => 'VIEW'
            ),
            array(
                'name' => 'wbtm_view_seats_text',
                'label' => __( 'View Seats', 'bus-ticket-booking-with-seat-reservation' ),
                'desc' => __( 'Enter the text which you want to display as View Seats button Result Page.', 'bus-ticket-booking-with-seat-reservation' ),
                'type' => 'text',
                'default' => 'View Seats'
            ),

             array(
                'name' => 'wbtm_start_arrival_time_text',
                'label' => __( 'Start & Arrival Time', 'bus-ticket-booking-with-seat-reservation' ),
                'desc' => __( 'Enter the text which you want to display as Start & Arrival Time Details Page.', 'bus-ticket-booking-with-seat-reservation' ),
                'type' => 'text',
                'default' => 'Start & Arrival Time'
            ),

             array(
                'name' => 'wbtm_seat_no_text',
                'label' => __( 'Seat No', 'bus-ticket-booking-with-seat-reservation' ),
                'desc' => __( 'Enter the text which you want to display as Seat No Details Page.', 'bus-ticket-booking-with-seat-reservation' ),
                'type' => 'text',
                'default' => 'Seat No'
            ),

             array(
                'name' => 'wbtm_remove_text',
                'label' => __( 'Remove', 'bus-ticket-booking-with-seat-reservation' ),
                'desc' => __( 'Enter the text which you want to display as Remove Details Page.', 'bus-ticket-booking-with-seat-reservation' ),
                'type' => 'text',
                'default' => 'Remove'
            ),
             array(
                'name' => 'wbtm_total_text',
                'label' => __( 'Total', 'bus-ticket-booking-with-seat-reservation' ),
                'desc' => __( 'Enter the text which you want to display as Total Details Page.', 'bus-ticket-booking-with-seat-reservation' ),
                'type' => 'text',
                'default' => 'Total'
            ),
             array(
                'name' => 'wbtm_book_now_text',
                'label' => __( 'BOOK NOW', 'bus-ticket-booking-with-seat-reservation' ),
                'desc' => __( 'Enter the text which you want to display as BOOK NOW button details Page.', 'bus-ticket-booking-with-seat-reservation' ),
                'type' => 'text',
                'default' => 'BOOK NOW'
            ),

             array(
                'name' => 'wbtm_bus_no_text',
                'label' => __( 'Bus No:', 'bus-ticket-booking-with-seat-reservation' ),
                'desc' => __( 'Enter the text which you want to display as Bus No single bus details Page.', 'bus-ticket-booking-with-seat-reservation' ),
                'type' => 'text',
                'default' => 'Bus No:'
            ),
            array(
                'name' => 'wbtm_total_seat_text',
                'label' => __('Total Seat:', 'bus-ticket-booking-with-seat-reservation' ),
                'desc' => __('Enter the text which you want to display as Total Seat  bus details Page.', 'bus-ticket-booking-with-seat-reservation' ),
                'type' => 'text',
                'default' => 'Total Seat:'
            ),
            array(
                'name' => 'wbtm_boarding_points_text',
                'label' => __('Boarding Points', 'bus-ticket-booking-with-seat-reservation' ),
             'desc' => __('Enter the text which you want to display as Boarding Points single bus details Page.', 'bus-ticket-booking-with-seat-reservation' ),
                'type' => 'text',
                'default' => 'Boarding Points'
            ),  
             array(
                'name' => 'wbtm_dropping_points_text',
                'label' => __('Dropping Points', 'bus-ticket-booking-with-seat-reservation' ),
             'desc' => __('Enter the text which you want to display as Dropping Points single bus details Page.', 'bus-ticket-booking-with-seat-reservation' ),
                'type' => 'text',
                'default' => 'Dropping Points'
            ),  
            
             array(
                'name' => 'wbtm_select_journey_date_text',
                'label' => __('Select Journey Date', 'bus-ticket-booking-with-seat-reservation' ),
             'desc' => __('Enter the text which you want to display as Select Journey Date single bus details Page.', 'bus-ticket-booking-with-seat-reservation' ),
                'type' => 'text',
                'default' => 'Select Journey Date'
            ),
  
          array(
                'name' => 'wbtm_search_text',
                'label' => __('Search', 'bus-ticket-booking-with-seat-reservation' ),
             'desc' => __('Enter the text which you want to display as search button single bus details Page.', 'bus-ticket-booking-with-seat-reservation' ),
                'type' => 'text',
                'default' => 'Search'
            ),

         array(
                'name' => 'wbtm_seat_list_text',
                'label' => __('Seat List:', 'bus-ticket-booking-with-seat-reservation' ),
             'desc' => __('Enter the text which you want to display as search button single bus seat list in cart Page.', 'bus-ticket-booking-with-seat-reservation' ),
                'type' => 'text',
                'default' => 'Seat List:'
            ),

         


        ),

        );

        return $settings_fields;
    }

    function plugin_page() {
        echo '<div class="wrap">';

        $this->settings_api->show_navigation();
        $this->settings_api->show_forms();

        echo '</div>';
    }

    /**
     * Get all the pages
     *
     * @return array page names with key value pairs
     */
    function get_pages() {
        $pages = get_pages();
        $pages_options = array();
        if ( $pages ) {
            foreach ($pages as $page) {
                $pages_options[$page->ID] = $page->post_title;
            }
        }

        return $pages_options;
    }

}
endif;

$settings = new MAGE_Events_Setting_Controls();


function bus_get_option( $option, $section, $default = '' ) {
    $options = get_option( $section );

    if ( isset( $options[$option] ) ) {
        return $options[$option];
    }
    
    return $default;
}