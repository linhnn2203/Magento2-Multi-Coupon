<?php

namespace Sm\MultiCoupon\Controller\Cart;

class CouponPost extends \Magento\Checkout\Controller\Cart\CouponPost
{
    /**
     * Initialize coupon
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $couponCode = $this->getRequest()->getParam('remove') == 1
            ? ''
            : trim($this->getRequest()->getParam('coupon_code'));
        $cartQuote = $this->cart->getQuote();
        $oldCouponCode = $cartQuote->getCouponCode();
        $codeLength = strlen($couponCode);
        if (!$codeLength && !strlen($oldCouponCode)) {
            return $this->_goBack();
        }

        try {
            $removeCouponValue = $this->getRequest()->getParam('remove_value');
            $isCodeLengthValid = $codeLength && $codeLength <= \Magento\Checkout\Helper\Cart::COUPON_CODE_MAX_LENGTH;
            $itemsCount = $cartQuote->getItemsCount();
            $couponUpdate = $oldCouponCode;
            if ($itemsCount) {
                $cartQuote->getShippingAddress()->setCollectShippingRates(true);
                if ($oldCouponCode) {
                    $oldCouponArray = explode(',', $oldCouponCode);
                    if (trim($removeCouponValue) != '') {
                        $oldCouponArray = implode(',', array_diff($oldCouponArray, [$removeCouponValue]));
                        $cartQuote->setCouponCode($oldCouponArray)->collectTotals()->save();
                    } else {
                        $coupon = $this->couponFactory->create()->load($couponCode, 'code');
                        if ($coupon->getCode()) {
                            if (!in_array($couponCode, $oldCouponArray)) {
                                $couponUpdate = $oldCouponCode . ',' . $couponCode;
                            }
                        }
                        $cartQuote->setCouponCode($isCodeLengthValid ? $couponUpdate : '')->collectTotals();
                        $cartQuote->setCouponCode($couponUpdate)->save();
                    }
                } else {
                    $cartQuote->setCouponCode($isCodeLengthValid ? $couponCode : '')->collectTotals();
                }
                $this->quoteRepository->save($cartQuote);
            }

            if ($codeLength) {
                $escaper = $this->_objectManager->get(\Magento\Framework\Escaper::class);
                $coupon = $this->couponFactory->create()->load($couponCode, 'code');
                if (!$itemsCount) {
                    if ($isCodeLengthValid && $coupon->getId()) {
                        $this->_checkoutSession->getQuote()->setCouponCode($couponCode)->save();
                        $this->messageManager->addSuccessMessage(
                            __(
                                'You used coupon code "%1".',
                                $escaper->escapeHtml($couponCode)
                            )
                        );
                    } else {
                        $this->messageManager->addErrorMessage(
                            __(
                                'The coupon code "%1" is not valid .',
                                $escaper->escapeHtml($couponCode)
                            )
                        );
                    }
                } else {
                    $couponData = explode(",", $cartQuote->getCouponCode());
                    if ($isCodeLengthValid && $coupon->getId()&& in_array($couponCode, $couponData)) {
                        $this->messageManager->addSuccessMessage(
                            __(
                                'You used coupon code "%1".',
                                $escaper->escapeHtml($couponCode)
                            )
                        );
                    } else {
                        $this->messageManager->addErrorMessage(
                            __(
                                'The coupon code "%1" is not valid .',
                                $escaper->escapeHtml($couponCode)
                            )
                        );
                    }
                }
            } else {
                $this->messageManager->addSuccessMessage(__('You canceled the coupon code.'));
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('We cannot apply the coupon code.'));
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
        }

        return $this->_goBack();
    }
}
