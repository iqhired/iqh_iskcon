<?php
/*
*	Tourmaster Plugin
*	---------------------------------------------------------------------
*	for tour post type
*	---------------------------------------------------------------------
*/

// create post type
add_action('init', 'tourmaster_tour_bus_booking_init');
if( !function_exists('tourmaster_tour_bus_booking_init') ){
    function tourmaster_tour_bus_booking_init() {

        // custom post type
        $supports = apply_filters('tourmaster_custom_post_support', array('title', 'author', 'custom-fields'), 'bus_booking');

        $labels = array(
            'name'               => esc_html__('Tour Bus Booking', 'tourmaster'),
            'singular_name'      => esc_html__('Tour Bus Booking', 'tourmaster'),
            'menu_name'          => esc_html__('Tour Bus Booking', 'tourmaster'),
            'name_admin_bar'     => esc_html__('Tour Bus Booking', 'tourmaster'),
            'add_new'            => esc_html__('Add New', 'tourmaster'),
            'add_new_item'       => esc_html__('Add New Bus Booking', 'tourmaster'),
            'new_item'           => esc_html__('New Bus Booking', 'tourmaster'),
            'edit_item'          => esc_html__('Edit Bus Booking', 'tourmaster'),
            'view_item'          => esc_html__('View Bus Booking', 'tourmaster'),
            'all_items'          => esc_html__('All Bus Booking', 'tourmaster'),
            'search_items'       => esc_html__('Search Bus Booking', 'tourmaster'),
            'parent_item_colon'  => esc_html__('Parent Bus Booking:', 'tourmaster'),
            'not_found'          => esc_html__('No Bus Booking found.', 'tourmaster'),
            'not_found_in_trash' => esc_html__('No Bus Booking found in Trash.', 'tourmaster')
        );
        $args = array(
            'labels'             => $labels,
            'description'        => esc_html__('Description.', 'tourmaster'),
            'public'             => true,
            'publicly_queryable' => false,
            'exclude_from_search'=> true,
            'show_ui'            => true,
            'show_in_admin_bar'  => false,
            'show_in_nav_menus'  => false,
            'show_in_menu'       => true,
            'query_var'          => true,
            'map_meta_cap' 		 => true,
            'capabilities' => array(
                'edit_post'          => 'edit_bus_booking',
                'read_post'          => 'read_bus_booking',
                'delete_post'        => 'delete_bus_booking',
//                'delete_posts'       => 'delete_bus_bookings',
//                'edit_posts'         => 'edit_bus_bookings',
//                'create_post'       => 'edit_bus_bookings',
                'edit_others_posts'  => 'edit_others_bus_bookings',
                'delete_others_posts'  	=> 'edit_others_bus_bookings',
                'publish_posts'      	=> 'publish_bus_bookings',
                'edit_published_posts'  => 'publish_bus_bookings',
                'read_private_posts' 	=> 'read_private_bus_bookings',
                'edit_private_posts' 	=> 'read_private_bus_bookings',
                'delete_private_posts' 	=> 'read_private_bus_bookings',
            ),
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => $supports
        );
        register_post_type('tour_bus_booking', $args);

        // apply single template filter
        add_filter('single_template', 'tourmaster_tour_bus_booking_template');

    }
} // tourmaster_post_type_init

if( !function_exists('tourmaster_tour_bus_booking_template') ){
    function tourmaster_tour_bus_booking_template( $template ){

        if( get_post_type() == 'bus_booking' ){
            $template = get_404_template();
        }

        return $template;
    }
}

// create an option
if( is_admin() ){ add_action('after_setup_theme', 'tourmaster_tour_bus_booking_option_init'); }
if( !function_exists('tourmaster_tour_bus_booking_option_init') ){
    function tourmaster_tour_bus_booking_option_init(){

        if( class_exists('tourmaster_page_option') ){
            new tourmaster_page_option(array(
                'post_type' => array('tour_bus_booking'),
                'title' => esc_html__('Bus Booking Settings', 'tourmaster'),
                'title-icon' => 'fa fa-plane',
                'slug' => 'tourmaster-bus-booking-option',
                'options' => apply_filters('tourmaster_tour_options', array(

                    'general' => array(
                        'title' => esc_html__('General', 'tourmaster'),
                        'options' => array(
                            'bus-booking-code' => array(
                                'title' => esc_html__('Bus Booking Code', 'tourmaster'),
                                'type' => 'text',
                                'single' => 'tourmaster-bus-booking-code'
                            ),
                            'bus-booking-amount' => array(
                                'title' => esc_html__('Bus Booking Amount', 'tourmaster'),
                                'type' => 'text',
                                'description' => esc_html__('Number of bus booking available for uses. Leave this field blank for unlimited use.', 'tourmaster')
                            ),
                            'bus-booking-expiry' => array(
                                'title' => esc_html__('Bus Booking Expiry', 'tourmaster'),
                                'type' => 'datepicker',
                            ),
                            'bus-booking-discount-type' => array(
                                'title' => esc_html__('Bus Booking Discount Type', 'tourmaster'),
                                'type' => 'combobox',
                                'options' => array(
                                    'percent' => esc_html__('Percent', 'tourmaster'),
                                    'amount' => esc_html__('Amount', 'tourmaster'),
                                )
                            ),
                            'bus-booking-discount-amount' => array(
                                'title' => esc_html__('Bus Booking Discount Amount', 'tourmaster'),
                                'type' => 'text',
                                'description' => esc_html__('Only number is allowed here', 'tourmaster')
                            ),
                            'apply-to-specific-tour' => array(
                                'title' => esc_html__('Apply Bus Booking To Only Specific Tour', 'tourmaster'),
                                'type' => 'textarea',
                                'description' => esc_html__('Fill tour ID separated by comma', 'tourmaster')
                            ),

                        )
                    ),

                )) // tourmaster_tour_options
            )); // tourmaster_page_option
        }
    }
}