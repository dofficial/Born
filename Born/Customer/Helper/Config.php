<?php

namespace Born\Customer\Helper;

/**
 * Class Config
 * @package Born\Customer\Helper
 */
class Config extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_DOWNLOAD_ENABLED = 'checkout/cart/download_items_enabled';

    /**
     * @param $path
     * @return mixed
     */
    private function getConfigValue($path)
    {
        return $this->scopeConfig->getValue($path, 'store');
    }

    /**
     * @return mixed
     */
    public function isDownloadEnabled()
    {
        return $this->getConfigValue(self::XML_PATH_DOWNLOAD_ENABLED);

    }
}