<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Sm\MultiCoupon\Block\Adminhtml\Order\Create\Coupons;

class Form extends \Magento\Sales\Block\Adminhtml\Order\Create\Coupons\Form
{
    /**
    * Get coupon code
    *
    * @return string
    */
    public function getCouponCode()
    {
        return $this->getParentBlock()->getQuote()->getCouponCode();
//        return array_filter(explode(",", $this->getParentBlock()->getQuote()->getCouponCode()));
    }
}
