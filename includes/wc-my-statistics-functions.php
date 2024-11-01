<?php

add_action('wc_my_statistics_accounts_tab', 'wc_my_statistics_orders');

if ( !function_exists( 'wc_my_statistics_orders' ) ) :
    function wc_my_statistics_orders($user_id) {
        
        
        $user_stats = wc_my_statistics_get_stats($user_id);

        $settings = get_wc_my_statistics_settings();

        if ('both' == $settings['myaccount_tables']) {

            if (isset($user_stats['products']) && !empty($user_stats['products'])) {
                wc_my_statistics_get_product_table($user_stats, $settings);
            }

            wc_my_statistics_get_order_table($user_stats, $settings);
        
        } else if ('order' == $settings['myaccount_tables']) {

            wc_my_statistics_get_order_table($user_stats, $settings);

        } else if ('product' == $settings['myaccount_tables']) {

            wc_my_statistics_get_product_table($user_stats, $settings);

        } 
        
    }
endif;


if ( !function_exists( 'wc_my_statistics_get_stats' ) ) :

    function wc_my_statistics_get_stats($user_id, $start_date = '', $end_date = '') {
        $order_stats = array (
            'orders-completed' => 0,
            'orders-processing' => 0,
            'orders-pending' => 0,
            'orders-cancelled' => 0,
            'orders-refunded' => 0,
            'orders-failed' => 0,
            'amount-completed' => 0,
            'amount-processing' => 0,
            'amount-pending' => 0,
            'amount-cancelled' => 0,
            'amount-refunded' => 0,
            'amount-failed' => 0,
            'total-spent' => 0,
            'total-shipping' => 0,
            'total-taxes' => 0,
        );

        $products = array ();

        if (!empty($user_id)) {
            $settings = get_wc_my_statistics_settings();
            $args = array(
                'customer_id' => $user_id,
                'post_type' => 'shop_order',
                'limit' => -1
            );

            if (isset($settings['start_date']) && !empty($settings['start_date']) && !empty($start_date) && $settings['start_date'] > $start_date) {
                // customer has somehow bypassed html validation and selected date before the start date. so we set start date from settings.
                $start_date = $settings['start_date'];
            } else if(isset($settings['start_date']) && !empty($settings['start_date']) && empty($start_date)) {
                // If no start date is selected then we set start date from settings.
                $start_date = $settings['start_date'];
            }

            if (!empty($start_date) && !empty($end_date)) {
                $start_date = $start_date.' 00:00:00';
                $end_date = $end_date.' 23:59:59';
                $args['date_created']= strtotime($start_date) .'...'. strtotime($end_date);
            } else if (empty($start_date) && !empty($end_date)) {
                $end_date = $end_date.' 23:59:59';
                $args['date_created'] = '<'. strtotime($end_date);
            } else if (empty($end_date) && !empty($start_date)) {
                $start_date = $start_date.' 00:00:00';
                $args['date_created']= strtotime($start_date) .'...'. time();
            }
           
            $orders = wc_get_orders( $args );

            if (!empty($orders)) {
                foreach($orders as $order) {

                    if ( 'processing' == $order->get_status() ) {
                        $order_stats['amount-processing']+= $order->get_total();
                        $order_stats['total-spent']+= $order->get_total();
                        $order_stats['total-shipping']+= $order->get_shipping_total();
                        $order_stats['total-taxes']+= $order->get_total_tax();
                        $order_stats['orders-processing']+= 1;
                        if (!empty($order->get_items())) {
                            foreach ( $order->get_items() as $item_id => $item ) {
                                $product_id = $item->get_product_id();
                                if (isset($products[$product_id])) {
                                    $products[$product_id]['count']+= $item->get_quantity();
                                } else {
                                    $products[$product_id]['count'] = $item->get_quantity();
                                    $products[$product_id]['name'] = $item->get_name();
                                }
                            }
                        }
                        
                    } else if ( 'completed' == $order->get_status() ) {
                        $order_stats['amount-completed']+= $order->get_total();
                        $order_stats['total-spent']+= $order->get_total();
                        $order_stats['total-shipping']+= $order->get_shipping_total();
                        $order_stats['total-taxes']+= $order->get_total_tax();
                        $order_stats['orders-completed']+= 1;
                        if (!empty($order->get_items())) {
                            foreach ( $order->get_items() as $item_id => $item ) {
                                $product_id = $item->get_product_id();
                                if (isset($products[$product_id])) {
                                    $products[$product_id]['count']+= $item->get_quantity();
                                } else {
                                    $products[$product_id]['count'] = $item->get_quantity();
                                    $products[$product_id]['name'] = $item->get_name();
                                }
                            }
                        }
                    } else if ( 'pending' == $order->get_status() ) {
                        $order_stats['amount-pending']+= $order->get_total();
                        $order_stats['orders-pending']+= 1;
                    } else if ( 'cancelled' == $order->get_status() ) {
                        $order_stats['amount-cancelled']+= $order->get_total();
                        $order_stats['orders-cancelled']+= 1;
                    } else if ( 'refunded' == $order->get_status() ) {
                        $order_stats['amount-refunded']+= $order->get_total();
                        $order_stats['orders-refunded']+= 1;
                    } else if ( 'failed' == $order->get_status() ) {
                        $order_stats['amount-failed']+= $order->get_total();
                        $order_stats['orders-failed']+= 1;
                    }
                }
            }
        }

        return array(
            'orders' => $order_stats ,
            'products' => $products
        );
    }
endif;



if ( !function_exists( 'get_wc_my_statistics_settings' ) ) :
    function get_wc_my_statistics_settings() {

        $settings = array (
            'display_in_account_tab' => get_option( 'wc-my-statistics_display_in_account_tab', 'yes' ),
            'account_tab_slug' => get_option( 'wc-my-statistics_account_tab_slug', 'wc-my-statistics' ),
            'account_tab_title' => get_option( 'wc-my-statistics_account_tab_title', 'My Statistics' ),
            'myaccount_tables' => get_option( 'wc-my-statistics_myaccount_tables', 'both' ),
            'no_orders_found_msg' => get_option( 'wc-my-statistics_no_orders_found_msg', 'No Orders Found' ),
            'order_status_label' => get_option( 'wc-my-statistics_order_status_label', 'ORDER STATUS' ),
            'order_count_label' => get_option( 'wc-my-statistics_order_count_label', 'COUNT' ),
            'product_table_heading' => get_option( 'wc-my-statistics_product_table_heading', 'TOTAL NUMBER OF PRODUCTS PURCHASED.' ),
            'product_name_label' => get_option( 'wc-my-statistics_product_name_label', 'PRODUCT NAME' ),
            'product_count_label' => get_option( 'wc-my-statistics_product_count_label', 'COUNT' ),
            'start_date_label' => get_option( 'wc-my-statistics_start_date_label', 'Start Date' ),
            'start_date' => get_option( 'wc-my-statistics_start_date', '' ),
            'end_date_label' => get_option( 'wc-my-statistics_end_date_label', 'End Date' ),
            'submit_btn_label' => get_option( 'wc-my-statistics_submit_btn_label', 'Load Data' ),
            'export_csv_btn' => get_option( 'wc-my-statistics_export_csv_btn', 'yes' ),
            'export_btn_label' => get_option( 'wc-my-statistics_export_btn_label', 'Export as CSV' ),
        );
        return $settings;
    }
endif;



if ( !function_exists( 'wc_my_statistics_get_order_table' ) ) :
    function wc_my_statistics_get_order_table($user_stats, $settings) {

        if (isset($user_stats['orders']) && !empty($user_stats['orders']) && !empty($user_stats['orders']['total-spent'])) {
            wp_enqueue_style('wc-my-statistics-datatables-css');
            wp_enqueue_style('wc-my-statistics-css');
            wp_enqueue_script('wc-my-statistics-js');
            
            $orders = $user_stats['orders'];
            $currency = get_woocommerce_currency_symbol();
            ?>
            <table id="wc-my-statistics-order-table" class="woocommerce-table woocommerce-table--order-details shop_table order_details">
                <thead>
                    <tr>
                        <th  class="woocommerce-orders-table__header woocommerce-orders-table__header-order-number">
                            <b><?php echo esc_html($settings['order_status_label']);  ?> </b>
                        </th>
                        <th  class="woocommerce-orders-table__header woocommerce-orders-table__header-order-number">
                            <b><?php echo esc_html($settings['order_count_label']); ?> </b>
                        </th>
                    </tr>
                </thead>
                <tbody>
                <?php
                foreach ($orders as $key => $value) {
                    if ( 'orders-completed' == $key && !empty($value)) {
                        ?>
                        <tr>
                            <th><?php esc_html_e('Completed Orders', 'wc-my-statistics'); ?></th>
                            <td><?php echo esc_html($value); ?></td>
                        </tr>
                        <?php
                    } else if ( 'orders-processing' == $key && !empty($value)) {
                        ?>
                        <tr>
                            <th><?php esc_html_e('Processing Orders', 'wc-my-statistics'); ?></th>
                            <td><?php echo esc_html($value); ?></td>
                        </tr>
                        <?php
                    } else if ( 'orders-pending' == $key && !empty($value)) {
                        ?>
                        <tr>
                            <th><?php esc_html_e('Pending Orders', 'wc-my-statistics'); ?></th>
                            <td><?php echo esc_html($value); ?></td>
                        </tr>
                        <?php
                    } else if ( 'orders-cancelled' == $key && !empty($value)) {
                        ?>
                        <tr>
                            <th><?php esc_html_e('Cancelled Orders', 'wc-my-statistics'); ?></th>
                            <td><?php echo esc_html($value); ?></td>
                        </tr>
                        <?php
                    } else if ( 'orders-refunded' == $key && !empty($value)) {
                        ?>
                        <tr>
                            <th><?php esc_html_e('Refunded Orders', 'wc-my-statistics'); ?></th>
                            <td><?php echo esc_html($value); ?></td>
                        </tr>
                        <?php
                    } else if ( 'orders-failed' == $key && !empty($value)) {
                        ?>
                        <tr>
                            <th><?php esc_html_e('Failed Orders', 'wc-my-statistics'); ?></th>
                            <td><?php echo esc_html($value); ?></td>
                        </tr>
                        <?php
                    } else if ( 'total-spent' == $key && !empty($value)) {
                        ?>
                        <tfoot>
                            <tr>
                                <th><?php esc_html_e('Total Amount Spent', 'wc-my-statistics'); ?></th>
                                <td><div id="wc-my-statistics-ot-footer"><?php echo esc_html($currency); ?><?php echo esc_html(number_format($value, 2)); ?></div></td>
                            </tr>
                        </tfoot>
                        <?php
                    }
                }
                ?>
                </tbody>
            </table>
                <?php

        } else {
            ?>
            <p> <?php echo esc_html($settings['no_orders_found_msg']); ?></p>
            <?php
        }
    }
endif;

if ( !function_exists( 'wc_my_statistics_get_product_table' ) ) :
    function wc_my_statistics_get_product_table($user_stats, $settings) {

        if (isset($user_stats['products']) && !empty($user_stats['products'])) {
            wp_enqueue_style('wc-my-statistics-datatables-css');
            wp_enqueue_style('wc-my-statistics-css');
            wp_enqueue_script('wc-my-statistics-js');
            
            $products = $user_stats['products'];

            $start_date = '';
            if (!empty($settings['start_date'])) {
                $start_date = $settings['start_date'];
            }
            ?>
            <div id="wc-my-statistics-dt-fields">
                <span id="wc-my-stats-p-start-date-label"><?php echo esc_html($settings['start_date_label']); ?></span>
                <input type="date" id="wc-my-stats-p-start-date" value="<?php echo esc_html($start_date); ?>" min="<?php echo esc_html($start_date); ?>">
                <span id="wc-my-stats-p-end-date-label"><?php echo esc_html($settings['end_date_label']); ?></span>
                <input type="date" id="wc-my-stats-p-end-date" min="<?php echo esc_html($start_date); ?>">
                <input type="button" id="wc-my-stats-p-load-data" value="<?php echo esc_html($settings['submit_btn_label']); ?>">
                <?php
                if ( 'yes' == $settings['export_csv_btn']) {
                    ?>
                <input type="button" id="wc-my-stats-p-export-csv" value="<?php echo esc_html($settings['export_btn_label']); ?>">
                    <?php
                }
                ?>
            </div>
            <table id="wc-my-statistics-product-table" class="woocommerce-table woocommerce-table--order-details shop_table order_details" style="margin-top: 30px;">
                <thead>
                    <tr>
                        <th colspan = "2"  class="woocommerce-orders-table__header woocommerce-orders-table__header-order-number">
                            <b><?php echo esc_html($settings['product_table_heading']); ?></b>
                        </th>
                    </tr>
                    <tr>
                        <th  class="woocommerce-orders-table__header woocommerce-orders-table__header-order-number">
                            <b><?php echo esc_html($settings['product_name_label']); ?> </b>
                        </th>
                        <th  class="woocommerce-orders-table__header woocommerce-orders-table__header-order-number">
                            <b><?php echo esc_html($settings['product_count_label']); ?> </b>
                        </th>
                    </tr>
                </thead>
                <tbody>
                <?php
                foreach ($products as $product_id => $product) {
                    if ( isset($product['count']) && isset($product['name'])) {
                        ?>
                        <tr>
                            <th><?php echo esc_html($product['name']); ?></th>
                            <td><?php echo esc_html($product['count']); ?></td>
                        </tr>
                        <?php
                    } 
                }
                ?>
                </tbody>
            </table>
                <?php
        } else {
            ?>
            <p> <?php echo esc_html($settings['no_orders_found_msg']); ?></p>
            <?php
        }
    }
endif;



function wc_my_statistics_load_data_callback() {

    $response = array(
        'orders' => array(),
        'products' => array()
    );
    if ( isset( $_POST['wc_my_statistics_nonsense'] ) && wp_verify_nonce( $_POST['wc_my_statistics_nonsense'], 'ajax-nonsense' ) ) {
        $response = wc_my_statistics_get_stats(get_current_user_id(), sanitize_text_field($_POST['start_date']), sanitize_text_field($_POST['end_date']));
    }
	wp_send_json( $response );

}
add_action( 'wp_ajax_wc_my_statistics_load_data', 'wc_my_statistics_load_data_callback' );


function wc_my_statistics_export_csv_callback() {

    if ( isset( $_POST['wc_my_statistics_exportcsv'] ) && wp_verify_nonce( $_POST['wc_my_statistics_exportcsv'], 'ajax-exportcsv' ) ) {

        $data = wc_my_statistics_get_stats(get_current_user_id(), sanitize_text_field($_POST['start_date']), sanitize_text_field($_POST['end_date']));

        $products = array();
        if (isset($data['products']) && !empty($data['products'])) {
            $products = $data['products'];
        }
        mb_internal_encoding('UTF-8');
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="export.csv"');
    
        echo chr(0xEF) . chr(0xBB) . chr(0xBF);
        $output = fopen('php://output', 'w');
        fputs($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($output, array('Product Name', 'Purchases'));

        if (!empty($products)) {
            foreach ($products as $product_id => $product_details) {
                $row = array (
                    $product_details['name'],
                    $product_details['count'],
                );
                fputcsv($output, $row);
              }
        }
        fclose($output);
    }
	wp_die();

}
add_action( 'wp_ajax_wc_my_statistics_export_csv', 'wc_my_statistics_export_csv_callback' );
