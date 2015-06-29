<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://orionorigin.com
 * @since      3.0
 *
 * @package    Wpd
 * @subpackage Wpd/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      3.0
 * @package    Wpd
 * @subpackage Wpd/includes
 * @author     ORION <support@orionorigin.com>
 */
class Wpd {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    3.0
	 * @access   protected
	 * @var      Wpd_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    3.0
	 * @access   protected
	 * @var      string    $wpd    The string used to uniquely identify this plugin.
	 */
	protected $wpd;

	/**
	 * The current version of the plugin.
	 *
	 * @since    3.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    3.0
	 */
	public function __construct() {

		$this->wpd = 'wpd';
		$this->version = WPD_VERSION;

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Wpd_Loader. Orchestrates the hooks of the plugin.
	 * - Wpd_i18n. Defines internationalization functionality.
	 * - Wpd_Admin. Defines all hooks for the admin area.
	 * - Wpd_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    3.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wpd-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wpd-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wpd-admin.php';
                
                /**
		 * The class responsible for defining all actions that occur in the admin area and related to products.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wpd-product.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wpd-public.php';

		$this->loader = new Wpd_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Wpd_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    3.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Wpd_i18n();
		$plugin_i18n->set_domain( $this->get_wpd() );

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    3.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new WPD_Admin( $this->get_wpd(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
                $this->loader->add_action( 'admin_init', $plugin_admin, 'wpc_redirect' );
                
                $this->loader->add_action( 'admin_menu', $plugin_admin, 'add_woo_parts_submenu');
                
                //General
                $this->loader->add_action( 'init', $plugin_admin, 'init_sessions', 1);
                $this->loader->add_action( 'init', $plugin_admin, 'init_globals');
                $this->loader->add_action( 'wpc_admin_field_wpc-icon-select', $plugin_admin, 'get_icon_selector_field');
                $this->loader->add_action('admin_notices', $plugin_admin, 'notify_customization_page_missing');
                $this->loader->add_action('admin_notices', $plugin_admin, 'notify_minmimum_required_parameters');
                $this->loader->add_action('admin_notices', $plugin_admin, 'run_wpc_db_updates_requirements');
                $this->loader->add_action('wp_ajax_run_updater', $plugin_admin, 'run_wpc_updater');
                $this->loader->add_filter('upload_mimes', $plugin_admin, 'wpc_add_custom_mime_types');
                $this->loader->add_action( 'admin_notices', $plugin_admin, 'get_ad_messages' );
                
                //Products
                $product_admin=new WPD_Product();
                $this->loader->add_action( 'add_meta_boxes', $product_admin, 'wpc_register_product_metabox', 1);
                $this->loader->add_action( 'woocommerce_product_write_panel_tabs',$product_admin, 'get_product_tab_label');
                $this->loader->add_action( 'woocommerce_product_write_panels', $product_admin, 'get_product_tab_data');
                $this->loader->add_action( 'wp_ajax_get_output_setting_tab_data_content', $product_admin, 'get_output_setting_tab_data_content_ajx' );
                $this->loader->add_action( 'wp_ajax_get_product_tab_data_content', $product_admin, 'get_product_tab_data_content_ajx' );
//                $this->loader->add_action( 'woocommerce_product_options_general_product_data', $product_admin, 'get_canvas_clip_dimensions_fields' );
                $this->loader->add_action( 'admin_notices', $product_admin, 'get_customizable_product_errors' );
                $this->loader->add_action('save_post_product', $product_admin, 'save_customizable_meta');                
                $this->loader->add_filter('manage_edit-product_columns', $product_admin, 'get_product_columns');
                $this->loader->add_action('manage_product_posts_custom_column', $product_admin, 'get_products_columns_values', 5, 2);
                
                //Cliparts hooks
                $clipart=new WPD_Clipart();
                $this->loader->add_action( 'init', $clipart, 'register_cpt_cliparts');
                $this->loader->add_action( 'add_meta_boxes', $clipart, 'get_cliparts_metabox');
                $this->loader->add_action( 'save_post_wpc-cliparts', $clipart, 'save_cliparts' );
                
                $wpd_design=new WPD_Design();
                //Allow us to hide the wpc_data_upl meta from the meta list in the order details page
                $this->loader->add_filter( 'woocommerce_hidden_order_itemmeta', $wpd_design, 'unset_wpc_data_upl_meta');

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    3.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new WPD_Public( $this->get_wpd(), $this->get_version() );
                $plugin_admin = new WPD_Admin( $this->get_wpd(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
                $this->loader->add_action( 'init', $plugin_public, 'register_shortcodes');
                $this->loader->add_action( 'woocommerce_after_add_to_cart_button', $plugin_public, 'get_customize_btn');
                $this->loader->add_action( 'wp_ajax_handle_picture_upload', $plugin_public, 'handle_picture_upload');
                $this->loader->add_action( 'wp_ajax_nopriv_handle_picture_upload', $plugin_public, 'handle_picture_upload');
                
                $this->loader->add_filter( 'woocommerce_loop_add_to_cart_link', $plugin_public, 'get_customize_btn_loop',10,2);
                

            	//Add query vars and rewrite rules
                $this->loader->add_filter('query_vars', $plugin_public, 'wpd_add_query_vars');
//            	$this->loader->add_filter('rewrite_rules_array', $plugin_public, 'wpd_add_rewrite_rules',99);
                $this->loader->add_filter('init', $plugin_public, 'wpd_add_rewrite_rules',99);
                
                //Products
                $wpd_product=new WPD_Product();                
                $this->loader->add_action( 'wp_ajax_get_customizer_url', $wpd_product, 'get_url_ajax' );
                $this->loader->add_action( 'wp_ajax_nopriv_get_customizer_url', $wpd_product, 'get_url_ajax' );
                $this->loader->add_action( 'woocommerce_add_to_cart', $wpd_product, 'set_custom_upl_cart_item_data',99,6);
                
                //Variable filters
                $this->loader->add_action( 'init', $plugin_public, 'set_variable_action_filters', 99);
                
                //Sessions
                $this->loader->add_action( 'init', $plugin_admin, 'init_sessions', 1);
                
                //Design hooks
                $wpd_design=new WPD_Design();
                $this->loader->add_action( 'wp_ajax_handle-custom-design-upload', $wpd_design, 'handle_custom_design_upload');
                $this->loader->add_action( 'wp_ajax_nopriv_handle-custom-design-upload', $wpd_design, 'handle_custom_design_upload');
                $this->loader->add_action( 'wp_ajax_add_custom_design_to_cart', $wpd_design, 'add_custom_design_to_cart_ajax' );
                $this->loader->add_action( 'wp_ajax_nopriv_add_custom_design_to_cart', $wpd_design, 'add_custom_design_to_cart_ajax' );
                $this->loader->add_action( 'wp_ajax_save_custom_design_for_later', $wpd_design, 'save_custom_design_for_later_ajax' );
                $this->loader->add_action( 'wp_ajax_nopriv_save_custom_design_for_later', $wpd_design, 'save_custom_design_for_later_ajax' );
                $this->loader->add_action( 'wp_ajax_save_canvas_to_session', $wpd_design, 'save_canvas_to_session_ajax' );
                $this->loader->add_action( 'wp_ajax_nopriv_save_canvas_to_session', $wpd_design, 'save_canvas_to_session_ajax' );
                $this->loader->add_action( 'wp_ajax_delete_saved_design', $wpd_design, 'delete_saved_design_ajax' );
                $this->loader->add_action( 'wp_ajax_nopriv_delete_saved_design', $wpd_design, 'delete_saved_design_ajax' );
                $this->loader->add_action( 'woocommerce_admin_order_item_values', $wpd_design, 'get_order_custom_admin_data',10,3);
                $this->loader->add_action( 'woocommerce_add_order_item_meta', $wpd_design, 'save_customized_item_meta',10,3);
                $this->loader->add_action( 'woocommerce_before_cart_item_quantity_zero', $wpd_design, 'remove_wpc_customization');
                $this->loader->add_action( 'wp_ajax_get_watermarked_preview', $wpd_design, 'get_watermarked_preview' );
                $this->loader->add_action( 'wp_ajax_nopriv_get_watermarked_preview', $wpd_design, 'get_watermarked_preview' );
                
                $this->loader->add_action( 'wp_ajax_generate_downloadable_file', $wpd_design, 'generate_downloadable_file' );
                $this->loader->add_action( 'wp_ajax_nopriv_generate_downloadable_file', $wpd_design, 'generate_downloadable_file' );
                //User my account page
                $this->loader->add_filter( 'woocommerce_order_item_quantity_html', $wpd_design, 'get_user_account_products_meta',11,2);
                
                //Allow us to hide the wpc_data_upl meta from the meta list in the order details page
                $this->loader->add_filter( 'woocommerce_hidden_order_itemmeta', $wpd_design, 'unset_wpc_data_upl_meta');
                $this->loader->add_filter( 'woocommerce_add_cart_item_data', $wpd_design,'force_individual_cart_items', 10, 2 );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    3.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     3.0
	 * @return    string    The name of the plugin.
	 */
	public function get_wpd() {
		return $this->wpd;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     3.0
	 * @return    Wpd_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     3.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
