<?php

namespace Born\Customer\Test\Unit\Plugin\CustomerData;

/**
 * Class CartTest
 * @package Born\Customer\Test\Unit\Plugin\CustomerData
 */
class CartTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Born\Customer\Block\Cart\Download $blockClass
     */
    protected $plugin;

    protected $mockSubject;

    public function setUp()
    {
        $this->mockSubject =
            $this->getMockBuilder(\Magento\Checkout\CustomerData\Cart::class)
                ->disableOriginalConstructor()->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->plugin = $objectManager->getObject('Born\Customer\Plugin\Magento\Checkout\CustomerData\Cart');
    }

    /**
     * Test plugin to ensure data has been set
     */
    public function testAfterGetSectionData()
    {
        $afterData = $this->plugin->afterGetSectionData($this->mockSubject, []);
        $this->assertArrayHasKey('download_cart_enabled', $afterData);
        $this->assertArrayHasKey('download_cart_action', $afterData);

    }
}