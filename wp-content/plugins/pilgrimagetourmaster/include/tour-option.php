<?php
	/*
	*	Tourmaster Plugin
	*	---------------------------------------------------------------------
	*	for tour post type
	*	---------------------------------------------------------------------
	*/

	// create post type
	add_action('init', 'tourmaster_tour_init');
	if( !function_exists('tourmaster_tour_init') ){
		function tourmaster_tour_init() {

			// custom post type
			$slug = apply_filters('tourmaster_custom_post_slug', 'tour', 'tour');
			$supports = apply_filters('tourmaster_custom_post_support', array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'custom-fields'), 'tour');
            add_filter( 'manage_posts_columns', 'revealid_add_id_column', 5 );
            add_action( 'manage_posts_custom_column', 'revealid_id_column_content', 5, 2 );


//            function revealid_add_id_column( $columns ) {
//                $columns['revealid_id'] = 'Tour ID';
//                return $columns;
//            }

            function revealid_id_column_content( $column, $id ) {
                if( 'revealid_id' == $column ) {
                    echo $id;
                }
            }

            function revealid_add_id_column( $columns ) {
                $checkbox = array_slice( $columns , 0, 1 );
                $columns = array_slice( $columns , 1 );

                $id['revealid_id'] = 'ID';

                $columns = array_merge( $checkbox, $id, $columns );
                return $columns;
            }

			$labels = array(
				'name'               => esc_html__('Tour', 'tourmaster'),
				'singular_name'      => esc_html__('Tour', 'tourmaster'),
				'menu_name'          => esc_html__('Tour', 'tourmaster'),
				'name_admin_bar'     => esc_html__('Tour', 'tourmaster'),
				'add_new'            => esc_html__('Add New', 'tourmaster'),
				'add_new_item'       => esc_html__('Add New Tour', 'tourmaster'),
				'new_item'           => esc_html__('New Tour', 'tourmaster'),
				'edit_item'          => esc_html__('Edit Tour', 'tourmaster'),
				'view_item'          => esc_html__('View Tour', 'tourmaster'),
				'all_items'          => esc_html__('All Tour', 'tourmaster'),
				'search_items'       => esc_html__('Search Tour', 'tourmaster'),
				'parent_item_colon'  => esc_html__('Parent Tour:', 'tourmaster'),
				'not_found'          => esc_html__('No tour found.', 'tourmaster'),
				'not_found_in_trash' => esc_html__('No tour found in Trash.', 'tourmaster')
			);
			$args = array(
				'labels'             => $labels,
				'description'        => esc_html__('Description.', 'tourmaster'),
				'public'             => true,
				'publicly_queryable' => true,
				'exclude_from_search'=> false,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'query_var'          => true,
				'rewrite'            => array('slug' => $slug),
				'map_meta_cap' 		 => true,
				'capabilities' => array(
					'edit_post'          => 'edit_tour',
					'read_post'          => 'read_tour',
					'delete_post'        => 'delete_tour',
//					'delete_posts'       => 'delete_tours',
//					'edit_posts'         => 'edit_tours',
//					'create_posts'       => 'edit_tours',
					'edit_others_posts'  	=> 'edit_others_tours',
					'delete_others_posts '  => 'edit_others_tours',
					'publish_posts'      	=> 'publish_tours',
					'edit_published_posts' 	=> 'publish_tours',
					'read_private_posts' 	=> 'read_private_tours',
					'edit_private_posts' 	=> 'read_private_tours',
					'delete_private_posts' 	=> 'read_private_tours',
				),
				'has_archive'        => false,
				'hierarchical'       => false,
				'menu_position'      => null,
				'supports'           => $supports
			);
			register_post_type('tour', $args);

			// custom taxonomy
			$slug = apply_filters('tourmaster_custom_post_slug', 'tour-category', 'tour_category');
			$args = array(
				'hierarchical'      => true,
				'label'             => esc_html__('Tour Category', 'tourmaster'),
				'show_ui'           => true,
				'show_admin_column' => true,
				'query_var'         => true,
				'rewrite'           => array('slug' => $slug),
				'capabilities'		=> array(
					'manage_terms' => 'manage_tour_category',
					'edit_terms' => 'manage_tour_category',
					'delete_terms' => 'manage_tour_category',
					'assign_terms' => 'manage_tour_category'
				)
			);
			register_taxonomy('tour_category', array('tour'), $args);
			register_taxonomy_for_object_type('tour_category', 'tour');

			$slug = apply_filters('tourmaster_custom_post_slug', 'tour-tag', 'tour_tag');
			$args = array(
				'hierarchical'      => false,
				'label'             => esc_html__('Tour Tag', 'tourmaster'),
				'show_ui'           => true,
				'show_admin_column' => true,
				'query_var'         => true,
				'rewrite'           => array('slug' => $slug),
				'capabilities'		=> array(
					'manage_terms' => 'manage_tour_tag',
					'edit_terms' => 'manage_tour_tag',
					'delete_terms' => 'manage_tour_tag',
					'assign_terms' => 'manage_tour_tag'
				)
			);
			register_taxonomy('tour_tag', array('tour'), $args);
			register_taxonomy_for_object_type('tour_tag', 'tour');

			// custom taxonomy meta
			new tourmaster_taxonomy_option(array(
				'taxonomy' => 'tour_category',
				'options' => array(
					'thumbnail' => array(
						'title' => esc_html__('Thumbnail', 'tourmaster'),
						'type' => 'upload'
					)
				)
			));
			new tourmaster_taxonomy_option(array(
				'taxonomy' => 'tour_tag',
				'options' => array(
					'thumbnail' => array(
						'title' => esc_html__('Thumbnail', 'tourmaster'),
						'type' => 'upload'
					)
				)
			));

			// apply single template filter
			add_filter('single_template', 'tourmaster_tour_template');


		}
	} // tourmaster_post_type_init

	if( !function_exists('tourmaster_tour_template') ){
		function tourmaster_tour_template( $template ){

			if( get_post_type() == 'tour' ){
				$tour_style = tourmaster_get_option('general', 'single-tour-style', 'style-1');
				if( $tour_style == 'style-1' ){
					$template = TOURMASTER_LOCAL . '/single/tour.php';
				}else if( $tour_style == 'style-2' ){
					$template = TOURMASTER_LOCAL . '/single/tour-2.php';
				}
			}

			return $template;
		}
	}

	// add page builder to tour
	if( is_admin() ){ add_filter('gdlr_core_page_builder_post_type', 'tourmaster_gdlr_core_tour_add_page_builder'); }
	if( !function_exists('tourmaster_gdlr_core_tour_add_page_builder') ){
		function tourmaster_gdlr_core_tour_add_page_builder( $post_type ){
			$post_type[] = 'tour';
			return $post_type;
		}
	}

	// init page builder value
	if( is_admin() ){ add_filter('gdlr_core_tour_page_builder_val_init', 'tourmaster_tour_page_builder_val_init'); }
	if( !function_exists('tourmaster_tour_page_builder_val_init') ){
		function tourmaster_tour_page_builder_val_init( $value ){
   //       $value = '[{"template":"wrapper","type":"background","value":{"id":"","class":"","content-layout":"boxed","max-width-wrapper":"","max-width":"","enable-space":"disable","hide-this-wrapper-in":"none","animation":"none","animation-location":"0.8","full-height":"enable","decrease-height":"0px","centering-content":"disable","background-type":"color","background-color-style":"plain","background-color":"","background-color-opacity":"1","background-gradient-color":"","background-gradient-color-opacity":"1","background-image":"","mobile-background-image":"","background-image-style":"cover","background-image-position":"center","background-video-url":"","background-video-url-mp4":"","background-video-url-webm":"","background-video-url-ogg":"","background-video-image":"","background-pattern":"pattern-1","background-opacity":"","background-filter":"none","background-blur-size":"","pattern-opacity":"1","parallax-speed":"0.8","overflow":"visible","enable-container-background":"disable","container-background-color":"","container-shadow-color":"","container-shadow-opacity":"0.2","enable-background-particle":"disable","enable-marquee":"disable","marquee-text":"","marquee-position":"top","marquee-position-offset":"","marquee-speed":"10000","marquee-direction":"left","marquee-font-size":"","marquee-font-weight":"","marquee-font-letter-spacing":"","marquee-text-color":"","marquee-opacity":"1","border-type":"none","border-pre-spaces":{"top":"20px","right":"20px","bottom":"20px","left":"20px","settings":"link"},"border-width":{"top":"1px","right":"1px","bottom":"1px","left":"1px","settings":"link"},"border-color":"#ffffff","border-style":"solid","background-shadow-color":"","background-shadow-opacity":"0.2","padding":{"top":"30px","right":"0px","bottom":"30px","left":"0px","settings":"unlink"},"margin":{"top":"0px","right":"0px","bottom":"0px","left":"0px","settings":"link"},"container-margin":{"top":"0px","right":"0px","bottom":"0px","left":"0px","settings":"link"},"mobile-padding":{"top":"","right":"","bottom":"","left":"","settings":"link"},"mobile-margin":{"top":"","right":"","bottom":"","left":"","settings":"link"},"skin":"Blue Icon"},"items":[{"template":"element","type":"tab","value":{"id":"","class":"","tabs":[{"title":"Details","content":"<div id=\"yatra-details\"><p><strong>Importance of Yatra<strong>:<\/strong><\/strong>Uttar Bharat Pilgrimage is a unique opportunity to visit those important places which are considered as the epitome for purifying oneself and also the forefathers. In this pilgrimage we visit Ayodhya - the birthplace of Supreme Lord Sri Ramachandra, Naimisharanya - the place where Srimad Bhagavatam was spoken by Suta Goswami, Chitrakoot - where Sri Sita, Rama, Lakshmana spent many years, Prayagraj - which hosts the historic Triveni Sangam - also famous for its Margha mela and Kumbha mela, Kashi - one of the important moksha-puri, Gaya - the abode of the lotus feet of Sri Vishnu.<\/p><p>Along with these magnanimous places, we also visit interesting places in and around...like the Bindu Madhava temple, Veni Madhava temple, the Gupta Godavari, Akshaya vat and so on. This pilgrimage is also attractive because of inclusions like - Triveni sangam bath, Sarayu river bath, Ganga Arati at Kashi, Pinda-Dana and Putra-Tarpana at Gaya, visit to Bodha-Gaya where Lord Buddha achieved enlightenment.<\/p><p>Complimented with ecstatic kirtan, Satvik food, luxurious accommodation and pastime narration\u2019s - this pilgrimage is not to be missed at all.<\/p><p>\u00a0<\/p><hr \/><p>[gdlr_widget_list title=\"Places to visit\" title-color=\"\" background-color=\"\"\u00a0 color=\"\" border-color=\"\" ]<\/p><ul><li class=\"gdlr-core-icon-list-content-wrap\"><span class=\"gdlr-core-icon-list-content\">Kumbakonam - Chakrapani,Sarangapani, Ramaswamy, Uppilliyappan temples.<\/span><\/li><li class=\"gdlr-core-icon-list-content-wrap\">Srirangam - Ranganatha Swamy, Yamunacharya, Narasimha temples, Kaveri and more<\/li><\/ul><p>[\/gdlr_widget_list]<\/p><hr \/><p>[gallery source=\"gdlr-core\" style=\"slider\" slider-navigation=\"bullet\" thumbnail-size=\"\" columns=\"1\" size=\"large\" ids=\"4489,4488,4485\"]<\/p><\/div>"},{"title": "Yatra Packages","content":"<div id=\"yatra-packages\"><p>[gdlr_widget_list]<\/p><div style=\"background-color: rgba(16, 48, 105, 0.9); color: #fff; width: 100%; padding: 10px;\"><strong>Normal Package<\/strong><\/div><ul style=\"padding-left: 25px; padding-top: 25px; padding-bottom: 10px; line-height: 1.7;\"><li><strong>Price<\/strong> : Adult - Rs.6,500 Sr Citizen - Rs.6,000 Child - Rs.6,000<\/li><li><b>Accommodation<\/b> : Dormitory<\/li><li><strong>Food<\/strong>: Vegetarian food will be provided.<\/li><\/ul><div style=\"background-color: rgba(16, 48, 105, 0.9); color: #fff; width: 100%; padding: 10px;\"><strong>Deluxe Package<\/strong><\/div><ul style=\"padding-left: 25px; padding-top: 25px; padding-bottom: 10px; line-height: 1.7;\"><li><strong>Price<\/strong> : Adult - Rs.8000, Sr Citizen - Rs.7000 Child - Rs.7000.<\/li><li><b>Accommodation:<\/b> 3-star accommodation, Vegetarian food.<\/li><li><strong>Food<\/strong>: Vegetarian food will be provided.<\/li><\/ul><div style=\"background-color: rgba(16, 48, 105, 0.9); color: #fff; width: 100%; padding: 10px;\"><strong>Super Deluxe Package<\/strong><\/div><ul style=\"padding-left: 25px; padding-top: 25px; padding-bottom: 10px; line-height: 1.7;\"><li><strong>Price<\/strong> : Adult - Rs. 8000, Sr Citizen - Rs. 7000 Child - Rs. 7000.<\/li><li><b>Accommodation: <\/b>3-star accommodation, Vegetarian food.<\/li><li><strong>Food<\/strong>: Vegetarian food will be provided.<\/li><\/ul><\/div>"},{"title":"Itinerary","content":"<div class=\"gdlr-core-pbf-element\"><div class=\"gdlr-core-toggle-box-item gdlr-core-item-pdlr gdlr-core-item-pdb gdlr-core-toggle-box-style-background-title gdlr-core-left-align\" style=\"padding-bottom: 15px;\"><div class=\"gdlr-core-toggle-box-item-tab clearfix gdlr-core-active\"><div class=\"gdlr-core-toggle-box-item-icon gdlr-core-js gdlr-core-skin-icon \">\u00a0<\/div><div class=\"gdlr-core-toggle-box-item-content-wrapper\"><h4 class=\"gdlr-core-toggle-box-item-title gdlr-core-js gdlr-core-skin-e-background gdlr-core-skin-e-content\"><span class=\"gdlr-core-head\">Day 1<\/span>\u00a0Arrive in Z\u00fcrich, Switzerland<\/h4><div class=\"gdlr-core-toggle-box-item-content\"><p>We will meet at 4 p.m. at our hotel in Luzern (Lucerne) for a \u201cWelcome to Switzerland\u201d meeting. Then we will take a meandering evening walk through Switzerlands most charming lakeside town, and get acquainted with one another over dinner together. Sleep in Luzern (2 nights). No bus. Walking: light.<\/p><\/div><\/div><\/div><div class=\"gdlr-core-toggle-box-item-tab clearfix gdlr-core-active\"><div class=\"gdlr-core-toggle-box-item-icon gdlr-core-js gdlr-core-skin-icon \">\u00a0<\/div><div class=\"gdlr-core-toggle-box-item-content-wrapper\"><h4 class=\"gdlr-core-toggle-box-item-title gdlr-core-js gdlr-core-skin-e-background gdlr-core-skin-e-content\"><span class=\"gdlr-core-head\">Day 2<\/span>Z\u00fcrich\u2013Biel\/Bienne\u2013Neuch\u00e2tel\u2013Geneva<\/h4><div class=\"gdlr-core-toggle-box-item-content\"><p>Enjoy an orientation walk of Zurich\u2019s OLD TOWN, Switzerland\u2019s center of banking and commerce. Then, leave Zurich and start your Swiss adventure. You\u2019ll quickly discover that Switzerland isn\u2019t just home to the Alps, but also to some of the most beautiful lakes. First, stop at the foot of the Jura Mountains in the picturesque town of Biel, known as Bienne by French-speaking Swiss, famous for watch-making, and explore the historical center. Next, enjoy a scenic drive to lakeside Neuch\u00e2tel, dominated by the medieval cathedral and castle. Time to stroll along the lake promenade before continuing to stunning Geneva, the second-largest city in Switzerland, with its fantastic lakeside location and breathtaking panoramas of the Alps.<\/p><\/div><\/div><\/div><div class=\"gdlr-core-toggle-box-item-tab clearfix gdlr-core-active\"><div class=\"gdlr-core-toggle-box-item-icon gdlr-core-js gdlr-core-skin-icon \">\u00a0<\/div><div class=\"gdlr-core-toggle-box-item-content-wrapper\"><h4 class=\"gdlr-core-toggle-box-item-title gdlr-core-js gdlr-core-skin-e-background gdlr-core-skin-e-content\"><span class=\"gdlr-core-head\">Day 3<\/span>Enchanting Engelberg<\/h4><div class=\"gdlr-core-toggle-box-item-content\"><p>Our morning drive takes us from Swiss lakes to Swiss Army. At the once-secret Swiss army bunker at Fortress F\u00fcrigen, we will see part of the massive defense system designed to keep Switzerland strong and neutral. Afterward, a short drive into the countryside brings us to the charming Alpine village of Engelberg, our picturesque home for the next two days. We will settle into our lodge then head out for an orientation walk. Our stroll through the village will end at the Engelberg Abbey, a Benedictine monastery with its own cheese-making operation. You will have free time to wander back before dinner together. Sleep in Engelberg (2 nights). Bus: 1 hr. Walking: light.<\/p><\/div><\/div><\/div><div class=\"gdlr-core-toggle-box-item-tab clearfix gdlr-core-active\"><div class=\"gdlr-core-toggle-box-item-icon gdlr-core-js gdlr-core-skin-icon \">\u00a0<\/div><div class=\"gdlr-core-toggle-box-item-content-wrapper\"><h4 class=\"gdlr-core-toggle-box-item-title gdlr-core-js gdlr-core-skin-e-background gdlr-core-skin-e-content\"><span class=\"gdlr-core-head\">Day 4<\/span>Interlaken Area. Excursion to The Jungfrau Massif<\/h4><div class=\"gdlr-core-toggle-box-item-content\"><p>An unforgettable trip to the high Alpine wonderland of ice and snow is the true highlight of a visit to Switzerland. Globus Local Favorite At an amazing 11,332 feet, the JUNGFRAUJOCH is Europe\u2019s highest railway station. Jungfrau\u2019s 13,642-foot summit was first ascended in 1811 and in 1912 the rack railway was opened. There are lots of things to do here: enjoy the ALPINE SENSATION, THE PANORAMA 360\u00b0 EXPERIENCE, and the ICE PALACE. Also receive your JUNGFRAU PASSPORT as a souvenir to take home with you. The round trip to the \u201cTop of Europe\u201d by MOUNTAIN TRAIN will take most of the day.<\/p><\/div><\/div><\/div><div class=\"gdlr-core-toggle-box-item-tab clearfix gdlr-core-active\"><div class=\"gdlr-core-toggle-box-item-icon gdlr-core-js gdlr-core-skin-icon \">\u00a0<\/div><div class=\"gdlr-core-toggle-box-item-content-wrapper\"><h4 class=\"gdlr-core-toggle-box-item-title gdlr-core-js gdlr-core-skin-e-background gdlr-core-skin-e-content\"><span class=\"gdlr-core-head\">Day 5<\/span>Lake Geneva and Ch\u00e2teau de Chillon<\/h4><div class=\"gdlr-core-toggle-box-item-content\"><p>Its market day in Lausanne! Enjoy browsing and packing a picnic lunch for our 11 a.m. boat cruise on Lake Geneva. A few miles down-shore we will dock at Ch\u00e2teau de Chillon, where we will have a guided tour of this delightfully medieval castle on the water. On our way back we will take time to peek into the vineyards surrounding Lutry before returning to Lausanne. Boat: 2 hrs. Bus: 1 hr. Walking: moderate.<\/p><\/div><\/div><\/div><\/div><\/div><hr \/><div class=\"\"><iframe src=\"https:\/\/www.google.com\/maps\/d\/embed?mid=1mGgtylMQHGAKR6HR8r8YLe5W4LU\" width=\"640\" height=\"480\"><\/iframe><\/div>"},{"title":"Seva","content":"<div id=\"seva-details\"><ul><li>Vaishnava Bhojana seva - Rs.5000<\/li><li>Sponsor a pilgrim - Rs.35000<\/li><li>Tulsi Archana (1 place)- Rs.500<\/li><li>Tulsi Archana at 6 places - Rs.3000<\/li><li>Pushparchana (1 place) - Rs.500<\/li><li>Pushparchana (6places) - Rs.3000<\/li><li>Bhoga\/Naivedya seva (1place) - Rs.1000<\/li><li>Bhoga\/Naivedya seva (6places) - Rs.6000<\/li><li>Hundi seva - any amount<\/li><\/ul><\/div>"}],"style":"style2-horizontal","align":"left","tab-title-color":"","tab-title-active-color":"","tab-title-background-color":"","tab-title-active-background-color":"","tab-title-border-color":"","tab-title-border-active-color":"","tab-content-color":"","padding-bottom":"30px"}}]}]';
//            $value = '[{"template":"wrapper","type":"background","value":{"id":"","class":"","content-layout":"boxed","max-width-wrapper":"","max-width":"","enable-space":"disable","hide-this-wrapper-in":"none","animation":"none","animation-location":"0.8","full-height":"enable","decrease-height":"0px","centering-content":"disable","background-type":"color","background-color-style":"plain","background-color":"","background-color-opacity":"1","background-gradient-color":"","background-gradient-color-opacity":"1","background-image":"","mobile-background-image":"","background-image-style":"cover","background-image-position":"center","background-video-url":"","background-video-url-mp4":"","background-video-url-webm":"","background-video-url-ogg":"","background-video-image":"","background-pattern":"pattern-1","background-opacity":"","background-filter":"none","background-blur-size":"","pattern-opacity":"1","parallax-speed":"0.8","overflow":"visible","enable-container-background":"disable","container-background-color":"","container-shadow-color":"","container-shadow-opacity":"0.2","enable-background-particle":"disable","enable-marquee":"disable","marquee-text":"","marquee-position":"top","marquee-position-offset":"","marquee-speed":"10000","marquee-direction":"left","marquee-font-size":"","marquee-font-weight":"","marquee-font-letter-spacing":"","marquee-text-color":"","marquee-opacity":"1","border-type":"none","border-pre-spaces":{"top":"20px","right":"20px","bottom":"20px","left":"20px","settings":"link"},"border-width":{"top":"1px","right":"1px","bottom":"1px","left":"1px","settings":"link"},"border-color":"#ffffff","border-style":"solid","background-shadow-color":"","background-shadow-opacity":"0.2","padding":{"top":"30px","right":"0px","bottom":"30px","left":"0px","settings":"unlink"},"margin":{"top":"0px","right":"0px","bottom":"0px","left":"0px","settings":"link"},"container-margin":{"top":"0px","right":"0px","bottom":"0px","left":"0px","settings":"link"},"mobile-padding":{"top":"","right":"","bottom":"","left":"","settings":"link"},"mobile-margin":{"top":"","right":"","bottom":"","left":"","settings":"link"},"skin":"Blue Icon"},"items":[{"template":"element","type":"tab","value":{"id":"","class":"","tabs":[{"title":"Details","content":"<div id=\"yatra-details\"><p><strong>Importance of Yatra<strong>:<\/strong><\/strong>Uttar Bharat Pilgrimage is a unique opportunity to visit those important places which are considered as the epitome for purifying oneself and also the forefathers. In this pilgrimage we visit Ayodhya - the birthplace of Supreme Lord Sri Ramachandra, Naimisharanya - the place where Srimad Bhagavatam was spoken by Suta Goswami, Chitrakoot - where Sri Sita, Rama, Lakshmana spent many years, Prayagraj - which hosts the historic Triveni Sangam - also famous for its Margha mela and Kumbha mela, Kashi - one of the important moksha-puri, Gaya - the abode of the lotus feet of Sri Vishnu.<\/p><p>Along with these magnanimous places, we also visit interesting places in and around...like the Bindu Madhava temple, Veni Madhava temple, the Gupta Godavari, Akshaya vat and so on. This pilgrimage is also attractive because of inclusions like - Triveni sangam bath, Sarayu river bath, Ganga Arati at Kashi, Pinda-Dana and Putra-Tarpana at Gaya, visit to Bodha-Gaya where Lord Buddha achieved enlightenment.<\/p><p>Complimented with ecstatic kirtan, Satvik food, luxurious accommodation and pastime narration\u2019s - this pilgrimage is not to be missed at all.<\/p><p>\u00a0<\/p><hr \/><p>[gdlr_widget_list title=\"Places to visit\" title-color=\"\" background-color=\"\"\u00a0 color=\"\" border-color=\"\" ]<\/p><ul><li class=\"gdlr-core-icon-list-content-wrap\"><span class=\"gdlr-core-icon-list-content\">Kumbakonam - Chakrapani,Sarangapani, Ramaswamy, Uppilliyappan temples.<\/span><\/li><li class=\"gdlr-core-icon-list-content-wrap\">Srirangam - Ranganatha Swamy, Yamunacharya, Narasimha temples, Kaveri and more<\/li><\/ul><p>[\/gdlr_widget_list]<\/p><hr \/><p>[gallery source=\"gdlr-core\" style=\"slider\" slider-navigation=\"bullet\" thumbnail-size=\"\" columns=\"1\" size=\"large\" ids=\"4489,4488,4485\"]<\/p><\/div>"},{"title":"Yatra Packages","content":"<div id=\"yatra-packages\">\n<p>[gdlr_widget_list]<\/p>\n<div style=\"background-color: #f3f3f3; color: #4692e7; width: 100%; padding: 10px;\"><strong>Normal Package<\/strong><\/div>\n<ul style=\"padding-left: 10px;padding-top: 15px;padding-bottom: 20px;line-height: 1.7;border: 1px solid #f3f3f3;font-size: large !important;margin-bottom: 20px;\">\n<li><strong>Price<\/strong> : Adult - Rs.55,000 Sr Citizen - Rs.54,000 Child - Rs.46,000<\/li>\n<li><b>Accommodation<\/b> : Dormitory<\/li>\n<li><strong>Food<\/strong>: Vegetarian food will be provided.<\/li>\n<\/ul>\n<div style=\"background-color: #f3f3f3; color: #4692e7; width: 100%; padding: 10px;\"><strong>Deluxe Package<\/strong><\/div>\n<ul style=\"padding-left: 10px;padding-top: 15px;padding-bottom: 20px;line-height: 1.7;border: 1px solid #f3f3f3;font-size: large !important;margin-bottom: 20px;\">\n<li><strong>Price<\/strong> : Adult - Rs.60,000, Sr Citizen - Rs.59,000 Child - Rs.46,000.<\/li>\n<li><b>Accommodation:<\/b> 3-star accommodation, Vegetarian food.<\/li>\n<li><strong>Food<\/strong>: Vegetarian food will be provided.<\/li>\n<\/ul>\n<div style=\"background-color: #f3f3f3; color: #4692e7; width: 100%; padding: 10px;\"><strong>Super Deluxe Package<\/strong><\/div>\n<ul style=\"padding-left: 10px;padding-top: 15px;padding-bottom: 20px;line-height: 1.7;border: 1px solid #f3f3f3;font-size: large !important;margin-bottom: 20px;\">\n<li><strong>Price<\/strong> : Adult - Rs. 66,000, Sr Citizen - Rs. 65,000 Child - Rs. 46,000.<\/li>\n<li><b>Accommodation: <\/b>3-star accommodation, Vegetarian food.<\/li>\n<li><strong>Food<\/strong>: Vegetarian food will be provided.<\/li>\n<\/ul>\n<\/div>\n"},{"title":"Itinerary","content":"<div class=\"gdlr-core-pbf-element\"><div class=\"gdlr-core-toggle-box-item gdlr-core-item-pdlr gdlr-core-item-pdb gdlr-core-toggle-box-style-background-title gdlr-core-left-align\" style=\"padding-bottom: 15px;\"><div class=\"gdlr-core-toggle-box-item-tab clearfix gdlr-core-active\"><div class=\"gdlr-core-toggle-box-item-icon gdlr-core-js gdlr-core-skin-icon \">\u00a0<\/div><div class=\"gdlr-core-toggle-box-item-content-wrapper\"><h4 class=\"gdlr-core-toggle-box-item-title gdlr-core-js gdlr-core-skin-e-background gdlr-core-skin-e-content\"><span class=\"gdlr-core-head\">Day 1<\/span>\u00a0Arrive in Z\u00fcrich, Switzerland<\/h4><div class=\"gdlr-core-toggle-box-item-content\"><p>We will meet at 4 p.m. at our hotel in Luzern (Lucerne) for a \u201cWelcome to Switzerland\u201d meeting. Then we will take a meandering evening walk through Switzerlands most charming lakeside town, and get acquainted with one another over dinner together. Sleep in Luzern (2 nights). No bus. Walking: light.<\/p><\/div><\/div><\/div><div class=\"gdlr-core-toggle-box-item-tab clearfix gdlr-core-active\"><div class=\"gdlr-core-toggle-box-item-icon gdlr-core-js gdlr-core-skin-icon \">\u00a0<\/div><div class=\"gdlr-core-toggle-box-item-content-wrapper\"><h4 class=\"gdlr-core-toggle-box-item-title gdlr-core-js gdlr-core-skin-e-background gdlr-core-skin-e-content\"><span class=\"gdlr-core-head\">Day 2<\/span>Z\u00fcrich\u2013Biel\/Bienne\u2013Neuch\u00e2tel\u2013Geneva<\/h4><div class=\"gdlr-core-toggle-box-item-content\"><p>Enjoy an orientation walk of Zurich\u2019s OLD TOWN, Switzerland\u2019s center of banking and commerce. Then, leave Zurich and start your Swiss adventure. You\u2019ll quickly discover that Switzerland isn\u2019t just home to the Alps, but also to some of the most beautiful lakes. First, stop at the foot of the Jura Mountains in the picturesque town of Biel, known as Bienne by French-speaking Swiss, famous for watch-making, and explore the historical center. Next, enjoy a scenic drive to lakeside Neuch\u00e2tel, dominated by the medieval cathedral and castle. Time to stroll along the lake promenade before continuing to stunning Geneva, the second-largest city in Switzerland, with its fantastic lakeside location and breathtaking panoramas of the Alps.<\/p><\/div><\/div><\/div><div class=\"gdlr-core-toggle-box-item-tab clearfix gdlr-core-active\"><div class=\"gdlr-core-toggle-box-item-icon gdlr-core-js gdlr-core-skin-icon \">\u00a0<\/div><div class=\"gdlr-core-toggle-box-item-content-wrapper\"><h4 class=\"gdlr-core-toggle-box-item-title gdlr-core-js gdlr-core-skin-e-background gdlr-core-skin-e-content\"><span class=\"gdlr-core-head\">Day 3<\/span>Enchanting Engelberg<\/h4><div class=\"gdlr-core-toggle-box-item-content\"><p>Our morning drive takes us from Swiss lakes to Swiss Army. At the once-secret Swiss army bunker at Fortress F\u00fcrigen, we will see part of the massive defense system designed to keep Switzerland strong and neutral. Afterward, a short drive into the countryside brings us to the charming Alpine village of Engelberg, our picturesque home for the next two days. We will settle into our lodge then head out for an orientation walk. Our stroll through the village will end at the Engelberg Abbey, a Benedictine monastery with its own cheese-making operation. You will have free time to wander back before dinner together. Sleep in Engelberg (2 nights). Bus: 1 hr. Walking: light.<\/p><\/div><\/div><\/div><div class=\"gdlr-core-toggle-box-item-tab clearfix gdlr-core-active\"><div class=\"gdlr-core-toggle-box-item-icon gdlr-core-js gdlr-core-skin-icon \">\u00a0<\/div><div class=\"gdlr-core-toggle-box-item-content-wrapper\"><h4 class=\"gdlr-core-toggle-box-item-title gdlr-core-js gdlr-core-skin-e-background gdlr-core-skin-e-content\"><span class=\"gdlr-core-head\">Day 4<\/span>Interlaken Area. Excursion to The Jungfrau Massif<\/h4><div class=\"gdlr-core-toggle-box-item-content\"><p>An unforgettable trip to the high Alpine wonderland of ice and snow is the true highlight of a visit to Switzerland. Globus Local Favorite At an amazing 11,332 feet, the JUNGFRAUJOCH is Europe\u2019s highest railway station. Jungfrau\u2019s 13,642-foot summit was first ascended in 1811 and in 1912 the rack railway was opened. There are lots of things to do here: enjoy the ALPINE SENSATION, THE PANORAMA 360\u00b0 EXPERIENCE, and the ICE PALACE. Also receive your JUNGFRAU PASSPORT as a souvenir to take home with you. The round trip to the \u201cTop of Europe\u201d by MOUNTAIN TRAIN will take most of the day.<\/p><\/div><\/div><\/div><div class=\"gdlr-core-toggle-box-item-tab clearfix gdlr-core-active\"><div class=\"gdlr-core-toggle-box-item-icon gdlr-core-js gdlr-core-skin-icon \">\u00a0<\/div><div class=\"gdlr-core-toggle-box-item-content-wrapper\"><h4 class=\"gdlr-core-toggle-box-item-title gdlr-core-js gdlr-core-skin-e-background gdlr-core-skin-e-content\"><span class=\"gdlr-core-head\">Day 5<\/span>Lake Geneva and Ch\u00e2teau de Chillon<\/h4><div class=\"gdlr-core-toggle-box-item-content\"><p>Its market day in Lausanne! Enjoy browsing and packing a picnic lunch for our 11 a.m. boat cruise on Lake Geneva. A few miles down-shore we will dock at Ch\u00e2teau de Chillon, where we will have a guided tour of this delightfully medieval castle on the water. On our way back we will take time to peek into the vineyards surrounding Lutry before returning to Lausanne. Boat: 2 hrs. Bus: 1 hr. Walking: moderate.<\/p><\/div><\/div><\/div><\/div><\/div><hr \/><div class=\"\"><iframe src=\"https:\/\/www.google.com\/maps\/d\/embed?mid=1mGgtylMQHGAKR6HR8r8YLe5W4LU\" width=\"640\" height=\"480\"><\/iframe><\/div>"},{"title":"Seva","content":"<div id=\"seva-details\"><ul><li>Vaishnava Bhojana seva - Rs.5000<\/li><li>Sponsor a pilgrim - Rs.35000<\/li><li>Tulsi Archana (1 place)- Rs.500<\/li><li>Tulsi Archana at 6 places - Rs.3000<\/li><li>Pushparchana (1 place) - Rs.500<\/li><li>Pushparchana (6places) - Rs.3000<\/li><li>Bhoga\/Naivedya seva (1place) - Rs.1000<\/li><li>Bhoga\/Naivedya seva (6places) - Rs.6000<\/li><li>Hundi seva - any amount<\/li><\/ul><\/div>"}],"style":"style2-horizontal","align":"left","tab-title-color":"","tab-title-active-color":"","tab-title-background-color":"","tab-title-active-background-color":"","tab-title-border-color":"","tab-title-border-active-color":"","tab-content-color":"","padding-bottom":"30px"}}]}]';
            $value = '[{"template":"wrapper","type":"background","value":{"id":"","class":"","content-layout":"boxed","max-width-wrapper":"","max-width":"","enable-space":"disable","hide-this-wrapper-in":"none","animation":"none","animation-location":"0.8","full-height":"enable","decrease-height":"0px","centering-content":"disable","background-type":"color","background-color-style":"plain","background-color":"","background-color-opacity":"1","background-gradient-color":"","background-gradient-color-opacity":"1","background-image":"","mobile-background-image":"","background-image-style":"cover","background-image-position":"center","background-video-url":"","background-video-url-mp4":"","background-video-url-webm":"","background-video-url-ogg":"","background-video-image":"","background-pattern":"pattern-1","background-opacity":"","background-filter":"none","background-blur-size":"","pattern-opacity":"1","parallax-speed":"0.8","overflow":"visible","enable-container-background":"disable","container-background-color":"","container-shadow-color":"","container-shadow-opacity":"0.2","enable-background-particle":"disable","enable-marquee":"disable","marquee-text":"","marquee-position":"top","marquee-position-offset":"","marquee-speed":"10000","marquee-direction":"left","marquee-font-size":"","marquee-font-weight":"","marquee-font-letter-spacing":"","marquee-text-color":"","marquee-opacity":"1","border-type":"none","border-pre-spaces":{"top":"20px","right":"20px","bottom":"20px","left":"20px","settings":"link"},"border-width":{"top":"1px","right":"1px","bottom":"1px","left":"1px","settings":"link"},"border-color":"#ffffff","border-style":"solid","background-shadow-color":"","background-shadow-opacity":"0.2","padding":{"top":"30px","right":"0px","bottom":"30px","left":"0px","settings":"unlink"},"margin":{"top":"0px","right":"0px","bottom":"0px","left":"0px","settings":"link"},"container-margin":{"top":"0px","right":"0px","bottom":"0px","left":"0px","settings":"link"},"mobile-padding":{"top":"","right":"","bottom":"","left":"","settings":"link"},"mobile-margin":{"top":"","right":"","bottom":"","left":"","settings":"link"},"skin":"Blue Icon"},"items":[{"template":"element","type":"tab","value":{"id":"","class":"","tabs":[{"title":"Details","content":"<div id=\"yatra-details\"><p><strong>Importance of Yatra<strong>:<\/strong><\/strong>Uttar Bharat Pilgrimage is a unique opportunity to visit those important places which are considered as the epitome for purifying oneself and also the forefathers. In this pilgrimage we visit Ayodhya - the birthplace of Supreme Lord Sri Ramachandra, Naimisharanya - the place where Srimad Bhagavatam was spoken by Suta Goswami, Chitrakoot - where Sri Sita, Rama, Lakshmana spent many years, Prayagraj - which hosts the historic Triveni Sangam - also famous for its Margha mela and Kumbha mela, Kashi - one of the important moksha-puri, Gaya - the abode of the lotus feet of Sri Vishnu.<\/p><p>Along with these magnanimous places, we also visit interesting places in and around...like the Bindu Madhava temple, Veni Madhava temple, the Gupta Godavari, Akshaya vat and so on. This pilgrimage is also attractive because of inclusions like - Triveni sangam bath, Sarayu river bath, Ganga Arati at Kashi, Pinda-Dana and Putra-Tarpana at Gaya, visit to Bodha-Gaya where Lord Buddha achieved enlightenment.<\/p><p>Complimented with ecstatic kirtan, Satvik food, luxurious accommodation and pastime narration\u2019s - this pilgrimage is not to be missed at all.<\/p><p>\u00a0<\/p><hr \/><p>[gdlr_widget_list title=\"Places to visit\" title-color=\"\" background-color=\"\"\u00a0 color=\"\" border-color=\"\" ]<\/p><ul><li class=\"gdlr-core-icon-list-content-wrap\"><span class=\"gdlr-core-icon-list-content\">Kumbakonam - Chakrapani,Sarangapani, Ramaswamy, Uppilliyappan temples.<\/span><\/li><li class=\"gdlr-core-icon-list-content-wrap\">Srirangam - Ranganatha Swamy, Yamunacharya, Narasimha temples, Kaveri and more<\/li><\/ul><p>[\/gdlr_widget_list]<\/p><hr \/><p>[gallery source=\"gdlr-core\" style=\"slider\" slider-navigation=\"bullet\" thumbnail-size=\"\" columns=\"1\" size=\"large\" ids=\"4489,4488,4485\"]<\/p><\/div>"},{"title":"Yatra Packages","content":"<div id=\"yatra-packages\">\n<p>[gdlr_widget_list]<\/p>\n<div style=\"background-color: #f3f3f3; color: #4692e7; width: 100%; padding: 10px;\"><strong>Normal Package<\/strong><\/div>\n<ul style=\"padding-left: 10px;padding-top: 15px;padding-bottom: 20px;line-height: 1.7;border: 1px solid #f3f3f3;font-size: large !important;margin-bottom: 20px;\">\n<li><strong>Price<\/strong> : Adult - Rs.55,000 Sr Citizen - Rs.54,000 Child - Rs.46,000<\/li>\n<li><b>Accommodation<\/b> : Dormitory<\/li>\n<li><strong>Food<\/strong>: Vegetarian food will be provided.<\/li>\n<\/ul>\n<div style=\"background-color: #f3f3f3; color: #4692e7; width: 100%; padding: 10px;\"><strong>Deluxe Package<\/strong><\/div>\n<ul style=\"padding-left: 10px;padding-top: 15px;padding-bottom: 20px;line-height: 1.7;border: 1px solid #f3f3f3;font-size: large !important;margin-bottom: 20px;\">\n<li><strong>Price<\/strong> : Adult - Rs.60,000, Sr Citizen - Rs.59,000 Child - Rs.46,000.<\/li>\n<li><b>Accommodation:<\/b> 3-star accommodation, Vegetarian food.<\/li>\n<li><strong>Food<\/strong>: Vegetarian food will be provided.<\/li>\n<\/ul>\n<div style=\"background-color: #f3f3f3; color: #4692e7; width: 100%; padding: 10px;\"><strong>Super Deluxe Package<\/strong><\/div>\n<ul style=\"padding-left: 10px;padding-top: 15px;padding-bottom: 20px;line-height: 1.7;border: 1px solid #f3f3f3;font-size: large !important;margin-bottom: 20px;\">\n<li><strong>Price<\/strong> : Adult - Rs. 66,000, Sr Citizen - Rs. 65,000 Child - Rs. 46,000.<\/li>\n<li><b>Accommodation: <\/b>3-star accommodation, Vegetarian food.<\/li>\n<li><strong>Food<\/strong>: Vegetarian food will be provided.<\/li>\n<\/ul>\n<\/div>\n"},{"title":"Itinerary","content":"<div class=\"gdlr-core-pbf-element\"><div class=\"gdlr-core-toggle-box-item gdlr-core-item-pdlr gdlr-core-item-pdb gdlr-core-toggle-box-style-background-title gdlr-core-left-align\" style=\"padding-bottom: 15px;\"><div class=\"gdlr-core-toggle-box-item-tab clearfix gdlr-core-active\"><div class=\"gdlr-core-toggle-box-item-content-wrapper\"><h4 class=\"gdlr-core-toggle-box-item-title gdlr-core-js gdlr-core-skin-e-background gdlr-core-skin-e-content\"><span class=\"gdlr-core-head\">Day 1<\/span>\u00a0Arrive in Z\u00fcrich, Switzerland<\/h4><div class=\"gdlr-core-toggle-box-item-content\"><p>We will meet at 4 p.m. at our hotel in Luzern (Lucerne) for a \u201cWelcome to Switzerland\u201d meeting. Then we will take a meandering evening walk through Switzerlands most charming lakeside town, and get acquainted with one another over dinner together. Sleep in Luzern (2 nights). No bus. Walking: light.<\/p><\/div><\/div><\/div><div class=\"gdlr-core-toggle-box-item-tab clearfix gdlr-core-active\"><div class=\"gdlr-core-toggle-box-item-icon gdlr-core-js gdlr-core-skin-icon \">\u00a0<\/div><div class=\"gdlr-core-toggle-box-item-content-wrapper\"><h4 class=\"gdlr-core-toggle-box-item-title gdlr-core-js gdlr-core-skin-e-background gdlr-core-skin-e-content\"><span class=\"gdlr-core-head\">Day 2<\/span>Z\u00fcrich\u2013Biel\/Bienne\u2013Neuch\u00e2tel\u2013Geneva<\/h4><div class=\"gdlr-core-toggle-box-item-content\"><p>Enjoy an orientation walk of Zurich\u2019s OLD TOWN, Switzerland\u2019s center of banking and commerce. Then, leave Zurich and start your Swiss adventure. You\u2019ll quickly discover that Switzerland isn\u2019t just home to the Alps, but also to some of the most beautiful lakes. First, stop at the foot of the Jura Mountains in the picturesque town of Biel, known as Bienne by French-speaking Swiss, famous for watch-making, and explore the historical center. Next, enjoy a scenic drive to lakeside Neuch\u00e2tel, dominated by the medieval cathedral and castle. Time to stroll along the lake promenade before continuing to stunning Geneva, the second-largest city in Switzerland, with its fantastic lakeside location and breathtaking panoramas of the Alps.<\/p><\/div><\/div><\/div><div class=\"gdlr-core-toggle-box-item-tab clearfix gdlr-core-active\"><div class=\"gdlr-core-toggle-box-item-icon gdlr-core-js gdlr-core-skin-icon \">\u00a0<\/div><div class=\"gdlr-core-toggle-box-item-content-wrapper\"><h4 class=\"gdlr-core-toggle-box-item-title gdlr-core-js gdlr-core-skin-e-background gdlr-core-skin-e-content\"><span class=\"gdlr-core-head\">Day 3<\/span>Enchanting Engelberg<\/h4><div class=\"gdlr-core-toggle-box-item-content\"><p>Our morning drive takes us from Swiss lakes to Swiss Army. At the once-secret Swiss army bunker at Fortress F\u00fcrigen, we will see part of the massive defense system designed to keep Switzerland strong and neutral. Afterward, a short drive into the countryside brings us to the charming Alpine village of Engelberg, our picturesque home for the next two days. We will settle into our lodge then head out for an orientation walk. Our stroll through the village will end at the Engelberg Abbey, a Benedictine monastery with its own cheese-making operation. You will have free time to wander back before dinner together. Sleep in Engelberg (2 nights). Bus: 1 hr. Walking: light.<\/p><\/div><\/div><\/div><div class=\"gdlr-core-toggle-box-item-tab clearfix gdlr-core-active\"><div class=\"gdlr-core-toggle-box-item-icon gdlr-core-js gdlr-core-skin-icon \">\u00a0<\/div><div class=\"gdlr-core-toggle-box-item-content-wrapper\"><h4 class=\"gdlr-core-toggle-box-item-title gdlr-core-js gdlr-core-skin-e-background gdlr-core-skin-e-content\"><span class=\"gdlr-core-head\">Day 4<\/span>Interlaken Area. Excursion to The Jungfrau Massif<\/h4><div class=\"gdlr-core-toggle-box-item-content\"><p>An unforgettable trip to the high Alpine wonderland of ice and snow is the true highlight of a visit to Switzerland. Globus Local Favorite At an amazing 11,332 feet, the JUNGFRAUJOCH is Europe\u2019s highest railway station. Jungfrau\u2019s 13,642-foot summit was first ascended in 1811 and in 1912 the rack railway was opened. There are lots of things to do here: enjoy the ALPINE SENSATION, THE PANORAMA 360\u00b0 EXPERIENCE, and the ICE PALACE. Also receive your JUNGFRAU PASSPORT as a souvenir to take home with you. The round trip to the \u201cTop of Europe\u201d by MOUNTAIN TRAIN will take most of the day.<\/p><\/div><\/div><\/div><div class=\"gdlr-core-toggle-box-item-tab clearfix gdlr-core-active\"><div class=\"gdlr-core-toggle-box-item-icon gdlr-core-js gdlr-core-skin-icon \">\u00a0<\/div><div class=\"gdlr-core-toggle-box-item-content-wrapper\"><h4 class=\"gdlr-core-toggle-box-item-title gdlr-core-js gdlr-core-skin-e-background gdlr-core-skin-e-content\"><span class=\"gdlr-core-head\">Day 5<\/span>Lake Geneva and Ch\u00e2teau de Chillon<\/h4><div class=\"gdlr-core-toggle-box-item-content\"><p>Its market day in Lausanne! Enjoy browsing and packing a picnic lunch for our 11 a.m. boat cruise on Lake Geneva. A few miles down-shore we will dock at Ch\u00e2teau de Chillon, where we will have a guided tour of this delightfully medieval castle on the water. On our way back we will take time to peek into the vineyards surrounding Lutry before returning to Lausanne. Boat: 2 hrs. Bus: 1 hr. Walking: moderate.<\/p><\/div><\/div><\/div><\/div><\/div><hr \/><div class=\"\"><iframe src=\"https:\/\/www.google.com\/maps\/d\/embed?mid=1mGgtylMQHGAKR6HR8r8YLe5W4LU\" width=\"640\" height=\"480\"><\/iframe><\/div>"},{"title":"Seva","content":"<div id=\"seva-details\"><ul><li>Vaishnava Bhojana seva - Rs.5000<\/li><li>Sponsor a pilgrim - Rs.35000<\/li><li>Tulsi Archana (1 place)- Rs.500<\/li><li>Tulsi Archana at 6 places - Rs.3000<\/li><li>Pushparchana (1 place) - Rs.500<\/li><li>Pushparchana (6places) - Rs.3000<\/li><li>Bhoga\/Naivedya seva (1place) - Rs.1000<\/li><li>Bhoga\/Naivedya seva (6places) - Rs.6000<\/li><li>Hundi seva - any amount<\/li><\/ul><\/div>"}],"style":"style2-horizontal","align":"left","tab-title-color":"","tab-title-active-color":"","tab-title-background-color":"","tab-title-active-background-color":"","tab-title-border-color":"","tab-title-border-active-color":"","tab-content-color":"","padding-bottom":"30px"}}]}]';


//			$value = '[{"template":"wrapper","type":"background","value":{"id":"","class":"","content-layout":"full","max-width":"","enable-space":"enable","hide-this-wrapper-in":"none","animation":"none","animation-location":"0.8","full-height":"disable","decrease-height":"0px","centering-content":"disable","background-type":"color","background-color":"","background-image":"","background-image-style":"cover","background-image-position":"center","background-video-url":"","background-video-url-mp4":"","background-video-url-webm":"","background-video-url-ogg":"","background-video-image":"","background-pattern":"pattern-1","pattern-opacity":"1","parallax-speed":"0.8","overflow":"visible","border-type":"none","border-pre-spaces":{"top":"20px","right":"20px","bottom":"20px","left":"20px","settings":"link"},"border-width":{"top":"1px","right":"1px","bottom":"1px","left":"1px","settings":"link"},"border-color":"#ffffff","border-style":"solid","padding":{"top":"0px","right":"0px","bottom":"0px","left":"0px","settings":"unlink"},"margin":{"top":"0px","right":"0px","bottom":"0px","left":"0px","settings":"link"},"skin":""},"items":[{"template":"element","type":"content-navigation","value":{"id":"","class":"","tabs":[{"id":"detail","title":"Detail"},{"id":"itinerary","title":"Itinerary"},{"id":"map","title":"Map"},{"id":"photos","title":"Photos"},{"id":"tourmaster-single-review","title":"Reviews"}],"padding-bottom":"0px"}}]},{"template":"wrapper","type":"background","value":{"id":"detail","class":"","content-layout":"boxed","max-width":"","enable-space":"disable","hide-this-wrapper-in":"none","animation":"none","animation-location":"0.8","full-height":"disable","decrease-height":"0px","centering-content":"disable","background-type":"color","background-color":"","background-image":"","background-image-style":"cover","background-image-position":"center","background-video-url":"","background-video-url-mp4":"","background-video-url-webm":"","background-video-url-ogg":"","background-video-image":"","background-pattern":"pattern-1","pattern-opacity":"1","parallax-speed":"0.8","overflow":"visible","border-type":"none","border-pre-spaces":{"top":"20px","right":"20px","bottom":"20px","left":"20px","settings":"link"},"border-width":{"top":"1px","right":"1px","bottom":"1px","left":"1px","settings":"link"},"border-color":"#ffffff","border-style":"solid","padding":{"top":"70px","right":"0px","bottom":"30px","left":"0px","settings":"unlink"},"margin":{"top":"0px","right":"0px","bottom":"0px","left":"0px","settings":"link"},"skin":"Blue Icon"},"items":[{"template":"element","type":"title","value":{"id":"","class":"","title":"Tour Details","caption":"","caption-position":"bottom","title-width":"300px","title-link-text":"","title-link":"","title-link-target":"_self","text-align":"left","left-media-type":"icon","left-icon":"fa fa-file-text-o","left-image":"","enable-side-border":"disable","side-border-size":"1px","side-border-spaces":"30px","side-border-style":"solid","side-border-divider-color":"","heading-tag":"h6","icon-font-size":"18px","title-font-size":"24px","title-font-weight":"600","title-font-style":"normal","title-font-letter-spacing":"0px","title-font-uppercase":"disable","caption-font-size":"16px","caption-font-weight":"400","caption-font-style":"italic","caption-font-letter-spacing":"0px","caption-font-uppercase":"disable","left-icon-color":"","title-color":"","title-link-hover-color":"","caption-color":"","caption-spaces":"10px","media-margin":{"top":"0px","right":"15px","bottom":"0px","left":"0px","settings":"unlink"},"padding-bottom":"35px"}},{"template":"element","type":"text-box","value":{"id":"","class":"","content":"<p>Maecenas sed diam eget risus varius blandit sit amet non magna. Cras mattis consectetur purus sit amet fermentum. Duis mollis, est non commodo luctus, nisi erat porttitor ligula, eget lacinia odio sem nec elit. Donec id elit non mi porta gravida at eget metus. Donec id elit non mi porta gravida at eget metus.</p><p>Aenean lacinia bibendum nulla sed consectetur. Maecenas faucibus mollis interdum. Cras mattis consectetur purus sit amet fermentum. Curabitur blandit tempus porttitor. Nulla vitae elit libero, a pharetra augue. Vivamus sagittis lacus vel augue laoreet rutrum.</p>","text-align":"left","font-size":"","padding-bottom":"30px"}},{"template":"element","type":"divider","value":{"id":"","class":"","type":"normal","icon-type":"icon","image":"","icon":"fa fa-film","style":"solid","align":"center","icon-size":"15px","divider-size":"1px","divider-width":"","padding-bottom":"19px","icon-color":"","divider-color":""}},{"template":"wrapper","type":"column","column":"30","value":{"id":"","class":"","max-width":"","min-height":"","hide-this-wrapper-in":"none","animation":"none","animation-location":"0.8","full-height":"disable","decrease-height":"0px","sync-height":"","centering-sync-height-content":"disable","background-type":"color","background-extending":"none","background-color":"","background-image":"","background-image-style":"cover","background-image-position":"center","background-video-url":"","background-video-url-mp4":"","background-video-url-webm":"","background-video-url-ogg":"","background-video-image":"","background-pattern":"pattern-1","background-opacity":"1","parallax-speed":"0.8","border-type":"none","border-pre-spaces":{"top":"20px","right":"20px","bottom":"20px","left":"20px","settings":"link"},"border-width":{"top":"1px","right":"1px","bottom":"1px","left":"1px","settings":"link"},"border-color":"#ffffff","border-style":"solid","padding":{"top":"0px","right":"0px","bottom":"0px","left":"0px","settings":"link"},"margin":{"top":"0px","right":"0px","bottom":"0px","left":"0px","settings":"link"},"skin":""},"items":[{"template":"element","type":"title","value":{"id":"","class":"","title":"Departure & Return Location ","caption":"","caption-position":"top","title-link-text":"","title-link":"","title-link-target":"_self","text-align":"left","left-media-type":"image","left-icon":"fa fa-gear","left-image":"","enable-side-border":"disable","side-border-size":"1px","side-border-spaces":"30px","side-border-style":"solid","side-border-divider-color":"","heading-tag":"h3","icon-font-size":"30px","title-font-size":"15px","title-font-weight":"500","title-font-style":"normal","title-font-letter-spacing":"0px","title-font-uppercase":"disable","caption-font-size":"16px","caption-font-weight":"400","caption-font-style":"italic","caption-font-letter-spacing":"0px","caption-font-uppercase":"disable","title-color":"","title-link-hover-color":"","caption-color":"","caption-spaces":"0px","media-margin":{"top":"10px","right":"30px","bottom":"5px","left":"0px","settings":"unlink"},"padding-bottom":"0px"}}]},{"template":"wrapper","type":"column","column":"30","items":[{"template":"element","type":"text-box","value":{"id":"","class":"","content":"<p>John F.K. International Airport (<a href=\"#\">Google Map</a>)</p>","text-align":"left","font-size":"","padding-bottom":"0px"}}]},{"template":"element","type":"divider","value":{"id":"","class":"","type":"normal","icon-type":"icon","image":"","icon":"fa fa-film","style":"solid","align":"center","icon-size":"15px","divider-size":"1px","divider-width":"","padding-bottom":"19px","icon-color":"","divider-color":""}},{"template":"wrapper","type":"column","column":"30","value":{"id":"","class":"","max-width":"","min-height":"","hide-this-wrapper-in":"none","animation":"none","animation-location":"0.8","full-height":"disable","decrease-height":"0px","sync-height":"","centering-sync-height-content":"disable","background-type":"color","background-extending":"none","background-color":"","background-image":"","background-image-style":"cover","background-image-position":"center","background-video-url":"","background-video-url-mp4":"","background-video-url-webm":"","background-video-url-ogg":"","background-video-image":"","background-pattern":"pattern-1","background-opacity":"1","parallax-speed":"0.8","border-type":"none","border-pre-spaces":{"top":"20px","right":"20px","bottom":"20px","left":"20px","settings":"link"},"border-width":{"top":"1px","right":"1px","bottom":"1px","left":"1px","settings":"link"},"border-color":"#ffffff","border-style":"solid","padding":{"top":"0px","right":"0px","bottom":"0px","left":"0px","settings":"link"},"margin":{"top":"0px","right":"0px","bottom":"0px","left":"0px","settings":"link"},"skin":""},"items":[{"template":"element","type":"title","value":{"id":"","class":"","title":"Departure Time","caption":"","caption-position":"top","title-link-text":"","title-link":"","title-link-target":"_self","text-align":"left","left-media-type":"image","left-icon":"fa fa-gear","left-image":"","enable-side-border":"disable","side-border-size":"1px","side-border-spaces":"30px","side-border-style":"solid","side-border-divider-color":"","heading-tag":"h3","icon-font-size":"30px","title-font-size":"15px","title-font-weight":"500","title-font-style":"normal","title-font-letter-spacing":"0px","title-font-uppercase":"disable","caption-font-size":"16px","caption-font-weight":"400","caption-font-style":"italic","caption-font-letter-spacing":"0px","caption-font-uppercase":"disable","title-color":"","title-link-hover-color":"","caption-color":"","caption-spaces":"0px","media-margin":{"top":"10px","right":"30px","bottom":"5px","left":"0px","settings":"unlink"},"padding-bottom":"0px"}}]},{"template":"wrapper","type":"column","column":"30","items":[{"template":"element","type":"text-box","value":{"id":"","class":"","content":"<p>3 Hours Before Flight Time</p>","text-align":"left","font-size":"","padding-bottom":"0px"}}]},{"template":"element","type":"divider","value":{"id":"","class":"","type":"normal","icon-type":"icon","image":"","icon":"fa fa-film","style":"solid","align":"center","icon-size":"15px","divider-size":"1px","divider-width":"","padding-bottom":"19px","icon-color":"","divider-color":""}},{"template":"wrapper","type":"column","column":"30","value":{"id":"","class":"","max-width":"","min-height":"","hide-this-wrapper-in":"none","animation":"none","animation-location":"0.8","full-height":"disable","decrease-height":"0px","sync-height":"","centering-sync-height-content":"disable","background-type":"color","background-extending":"none","background-color":"","background-image":"","background-image-style":"cover","background-image-position":"center","background-video-url":"","background-video-url-mp4":"","background-video-url-webm":"","background-video-url-ogg":"","background-video-image":"","background-pattern":"pattern-1","background-opacity":"1","parallax-speed":"0.8","border-type":"none","border-pre-spaces":{"top":"20px","right":"20px","bottom":"20px","left":"20px","settings":"link"},"border-width":{"top":"1px","right":"1px","bottom":"1px","left":"1px","settings":"link"},"border-color":"#ffffff","border-style":"solid","padding":{"top":"0px","right":"0px","bottom":"0px","left":"0px","settings":"link"},"margin":{"top":"0px","right":"0px","bottom":"0px","left":"0px","settings":"link"},"skin":""},"items":[{"template":"element","type":"title","value":{"id":"","class":"","title":"Price Includes","caption":"","caption-position":"top","title-link-text":"","title-link":"","title-link-target":"_self","text-align":"left","left-media-type":"image","left-icon":"fa fa-gear","left-image":"","enable-side-border":"disable","side-border-size":"1px","side-border-spaces":"30px","side-border-style":"solid","side-border-divider-color":"","heading-tag":"h3","icon-font-size":"30px","title-font-size":"15px","title-font-weight":"500","title-font-style":"normal","title-font-letter-spacing":"0px","title-font-uppercase":"disable","caption-font-size":"16px","caption-font-weight":"400","caption-font-style":"italic","caption-font-letter-spacing":"0px","caption-font-uppercase":"disable","title-color":"","title-link-hover-color":"","caption-color":"","caption-spaces":"0px","media-margin":{"top":"10px","right":"30px","bottom":"5px","left":"0px","settings":"unlink"},"padding-bottom":"0px"}}]},{"template":"wrapper","type":"column","column":"30","items":[{"template":"element","type":"icon-list","value":{"id":"","class":"","tabs":[{"icon":"fa fa-check","icon-hover":"","title":"Air fares","link-url":"","link-target":"_self"},{"icon":"fa fa-check","icon-hover":"","title":"3 Nights Hotel Accomodation","link-url":"","link-target":"_self"},{"icon":"fa fa-check","icon-hover":"","title":"Tour Guide","link-url":"","link-target":"_self"},{"icon":"fa fa-check","icon-hover":"","title":"Entrance Fees","link-url":"","link-target":"_self"},{"icon":"fa fa-check","icon-hover":"","title":"All transportation in destination location","link-url":"","link-target":"_self"}],"columns":"60","enable-divider":"disable","icon-background":"none","icon-color":"#4692e7","icon-background-color":"","content-color":"","border-color":"","icon-size":"14px","content-size":"14px","list-bottom-margin":"10px","padding-bottom":"10px"}}]},{"template":"element","type":"divider","value":{"id":"","class":"","type":"normal","icon-type":"icon","image":"","icon":"fa fa-film","style":"solid","align":"center","icon-size":"15px","divider-size":"1px","divider-width":"","padding-bottom":"19px","icon-color":"","divider-color":""}},{"template":"wrapper","type":"column","column":"30","value":{"id":"","class":"","max-width":"","min-height":"","hide-this-wrapper-in":"none","animation":"none","animation-location":"0.8","full-height":"disable","decrease-height":"0px","sync-height":"","centering-sync-height-content":"disable","background-type":"color","background-extending":"none","background-color":"","background-image":"","background-image-style":"cover","background-image-position":"center","background-video-url":"","background-video-url-mp4":"","background-video-url-webm":"","background-video-url-ogg":"","background-video-image":"","background-pattern":"pattern-1","background-opacity":"1","parallax-speed":"0.8","border-type":"none","border-pre-spaces":{"top":"20px","right":"20px","bottom":"20px","left":"20px","settings":"link"},"border-width":{"top":"1px","right":"1px","bottom":"1px","left":"1px","settings":"link"},"border-color":"#ffffff","border-style":"solid","padding":{"top":"0px","right":"0px","bottom":"0px","left":"0px","settings":"link"},"margin":{"top":"0px","right":"0px","bottom":"0px","left":"0px","settings":"link"},"skin":""},"items":[{"template":"element","type":"title","value":{"id":"","class":"","title":"Price Excludes","caption":"","caption-position":"top","title-link-text":"","title-link":"","title-link-target":"_self","text-align":"left","left-media-type":"image","left-icon":"fa fa-gear","left-image":"","enable-side-border":"disable","side-border-size":"1px","side-border-spaces":"30px","side-border-style":"solid","side-border-divider-color":"","heading-tag":"h3","icon-font-size":"30px","title-font-size":"15px","title-font-weight":"500","title-font-style":"normal","title-font-letter-spacing":"0px","title-font-uppercase":"disable","caption-font-size":"16px","caption-font-weight":"400","caption-font-style":"italic","caption-font-letter-spacing":"0px","caption-font-uppercase":"disable","title-color":"","title-link-hover-color":"","caption-color":"","caption-spaces":"0px","media-margin":{"top":"10px","right":"30px","bottom":"5px","left":"0px","settings":"unlink"},"padding-bottom":"0px"}}]},{"template":"wrapper","type":"column","column":"30","items":[{"template":"element","type":"icon-list","value":{"id":"","class":"","tabs":[{"icon":"fa fa-close","icon-hover":"","title":"Guide Service Fee","link-url":"","link-target":"_self"},{"icon":"fa fa-close","icon-hover":"","title":"Driver Service Fee","link-url":"","link-target":"_self"},{"icon":"fa fa-close","icon-hover":"","title":"Any Private Expenses","link-url":"","link-target":"_self"},{"icon":"fa fa-close","icon-hover":"","title":"Room Service Fees","link-url":"","link-target":"_self"}],"columns":"60","enable-divider":"disable","icon-background":"none","icon-color":"#7f7f7f","icon-background-color":"","content-color":"","border-color":"","icon-size":"14px","content-size":"14px","list-bottom-margin":"10px","padding-bottom":"10px"}}]},{"template":"element","type":"divider","value":{"id":"","class":"","type":"normal","icon-type":"icon","image":"","icon":"fa fa-film","style":"solid","align":"center","icon-size":"15px","divider-size":"1px","divider-width":"","padding-bottom":"19px","icon-color":"","divider-color":""}},{"template":"wrapper","type":"column","column":"30","value":{"id":"","class":"","max-width":"","min-height":"","hide-this-wrapper-in":"none","animation":"none","animation-location":"0.8","full-height":"disable","decrease-height":"0px","sync-height":"","centering-sync-height-content":"disable","background-type":"color","background-extending":"none","background-color":"","background-image":"","background-image-style":"cover","background-image-position":"center","background-video-url":"","background-video-url-mp4":"","background-video-url-webm":"","background-video-url-ogg":"","background-video-image":"","background-pattern":"pattern-1","background-opacity":"1","parallax-speed":"0.8","border-type":"none","border-pre-spaces":{"top":"20px","right":"20px","bottom":"20px","left":"20px","settings":"link"},"border-width":{"top":"1px","right":"1px","bottom":"1px","left":"1px","settings":"link"},"border-color":"#ffffff","border-style":"solid","padding":{"top":"0px","right":"0px","bottom":"0px","left":"0px","settings":"link"},"margin":{"top":"0px","right":"0px","bottom":"0px","left":"0px","settings":"link"},"skin":""},"items":[{"template":"element","type":"title","value":{"id":"","class":"","title":"Complementaries","caption":"","caption-position":"top","title-link-text":"","title-link":"","title-link-target":"_self","text-align":"left","left-media-type":"image","left-icon":"fa fa-gear","left-image":"","enable-side-border":"disable","side-border-size":"1px","side-border-spaces":"30px","side-border-style":"solid","side-border-divider-color":"","heading-tag":"h3","icon-font-size":"30px","title-font-size":"15px","title-font-weight":"500","title-font-style":"normal","title-font-letter-spacing":"0px","title-font-uppercase":"disable","caption-font-size":"16px","caption-font-weight":"400","caption-font-style":"italic","caption-font-letter-spacing":"0px","caption-font-uppercase":"disable","title-color":"","title-link-hover-color":"","caption-color":"","caption-spaces":"0px","media-margin":{"top":"10px","right":"30px","bottom":"5px","left":"0px","settings":"unlink"},"padding-bottom":"0px"}}]},{"template":"wrapper","type":"column","column":"30","items":[{"template":"element","type":"icon-list","value":{"id":"","class":"","tabs":[{"icon":"fa fa-check","icon-hover":"","title":"Umbrella","link-url":"","link-target":"_self"},{"icon":"fa fa-check","icon-hover":"","title":"Sunscreen","link-url":"","link-target":"_self"},{"icon":"fa fa-check","icon-hover":"","title":"T-Shirt","link-url":"","link-target":"_self"},{"icon":"fa fa-check","icon-hover":"","title":"Entrance Fees","link-url":"","link-target":"_self"}],"columns":"60","enable-divider":"disable","icon-background":"none","icon-color":"#4692e7","icon-background-color":"","content-color":"","border-color":"","icon-size":"14px","content-size":"14px","list-bottom-margin":"10px","padding-bottom":"10px"}}]},{"template":"element","type":"divider","value":{"id":"","class":"","type":"normal","icon-type":"icon","image":"","icon":"fa fa-film","style":"solid","align":"center","icon-size":"15px","divider-size":"1px","divider-width":"","padding-bottom":"45px","icon-color":"","divider-color":""}},{"template":"element","type":"title","value":{"id":"","class":"","title":"What to Expect","caption":"","caption-position":"bottom","title-link-text":"","title-link":"","title-link-target":"_self","text-align":"left","left-media-type":"none","left-icon":"fa fa-file-text-o","left-image":"","enable-side-border":"disable","side-border-size":"1px","side-border-spaces":"30px","side-border-style":"solid","side-border-divider-color":"","heading-tag":"h6","icon-font-size":"18px","title-font-size":"16px","title-font-weight":"600","title-font-style":"normal","title-font-letter-spacing":"0px","title-font-uppercase":"disable","caption-font-size":"16px","caption-font-weight":"400","caption-font-style":"italic","caption-font-letter-spacing":"0px","caption-font-uppercase":"disable","title-color":"","title-link-hover-color":"","caption-color":"","caption-spaces":"10px","media-margin":{"top":"0px","right":"15px","bottom":"0px","left":"0px","settings":"unlink"},"padding-bottom":"30px"}},{"template":"element","type":"text-box","value":{"id":"","class":"","content":"<p>Curabitur blandit tempus porttitor. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras mattis consectetur purus sit amet fermentum. Etiam porta sem malesuada magna mollis euismod. Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p><p>Maecenas sed diam eget risus varius blandit sit amet non magna. Morbi leo risus, porta ac consectetur ac, vestibulum at eros. Nullam id dolor id nibh ultricies vehicula ut id elit. Donec ullamcorper nulla non metus auctor fringilla.</p>","text-align":"left","font-size":"","padding-bottom":"10px"}},{"template":"element","type":"icon-list","value":{"id":"","class":"","tabs":[{"icon":"fa fa-dot-circle-o","icon-hover":"","title":"Ipsum Amet Mattis Pellentesque","link-url":"","link-target":"_self"},{"icon":"fa fa-dot-circle-o","icon-hover":"","title":"Ultricies Vehicula Mollis Vestibulum Fringilla","link-url":"","link-target":"_self"},{"icon":"fa fa-dot-circle-o","icon-hover":"","title":"Condimentum Sollicitudin Fusce Vestibulum Ultricies","link-url":"","link-target":"_self"},{"icon":"fa fa-dot-circle-o","icon-hover":"","title":"Sollicitudin Consectetur Quam Ligula Vehicula","link-url":"","link-target":"_self"},{"icon":"fa fa-dot-circle-o","icon-hover":"","title":"Cursus Pharetra Purus Porta Parturient","link-url":"","link-target":"_self"},{"icon":"fa fa-dot-circle-o","icon-hover":"","title":"Risus Malesuada Tellus Porta Commodo","link-url":"","link-target":"_self"}],"columns":"60","enable-divider":"disable","icon-background":"none","icon-color":"#4692e7","icon-background-color":"","content-color":"","border-color":"","icon-size":"14px","content-size":"14px","list-bottom-margin":"10px","padding-bottom":"30px"}},{"template":"element","type":"divider","value":{"id":"","class":"","type":"normal","icon-type":"icon","image":"","icon":"fa fa-film","style":"solid","align":"center","icon-size":"15px","divider-size":"1px","divider-width":"","padding-bottom":"15px","icon-color":"","divider-color":""}}]},{"template":"wrapper","type":"background","value":{"id":"itinerary","class":"","content-layout":"boxed","max-width":"","enable-space":"disable","hide-this-wrapper-in":"none","animation":"none","animation-location":"0.8","full-height":"disable","decrease-height":"0px","centering-content":"disable","background-type":"color","background-color":"","background-image":"","background-image-style":"cover","background-image-position":"center","background-video-url":"","background-video-url-mp4":"","background-video-url-webm":"","background-video-url-ogg":"","background-video-image":"","background-pattern":"pattern-1","pattern-opacity":"1","parallax-speed":"0.8","overflow":"visible","border-type":"none","border-pre-spaces":{"top":"20px","right":"20px","bottom":"20px","left":"20px","settings":"link"},"border-width":{"top":"1px","right":"1px","bottom":"1px","left":"1px","settings":"link"},"border-color":"#ffffff","border-style":"solid","padding":{"top":"20px","right":"0px","bottom":"30px","left":"0px","settings":"unlink"},"margin":{"top":"0px","right":"0px","bottom":"0px","left":"0px","settings":"link"},"skin":"Blue Icon"},"items":[{"template":"element","type":"title","value":{"id":"","class":"","title":"Itinerary","caption":"","caption-position":"bottom","title-link-text":"","title-link":"","title-link-target":"_self","text-align":"left","left-media-type":"icon","left-icon":"fa fa-bus","left-image":"","heading-tag":"h6","icon-font-size":"18px","title-font-size":"24px","title-font-weight":"600","title-font-style":"normal","title-font-letter-spacing":"0px","title-font-uppercase":"disable","caption-font-size":"16px","caption-font-weight":"400","caption-font-style":"italic","caption-font-letter-spacing":"0px","caption-font-uppercase":"disable","left-icon-color":"","title-color":"","title-link-hover-color":"","caption-color":"","caption-spaces":"10px","media-margin-right":"15px","padding-bottom":"35px"}},{"template":"element","type":"toggle-box","value":{"id":"","class":"","tabs":[{"head-text":"Day 1","title":" Arrive in Zürich, Switzerland","content":"We\'ll meet at 4 p.m. at our hotel in Luzern (Lucerne) for a \"Welcome to Switzerland\" meeting. Then we\'ll take a meandering evening walk through Switzerland\'s most charming lakeside town, and get acquainted with one another over dinner together. Sleep in Luzern (2 nights). No bus. Walking: light.\n","active":"enable"},{"head-text":"Day 2","title":"Zürich–Biel/Bienne–Neuchâtel–Geneva","content":"Enjoy an orientation walk of Zurich’s OLD TOWN, Switzerland’s center of banking and commerce. Then, leave Zurich and start your Swiss adventure. You’ll quickly discover that Switzerland isn’t just home to the Alps, but also to some of the most beautiful lakes. First, stop at the foot of the Jura Mountains in the picturesque town of Biel, known as Bienne by French-speaking Swiss, famous for watch-making, and explore the historical center. Next, enjoy a scenic drive to lakeside Neuchâtel, dominated by the medieval cathedral and castle. Time to stroll along the lake promenade before continuing to stunning Geneva, the second-largest city in Switzerland, with its fantastic lakeside location and breathtaking panoramas of the Alps.","active":"enable"},{"head-text":"Day 3","title":"Enchanting Engelberg","content":"Our morning drive takes us from Swiss lakes to Swiss Army. At the once-secret Swiss army bunker at Fortress Fürigen, we\'ll see part of the massive defense system designed to keep Switzerland strong and neutral. Afterward, a short drive into the countryside brings us to the charming Alpine village of Engelberg, our picturesque home for the next two days. We\'ll settle into our lodge then head out for an orientation walk. Our stroll through the village will end at the Engelberg Abbey, a Benedictine monastery with its own cheese-making operation. You\'ll have free time to wander back before dinner together. Sleep in Engelberg (2 nights). Bus: 1 hr. Walking: light.","active":"enable"},{"head-text":"Day 4","title":"Interlaken Area. Excursion to The Jungfrau Massif","content":"An unforgettable trip to the high Alpine wonderland of ice and snow is the true highlight of a visit to Switzerland. Globus Local Favorite At an amazing 11,332 feet, the JUNGFRAUJOCH is Europe’s highest railway station. Jungfrau’s 13,642-foot summit was first ascended in 1811 and in 1912 the rack railway was opened. There are lots of things to do here: enjoy the ALPINE SENSATION, THE PANORAMA 360° EXPERIENCE, and the ICE PALACE. Also receive your JUNGFRAU PASSPORT as a souvenir to take home with you. The round trip to the “Top of Europe” by MOUNTAIN TRAIN will take most of the day.","active":"enable"},{"head-text":"Day 5","title":"Lake Geneva and Château de Chillon","content":"It\'s market day in Lausanne! Enjoy browsing and packing a picnic lunch for our 11 a.m. boat cruise on Lake Geneva. A few miles down-shore we\'ll dock at Château de Chillon, where we\'ll have a guided tour of this delightfully medieval castle on the water. On our way back we\'ll take time to peek into the vineyards surrounding Lutry before returning to Lausanne. Boat: 2 hrs. Bus: 1 hr. Walking: moderate.","active":"enable"}],"style":"background-title","align":"left","padding-bottom":"25px"}},{"template":"element","type":"divider","value":{"id":"","class":"","type":"normal","icon-type":"icon","image":"","icon":"fa fa-film","style":"solid","align":"center","icon-size":"15px","divider-size":"2px","divider-width":"","padding-bottom":"25px","icon-color":"","divider-color":""}}]},{"template":"wrapper","type":"background","value":{"id":"map","class":"","content-layout":"boxed","max-width":"","enable-space":"disable","hide-this-wrapper-in":"none","animation":"none","animation-location":"0.8","full-height":"disable","decrease-height":"0px","centering-content":"disable","background-type":"color","background-color":"","background-image":"","background-image-style":"cover","background-image-position":"center","background-video-url":"","background-video-url-mp4":"","background-video-url-webm":"","background-video-url-ogg":"","background-video-image":"","background-pattern":"pattern-1","pattern-opacity":"1","parallax-speed":"0.8","overflow":"visible","border-type":"none","border-pre-spaces":{"top":"20px","right":"20px","bottom":"20px","left":"20px","settings":"link"},"border-width":{"top":"1px","right":"1px","bottom":"1px","left":"1px","settings":"link"},"border-color":"#ffffff","border-style":"solid","padding":{"top":"0px","right":"0px","bottom":"30px","left":"0px","settings":"unlink"},"margin":{"top":"0px","right":"0px","bottom":"0px","left":"0px","settings":"link"},"skin":"Blue Icon"},"items":[{"template":"element","type":"title","value":{"id":"","class":"","title":"Map","caption":"","caption-position":"bottom","title-link-text":"","title-link":"","title-link-target":"_self","text-align":"left","left-media-type":"icon","left-icon":"fa fa-map-o","left-image":"","heading-tag":"h6","icon-font-size":"18px","title-font-size":"24px","title-font-weight":"600","title-font-style":"normal","title-font-letter-spacing":"0px","title-font-uppercase":"disable","caption-font-size":"16px","caption-font-weight":"400","caption-font-style":"italic","caption-font-letter-spacing":"0px","caption-font-uppercase":"disable","left-icon-color":"","title-color":"","title-link-hover-color":"","caption-color":"","caption-spaces":"10px","media-margin-right":"15px","padding-bottom":"35px"}},{"template":"element","type":"text-box","value":{"id":"","class":"","content":"<div class=\"\">\n<iframe src=\"https://www.google.com/maps/d/embed?mid=1mGgtylMQHGAKR6HR8r8YLe5W4LU\" width=\"100%\" height=\"480\"></iframe></div>\n","text-align":"left","font-size":"","padding-bottom":"55px"}},{"template":"element","type":"divider","value":{"id":"","class":"","type":"normal","icon-type":"icon","image":"","icon":"fa fa-film","style":"solid","align":"center","icon-size":"15px","divider-size":"2px","divider-width":"","padding-bottom":"25px","icon-color":"","divider-color":""}}]},{"template":"wrapper","type":"background","value":{"id":"photos","class":"","content-layout":"boxed","max-width":"","enable-space":"disable","hide-this-wrapper-in":"none","animation":"none","animation-location":"0.8","full-height":"disable","decrease-height":"0px","centering-content":"disable","background-type":"color","background-color":"","background-image":"","background-image-style":"cover","background-image-position":"center","background-video-url":"","background-video-url-mp4":"","background-video-url-webm":"","background-video-url-ogg":"","background-video-image":"","background-pattern":"pattern-1","pattern-opacity":"1","parallax-speed":"0.8","overflow":"visible","border-type":"none","border-pre-spaces":{"top":"20px","right":"20px","bottom":"20px","left":"20px","settings":"link"},"border-width":{"top":"1px","right":"1px","bottom":"1px","left":"1px","settings":"link"},"border-color":"#ffffff","border-style":"solid","padding":{"top":"0px","right":"0px","bottom":"30px","left":"0px","settings":"unlink"},"margin":{"top":"0px","right":"0px","bottom":"0px","left":"0px","settings":"link"},"skin":"Blue Icon"},"items":[{"template":"element","type":"title","value":{"id":"","class":"","title":"Photos","caption":"","caption-position":"bottom","title-link-text":"","title-link":"","title-link-target":"_self","text-align":"left","left-media-type":"icon","left-icon":"icon_images","left-image":"","heading-tag":"h6","icon-font-size":"18px","title-font-size":"24px","title-font-weight":"600","title-font-style":"normal","title-font-letter-spacing":"0px","title-font-uppercase":"disable","caption-font-size":"16px","caption-font-weight":"400","caption-font-style":"italic","caption-font-letter-spacing":"0px","caption-font-uppercase":"disable","left-icon-color":"","title-color":"","title-link-hover-color":"","caption-color":"","caption-spaces":"10px","media-margin-right":"15px","padding-bottom":"35px"}},{"template":"element","type":"gallery","value":{"id":"","class":"","gallery":[{"id":"4602","thumbnail":"http://demo.goodlayers.com/traveltour/wp-content/uploads/2017/01/pexels-photo-copy-2-150x150.jpg"},{"id":"4555","thumbnail":"http://demo.goodlayers.com/traveltour/wp-content/uploads/2016/11/photo-1451337516015-6b6e9a44a8a3-150x150.jpg"},{"id":"4556","thumbnail":"http://demo.goodlayers.com/traveltour/wp-content/uploads/2016/11/italian-landscape-mountains-nature-150x150.jpg"},{"id":"4489","thumbnail":"http://demo.goodlayers.com/traveltour/wp-content/uploads/2016/06/shutterstock_195507533-150x150.jpg"}],"pagination":"none","show-amount":"20","pagination-style":"default","pagination-align":"default","style":"slider","max-slider-height":"500px","overlay":"icon-hover","show-caption":"disable","overlay-on-hover":"disable","column":"3","layout":"fitrows","slider-navigation":"bullet","slider-effects":"default","enable-direction-navigation":"disable","thumbnail-navigation":"below-slider","carousel-autoslide":"enable","grid-slider-navigation":"navigation","thumbnail-size":"Large Landscape 3","slider-thumbnail-size":"Portfolio Thumbnail","image-bottom-margin":"","padding-bottom":"30px"}}]}]';
//
			return json_decode($value, true);
		}
	}

	// create an option
	if( is_admin() ){ add_action('after_setup_theme', 'tourmaster_tour_option_init'); }
	if( !function_exists('tourmaster_tour_option_init') ){
		function tourmaster_tour_option_init(){

			$header_image_options = array(
				'feature-image' => esc_html__('Feature Image', 'tourmaster'),
				'custom-image' => esc_html__('Custom Image', 'tourmaster'),
				'slider' => esc_html__('Slider', 'tourmaster'),
				'gallery' => esc_html__('Gallery', 'tourmaster'),
				'video' => esc_html__('Video ( Youtube & Vimeo )', 'tourmaster'),
				'html5-video' => esc_html__('Html5 Video', 'tourmaster'),
			);
			if( !function_exists('gdlr_core_get_lightbox_atts') ){
				unset($header_image_options['gallery']);
			}

			if( class_exists('tourmaster_page_option') ){
				new tourmaster_page_option(array(
					'post_type' => array('tour'),
					'title' => esc_html__('Tour Settings', 'tourmaster'),
					'title-icon' => 'fa fa-plane',
					'slug' => 'tourmaster-tour-option',
					'options' => apply_filters('tourmaster_tour_options', array(

						'general' => array(
							'title' => esc_html__('General', 'tourmaster'),
							'options' => array(
								'header-image' => array(
									'title' => esc_html__('Header Background', 'tourmaster'),
									'type' => 'combobox',
									'options' => $header_image_options
								),
								'header-image-custom' => array(
									'title' => esc_html__('Header Custom Image', 'tourmaster'),
									'type' => 'upload',
									'condition' => array( 'header-image' => 'custom-image' )
								),
								'header-top-padding' => array(
									'title' => esc_html__('Header Top Padding', 'tourmaster'),
									'type' => 'text',
									'data-input-type' => 'pixel',
									'condition' => array( 'header-image' => array('feature-image', 'custom-image') ),
									'description' => esc_html__('Leaving this field blank to use default value from tourmaster option', 'tourmaster')
								),
								'header-bottom-padding' => array(
									'title' => esc_html__('Header Bottom Padding', 'tourmaster'),
									'type' => 'text',
									'data-input-type' => 'pixel',
									'condition' => array( 'header-image' => array('feature-image', 'custom-image') ),
									'description' => esc_html__('Leaving this field blank to use default value from tourmaster option', 'tourmaster')
								),
								'header-background-overlay-opacity' => array(
									'title' => esc_html__('Title Background Overlay Opacity', 'tourmaster'),
									'type' => 'text',
									'description' => esc_html__('Fill the number between 0 - 1 ( Leave Blank For Default Value )', 'tourmaster'),
								),
								'header-background-gradient' => array(
									'title' => esc_html__('Title Background Gradient', 'tourmaster'),
									'type' => 'combobox',
									'options' => array(
										'default' => esc_html__('Default', 'tourmaster'),
										'both' => esc_html__('Both', 'tourmaster'),
										'top' => esc_html__('Top', 'tourmaster'),
										'bottom' => esc_html__('Bottom', 'tourmaster'),
										'none' => esc_html__('None', 'tourmaster'),
									),
								),
								'header-slider' => array(
									'title' => esc_html__('Slider Images', 'tourmaster'),
									'type' => 'custom',
									'item-type' => 'gallery',
									'condition' => array( 'header-image'=>array('slider', 'gallery') ),
									'wrapper-class' => 'tourmaster-fullsize',
								),
								'lightbox-video-url' => array(
									'title' => esc_html__('Lightbox Video URL', 'tourmaster'),
									'type' => 'text',
									'condition' => array( 'header-image' => 'gallery' ),
								),
								'header-slider-thumbnail' => array(
									'title' => esc_html__('Slider Images Thumbnail Size', 'tourmaster'),
									'type' => 'combobox',
									'options' => 'thumbnail-size',
									'condition' => array( 'header-image'=>'slider' ),
								),
								'background-video-url' => array(
									'title' => esc_html__('Background Video URL', 'tourmaster'),
									'type' => 'text',
									'condition' => array( 'header-image' => 'video' ),
								),
								'background-video-url-mp4' => array(
									'title' => esc_html__('Background Video URL (MP4)', 'tourmaster'),
									'type' => 'text',
									'condition' => array( 'header-image' => 'html5-video' ),
								),
								'background-video-url-webm' => array(
									'title' => esc_html__('Background Video URL (WEBM)', 'tourmaster'),
									'type' => 'text',
									'condition' => array( 'header-image' => 'html5-video' ),
								),
								'background-video-url-ogg' => array(
									'title' => esc_html__('Background Video URL (ogg)', 'tourmaster'),
									'type' => 'text',
									'condition' => array( 'header-image' => 'html5-video' ),
								),
								'background-video-image' => array(
									'title' => esc_html__('Background Image Fallback', 'tourmaster'),
									'type' => 'upload',
									'condition' => array( 'header-image' => array('video', 'html5-video') ),
									'description' => esc_html__('This background will be showing up when the device you\'re using cannot render the video as background ( eg. mobile device )', 'tourmaster'),
								),

								'show-wordpress-editor-content' => array(
									'title' => esc_html__('Show Wordpress Editor Content', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'enable'
								),

								'enable-page-title' => array(
									'title' => esc_html__('Enable Page Title', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'enable'
								),
								'enable-header-review-number' => array(
									'title' => esc_html__('Enable Header Review Number', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'enable'
								),
								'promo-text' => array(
									'title' => esc_html__('Promo Text', 'tourmaster'),
									'type' => 'text'
								),
								'promo-text-ribbon-text-color' => array(
									'title' => esc_html__('Promo Text Ribbon Text Color', 'tourmaster'),
									'type' => 'colorpicker',
									'default' => '#ffffff'
								),
								'promo-text-ribbon-background' => array(
									'title' => esc_html__('Promo Text Ribbon Background', 'tourmaster'),
									'type' => 'colorpicker',
									'default' => '#467be7'
								),

								'sidebar-widget' => array(
									'title' => esc_html__('Sidebar Widget', 'tourmaster'),
									'type' => 'combobox',
									'options' => 'sidebar-default',
									'default' => 'default'
								),

							)
						), // general


						'tour-settings' => array(
							'title' => esc_html__('Tour Settings', 'tourmaster'),
							'options' => array(
								'form-settings' => array(
									'title' =>  esc_html__('Reservation Bar', 'tourmaster'),
									'type' => 'combobox',
									'options' => array(
										'booking' => esc_html__('Only Booking Form', 'tourmaster'),
										'enquiry' => esc_html__('Only Enquiry Form', 'tourmaster'),
										'both' => esc_html__('Both Booking & Enquiry Form', 'tourmaster'),
										'custom' => esc_html__('Custom Code', 'tourmaster'),
										'none' => esc_html__('None ( Hide the right side out )', 'tourmaster'),
									),
									'default' => 'booking'
								),
								'form-custom-title' => array(
									'title' => esc_html__('Custom Code Title', 'tourmaster'),
									'type' => 'text',
									'condition' => array( 'form-settings' => 'custom' ),
									'description' => esc_html__('Leave this field blank to display header price', 'tourmaster')
								),
								'form-custom-code' => array(
									'title' => esc_html__('Custom Code', 'tourmaster'),
									'type' => 'textarea',
									'condition' => array( 'form-settings' => 'custom' )
								),
								'show-price' => array(
									'title' =>  esc_html__('Show Header Price', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'enable',
									'condition' => array( 'form-settings' => 'enquiry' )
								),
								'date-selection-type' => array(
									'title' =>  esc_html__('Date Selection Type', 'tourmaster'),
									'type' => 'combobox',
									'options' => array(
										'calendar' => esc_html__('Calendar', 'tourmaster'),
										'date-list' => esc_html__('Date List', 'tourmaster')
									),
									'condition' => array('form-settings' => array('booking', 'both') )
								),
								'last-minute-booking' => array(
									'title' =>  esc_html__('Last Minute Booking (Hour)', 'tourmaster'),
									'type' => 'text',
									'condition' => array('form-settings' => array('booking', 'both') ),
									'description' =>  esc_html__('Specify the number of hours prior to the travel time you want to close the booking system.', 'tourmaster'),
								),
								'book-in-advance' => array(
									'title' =>  esc_html__('Book In Advance (Month)', 'tourmaster'),
									'type' => 'text',
									'single' => 'tourmaster-book-in-advance',
									'condition' => array('form-settings' => array('booking', 'both') ),
									'description' =>  esc_html__('For example, If you fill the number "10" (for ten months) and today is in March 2019, customers will have an ability to book the tour from today until Jan 2020 (ten months from current month). Leave this field blank for unlimited booking in advanced.', 'tourmaster'),
								),
//                                'require-bus-booking' => array(
//                                    'title' =>  esc_html__('Require Bus Booking', 'tourmaster'),
//                                    'type' => 'checkbox',
//                                    'default' => 'enable'
//                                ),
								'deposit-booking' => array(
									'title' =>  esc_html__('Deposit Booking', 'tourmaster'),
									'type' => 'combobox',
									'options' => array(
										'default' => esc_html__('Default', 'tourmaster'),
										'enable' => esc_html__('Enable (Custom)', 'tourmaster'),
										'disable' => esc_html__('Disable', 'tourmaster')
									),
									'description' => esc_html__('Default value can be set at the "Tourmaster" plugin option.', 'tourmaster'),
									'condition' => array('form-settings' => array('booking', 'both') ),
								),
								'deposit-amount' => array(
									'title' =>  esc_html__('Deposit Amount (%)', 'tourmaster'),
									'type' => 'text',
									'description' => esc_html__('Only fill number here.', 'tourmaster'),
									'condition' => array('form-settings' => array('booking', 'both'), 'deposit-booking' => 'enable')
								),
								'tour-price-text' => array(
									'title' =>  esc_html__('Tour Price Text', 'tourmaster'),
									'type' => 'text',
									'description' => esc_html__('Use for search function and displaying as tour information. Only fill number here.', 'tourmaster'),
									'condition' => array( 'form-settings' => array('booking', 'enquiry', 'both', 'custom') )
								),
								'tour-price-discount-text' => array(
									'title' =>  esc_html__('Tour Price Discount Text', 'tourmaster'),
									'type' => 'text',
									'description' => esc_html__('Use for search function and displaying as tour information. Only fill number here.', 'tourmaster'),
									'condition' => array( 'form-settings' => array('booking', 'enquiry', 'both', 'custom') )
								),
								'duration-text' => array(
									'title' =>  esc_html__('Duration Text', 'tourmaster'),
									'type' => 'text',
									'description' => esc_html__('Only for displaying as tour information.', 'tourmaster'),
								),
								'multiple-duration' => array(
									'title' =>  esc_html__('Duration (Days)', 'tourmaster'),
									'type' => 'text',
									'condition' => array('tour-type' => 'multiple'),
									'description' => esc_html__('Ex. Fill "3" for three days (Only Number is Allowed)', 'tourmaster'),
								),
								'departure-date' => array(
									'title' =>  esc_html__('Departure Date', 'tourmaster'),
									'type' => 'text',
									'description' => esc_html__('Only for displaying as tour information.', 'tourmaster'),
								),
								'departure-location' => array(
									'title' =>  esc_html__('Departure Location', 'tourmaster'),
									'type' => 'text',
									'description' => esc_html__('Only for displaying as tour information.', 'tourmaster'),
								),
								'return-location' => array(
									'title' =>  esc_html__('Destination Location', 'tourmaster'),
									'type' => 'text',
									'description' => esc_html__('Only for displaying as tour information.', 'tourmaster'),
								),
								'minimum-age' => array(
									'title' =>  esc_html__('Minimum Age', 'tourmaster'),
									'type' => 'text',
									'description' => esc_html__('Only for displaying as tour information.', 'tourmaster') . ' ' .
													 esc_html('Ex. "16+"', 'tourmaster')
								),
								'minimum-people-per-booking' => array(
									'title' =>  esc_html__('Minimum People Per Booking', 'tourmaster'),
									'type' => 'text',
									'single' => 'tourmaster-min-people-per-booking',
									'condition' => array('form-settings' => array('booking', 'both') ),
								),
								'maximum-people-per-booking' => array(
									'title' =>  esc_html__('Maximum People Per Booking', 'tourmaster'),
									'type' => 'text',
									'single' => 'tourmaster-max-people-per-booking',
									'condition' => array('form-settings' => array('booking', 'both') ),
								),
								'maximum-people' => array(
									'title' =>  esc_html__('Maximum People', 'tourmaster'),
									'type' => 'text',
									'single' => 'tourmaster-max-people',
									'condition' => array( 'form-settings' => array('booking', 'both') ),
									'description' => esc_html('Only for displaying as tour information.', 'tourmaster')
								),
								'display-single-tour-info' => array(
									'title' =>  esc_html__('Display Single Tour Info', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'enable'
								),
								'require-each-traveller-info' => array(
									'title' =>  esc_html__('Require Each Pilgrim\'s Info', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'enable',
									'condition' => array( 'form-settings' => array('booking', 'both') ),
									'description' => esc_html('This option requires customer to fill name and last name of each traveller.', 'tourmaster')
								),
//								'require-traveller-info-title' => array(
//									'title' =>  esc_html__('Require Pilgrim\'s Title (Mr/Mrs)', 'tourmaster'),
//									'type' => 'checkbox',
//									'condition' => array( 'require-each-traveller-info' => 'enable', 'form-settings' => array('booking', 'both') ),
//									'default' => 'enable'
//								),
                                'tour-travellers-id' => array(
                                    'title' =>  esc_html__('Required Pilgrim\'s IDs', 'tourmaster'),
                                    'type' => 'multi-combobox',
                                    'options' => 'post_type',
                                    'options-data' => 'tour_travellers_id',
                                    'condition' => array('form-settings' => array('booking', 'both') ),
                                ),
//								'require-traveller-passport' => array(
//									'title' =>  esc_html__('Require Pilgrim\'s Passport', 'tourmaster'),
//									'type' => 'checkbox',
//									'condition' => array( 'require-each-traveller-info' => 'enable', 'form-settings' => array('booking', 'both') ),
//									'default' => 'disable'
//								),
								'tour-service' => array(
									'title' =>  esc_html__('Tour Service', 'tourmaster'),
									'type' => 'multi-combobox',
									'options' => 'post_type',
									'options-data' => 'tour_service',
									'condition' => array( 'form-settings' => array('booking', 'both') ),
								),
                                'tour-seva' => array(
                                    'title' =>  esc_html__('Seva List', 'tourmaster'),
                                    'type' => 'multi-combobox',
                                    'options' => 'post_type',
                                    'options-data' => 'tour_seva',
                                    'condition' => array('form-settings' => array('booking', 'both') ),
                                ),
								// 'social-share' => array(
								// 	'title' =>  esc_html__('Social Share', 'tourmaster'),
								// 	'type' => 'checkbox',
								// 	'default' => 'enable'
								// ),
//								'custom-excerpt' => array(
//									'title' =>  esc_html__('Custom Excerpt', 'tourmaster'),
//									'type' => 'textarea'
//								),
//								'link-proceed-booking-to-external-url' => array(
//									'title' =>  esc_html__('Link Proceed Booking Button To External URL', 'tourmaster'),
//									'type' => 'text',
//									'condition' => array('form-settings' => array('booking', 'both') ),
//									'description' => esc_html__('This option will ignore all booking variables.', 'tourmaster')
//								),
//								'external-url-text' => array(
//									'title' =>  esc_html__('External URL Text', 'tourmaster'),
//									'type' => 'textarea',
//									'condition' => array('form-settings' => array('booking', 'both') ),
//									'description' => esc_html__('Only works with external url.', 'tourmaster')
//								),
							)
						), // 'tour-settings'

						'date-price' => array(
							'title' => esc_html__('Date & Price', 'tourmaster'),
							'options' => array(
								'column-1-open' => array( 'type' => 'column','column-size' => 30, 'right-divider' => 'fa fa-angle-right', 'bottom-divider' => true ),
								'tour-type' => array(
									'title' => esc_html__('1. Select Tour Type', 'tourmaster'),
									'type' => 'radioimage',
									'options' => array(
										'single' => TOURMASTER_URL . '/images/option/type-one.jpg',
										'multiple' => TOURMASTER_URL . '/images/option/type-multiple.jpg'
									),
									'wrapper-class' => 'tourmaster-center-option'
								),
								'column-1-close' => array( 'type' => 'column-close' ),

								'column-2-open' => array( 'type' => 'column','column-size' => 30, 'bottom-divider' => true ),
								'tour-timing-method' => array(
									'title' => esc_html__('2. Select Timing Method', 'tourmaster'),
									'type' => 'radioimage',
									'options' => array(
										'single' => TOURMASTER_URL . '/images/option/timing-one.jpg',
										'recurring' => TOURMASTER_URL . '/images/option/timing-recurring.jpg'
									),
									'wrapper-class' => 'tourmaster-center-option'
								),
								'column-2-close' => array( 'type' => 'column-close', 'clear' => true ),

								'date-price' => array(
									'title' => esc_html__('Add Date & Price', 'tourmaster'),
									'type' => 'custom',
									'item-type' => 'tabs',
									'options' => array(
										'date' => array(
											'title' => esc_html__('Date', 'tourmaster'),
											'type' => 'datepicker',
											'wrapper_class' => 'tourmaster-small-title'
										),
										'day' => array(
											'title' => esc_html__('Day', 'tourmaster'),
											'type' => 'checkboxes',
											'options' => array(
												'monday' => esc_html__('Mon', 'tourmaster'),
												'tuesday' => esc_html__('Tue', 'tourmaster'),
												'wednesday' => esc_html__('Wed', 'tourmaster'),
												'thursday' => esc_html__('Thu', 'tourmaster'),
												'friday' => esc_html__('Fri', 'tourmaster'),
												'saturday' => esc_html__('Sat', 'tourmaster'),
												'sunday' => esc_html__('Sun', 'tourmaster'),
												'select-all' => esc_html__('Select All', 'tourmaster'),
												'deselect-all' => esc_html__('Deselect All', 'tourmaster'),
											)
										),
										'month' => array(
											'title' => esc_html__('Month', 'tourmaster'),
											'type' => 'checkboxes',
											'options' => array(
												'1' => esc_html__('Jan', 'tourmaster'),
												'2' => esc_html__('Feb', 'tourmaster'),
												'3' => esc_html__('Mar', 'tourmaster'),
												'4' => esc_html__('Apr', 'tourmaster'),
												'5' => esc_html__('May', 'tourmaster'),
												'6' => esc_html__('Jun', 'tourmaster'),
												'7' => esc_html__('Jul', 'tourmaster'),
												'8' => esc_html__('Aug', 'tourmaster'),
												'9' => esc_html__('Sep', 'tourmaster'),
												'10' => esc_html__('Oct', 'tourmaster'),
												'11' => esc_html__('Nov', 'tourmaster'),
												'12' => esc_html__('Dec', 'tourmaster'),
												'select-all' => esc_html__('Select All', 'tourmaster'),
												'deselect-all' => esc_html__('Deselect All', 'tourmaster'),
											)
										),
										'year' => array(
											'title' => esc_html__('Year', 'tourmaster'),
											'type' => 'checkboxes',
											'options' => array(
//												'2019' => '2019',
												'2020' => '2020',
												'2021' => '2021',
												'2022' => '2022',
												'2023' => '2023',
												'2024' => '2024',
												'2025' => '2025',
												'2026' => '2026',
												'2027' => '2027',
												'2028' => '2028',
											)
										),

										'extra-date-description' => array(
											'description' => esc_html__('Fill the date in yyyy-mm-dd format and separated the date using comma. Eg. 2020-12-25,2020-12-26,2020-12-27', 'tourmaster'),
											'type' => 'description'
										),
										'extra-date' => array(
											'title' => esc_html__('INCLUDE EXTA DATES USING DATE FORMAT', 'tourmaster'),
											'type' => 'textarea',
											'wrapper_class' => 'tourmaster-full-size',
											'title_color' => '#67b1a1'
										),
										'exclude-extra-date' => array(
											'title' => esc_html__('EXCLUDE EXTRA DATES USING DATE FORMAT', 'tourmaster'),
											'type' => 'textarea',
											'wrapper_class' => 'tourmaster-full-size',
											'title_color' => '#be7272'
										),

//										'pricing-title' => array(
//											'title' => esc_html__('PRICING', 'tourmaster'),
//											'type' => 'title',
//											'wrapper_class' => 'tourmaster-middle-with-divider'
//										),
										'pricing-method' => array(
											'title' => esc_html__('Pricing Method', 'tourmaster'),
											'type' => 'radioimage',
											'options' => array(
												'fixed' => TOURMASTER_URL . '/images/option/fixed-price.jpg',
//												'variable' => TOURMASTER_URL . '/images/option/variable-price.jpg',
//												'group' => TOURMASTER_URL . '/images/option/group-price.jpg'
											),
//											'description' => esc_html__('* Variable pricing will differentiate the price of adult, children, studnet and infant.', 'tourmaster'),
										),
										'pricing-room-base' => array(
											'title' => esc_html__('Enable Room Base', 'tourmaster'),
											'type' => 'radioimage',
											'options' => array(
//												'enable' => TOURMASTER_URL . '/images/option/room-base-enable.jpg',
												'disable' => TOURMASTER_URL . '/images/option/room-base-disable.jpg'
											),
											'condition' => array( 'pricing-method' => array( 'fixed' ) ),
//											'description' => esc_html__('* Calculate tour price based on the hotel\'s room. For example, 2 Adults 2 Rooms will be more expensive than 2 Adults 1 Room.', 'tourmaster'),
										),
//										'base-price-description' => array(
//											'description' => wp_kses(
//												__('When you choose <strong>Room Base Pricing</strong>. There will be 2 parts of the price. The final price will be the summary of these two.', 'tourmaster'),
//												array( 'strong' => array() )
//											),
//											'type' => 'description',
//											'condition' => array( 'pricing-method' => array( 'fixed', 'variable' ) )
//										),
										'package' => array(
											'title' => esc_html__('Add Package', 'tourmaster'),
											'type' => 'tabs',
											'settings' => array(
												'tab-title' => esc_html__('Default Package', 'tourmaster')
											),
											'options' => array(
												'default-package' => array(
													'title' => esc_html__('Default Package', 'tourmaster'),
													'type' => 'checkbox',
													'default' => 'disable',
													'description' => esc_html__('Enable to pre-selected this package on page load. Only the first package that enable this option is effected.')
												),
												'group-slug' => array(
													'title' => esc_html__('Package Group Alias', 'tourmaster'),
													'type' => 'text',
													'description' => esc_html__('Fill the same string on each package to group the people from 2 package together ( for Max people option ).')
												),
												'title' => array(
													'title' => esc_html__('Package Title', 'tourmaster'),
													'type' => 'text',
												),
												'caption' => array(
													'title' => esc_html__('Package Caption', 'tourmaster'),
													'type' => 'text',
												),
												'start-time' => array(
													'title' =>  esc_html__('Start Time', 'tourmaster'),
													'type' => 'time'
												),

												'base-price-title' => array(
													'title' => esc_html__('BASE PRICE', 'tourmaster'),
													'type' => 'title'
												),
												'person-price' => array(
													'title' => esc_html__('Price Per Pilgrim', 'tourmaster'),
													'type' => 'text',
													'description' => esc_html__('Price per Pilgrim (Fill only number).', 'tourmaster'),
												),
												'adult-price' => array(
													'title' => esc_html__('Adult', 'tourmaster'),
													'type' => 'text',
													'description' => esc_html__('Price per Pilgrim (Fill only number). * Leave this field blank to not apply.', 'tourmaster'),
												),
//                                                'sr-citizen-price' => array(
//                                                    'title' => esc_html__('Sr. Citizen', 'tourmaster'),
//                                                    'type' => 'text',
//                                                    'description' => esc_html__('Price per Pilgrim (Fill only number). * Leave this field blank to not apply.', 'tourmaster'),
//                                                ),
												'male-price' => array(
													'title' => esc_html__('Male', 'tourmaster'),
													'type' => 'text',
													'description' => esc_html__('Price per Pilgrim (Fill only number). * Leave this field blank to not apply.', 'tourmaster'),
												),
												'female-price' => array(
													'title' => esc_html__('Female', 'tourmaster'),
													'type' => 'text',
													'description' => esc_html__('Price per person (Fill only number). * Leave this field blank to not apply.', 'tourmaster'),
												),
												'children-price' => array(
													'title' => esc_html__('Child', 'tourmaster'),
													'type' => 'text',
													'description' => esc_html__('Price per person (Fill only number). * Leave this field blank to not apply.', 'tourmaster'),
												),
												'student-price' => array(
													'title' => esc_html__('Student', 'tourmaster'),
													'type' => 'text',
													'description' => esc_html__('Price per person (Fill only number). * Leave this field blank to not apply.', 'tourmaster'),
												),
												'infant-price' => array(
													'title' => esc_html__('Infant', 'tourmaster'),
													'type' => 'text',
													'description' => esc_html__('Price per person (Fill only number). * Leave this field blank to not apply.', 'tourmaster'),
												),
												'group-price' => array(
													'title' => esc_html__('Price per group', 'tourmaster'),
													'type' => 'text',
													'description' => esc_html__('Fill only number.', 'tourmaster'),
												),
												'max-group' => array(
													'title' => esc_html__('Max group', 'tourmaster'),
													'type' => 'text',
													'description' => esc_html__('How many group you can accept in this tour? (Fill only number).', 'tourmaster'),
												),
												'max-group-people' => array(
													'title' => esc_html__('Max people for each group', 'tourmaster'),
													'type' => 'text',
													'description' => esc_html__('People amount in each group (Fill only number).', 'tourmaster'),
												),
												'same-gender' => array(
													'title' => esc_html__('Same Gender Required', 'tourmaster'),
													'type' => 'checkbox',
													'default' => 'disable',
													'description' => esc_html__('This feature will allow only one gender in the this package. Ex. If female book first, the rest has to be female as well. However, mix gender will be allowed if women and men book at the same time by the same customer.', 'tourmaster')
												),

												'room-base-price-title' => array(
													'title' => esc_html__('ROOM BASED PRICE', 'tourmaster'),
													'type' => 'title',
												),
												'initial-price' => array(
													'title' => esc_html__('Initial Price', 'tourmaster'),
													'type' => 'text',
													'description' => esc_html__('This price based on 2 adults', 'tourmaster'),
												),
												'single-discount' => array(
													'title' => esc_html__('Single Discount', 'tourmaster'),
													'type' => 'text',
													'description' => esc_html__('This discount will be used for deducting the price of Initial Price. Ex, If you set Initial Price as $100 and Single Discount as $30. If there’re two guests in this room, they will pay for $100. However, if there\'s only one guest in this room, he/she will pay for only $70 instead of $100. This option is an alternative for single supplement.', 'tourmaster'),
												),
												'additional-person' => array(
													'title' => esc_html__('Additional Person', 'tourmaster'),
													'type' => 'text',
													'description' => esc_html__('Price per person (Fill only number).', 'tourmaster'),
												),
												'additional-adult' => array(
													'title' => esc_html__('Additional Adult', 'tourmaster'),
													'type' => 'text',
													'description' => esc_html__('Price per person (Fill only number). * Leave this field blank to not apply.', 'tourmaster'),
												),
//                                                'additional-sr-citizen' => array(
//                                                    'title' => esc_html__('Additional Sr. Citizen', 'tourmaster'),
//                                                    'type' => 'text',
//                                                    'description' => esc_html__('Price per person (Fill only number). * Leave this field blank to not apply.', 'tourmaster'),
//                                                ),
												'additional-male' => array(
													'title' => esc_html__('Additional Male', 'tourmaster'),
													'type' => 'text',
													'description' => esc_html__('Price per person (Fill only number). * Leave this field blank to not apply.', 'tourmaster'),
												),
												'additional-female' => array(
													'title' => esc_html__('Additional Female', 'tourmaster'),
													'type' => 'text',
													'description' => esc_html__('Price per person (Fill only number). * Leave this field blank to not apply.', 'tourmaster'),
												),
												'additional-children' => array(
													'title' => esc_html__('Additional Child', 'tourmaster'),
													'type' => 'text',
													'description' => esc_html__('Price per person (Fill only number). * Leave this field blank to not apply.', 'tourmaster'),
												),
												'additional-student' => array(
													'title' => esc_html__('Additional Student', 'tourmaster'),
													'type' => 'text',
													'description' => esc_html__('Price per person (Fill only number). * Leave this field blank to not apply.', 'tourmaster'),
												),
												'additional-infant' => array(
													'title' => esc_html__('Additional Infant', 'tourmaster'),
													'type' => 'text',
													'description' => esc_html__('Price per person (Fill only number). * Leave this field blank to not apply.', 'tourmaster'),
												),
												'minimum-people-per-booking' => array(
													'title' => esc_html__('Minimum People Per Booking', 'tourmaster'),
													'type' => 'text',
												),
												'max-room' => array(
													'title' => esc_html__('Max Room', 'tourmaster'),
													'type' => 'text',
												),
												'max-people-per-room' => array(
													'title' => esc_html__('Max People Per Room', 'tourmaster'),
													'type' => 'text',
												),
												'max-people' => array(
													'title' => esc_html__('Maximum People', 'tourmaster'),
													'type' => 'text'
												),
											),
											'condition' => array( 'pricing-method' => 'css-condition', 'pricing-room-base' => 'css-condition' ),
										),
										'select-package-text' => array(
											'title' => esc_html__('Select a Package Text', 'tourmaster'),
											'type' => 'text',
											'description' => esc_html__('Leave blank for default', 'tourmaster')
										)
									),
									'settings' => array(
										'tab-title' => esc_html__('Date', 'tourmaster') . '<i class="fa fa-edit" ></i>',
										'allow-duplicate' => '<i class="fa fa-copy" ></i>' . esc_html__('Duplicate', 'tourmaster'),
									),
									'condition' => array( 'tour-type' => 'css-condition', 'tour-timing-method' => 'css-condition' ),
									'wrapper-class' => 'tourmaster-with-bottom-divider'
								),
//
							),
						), // 'date-price'

//
						'group-message' => array(
							'title' => esc_html__('Reminder & Message', 'tourmaster'),
							'options' => array(
								'carbon-copy-mail' => array(
									'title' =>  esc_html__('Carbon Copy Email (CC)', 'tourmaster'),
									'type' => 'text',
									'single' => 'tourmaster-tour-cc-mail',
									'description' => esc_html__('Fill the email here to send a copy of an Admin Email for transaction related to this tour.', 'tourmaster')
								),
								'reminder-message-title' => array(
									'title' =>  esc_html__('Reminder Message', 'tourmaster'),
									'type' => 'title',
								),
								'enable-reminder-message' => array(
									'title' =>  esc_html__('Enable Reminder Message', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'disable',
									'single' => 'tourmaster-reminder-message',
									'description' => esc_html__('By, enabling this option, the system will automatically send a reminder message to customer\'s email.', 'tourmaster')
								),
								'reminder-message-days-before-travel' => array(
									'title' =>  esc_html__('Reminder Message Days Before Travel', 'tourmaster'),
									'type' => 'text',
									'condition' => array( 'enable-reminder-message' => 'enable' ),
									'description' => esc_html__('Only number is allowed here.', 'tourmaster')
								),
								'reminder-message-mail-subject' => array(
									'title' =>  esc_html__('Email Subject', 'tourmaster'),
									'type' => 'text',
									'condition' => array( 'enable-reminder-message' => 'enable' ),
								),
								'reminder-message-mail-message' => array(
									'title' =>  esc_html__('Email Message', 'tourmaster'),
									'type' => 'textarea',
									'condition' => array( 'enable-reminder-message' => 'enable' ),
								),
								'enable-reminder-message-admin-copy' => array(
									'title' =>  esc_html__('Send a copy to admin', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'disable',
									'condition' => array( 'enable-reminder-message' => 'enable' ),
								),

								'group-message-title' => array(
									'title' =>  esc_html__('Group Message', 'tourmaster'),
									'type' => 'title',
									'wrapper-class' => 'tourmaster-top-margin-wrapper'
								),
								'group-message-date' => array(
									'title' =>  esc_html__('Group Message Date', 'tourmaster'),
									'type' => 'datepicker',
									'description' => esc_html__('* To specify the exact group of customer that you want to send the message to.', 'tourmaster')
								),
								'group-message-mail-subject' => array(
									'title' =>  esc_html__('Email Subject', 'tourmaster'),
									'type' => 'text',
								),
								'group-message-mail-message' => array(
									'title' =>  esc_html__('Email Message', 'tourmaster'),
									'type' => 'textarea',
								),
								'enable-group-message-admin-copy' => array(
									'title' =>  esc_html__('Send a copy to admin', 'tourmaster'),
									'type' => 'checkbox',
									'default' => 'disable',
									'description' => esc_html__('Enable this to send the copy of the mail which cusmoter receieve to admin e-mail.', 'tourmaster')
								),
								'group-message-submit' => array(
									'button-title' =>  esc_html__('Send Email', 'tourmaster'),
									'type' => 'button',
									'data-type' => 'ajax',
									'data-action' => 'tourmaster_submit_group_message',
									'data-fields' => array( 'group-message-date', 'group-message-mail-subject', 'group-message-mail-message', 'enable-group-message-admin-copy', 'group-message-tour-id' )
								),
							)
						),

					)) // tourmaster_tour_options
				)); // tourmaster_page_option

				new tourmaster_page_option(array(
					'post_type' => array('tour'),
					'title' => esc_html__('Review Manager', 'tourmaster'),
					'title-icon' => 'fa fa-plane',
					'slug' => 'tourmaster-review-option',
					'options' => apply_filters('tourmaster_review_options', array(

						'manage-review' => array(
							'title' => esc_html__('Manage Review', 'tourmaster'),
							'options' => array(

								'manage-review' => array(
									'type' => 'manage-review'
								)

							)
						), // manage review

						'add-a-review' => array(
							'title' => esc_html__('Add A Review', 'tourmaster'),
							'options' => array(

								'add-review' => array(
									'type' => 'add-review'
								)

							)
						), // add a review

					))
				));

			} // function_exits

		} // tourmaster_tour_option_init
	}

	// modify settings for version 3.0
	add_filter('tourmaster-tour-option-init-value', 'tourmaster_tour_option_init_value', 10, 2);
	if( !function_exists('tourmaster_tour_option_init_value') ){
		function tourmaster_tour_option_init_value( $tour_option, $post_id ){

			if( !empty($tour_option['date-price']) ){

				$max_people = get_post_meta($post_id, 'tourmaster-max-people', true);
				$old_settings = array( 'person-price', 'adult-price','sr-citizen-price', 'children-price', 'student-price', 'infant-price',
					'initial-price', 'additional-person', 'additional-adult', 'additional-children', 'additional-student', 'additional-infant', 'max-people-per-room',
					'group-price', 'max-group', 'max-group-people'
				);

				foreach( $tour_option['date-price'] as $slug => $date_price ){
					if( empty($date_price['package']) ){
						$default_package = array();

						foreach( $old_settings as $old_slug ){
							if( !empty($date_price[$old_slug]) ){
								$default_package[$old_slug] = $date_price[$old_slug];
								unset($date_price[$old_slug]);
							}
						}

						if( !empty($default_package) ){
							if( !empty($max_people) ){
								$default_package['max-people'] = $max_people;
							}
							if( !empty($tour_option['start-time']) ){
								$default_package['start-time'] = $tour_option['start-time'];
								unset($tour_option['start-time']);
							}
							$date_price['package'] = array($default_package);
						}
					}

					$tour_option['date-price'][$slug] = $date_price;
				}
			}

			return $tour_option;
		}
	}

	// save tour meta option hook
	if( is_admin() ){ add_action('save_post_tour', 'tourmaster_save_tour_meta'); }
	if( !function_exists('tourmaster_save_tour_meta') ){
		function tourmaster_save_tour_meta( $post_id ){

			// check if nonce is available
			if( !isset($_POST['plugin_page_option_security']) ){
				return;
			}

			// vertify that the nonce is vaild
			if( !wp_verify_nonce($_POST['plugin_page_option_security'], 'tourmaster_page_option') ) {
				return;
			}

			// ignore the auto save
			if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ){
				return;
			}

			// check the user's permissions.
			if( isset($_POST['post_type']) && 'tour' == $_POST['post_type'] ) {
				if( !current_user_can('edit_post', $post_id) ){
					return;
				}
			}

			// additional meta field
			if( !empty($_POST['tourmaster-tour-option']) ){
				$tour_option = json_decode(tourmaster_process_post_data($_POST['tourmaster-tour-option']), true);

				// determine all available dates
				if( !empty($tour_option['date-price']) && !empty($tour_option['tour-timing-method']) ){
					$date_list = array();
					foreach( $tour_option['date-price'] as $settings ){
						$dates = tourmaster_get_tour_dates($settings, $tour_option['tour-timing-method']);
						$date_list = array_merge($date_list, $dates);
					}

					if( !empty($date_list) ){
						$date_list = array_unique($date_list);
						sort($date_list);
						update_post_meta($post_id, 'tourmaster-tour-date', implode(',', $date_list));

						$book_in_advance = empty($tour_option['book-in-advance'])? '': $tour_option['book-in-advance'];
						$date_avail = tourmaster_filter_tour_date($date_list, $book_in_advance);
						if( !empty($date_avail) ){
							update_post_meta($post_id, 'tourmaster-tour-date-avail', implode(',', $date_avail));
						}else{
							delete_post_meta($post_id, 'tourmaster-tour-date-avail');
						}
					}else{
						delete_post_meta($post_id, 'tourmaster-tour-date');
						delete_post_meta($post_id, 'tourmaster-tour-date-avail');
					}

					// tour duration
					if( !empty($tour_option['tour-type']) ){
						if( $tour_option['tour-type'] == 'single' ){
							update_post_meta($post_id, 'tourmaster-tour-duration', 1);
						}else if( !empty($tour_option['multiple-duration']) ){
							update_post_meta($post_id, 'tourmaster-tour-duration', $tour_option['multiple-duration']);
						}else{
							delete_post_meta($post_id, 'tourmaster-tour-duration');
						}
					}
				}else{
					delete_post_meta($post_id, 'tourmaster-tour-date');
					delete_post_meta($post_id, 'tourmaster-tour-date-avail');
				}

				// set the tour price
				if( !empty($tour_option['tour-price-discount-text']) ){
					update_post_meta($post_id, 'tourmaster-tour-price', $tour_option['tour-price-discount-text']);
					update_post_meta($post_id, 'tourmaster-tour-discount', 'true');
				}else if( !empty($tour_option['tour-price-text']) ){
					update_post_meta($post_id, 'tourmaster-tour-price', $tour_option['tour-price-text']);
					update_post_meta($post_id, 'tourmaster-tour-discount', 'false');
				}else{
					delete_post_meta($post_id, 'tourmaster-tour-price');
					delete_post_meta($post_id, 'tourmaster-tour-discount');
				}
			}
			tourmaster_update_review_score($post_id);
		} // tourmaster_save_tour_meta
	}

	// trigger the date available date every day
	add_action('tourmaster_schedule_hourly', 'tourmaster_hourly_filter_tour_date');
	if( !function_exists('tourmaster_hourly_filter_tour_date') ){
		function tourmaster_hourly_filter_tour_date(){
			global $wpdb;

			// filter available date
			$sql  = "SELECT post_id, meta_value FROM {$wpdb->postmeta} ";
		    $sql .= "WHERE meta_key = 'tourmaster-tour-date' ";
		    $results = $wpdb->get_results($sql);
		    if( !empty($results) ){
		    	foreach( $results as $result ){
		    		$date_list = explode(',', $result->meta_value);
		    		$book_in_advance = get_post_meta($result->post_id, 'tourmaster-book-in-advance', true);
					$date_avail = tourmaster_filter_tour_date($date_list, $book_in_advance);
					if( !empty($date_avail) ){
						update_post_meta($result->post_id, 'tourmaster-tour-date-avail', implode(',', $date_avail));
					}else{
						delete_post_meta($result->post_id, 'tourmaster-tour-date-avail');
					}
		    	}
		    }

		    // filter the depart status
		    $current_date = current_time('Y-m-d');
		    $sql  = "UPDATE {$wpdb->prefix}tourmaster_order ";
		    $sql .= "SET order_status = 'departed' ";
		    $sql .= "WHERE travel_date <= CURDATE() ";
		    $sql .= "AND order_status IN ('approved','online-paid')";
		    $wpdb->query($sql);


		} // tourmaster_hourly_filter_tour_date
	}

	// cancel booking
	add_action('tourmaster_schedule_daily', 'tourmaster_cancel_booking');
	if( !function_exists('tourmaster_cancel_booking') ){
		function tourmaster_cancel_booking(){

			$day_num = tourmaster_get_option('general', 'cancel-booking-day', '');
			if( empty($day_num) ){ return; }

			global $wpdb;

			$current_date = current_time('mysql');
			$cancel_date = date('Y-m-d H:i:s', (strtotime($current_date) - (intval($day_num) * 86400)));

			$sql  = "SELECT id FROM {$wpdb->prefix}tourmaster_order ";
		    $sql .= "WHERE booking_date <= '{$cancel_date}' ";
		    $sql .= "AND order_status IN ('pending','rejected')";
		    $results = $wpdb->get_results($sql);

 			if( !empty($results) ){

 				// update status
 				$sql  = "UPDATE {$wpdb->prefix}tourmaster_order ";
			    $sql .= "SET order_status = 'cancel' ";
			    $sql .= "WHERE id IN (";
			    $count = 0;
			    foreach( $results as $result ){ $count++;
			    	$sql .= ($count <= 1? '': ',') . $result->id;
			    }
			    $sql .= ")";
			   	$wpdb->query($sql);

 				// send email
 				foreach( $results as $result ){
 					tourmaster_mail_notification('booking-cancelled-mail', $result->id);
 				}
 			}
		} // tourmaster_cancel_booking
	}