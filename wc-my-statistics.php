<?php
/**
 * Plugin Name: My Statistics for WooCommerce
 * Description: My Statistics for WooCommerce enables the customers to see their Woocommerce order Statistics on my account tab.
 * Version: 1.0.4
 * Tags: customer-end-statistics, products-purchased, order-statistics, woocommerce-order-statistics, frontend-statistics
 * Text Domain: wc-my-statistics
 * Author: gamangrio
 * Author URI: https://www.linkedin.com/in/ghulam-ali-mangrio-9b668b25
 * Domain Path: /languages
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

if ( ! class_exists( 'wc_my_statistics' ) ) :
	final class wc_my_statistics {

		// Plugin Version
		public $version             = '1.0.4';

		// Instnace
		protected static $_instance = NULL;

		/**
		 * Setup Instance
		 * @since 1.0
		 * @version 1.0
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		/**
		 * Not allowed
		 * @since 1.0
		 * @version 1.0
		 */
		public function __clone() { _doing_it_wrong( __FUNCTION__, 'Cheatin&#8217; huh?', $this->version ); }

		/**
		 * Not allowed
		 * @since 1.0
		 * @version 1.0
		 */
		public function __wakeup() { _doing_it_wrong( __FUNCTION__, 'Cheatin&#8217; huh?', $this->version ); }

		/**
		 * Define
		 * @since 1.0
		 * @version 1.0
		 */
		private function define( $name, $value, $definable = true ) {
			if ( ! defined( $name ) )
				define( $name, $value );
			elseif ( ! $definable && defined( $name ) )
				_doing_it_wrong( 'wc_my_statistics->define()', 'Could not define: ' . $name . ' as it is already defined somewhere else!', $this->version );
		}

		/**
		 * Require File
		 * @since 1.0
		 * @version 1.0
		 */
		public function file( $required_file ) {
			$required_file = sanitize_text_field($required_file);
			$file_to_include = WC_MY_STATISTICS_INCLUDE . $required_file;
			$file_to_include = realpath($file_to_include);

			if ( file_exists( $file_to_include ) )
				require_once $file_to_include;
			else
				_doing_it_wrong( 'wc_my_statistics->file()', 'Requested file ' . $file_to_include . ' not found.', $this->version );
		}

		/**
		 * Construct
		 * @since 1.0
		 * @version 1.0
		 */
		public function __construct() {
			$this->define_constants();

			if (class_exists( 'woocommerce' )) {
				$this->load_modules();
				add_action( 'init', array( $this, 'add_translation' ) );
				add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ) ); 
				add_filter('plugin_action_links_'.plugin_basename(__FILE__), array( $this, 'plugin_page_settings_link') );
			} else {
				add_action( 'admin_notices', array( $this,'admin_notice' ) );
			}

			

		}

		/**
		 * Plugins page settings link
		 * @since 1.0.1
		 * @version 1.0.1
		**/
		public function plugin_page_settings_link( $links ) {
			$links[] = '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=wc-my-statistics' ) . '">' . __('Settings', 'wc-my-statistics') . '</a>';
	
			return $links;

		}

		/**
		 * Show notice if woocommerce not active
		 * @since 1.0.3
		 * @version 1.0.3
		**/
		public function admin_notice( ) {
			$message = 'The plugin <b>My Statistics for WooCommerce</b> requires WooCommerce to be installed and activated';
    		echo '<div class="notice notice-success is-dismissible"> <p>' . $message . '</p></div>';

		}

		/**
		 * Enqueue scripts
		 * @since 1.0.1
		 * @version 1.0.1
		**/
		public function scripts() {
			wp_register_style( 'wc-my-statistics-datatables-css', plugins_url('assets/css/datatables.css',__FILE__ ), array(), $this->version );
			wp_register_style( 'wc-my-statistics-css', plugins_url('assets/css/wc-my-statistics.css',__FILE__ ), array(), $this->version );
			wp_register_script( 'wc-my-statistics-datatables-js', plugins_url('assets/js/datatables.js',__FILE__ ), array('jquery'), $this->version, true ); 
			wp_register_script( 'wc-my-statistics-js', plugins_url('assets/js/wc-my-statistics-frontend.js',__FILE__ ), array('wc-my-statistics-datatables-js'), $this->version, true ); 

			wp_localize_script('wc-my-statistics-js', 'wc_my_statistics', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
				'nonsense' => wp_create_nonce('ajax-nonsense'),
				'exportcsv' => wp_create_nonce('ajax-exportcsv'),
				'store_currency' => get_woocommerce_currency_symbol()
            ));
		}
		
		/**
		 * Add translation support
		 * @since 1.0
		 * @version 1.0
		**/
		public function add_translation() {
			load_plugin_textdomain('wc-my-statistics', FALSE,  basename( dirname( __FILE__ ) ) . '/languages/');
		}
		

		/**
		 * Define Constants
		 * @since 1.0
		 * @version 1.0
		 */
		private function define_constants() {

			$this->define( 'WC_MY_STATISTICS_VERSION',        $this->version );
			$this->define( 'WC_MY_STATISTICS_SLUG',          'wc-my-statistics' );

			$this->define( 'WC_MY_STATISTICS',               __FILE__ );
			$this->define( 'WC_MY_STATISTICS_ROOT',          plugin_dir_path( WC_MY_STATISTICS  ) );
			$this->define( 'WC_MY_STATISTICS_INCLUDE', plugin_dir_path( WC_MY_STATISTICS ) . 'includes/' );
			$this->define( 'WC_MY_STATISTICS_ASSETS', plugin_dir_path( WC_MY_STATISTICS)  . 'assets/' );
						
		}
		
		/**
		 * Load Module
		 * @since 1.0
		 * @version 1.0
		 */
		public function load_modules() {
			$this->file( 'wc-my-statistics-functions.php' );
			$this->file( 'wc-my-statistics-myaccount-tab.php' );
			$this->file( 'wc-my-statistics-settings.php' );
		}

	}
	
endif;

if ( !function_exists( 'wc_my_statistics_plugin' ) ) :
	function wc_my_statistics_plugin() {
		wc_my_statistics::instance();
	}
endif;
add_action( 'plugins_loaded', 'wc_my_statistics_plugin' );	
