define([
    "jquery",
    "Magento_Ui/js/modal/alert",
    "mage/translate",
    "jquery/ui"
], function ($, alert, $t) {
    "use strict";

    $.widget('paypalplusbrasil.send_webhook_event', {
        options: {
            ajaxUrl: '',
            sendWebhookButton: '#send_webhook_event',
            webhook_event_types: '#payment_us_paypalplus_brasil_integration_webhook_event_types'
        },
        _create: function () {
            var self = this;

            $(this.options.sendWebhookButton).click(function (e) {
                e.preventDefault();
                self._ajaxSubmit();
            });
        },

        _ajaxSubmit: function () {
            $.ajax({
                url: this.options.ajaxUrl,
                data: {
                    webhook_event_types: $(this.options.webhook_event_types).val(),
                },
                dataType: 'json',
                showLoader: true,
                success: function (result) {
                    alert({
                        title: result.success ? $t('Success') : $t('Error'),
                        content: result.message
                    });
                }
            });
        }
    });

    return $.paypalplusbrasil.send_webhook_event;
});
