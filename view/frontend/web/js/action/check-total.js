define([
    'jquery',
    'mage/url',
    'Magento_Checkout/js/model/full-screen-loader'
], function ($, urlBuilder, fullScreenLoader) {
    return function (quote_id) {
        fullScreenLoader.startLoader();
        let total = 0;
        jQuery.ajax({
            url: urlBuilder.build('paypalplusbrasil/checkout/checktotal/quote_id/' + quote_id),
            type: 'GET',
            async: false,
            contentType: 'application/json',
            success: function (response) {
                if (response.success) {
                    total = response.total;
                }
            }
        });

        fullScreenLoader.stopLoader();
        return parseFloat(total);
    };
});
