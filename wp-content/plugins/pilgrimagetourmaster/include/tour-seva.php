<?php
/*
*	Tourmaster Plugin
*	---------------------------------------------------------------------
*	for tour post type
*	---------------------------------------------------------------------
*/

// create post type
add_action('init', 'tourmaster_tour_seva_init');
if( !function_exists('tourmaster_tour_seva_init') ){
    function tourmaster_tour_seva_init() {

        // custom post type
        $supports = apply_filters('tourmaster_custom_post_support', array('title', 'author', 'custom-fields'), 'seva');

        $labels = array(
            'name'               => esc_html__('Tour Seva', 'tourmaster'),
            'singular_name'      => esc_html__('Tour Seva', 'tourmaster'),
            'menu_name'          => esc_html__('Tour Seva', 'tourmaster'),
            'name_admin_bar'     => esc_html__('Tour Seva', 'tourmaster'),
            'add_new'            => esc_html__('Add New', 'tourmaster'),
            'add_new_item'       => esc_html__('Add New Seva', 'tourmaster'),
            'new_item'           => esc_html__('New Seva', 'tourmaster'),
            'edit_item'          => esc_html__('Edit Seva', 'tourmaster'),
            'view_item'          => esc_html__('View Seva', 'tourmaster'),
            'all_items'          => esc_html__('All Seva', 'tourmaster'),
            'search_items'       => esc_html__('Search Seva', 'tourmaster'),
            'parent_item_colon'  => esc_html__('Parent Seva:', 'tourmaster'),
            'not_found'          => esc_html__('No service found.', 'tourmaster'),
            'not_found_in_trash' => esc_html__('No service found in Trash.', 'tourmaster')
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
                'edit_post'          => 'edit_seva',
                'read_post'          => 'read_seva',
                'delete_post'        => 'delete_seva',
//					'delete_posts'       => 'delete_seva',
					// 'edit_posts'         => 'edit_sevas',
//					'create_posts'       => 'edit_sevas',
                'edit_others_posts'  	=> 'edit_others_sevas',
                'delete_others_posts'  	=> 'edit_others_sevas',
                'publish_posts'      	=> 'publish_sevas',
                'edit_published_posts'  => 'publish_sevas',
                'read_private_posts' 	=> 'read_private_sevas',
                'edit_private_posts' 	=> 'read_private_sevas',
                'delete_private_posts' 	=> 'read_private_sevas',
            ),
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => $supports
        );
        register_post_type('tour_seva', $args);

        // apply single template filter
        add_filter('single_template', 'tourmaster_tour_seva_template');

    }
} // tourmaster_post_type_init

if( !function_exists('tourmaster_tour_seva_template') ){
    function tourmaster_tour_seva_template( $template ){

        if( get_post_type() == 'seva' ){
            $template = get_404_template();
        }

        return $template;
    }
}

// create an option
if( is_admin() ){ add_action('after_setup_theme', 'tourmaster_tour_seva_option_init'); }
if( !function_exists('tourmaster_tour_seva_option_init') ){
    function tourmaster_tour_seva_option_init(){

        if( class_exists( 'tourmaster_page_option') ){
            new tourmaster_page_option(array(
                'post_type' => array('tour_seva'),
                'title' => esc_html__('Additional Seva Details', 'tourmaster'),
                'title-icon' => 'fa fa-money',
                'slug' => 'tourmaster-seva-option',
                'options' => apply_filters('tourmaster_tour_options', array(

                    'general' => array(
                        'title' => esc_html__('General', 'tourmaster'),
                        'options' => array(
                            'price' => array(
                                'title' => esc_html__('Amount', 'tourmaster'),
                                'type' => 'text',
                                'description' => esc_html__('Only number is allowed here', 'tourmaster')
                            ),

                        )
                    ),

                )) // tourmaster_tour_options
            )); // tourmaster_page_option
        }
    }
}