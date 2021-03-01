<?php
/**
 * Plugin Name:       WP Social Chat
 * Plugin URI:        https://quadlayers.com/portfolio/wordpress-whatsapp-chat/
 * Description:       WhatsApp Chat allows your visitors to contact you or your team through WhatsApp chat with a single click.
 * Version:           4.3.4
 * Author:            QuadLayers
 * Author URI:        https://quadlayers.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wp-whatsapp-chat
 * Domain Path:       /languages
 */
if (!defined('ABSPATH')) {
  die('-1');
}
if (!defined('QLWAPP_PLUGIN_NAME')) {
  define('QLWAPP_PLUGIN_NAME', 'IQ Whatsapp');
}
if (!defined('QLWAPP_PLUGIN_VERSION')) {
  define('QLWAPP_PLUGIN_VERSION', '4.3.4');
}
if (!defined('QLWAPP_PLUGIN_FILE')) {
  define('QLWAPP_PLUGIN_FILE', __FILE__);
}
if (!defined('QLWAPP_PLUGIN_DIR')) {
  define('QLWAPP_PLUGIN_DIR', __DIR__ . DIRECTORY_SEPARATOR);
}
if (!defined('QLWAPP_DOMAIN')) {
  define('QLWAPP_DOMAIN', 'qlwapp');
}
if (!defined('QLWAPP_WORDPRESS_URL')) {
  define('QLWAPP_WORDPRESS_URL', 'https://wordpress.org/plugins/wp-whatsapp-chat/');
}
if (!defined('QLWAPP_REVIEW_URL')) {
  define('QLWAPP_REVIEW_URL', 'https://wordpress.org/support/plugin/wp-whatsapp-chat/reviews/?filter=5#new-post');
}
if (!defined('QLWAPP_DEMO_URL')) {
  define('QLWAPP_DEMO_URL', 'https://quadlayers.com/portfolio/wordpress-whatsapp-chat/?utm_source=qlwapp_admin');
}
if (!defined('QLWAPP_PURCHASE_URL')) {
  define('QLWAPP_PURCHASE_URL', QLWAPP_DEMO_URL);
}
if (!defined('QLWAPP_SUPPORT_URL')) {
  define('QLWAPP_SUPPORT_URL', 'https://quadlayers.com/account/support/?utm_source=qlwapp_admin');
}
if (!defined('QLWAPP_GROUP_URL')) {
  define('QLWAPP_GROUP_URL', 'https://www.facebook.com/groups/quadlayers');
}

if (!class_exists('QLWAPP')) {

  class QLWAPP {

    protected static $instance;

    function ajax_dismiss_notice() {
      if (current_user_can('manage_options')) {

        if (!empty($_REQUEST) && check_admin_referer('qlwapp_dismiss_notice', 'nonce')) {

          if ($notice_id = ( isset($_REQUEST['notice_id']) ) ? sanitize_key($_REQUEST['notice_id']) : '') {

            update_user_meta(get_current_user_id(), $notice_id, true);

            wp_send_json($notice_id);
          }
        }
      }
      wp_die();
    }

    function add_notices() {

      if (!get_transient('qlwapp-first-rating') && !get_user_meta(get_current_user_id(), 'qlwapp-user-rating', true)) {
        ?>
        <div id="qlwapp-admin-rating" class="qlwapp-notice notice is-dismissible" data-notice_id="qlwapp-user-rating">
          <div class="notice-container" style="padding-top: 10px; padding-bottom: 10px; display: flex; justify-content: left; align-items: center;">
            <div class="notice-image">
              <img style="border-radius:50%;max-width: 90px;" src="<?php echo plugins_url('/assets/img/logo.jpg', QLWAPP_PLUGIN_FILE); ?>" alt="<?php echo esc_html(QLWAPP_PLUGIN_NAME); ?>>">
            </div>
            <div class="notice-content" style="margin-left: 15px;">
              <p>
                <?php printf(esc_html__('Hello! Thank you for choosing the %s plugin!', 'wp-whatsapp-chat'), QLWAPP_PLUGIN_NAME); ?>
                <br/>
                <?php esc_html_e('Could you please give it a 5-star rating on WordPress? We know its a big favor, but we\'ve worked very much and very hard to release this great product. Your feedback will boost our motivation and help us promote and continue to improve this product.', 'wp-whatsapp-chat'); ?>
              </p>
              <a href="<?php echo esc_url(QLWAPP_REVIEW_URL); ?>" class="button-primary" target="_blank">
                <?php esc_html_e('Yes, of course!', 'wp-whatsapp-chat'); ?>
              </a>
              <a href="<?php echo esc_url(QLWAPP_SUPPORT_URL); ?>" class="button-secondary" target="_blank">
                <?php esc_html_e('Report a bug', 'wp-whatsapp-chat'); ?>
              </a>
            </div>				
          </div>
        </div>
        <script>
          (function ($) {
            $('.qlwapp-notice').on('click', '.notice-dismiss', function (e) {
              e.preventDefault();
              var notice_id = $(e.delegateTarget).data('notice_id');
              $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                  notice_id: notice_id,
                  action: 'qlwapp_dismiss_notice',
                  nonce: '<?php echo wp_create_nonce('qlwapp_dismiss_notice'); ?>'
                },
                success: function (response) {
                  console.log(response);
                },
              });
            });
          })(jQuery);
        </script>
        <?php
      }
    }

    function includes() {
      include_once('includes/defaults.php');
      include_once('includes/frontend.php');
      include_once('includes/settings.php');
    }

    function languages() {
      load_plugin_textdomain('wp-whatsapp-chat', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }

    function add_premium_js() {
      if (!class_exists('QLWAPP_PRO')) {
        ?>
        <style>
          .qlwapp-premium-field {
            opacity: 0.5; 
            pointer-events: none;
          }
          .qlwapp-premium-field .description {
            display: block!important;
          }
        </style>
        <?php
      }
    }

    function init() {
      add_action('admin_footer', array($this, 'add_premium_js'));
      add_action('admin_notices', array($this, 'add_notices'));
      add_action('wp_ajax_qlwapp_dismiss_notice', array($this, 'ajax_dismiss_notice'));
    }

    public static function do_activation() {
      set_transient('qlwapp-first-rating', true, MONTH_IN_SECONDS);
    }

    public static function instance() {
      if (!isset(self::$instance)) {
        self::$instance = new self();
        self::$instance->languages();
        self::$instance->includes();
        self::$instance->init();
      }
      return self::$instance;
    }

  }

  add_action('plugins_loaded', array('QLWAPP', 'instance'));

  register_activation_hook(QLWAPP_PLUGIN_FILE, array('QLWAPP', 'do_activation'));
}
