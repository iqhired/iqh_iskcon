<?php
	/*
	*	Tourmaster Plugin
	*/

	if( !function_exists('tourmaster_plugin_activation') ){
		function tourmaster_plugin_activation(){

			// check previous plugin version
			$current_version = 3.03;

			tourmaster_table_init();


			tourmaster_set_plugin_role();

			wp_schedule_event(time(), 'hourly', 'tourmaster_schedule_hourly');
			wp_schedule_event(time(), 'daily', 'tourmaster_schedule_daily');

			// update the plugin version
			update_option('tourmaster-plugin-version', 	$current_version);
		}
	}
	if( !function_exists('tourmaster_plugin_deactivation') ){
		function tourmaster_plugin_deactivation(){
			wp_clear_scheduled_hook('tourmaster_schedule_hourly');
			wp_clear_scheduled_hook('tourmaster_schedule_daily');
		}
	}

	// add_action('plugins_loaded', 'tourmaster_custom_schedule');
	if( !function_exists('tourmaster_custom_schedule') ){
		function tourmaster_custom_schedule(){
			$current_time = strtotime("now");
			$daily_schedule = get_option('tourmaster_daily_schedule', '');
			if( empty($daily_schedule) || $current_time > $daily_schedule + 43200 ){
				do_action('tourmaster_schedule_daily');
				update_option('tourmaster_daily_schedule', $current_time);
			}

			$hourly_schedule = get_option('tourmaster_hourly_schedule', '');
			if( empty($hourly_schedule) || $current_time > $hourly_schedule + 3600 ){
				do_action('tourmaster_schedule_hourly');
				update_option('tourmaster_hourly_schedule', $current_time);
			}
		}
	}

	if( !function_exists('tourmaster_table_init') ){
		function tourmaster_table_init(){

			// require necessary function
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

			global $wpdb;
			$charset_collate = $wpdb->get_charset_collate();

			// order table
			$sql = "CREATE TABLE {$wpdb->prefix}tourmaster_order (
				id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				user_id bigint(20) UNSIGNED DEFAULT NULL,
				booking_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
				tour_id bigint(20) UNSIGNED DEFAULT NULL,
				travel_date date DEFAULT '0000-00-00' NOT NULL,
				package_group_slug varchar(100) DEFAULT '' NOT NULL,
				traveller_amount tinyint UNSIGNED DEFAULT NULL,
				male_amount tinyint UNSIGNED DEFAULT NULL,
				female_amount tinyint UNSIGNED DEFAULT NULL,
				contact_info longtext DEFAULT NULL,
				billing_info longtext DEFAULT NULL,
				traveller_info longtext DEFAULT NULL,
				coupon_code varchar(20) DEFAULT NULL,
				order_status varchar(20) DEFAULT NULL,
				payment_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
				total_price decimal(19,4) DEFAULT NULL,
				pricing_info longtext DEFAULT NULL,
				payment_info longtext DEFAULT NULL,
				booking_detail longtext DEFAULT NULL,
				PRIMARY KEY  (id)
			) {$charset_collate};";
			dbDelta($sql);

			// review table
			$sql = "CREATE TABLE {$wpdb->prefix}tourmaster_review (
				review_id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				review_tour_id bigint(20) UNSIGNED NOT NULL,
				order_id bigint(20) UNSIGNED DEFAULT NULL,
				review_score tinyint DEFAULT NULL,
				review_type varchar(20) DEFAULT NULL,
				review_description longtext DEFAULT NULL,
				review_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
				reviewer_name varchar(100) DEFAULT NULL,
				reviewer_email varchar(100) DEFAULT NULL,
				PRIMARY KEY  (review_id)
			) {$charset_collate};";
			dbDelta($sql);
		}
	}

	if( !function_exists('tourmaster_version_plugin_init') ){
		function tourmaster_version_plugin_init(){

			// version 2.0.0
			global $wpdb;

			$sql  = "SELECT * FROM {$wpdb->postmeta} ";
			$sql .= "WHERE meta_key = 'tourmaster-tour-option' ";
			$results = $wpdb->get_results($sql);

			foreach( $results as $result ){
				$tour_option = maybe_unserialize($result->meta_value);
				if( !empty($tour_option['tour-price-discount-text']) ){
					update_post_meta($result->post_id, 'tourmaster-tour-discount', 'true');
				}else{
					delete_post_meta($result->post_id, 'tourmaster-tour-discount');
				}
			}

			// version 3.0.0

			// calculate rating score again
			$sql  = "SELECT * FROM {$wpdb->postmeta} ";
			$sql .= "WHERE meta_key = 'tourmaster-tour-rating' ";
			$results = $wpdb->get_results($sql);

			foreach( $results as $result ){
				$rating = maybe_unserialize($result->meta_value);
				if( !empty($rating['reviewer']) ){
					$score = intval($rating['score']) / intval($rating['reviewer']);
					update_post_meta($result->post_id, 'tourmaster-tour-rating-score', $score);
				}else{
					delete_post_meta($result->post_id, 'tourmaster-tour-rating-score');
				}
			}

			// ver 3.0.0 - 1
			// move review table
			$results = tourmaster_get_booking_data();
			foreach( $results as $result ){

				// insert review data if exists
				if( !empty($result->review_date) && $result->review_date != '0000-00-00 00:00:00' ){
					tourmaster_insert_review_data(array(
						'tour_id' => $result->tour_id,
						'score' => $result->review_score,
						'type' =>  $result->review_type,
						'description' => $result->review_description,
						'date' => $result->review_date,
						'order_id' => $result->id
					));
				}
			}

			// drop the old column out
			$wpdb->query("ALTER TABLE {$wpdb->prefix}tourmaster_order DROP COLUMN review_score");
			$wpdb->query("ALTER TABLE {$wpdb->prefix}tourmaster_order DROP COLUMN review_type");
			$wpdb->query("ALTER TABLE {$wpdb->prefix}tourmaster_order DROP COLUMN review_description");
			$wpdb->query("ALTER TABLE {$wpdb->prefix}tourmaster_order DROP COLUMN review_date");

		} // tourmaster_version_plugin_init
	}

	add_action('tourmaster_after_save_plugin_option', 'tourmaster_set_plugin_custom_role', 99);
	if( !function_exists('tourmaster_set_plugin_custom_role') ){
		function tourmaster_set_plugin_custom_role(){

			// role/capability
			remove_role('tour_staff');
			remove_role('tour_author');

			// for tour staff
			$staff_cap = tourmaster_get_option('general', 'tour-staff-capability', '');
			$staff_capability = array( 'read' => true );
			if( !empty($staff_cap) ){
				foreach( $staff_cap as $cap ){
					$staff_capability[$cap] = true;
				}
			}
			$staff_capability['manage_woocommerce'] = true;
 			add_role('tour_staff', esc_html__('Tour Staff', 'tourmaster'), $staff_capability);

			// for tour author
			$author_cap = tourmaster_get_option('general', 'tour-author-capability', '');
			$author_capability = array( 'read' => true );
			if( !empty($author_cap) ){
				foreach( $author_cap as $cap ){
					$author_capability[$cap] = true;
				}
			}
			$author_capability['manage_woocommerce'] = true;
			add_role('tour_author', esc_html__('Tour Author', 'tourmaster'), $author_capability);

		}
	}
	if( !function_exists('tourmaster_set_plugin_role') ){
		function tourmaster_set_plugin_role(){

			// for administrator
			$post_type_cap = array('edit_%s', 'read_%s', 'delete_%s',
				'edit_%ss', 'edit_others_%ss', 'publish_%ss', 'read_private_%ss', 'delete_%ss');

			$admin = get_role('administrator');
			foreach( $post_type_cap as $cap ){
				$admin->add_cap(str_replace('%s', 'tour', $cap));
				$admin->add_cap(str_replace('%s', 'coupon', $cap));
                $admin->add_cap(str_replace('%s', 'travellers_id', $cap));
                // $admin->add_cap(str_replace('%s', 'seva', $cap));
				$admin->add_cap(str_replace('%s', 'service', $cap));
			}
			$admin->add_cap('manage_tour_category');
			$admin->add_cap('manage_tour_tag');
			$admin->add_cap('manage_tour_filter');
			$admin->add_cap('manage_tour_order');

			// custom role
			tourmaster_set_plugin_custom_role();

		} // tourmaster_set_plugin_role
	}