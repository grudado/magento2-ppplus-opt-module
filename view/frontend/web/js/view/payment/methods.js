define([
    'uiComponent',
    'Magento_Checkout/js/model/payment/renderer-list'
], function (Component, rendererList) {
    'use strict';
    rendererList.push(
        {
            type: 'paypalplus_brasil_creditcard',
            component: 'Paypal_PaypalPlusBrasil/js/view/payment/method-renderer/creditcard'
        }
    );
    return Component.extend({});
});
