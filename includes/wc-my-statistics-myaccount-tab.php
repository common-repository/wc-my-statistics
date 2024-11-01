<?php

if ( ! class_exists( 'wc_my_statistics_accounts_tab' ) ) :
    class wc_my_statistics_accounts_tab {

        public $tab_slug             = 'wc-my-statistics';
        public $tab_title             = 'My Statistics';

        /**
		 * Construct
		 * @since 1.0
		 * @version 1.0
		 */
        public function __construct() {

            $settings = get_wc_my_statistics_settings();

            $this->tab_slug = $settings['account_tab_slug'];
            $this->tab_title = $settings['account_tab_title'];

            if ( 'yes' == $settings['display_in_account_tab'] ) {
                add_action( 'init', array( $this, 'register_wc_my_statistics_endpoint'));
                add_filter( 'query_vars', array( $this, 'wc_my_statistics_query_vars'));
                add_filter( 'woocommerce_account_menu_items', array( $this, 'add_wc_my_statistics_tab'));
                add_filter( 'woocommerce_account_' . $this->tab_slug . '_endpoint', array( $this, 'add_wc_my_statistics_content'));
                add_filter( 'woocommerce_account_menu_items', array( $this, 'wc_my_statistics_reorder_account_menu'));
            }
            
        }

        /**
         * Register New Endpoint.
         *
         * @return void.
         */
        public function register_wc_my_statistics_endpoint() {
            add_rewrite_endpoint( $this->tab_slug, EP_ROOT | EP_PAGES );
        }

        /**
         * Add new query var.
         *
         * @param array $vars vars.
         *
         * @return array An array of items.
         */
        public function wc_my_statistics_query_vars( $vars ) {
            $vars[] = $this->tab_slug;
            return $vars;
        }

        /**
         * Add New tab in my account page.
         *
         * @param array $items myaccount Items.
         *
         * @return array Items including New tab.
         */
        public function add_wc_my_statistics_tab( $items ) {
            $items[$this->tab_slug] = $this->tab_title;
            return $items;
        }


        /**
         * Add content to the new tab.
         *
         * @return  string.
         */
        public function add_wc_my_statistics_content() {
            ?>
            <div class="wc-my-statistics-accounts-tab">
            <?php
            do_action( 'wc_my_statistics_accounts_tab', get_current_user_id() );
            ?>
            </div>
            <?php
        }

        /**
         * Reorder account items.
         * 
         * @return array Ordered Items.
         */
        public function wc_my_statistics_reorder_account_menu( $items ) {
            $rearranged_items = array();

            if (!empty($items)) {
                foreach ($items as $key => $value) {

                    if ( 'customer-logout' == $key ) {
                        $rearranged_items[$this->tab_slug] = $this->tab_title;
                        $rearranged_items[$key] = $value;
                    } else if( $this->tab_slug != $key) {
                        $rearranged_items[$key] = $value;
                    }
                    
                }
                
                return $rearranged_items;
            }
            return $items;
        }
      
    }

    new wc_my_statistics_accounts_tab();
endif;
