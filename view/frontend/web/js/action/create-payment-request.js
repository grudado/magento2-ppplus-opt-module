define([
    'jquery',
    'mage/url',
    'mage/storage',
    'Magento_Checkout/js/model/error-processor',
    'Magento_Checkout/js/model/full-screen-loader'
], function ($, urlBuilder, storage, errorProcessor, fullScreenLoader) {
    return function (quote_id, messageContainer) {
        fullScreenLoader.startLoader();
        return storage.get(
            urlBuilder.build('paypalplusbrasil/checkout/paymentrequest/quote_id/' + quote_id)
        ).done(
            function (response) {
                if (!response.success) {
                    errorProcessor.process(response, messageContainer);
                }
            }
        ).always(
            function () {
                fullScreenLoader.stopLoader();
            }
        );
    };
});
