/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'ko',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Sm_MultiCoupon/js/action/set-coupon-code',
    'Sm_MultiCoupon/js/action/cancel-coupon'
], function ($, ko, Component, quote, setCouponCodeAction, cancelCouponAction) {
    'use strict';
    var totals = quote.getTotals(),
        couponCode = ko.observable(null),
        CouponArray = ko.observable(null),
        isApplied;
    if (totals()) {
        couponCode(totals()['coupon_code']);
    }
    isApplied = ko.observable(couponCode() != null);
    return Component.extend({
        defaults: {
            template: 'Sm_MultiCoupon/discount'
        },
        couponCode: couponCode,
        /**
         * Applied flag
         */
        isApplied: isApplied,
        /**
         * Coupon code application procedure
         */
        apply: function () {
            var couponInput = document.getElementById("discount-code").value;
            if (couponCode()){
                CouponArray = couponCode().split(' ');
                if (!CouponArray.includes(couponInput)) {
                    CouponArray.push(couponInput);
                    couponCode(CouponArray.toString());
                }
            }else {
                couponCode(couponInput);
            }
            if (this.validate()) {
                setCouponCodeAction(couponCode(), isApplied);
            }
        },
        /**
         * Cancel using coupon
         */
        cancel: function (data) {
            CouponArray = couponCode().split(',');
            if (CouponArray.includes(data)){
                if (CouponArray.length > 1){
                    for (var i= 0; i < CouponArray.length; i++) {
                        if (CouponArray[i] === data) {
                            CouponArray.splice(i,1);
                            couponCode(CouponArray.toString());
                            setCouponCodeAction(couponCode(), isApplied,'cancel');
                        }
                    }
                }else {
                    couponCode('');
                    cancelCouponAction(isApplied);
                }
            }
        },
        /**
         * Coupon form validation
         *
         * @returns {Boolean}
         */
        validate: function () {
            var form = '#discount-form';
            return $(form).validation() && $(form).validation('isValid');
        }
    });
});
