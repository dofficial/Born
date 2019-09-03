define([
    'Magento_Checkout/js/view/minicart',
    'jquery',
    'ko',
    'mage/cookies'
], function (Component, $, cookies) {
    'use strict';

    return Component.extend({
        getFormKey: function(){
            return $.mage.cookies.get('form_key')
        }
    });
});
