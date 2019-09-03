<?php

namespace Born\Customer\Plugin\Magento\Checkout\CustomerData;

/**
 * Adds download cart data to customer data js object
 * Class Cart
 * @package Born\Customer\Plugin\Magento\Checkout\CustomerData
 */
class Cart
{
    protected $configHelper;

    /**
     * Cart constructor.
     * @param \Born\Customer\Helper\Config $configHelper
     */
    public function __construct(\Born\Customer\Helper\Config $configHelper)
    {
        $this->configHelper = $configHelper;
    }

    /**
     * @param \Magento\Checkout\CustomerData\Cart $subject
     * @param $sectionData
     * @return mixed
     */
    public function afterGetSectionData(\Magento\Checkout\CustomerData\Cart $subject, $sectionData)
    {
        $sectionData['download_cart_enabled'] = (bool)$this->configHelper->isDownloadEnabled();
        $sectionData['download_cart_action'] = \Born\Customer\Block\Cart\Download::FORM_POST_ACTION;

        return $sectionData;
    }
}