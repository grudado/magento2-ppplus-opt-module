define([
    'jquery',
    'loader'
], function ($) {
    'use strict';

    var iframeDivSelector = "#paypal-plus-form"

    return {
        showLoader: function (loaderUrl) {

                let iframeDiv = $(iframeDivSelector);

                try {
                    iframeDiv.loader({
                        icon: loaderUrl
                    });
                    iframeDiv.loader('show');
                } catch (error) {
                    console.log(error);
                }
        },

        hideLoader: function () {
            let iframeDiv = $(iframeDivSelector);

            try {
                iframeDiv.loader('hide');
            } catch (error) {
                console.log(error);
            }
        }
    }
});
