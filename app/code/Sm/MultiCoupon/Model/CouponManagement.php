<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Sm\MultiCoupon\Model;

use \Magento\Quote\Api\CouponManagementInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\SalesRule\Model\Coupon;

/**
 * Coupon management object.
 */
class CouponManagement extends \Magento\Framework\Model\AbstractExtensibleModel implements CouponManagementInterface
{
    /**
     * Quote repository.
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;
    /**
     * Constructs a coupon read service object.
     *
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository Quote repository.
     */
    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->_init(\Magento\SalesRule\Model\ResourceModel\Coupon::class);
    }
    /**
     * {@inheritdoc}
     */
    public function get($cartId)
    {
        /** @var  \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        return $quote->getCouponCode();
    }
    public function checkExistCoupon($couponCodeArray){
        foreach ($couponCodeArray as $c) {
            if($this->getResource()->exists($c)== false) {
                return false;
            }
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function set($cartId, $couponCode)
    {
        /** @var  \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        if (!$quote->getItemsCount()) {
            throw new NoSuchEntityException(__('The "%1" Cart doesn\'t contain products.', $cartId));
        }
        if (!$quote->getStoreId()) {
            throw new NoSuchEntityException(__('Cart isn\'t assigned to correct store'));
        }
        $quote->getShippingAddress()->setCollectShippingRates(true);
        $couponCodeArray = explode(',',$couponCode);
        if ($this->checkExistCoupon($couponCodeArray)){
            try {
                $quote->setCouponCode($couponCode);
                $this->quoteRepository->save($quote->collectTotals());
            } catch (\Exception $e) {
                throw new CouldNotSaveException(
                    __("The coupon code couldn't be applied. Verify the coupon code and try again.")
                );
            }
        }else{
            throw new NoSuchEntityException(__("The coupon code doesn't exist. Verify the code and try again."));
        }
        if ($quote->getCouponCode() != $couponCode) {
            throw new NoSuchEntityException(__("The coupon code isn't valid. Verify the code and try again."));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($cartId)
    {
        /** @var  \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        if (!$quote->getItemsCount()) {
            throw new NoSuchEntityException(__('The "%1" Cart doesn\'t contain products.', $cartId));
        }
        $quote->getShippingAddress()->setCollectShippingRates(true);
        try {
            $quote->setCouponCode('');
            $this->quoteRepository->save($quote->collectTotals());
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(
                __("The coupon code couldn't be deleted. Verify the coupon code and try again.")
            );
        }
        if ($quote->getCouponCode() != '') {
            throw new CouldNotDeleteException(
                __("The coupon code couldn't be deleted. Verify the coupon code and try again.")
            );
        }
        return true;
    }
}
