define([
    'Magento_Checkout/js/view/payment/default',
    'jquery',
    'ko',
    'underscore',
    'ppplusdcc',
    'Paypal_PaypalPlusBrasil/js/action/create-payment-request',
    'Paypal_PaypalPlusBrasil/js/action/check-total',
    'Paypal_PaypalPlusBrasil/js/helper/loader',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/totals',
    'Magento_Checkout/js/action/get-totals'
], function (
    Component,
    $,
    ko,
    _,
    ppplusdcc,
    createPaymentRequest,
    checkTotal,
    iframeLoader,
    quote,
    totals,
    getTotalsAction
) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Paypal_PaypalPlusBrasil/payment/creditcard',
            ppplus: null
        },

        initialize: function () {
            this._super();
            var self = this;

            iframeLoader.showLoader(this.getImageLoaderUrl());

            this.isChecked.subscribe(function (value) {
                iframeLoader.showLoader(this.getImageLoaderUrl());
                if (value === this.getCode()) {
                    var self = this;
                    setTimeout(function () {
                        self.initializeppPlus(self.paymentRequestData())
                    }, 2000);
                }
            }.bind(this));

            totals.totals.subscribe(function(){
                if(this.isChecked()){
                    this.initializePaypalPlusIframe();
                }
            }.bind(this))

            $(window).resize(function() {
                clearTimeout(window.resizedFinished);
                window.resizedFinished = setTimeout(function () {
                    $("#paypal-plus-form > iframe").hide();
                    self.initializeppPlus(self.paymentRequestData());
                });
            });
        },

        canSubmit: function () {
            try {
                let transactions = this.paymentRequestData().transactions;
                let grandTotal = 0;

                for (let i = 0; i < transactions.length; i++) {
                    grandTotal += parseFloat(transactions[i].amount.total);
                }

                return grandTotal === checkTotal(quote.getQuoteId());
            } catch (e) {
                console.error(e);
            }
            return false;
        },

        initObservable: function () {
            this._super()
                .observe([
                    'payID',
                    'rememberedCard',
                    'payerID',
                    'installmentsValue',
                    'paymentRequestData'
                ]);

            return this;
        },

        initializePaypalPlusIframe: function () {
            if ($("input[id=paypalplus_brasil_creditcard]:checked").length > 0) {
                $.when(createPaymentRequest(quote.getQuoteId(), this.messageContainer)).then(function (response) {
                    if (response.success) {
                        var data = response.data;
                        this.payID(data.id);
                        this.paymentRequestData(data);
                        this.initializeppPlus(data);
                    }
                }.bind(this));
            }
        },

        initializeppPlus: function (data) {
            if (!data || data === 'undefined') {
                return;
            }
            var self = this;
            this.ppplus = PAYPAL.apps.PPP({
                "approvalUrl": data.approval_url,
                "placeholder": "paypal-plus-form",
                "mode": data.mode,
                "payerFirstName": data.payer_first_name,
                "payerLastName": data.payer_last_name,
                "payerEmail": data.payer_email,
                "payerPhone": data.payer_phone,
                "payerTaxId": data.payer_tax_id,
                "payerTaxIdType": data.payer_tax_id_type,
                "language": "pt_BR",
                "country": "BR",
                "enableContinue": this.getCode() + "_continueButton",
                "disableContinue": this.getCode() + "_continueButton",
                "merchantInstallmentSelectionOptional": data.allow_installments,
                "merchantInstallmentSelection": data.installments,
                "disallowRememberedCards": data.disallow_remembered_cards,
                "rememberedCards": data.remembered_cards,
                "iframeHeight": data.iframe_height,

                onLoad: function () {
                    iframeLoader.hideLoader();
                },

                onContinue: function (rememberedCardsToken, payerId, paymentId, installments) {
                    if (typeof installments !== 'undefined') {
                        self.installmentsValue(installments.term);
                    } else {
                        self.installmentsValue('1');
                    }

                    self.rememberedCard(rememberedCardsToken);
                    self.payerID(payerId);

                    $('#ppplus').hide();
                    self.placeOrder();
                },

                onError: function (err) {
                    try {
                        //TODO TRATAR O QUE FAZER QUANDO OCORRER ALGUM ERRO

                        if (typeof err !== 'undefined') { //log & attach this error into the order if possible
                            var message = err[0];
                            var ppplusError = message.replace(/[\\"]/g, '');

                            switch (ppplusError) {
                                case "INTERNAL_SERVICE_ERROR": //javascript fallthrough
                                case "SOCKET_HANG_UP": //javascript fallthrough
                                case "socket hang up": //javascript fallthrough
                                case "connect ECONNREFUSED": //javascript fallthrough
                                case "connect ETIMEDOUT": //javascript fallthrough
                                case "UNKNOWN_INTERNAL_ERROR": //javascript fallthrough
                                case "fiWalletLifecycle_unknown_error": //javascript fallthrough
                                case "Failed to decrypt term info": //javascript fallthrough
                                case "RESOURCE_NOT_FOUND": //javascript fallthrough
                                case "INTERNAL_SERVER_ERROR":
                                    alert("Ocorreu um erro inesperado, por favor tente novamente. (" + ppplusError + ")"); //pt_BR
                                    //Generic error, inform the customer to try again; generate a new approval_url and reload the iFrame.
                                    // <<Insert Code Here>>
                                    break;

                                case "RISK_N_DECLINE": //javascript fallthrough
                                case "NO_VALID_FUNDING_SOURCE_OR_RISK_REFUSED": //javascript fallthrough
                                case "TRY_ANOTHER_CARD": //javascript fallthrough
                                case "NO_VALID_FUNDING_INSTRUMENT":
                                    alert("Seu pagamento não foi aprovado. Por favor utilize outro cartão, caso o problema persista entre em contato com o PayPal (0800-047-4482). (" + ppplusError + ")"); //pt_BR
                                    //Risk denial, inform the customer to try again; generate a new approval_url and reload the iFrame.
                                    // <<Insert Code Here>>
                                    break;

                                case "CARD_ATTEMPT_INVALID":
                                    alert("Ocorreu um erro inesperado, por favor tente novamente. (" + ppplusError + ")"); //pt_BR
                                    //03 maximum payment attempts with error, inform the customer to try again; generate a new approval_url and reload the iFrame.
                                    // <<Insert Code Here>>
                                    break;

                                case "INVALID_OR_EXPIRED_TOKEN":
                                    alert("A sua sessão expirou, por favor tente novamente. (" + ppplusError + ")"); //pt_BR
                                    //User session is expired, inform the customer to try again; generate a new approval_url and reload the iFrame.
                                    // <<Insert Code Here>>
                                    break;

                                case "CHECK_ENTRY":
                                    alert("Por favor revise os dados de Cartão de Crédito inseridos. (" + ppplusError + ")"); //pt_BR
                                    //Missing or invalid credit card information, inform your customer to check the inputs.
                                    // <<Insert Code Here>>
                                    break;

                                default:  //unknown error & reload payment flow
                                    alert("Ocorreu um erro inesperado, por favor tente novamente. (" + ppplusError + ")"); //pt_BR
                                //Generic error, inform the customer to try again; generate a new approval_url and reload the iFrame.
                                // <<Insert Code Here>>

                            }

                        }

                    } catch (e) { //treat exceptions here
                        console.log(e)
                    }
                }
            });
        },

        getData: function () {
            return {
                'method': this.getCode(),
                'additional_data': {
                    'pay_id': this.payID(),
                    'payer_id': this.payerID(),
                    'remembered_cards_token': this.rememberedCard(),
                    'installments': this.installmentsValue(),
                }
            };
        },

        beforePlaceOrder: function () {
            if (this.canSubmit()) {
                this.ppplus.doContinue();
            } else if(this.isChecked()) {
                alert('O valor do carrinho mudou, preencha os dados novamente');
                // Restart frame with correct values
                getTotalsAction([]);
                this.initializePaypalPlusIframe();
            }
        },

        getImageLoaderUrl: function () {
            return window.checkoutConfig.payment[this.item.method].loader_image_url;
        }
    })
});
