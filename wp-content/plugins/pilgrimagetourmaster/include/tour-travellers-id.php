<?php
	/*	
	*	Tourmaster Plugin
	*	---------------------------------------------------------------------
	*	for tour travellers ID post type
	*	---------------------------------------------------------------------
	*/

    // create post type
	add_action('init', 'tourmaster_tour_travellers_id_init');
	if( !function_exists('tourmaster_tour_travellers_id_init') ){
		function tourmaster_tour_travellers_id_init() {
			
			// custom post type
			$supports = apply_filters('tourmaster_custom_post_support', array('title', 'author', 'custom-fields'), 'travellers_id');

			$labels = array(
				'name'               => esc_html__('Tour Traveller ID', 'tourmaster'),
				'singular_name'      => esc_html__('Tour Traveller ID', 'tourmaster'),
				'menu_name'          => esc_html__('Tour Traveller ID', 'tourmaster'),
				'name_admin_bar'     => esc_html__('Tour Traveller ID', 'tourmaster'),
				'add_new'            => esc_html__('Add New', 'tourmaster'),
				'add_new_item'       => esc_html__('Add New Tour Traveller ID', 'tourmaster'),
				'new_item'           => esc_html__('New Tour Traveller ID', 'tourmaster'),
				'edit_item'          => esc_html__('Edit Tour Traveller ID', 'tourmaster'),
				'view_item'          => esc_html__('View Tour Traveller ID', 'tourmaster'),
				'all_items'          => esc_html__('All Tour Traveller ID', 'tourmaster'),
				'search_items'       => esc_html__('Search Tour Traveller ID', 'tourmaster'),
				'parent_item_colon'  => esc_html__('Parent Tour Traveller ID:', 'tourmaster'),
				'not_found'          => esc_html__('No Tour Traveller ID found.', 'tourmaster'),
				'not_found_in_trash' => esc_html__('No Tour Traveller ID found in Trash.', 'tourmaster')
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
					'edit_post'          => 'edit_travellers_id',
					'read_post'          => 'read_travellers_id',
					'delete_post'        => 'delete_travellers_id',
//					'delete_posts'       => 'delete_travellers_ids',
//					'edit_posts'         => 'edit_travellers_ids',
//					'create_posts'       => 'edit_travellers_ids',
					'edit_others_posts'  => 'edit_others_travellers_ids',
					'delete_others_posts'  	=> 'edit_others_travellers_ids',
					'publish_posts'      	=> 'publish_travellers_ids',
					'edit_published_posts'  => 'publish_travellers_ids',
					'read_private_posts' 	=> 'read_private_travellers_ids',
					'edit_private_posts' 	=> 'read_private_travellers_ids',
					'delete_private_posts' 	=> 'read_private_travellers_ids',
				),
				'has_archive'        => false,
				'hierarchical'       => false,
				'menu_position'      => null,
				'supports'           => $supports
			);
			register_post_type('tour_travellers_id', $args);

			// apply single template filter
			add_filter('single_template', 'tourmaster_tour_travellers_id_template');

		}
	} // tourmaster_post_type_init

	if( !function_exists('tourmaster_tour_travellers_id_template') ){
		function tourmaster_tour_travellers_id_template( $template ){

			if( get_post_type() == 'travellers_id' ){
				$template = get_404_template();
			}
			return $template;
		}
	}
	
	// create an option
	if( is_admin() ){ add_action('after_setup_theme', 'tourmaster_tour_travellers_id_option_init'); }
	if( !function_exists('tourmaster_tour_travellers_id_option_init')){
		function tourmaster_tour_travellers_id_option_init(){
			if( class_exists('tourmaster_page_option') ){
				new tourmaster_page_option(array(
					'post_type' => array('tour_travellers_id'),
					'title' => esc_html__('Additional Service', 'tourmaster'),
					'title-icon' => 'fa fa-plane',
					'slug' => 'tourmaster-travellers-id-option',

					'options' => apply_filters('tourmaster_tour_options', array(

						'general' => array(
							'title' => esc_html__('General', 'tourmaster'),
							'options' => array(
								'id-type-name' => array(
									'title' => esc_html__('ID Type Name', 'tourmaster'),
									'type' => 'text',
									'description' => esc_html__('Only text is allowed here', 'tourmaster')
								),
								'mandatory' => array(
									'title' => esc_html__('Mandatory', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'disable'
								),

							)
						),

					)) // tourmaster_tour_options
				)); // tourmaster_page_option
			}
		}
	}	