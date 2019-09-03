<?php

namespace Born\Customer\Test\Unit\Block\Cart;

/**
 * Class DownloadTest
 * @package Born\Customer\Test\Unit\Block\Cart
 */
class DownloadTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Born\Customer\Block\Cart\Download $blockClass
     */
    protected $blockClass;

    protected $configHelper;

    public function setUp()
    {
        $this->configHelper =
            $this->getMockBuilder(\Born\Customer\Helper\Config::class)
                ->disableOriginalConstructor()->getMock();
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->blockClass = $objectManager->getObject('Born\Customer\Block\Cart\Download', ['configHelper' => $this->configHelper]);
    }

    /**
     * Test return of post action data
     */
    public function testGetPostAction()
    {
        $returnVal = $this->blockClass->getPostAction();
        $this->assertEquals($returnVal, $this->blockClass::FORM_POST_ACTION);

    }

    /**
     * Test enabled state
     */
    public function testEnabled()
    {
        $this->configHelper->expects($this->once())->method('isDownloadEnabled')->willReturn(1);

        $this->assertEquals(1, $this->blockClass->isEnabled());

    }

    /**
     * Test disabled state
     */

    public function testDisabled()
    {
        $this->configHelper->expects($this->once())->method('isDownloadEnabled')->willReturn(0);

        $this->assertEquals(0, $this->blockClass->isEnabled());


    }
}