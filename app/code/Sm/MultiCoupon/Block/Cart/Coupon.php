<?php

namespace Sm\MultiCoupon\Block\Cart;

class Coupon extends \Magento\Checkout\Block\Cart\AbstractCart
{
    /**
     * @return array
     */
    public function getCouponCodes()
    {
        return array_filter(explode(",", $this->getQuote()->getCouponCode()));
    }
}
