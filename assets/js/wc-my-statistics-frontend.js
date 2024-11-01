jQuery(document).ready(function () {
    let wc_my_statistics_pt = jQuery('#wc-my-statistics-product-table').DataTable({
        lengthMenu: [
            [5, 10, 25, -1],
            [5, 10, 25, 'All'],
        ]
    });

    let wc_my_statistics_ot = jQuery('#wc-my-statistics-order-table').DataTable({
        searching: false, 
        paging: false,
    });
    
    jQuery('#wc-my-stats-p-load-data').click(function() {
        let start_date = jQuery('#wc-my-stats-p-start-date').val();
        let end_date = jQuery('#wc-my-stats-p-end-date').val();

        if (start_date || end_date) {
            var data = {
                action: 'wc_my_statistics_load_data',
                start_date: start_date,
                end_date: end_date,
                table: 'product',
                wc_my_statistics_nonsense: wc_my_statistics.nonsense,
            };
            // We can also pass the url value separately from ajaxurl for front end AJAX implementations
            jQuery.post(wc_my_statistics.ajaxurl, data, function(response) {
                
                if (response && response.products) {
                    wc_my_statistics_pt.clear().draw();
                    jQuery.map( response.products, function( val, i ) {

                        wc_my_statistics_pt.row.add([val.name,val.count]).draw();
                      });
                }

                if (response && response.orders) {
                    wc_my_statistics_ot.clear().draw();
                    jQuery.map( response.orders, function( val, order_status ) {
                        
                        if (order_status == 'total-spent') {
                            jQuery('#wc-my-statistics-ot-footer').html((Number(val).toFixed(2))+' '+wc_my_statistics.store_currency);
                        } else {
                            let order_type = '';
                            if (order_status == 'orders-completed') {
                                order_type = 'Completed Orders';
                            } else if (order_status == 'orders-processing') {
                                order_type = 'Processing Orders';
                            } else if (order_status == 'orders-pending') {
                                order_type = 'Pending Orders';
                            } else if (order_status == 'orders-cancelled') {
                                order_type = 'Cancelled Orders';
                            } else if (order_status == 'orders-refunded') {
                                order_type = 'Refunded Orders';
                            } else if (order_status == 'orders-failed') {
                                order_type = 'Failed Orders';
                            }
                            if (val && order_type) {
                                wc_my_statistics_ot.row.add([order_type,val]).draw();
                            }
                            
                        }
                        
                      });
                }
            });
        } else {
            jQuery('#wc-my-stats-p-start-date').focus();
        }
        
      
  });


  jQuery('#wc-my-stats-p-export-csv').click(function() {
    let start_date = jQuery('#wc-my-stats-p-start-date').val();
    let end_date = jQuery('#wc-my-stats-p-end-date').val();

        var data = {
            action: 'wc_my_statistics_export_csv',
            start_date: start_date,
            end_date: end_date,
            table: 'product',
            wc_my_statistics_exportcsv: wc_my_statistics.exportcsv,
        };
        // We can also pass the url value separately from ajaxurl for front end AJAX implementations
        jQuery.post(wc_my_statistics.ajaxurl, data, function(response) {
            
            var blob = new Blob([response], { type: 'text/csv' });
            var url = URL.createObjectURL(blob);
            var link = document.createElement('a');
            link.href = url;
            link.download = 'export.csv';
            link.click();
            URL.revokeObjectURL(url);

        });
  
});

});
