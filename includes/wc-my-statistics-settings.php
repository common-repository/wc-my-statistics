<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'wc_my_statistics_settings' ) && ! function_exists( 'wc_my_statistics_add_settings' ) ) :

	function wc_my_statistics_add_settings() {

		/**
		 * Settings class
		 *
		 * @since 1.0.0
		 */
		class wc_my_statistics_settings extends WC_Settings_Page {

			/**
			 * Setup settings class
			 *
			 * @since  1.0
			 */
			public function __construct() {

				$this->id    = 'wc-my-statistics';
				$this->label = __( 'WC My Statistics', 'wc-my-statistics' );

				add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
				add_action( 'woocommerce_settings_' . $this->id, array( $this, 'output' ) );
				add_action( 'woocommerce_settings_save_' . $this->id, array( $this, 'save' ) );
				add_action( 'woocommerce_sections_' . $this->id, array( $this, 'output_sections' ) );

			}

			/**
			 * Get sections
			 *
			 * @return array
			 */

			public function get_sections() {

				$sections = array(
					'general_settings'          => __( 'General Settings', 'wc-my-statistics' )
				);

				return apply_filters( 'woocomrce_get_sections_' . $this->id, $sections );
			}

			/**
			 * Get settings array
			 *
			 * @since 1.0.0
			 * @param string $current_section Optional. Defaults to empty string.
			 * @return array Array of settings
			 */
			public function get_settings( $current_section = '' ) {

					$table_options = array(
						'both' => __( 'Both Tables', 'wc-my-statistics' ),
						'order' => __( 'Order Table', 'wc-my-statistics' ),
						'product' => __( 'Product Table', 'wc-my-statistics' ),
					);

					$settings = apply_filters(
						$this->id . '_general_settings',
						array(

							array(
								'name' => __( 'General Settings', 'wc-my-statistics' ),
								'type' => 'title',
								'desc' => '',
								'id'   => $this->id . '_general_settings',
							),

							array(
								'name'    => __( 'No Orders Found message', 'wc-my-statistics' ),
								'id'      => $this->id . '_no_orders_found_msg',
								'type'    => 'text',
								'default' => 'No Orders Found',
								'desc'    => __( "Text to display when user has no purchase history", 'wc-my-statistics' ),
							),

							array(
								'name'    => __( 'Order Table STATUS label', 'wc-my-statistics' ),
								'id'      => $this->id . '_order_status_label',
								'type'    => 'text',
								'default' => 'ORDER STATUS',
								'desc'    => __( "Text to display on STATUS column of order table", 'wc-my-statistics' ),
							),

							array(
								'name'    => __( 'Order Table COUNT label', 'wc-my-statistics' ),
								'id'      => $this->id . '_order_count_label',
								'type'    => 'text',
								'default' => 'COUNT',
								'desc'    => __( "Text to display on COUNT column of order table", 'wc-my-statistics' ),
							),

							array(
								'name'    => __( 'Product Table Heading', 'wc-my-statistics' ),
								'id'      => $this->id . '_product_table_heading',
								'type'    => 'text',
								'default' => 'TOTAL NUMBER OF PRODUCTS PURCHASED.',
								'desc'    => __( "Heading to display before product table.", 'wc-my-statistics' ),
							),

							array(
								'name'    => __( 'Product Table NAME label', 'wc-my-statistics' ),
								'id'      => $this->id . '_product_name_label',
								'type'    => 'text',
								'default' => 'PRODUCT NAME',
								'desc'    => __( "Text to display on NAME column of product table", 'wc-my-statistics' ),
							),

							array(
								'name'    => __( 'Product Table COUNT label', 'wc-my-statistics' ),
								'id'      => $this->id . '_product_count_label',
								'type'    => 'text',
								'default' => 'COUNT',
								'desc'    => __( "Text to display on COUNT column of product table", 'wc-my-statistics' ),
							),

							array(
								'name'    => __( 'Start Date', 'wc-my-statistics' ),
								'id'      => $this->id . '_start_date',
								'type'    => 'date',
								'default' => '',
								'desc'    => __( "The minimum start date, order details before this date will not be visible in tables.", 'wc-my-statistics' ),
							),

							array(
								'name'    => __( 'Start Date label', 'wc-my-statistics' ),
								'id'      => $this->id . '_start_date_label',
								'type'    => 'text',
								'default' => 'Start Date',
							),

							array(
								'name'    => __( 'End Date label', 'wc-my-statistics' ),
								'id'      => $this->id . '_end_date_label',
								'type'    => 'text',
								'default' => 'End Date',
							),

							array(
								'name'    => __( 'Submit Button label', 'wc-my-statistics' ),
								'id'      => $this->id . '_submit_btn_label',
								'type'    => 'text',
								'default' => 'Load Data',
							),

							array(
								'name'    => __( 'Export Button label', 'wc-my-statistics' ),
								'id'      => $this->id . '_export_btn_label',
								'type'    => 'text',
								'default' => 'Export as CSV',
							),

							array(
								'type'    => 'checkbox',
								'id'      => $this->id . '_export_csv_btn',
								'name'    => __( "Display Export CSV button", 'wc-my-statistics' ),
								'default' => 'yes',
								'desc'    => __( "Display Export as CSV button below the Products table.", 'wc-my-statistics' ),
							),

							array(
								'type'    => 'checkbox',
								'id'      => $this->id . '_display_in_account_tab',
								'name'    => __( "Display plugin's tab", 'wc-my-statistics' ),
								'default' => 'yes',
								'desc'    => __( "Display plugin's tab in WooCommerce My Account page", 'wc-my-statistics' ),
							),

							array(
								'name'    => __( "Plugin's tab slug", 'wc-my-statistics' ),
								'id'      => $this->id . '_account_tab_slug',
								'type'    => 'text',
								'default' => 'wc-my-statistics',
								'desc'    => __( "Slug of plugin's tab in WooCommerce My Account page", 'wc-my-statistics' ),
							),

							array(
								'name'    => __( "Plugin's tab title", 'wc-my-statistics' ),
								'id'      => $this->id . '_account_tab_title',
								'type'    => 'text',
								'default' => 'My Statistics',
								'desc'    => __( "Title of plugin's tab in WooCommerce My Account page", 'wc-my-statistics' ),
							),

							array(
								'type'    => 'select',
								'id'      => $this->id . '_myaccount_tables',
								'name'    => __( "Display Tables Plugin's tab", 'wc-my-statistics' ),
								'options' => $table_options,
								'class'   => 'wc-enhanced-select',
								'desc'    => __( 'Select which tables you want to show in myaccounts tab', 'wc-my-statistics' ),
							),

							array(
								'type' => 'sectionend',
								'id'   => $this->id . '_product_settings',
							),

						)
					);

				return apply_filters( 'woocommerce_get_settings_' . $this->id, $settings, $current_section );

			}

			public function output() {

				global $current_section;

				$settings = $this->get_settings( $current_section );
				WC_Admin_Settings::output_fields( $settings );
			}

			/**
			 * Save settings
			 *
			 * @since 1.0
			 */
			public function save() {

				global $current_section;

				$settings = $this->get_settings( $current_section );
				WC_Admin_Settings::save_fields( $settings );
			}

		}

		return new wc_my_statistics_settings();

	}
	add_filter( 'woocommerce_get_settings_pages', 'wc_my_statistics_add_settings', 15 );

endif;
