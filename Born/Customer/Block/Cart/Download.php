<?php

namespace Born\Customer\Block\Cart;

use Magento\Framework\View\Element\Template;

/**
 * Class Download
 * @package Born\Customer\Block\Cart
 */
class Download extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Born\Customer\Helper\Config
     */
    protected $configHelper;
    const FORM_POST_ACTION = '/cart_download/Cart/Download';

    /**
     * Download constructor.
     * @param \Born\Customer\Helper\Config $configHelper
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(\Born\Customer\Helper\Config $configHelper, Template\Context $context, array $data = [])
    {
        parent::__construct($context, $data);
        $this->configHelper = $configHelper;
    }

    /**
     * @return string
     */
    public function getPostAction()
    {
        return self::FORM_POST_ACTION;
    }

    /**
     * @return mixed
     */
    public function isEnabled()
    {
        return $this->configHelper->isDownloadEnabled();
    }
}