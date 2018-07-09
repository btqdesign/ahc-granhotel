jQuery(document).ready(function ($) {
   
         jQuery('.paypal_plus_order_cancellations').change(function () {
                    var email_notify_order_cancellations = jQuery('.paypal_plus_email_notify_order_cancellations').closest('tr');
                    if (jQuery(this).val() !== 'disabled') {
                        email_notify_order_cancellations.show();
                    } else {
                        email_notify_order_cancellations.hide();
                    }
                }).change();
   
});
