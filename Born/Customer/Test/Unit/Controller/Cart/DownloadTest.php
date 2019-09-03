<?php

namespace Born\Customer\Test\Unit;

/**
 * Class DownloadTest
 * @package Born\Customer\Test\Unit
 */
class DownloadTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var \Born\Customer\Controller\Cart\Download $cartDownload
     */
    protected $cartDownload;
    protected $controllerClass;
    protected $objectManager;
    protected $formKeyValidator;
    protected $resultRedirectFactory;
    protected $fileFactory;
    protected $fileSystem;
    protected $checkoutSession;
    protected $currency;
    protected $loggerInterface;
    protected $messageManager;
    protected $request;
    protected $driverPool;
    protected $fileWriter;
    protected $quote;


    public function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->formKeyValidator = $this->getMockBuilder(\Magento\Framework\Data\Form\FormKey\Validator::class)
            ->disableOriginalConstructor()->getMock();

        $this->resultRedirectFactory =
            $this->getMockBuilder(\Magento\Framework\Controller\Result\RedirectFactory::class)
                ->disableOriginalConstructor()->getMock();
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()->getmock();

        $this->fileFactory =
            $this->getMockBuilder(\Magento\Framework\App\Response\Http\FileFactory::class)
                ->disableOriginalConstructor()->getMock();

        $this->fileSystem =
            $this->getMockBuilder(\Magento\Framework\Filesystem::class)
                ->disableOriginalConstructor()->getMock();

        $this->checkoutSession =
            $this->getMockBuilder(\Magento\Checkout\Model\Session::class)->setMethods(['getQuote'])
                ->disableOriginalConstructor()->getMock();

        $this->quote = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)->setMethods(['getItems'])
            ->disableOriginalConstructor()->getMock();

        $this->currency = $this->getMockBuilder(\Magento\Directory\Model\Currency::class)
            ->disableOriginalConstructor()->getMock();

        $this->loggerInterface = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()->getMock();

        $this->messageManager = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->disableOriginalConstructor()->getMock();

        $this->driverPool = $this->getMockBuilder(\Magento\Framework\Filesystem\Directory\WriteFactory::class)
            ->setMethods(
                ['getDriver', 'delete', 'renameFile', 'copyFile', 'createSymlink', 'changePermissions', 'changePermissionsRecursively', 'touch', 'isWritable', 'openFile', 'writeFile', 'getAbsolutePath', 'getRelativePath', 'read', 'search', 'isExist', 'stat', 'isReadable', 'isFile', 'isDirectory', 'readFile', 'create']
            )
            ->disableOriginalConstructor()->getMock();

        $this->fileWriter = $this->getMockBuilder(\Magento\Framework\Filesystem\File\WriteInterface::class)
            ->setMethods(
                ['writeCSV', 'flush', 'lock', 'unlock', 'read', 'readAll', 'readLine', 'readCsv', 'tell', 'seek', 'eof', 'close', 'stat', 'write']
            )
            ->disableOriginalConstructor()->getMock();

        $this->cartDownload = $this->objectManager->getObject(
            \Born\Customer\Controller\Cart\Download::class,
            [
                'filesystem' => $this->fileSystem,
                'filefactory' => $this->fileFactory,
                'directory' => $this->driverPool,
                'checkoutSession' => $this->checkoutSession,
                'logger' => $this->loggerInterface,
                'currency' => $this->currency,
                'formKeyValidator' => $this->formKeyValidator,
                'resultRedirectFactory' => $this->resultRedirectFactory,
                '_request' => $this->request,
                'messageManager' => $this->messageManager
            ]
        );
    }

    /**
     * Test when form key is invalid
     */
    public function testExecuteInvalidFormKey()
    {
        $redirect = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->formKeyValidator->expects($this->once())->method('validate')->with($this->request)->willReturn(false);
        $this->messageManager->expects($this->once())->method('addErrorMessage');
        $this->resultRedirectFactory->expects($this->once())->method('create')->willReturn($redirect);
        $redirect->expects($this->once())->method('setUrl')->with(null)->willReturnSelf();
        $this->assertEquals($redirect, $this->cartDownload->execute());

    }

    /**
     * Test when exception in main execute() is thrown
     */
    public function testExecuteException()
    {
        $redirect = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->formKeyValidator->expects($this->once())->method('validate')->with($this->request)->willReturn(true);
        $this->messageManager->expects($this->once())->method('addErrorMessage');
        $this->resultRedirectFactory->expects($this->once())->method('create')->willReturn($redirect);


        $this->driverPool->expects($this->once())->method('openFile')->will($this->throwException(new \Exception()));
        $redirect->expects($this->once())->method('setUrl')->with(null)->willReturnSelf();
        $this->assertEquals($redirect, $this->cartDownload->execute());

    }

    /**
     * Test successfull execute()
     */
    public function testExecuteSuccess()
    {

        $this->formKeyValidator->expects($this->once())->method('validate')->with($this->request)->willReturn(true);

        $this->driverPool->expects($this->once())->method('openFile')->willReturn($this->fileWriter);

        $this->checkoutSession->expects($this->once())->method('getQuote')->willReturn($this->quote);


        $cartItems = $this->getCartItems(1);

        $this->quote->expects($this->once())->method('getItems')->willReturn($cartItems);

        $this->messageManager->expects($this->never())->method('addErrorMessage');

        $response = $this->getMockBuilder(\Magento\Framework\App\ResponseInterface::class)
            ->disableOriginalConstructor()
            ->getMock();


        $this->fileFactory->expects($this->once())->method('create')->willReturn($response);

        $this->assertEquals($response, $this->cartDownload->execute());

    }

    /**
     *
     * function to change protected Data methods to public methods
     * @param $object
     * @param $methodName
     * @param array $parameters
     * @return mixed
     * @throws \ReflectionException
     */
    public function invokeProtectedMethod(&$object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    /*
     * Cart item generator
     */
    protected function getCartItems($count)
    {
        $cartItems = [];
        for ($i = 0; $i < $count; $i++) {
            $cartItem = $this->objectManager->getObject(
                \Magento\Framework\DataObject::class,
                []
            );
            $cartItem->setId(1);
            $cartItem->setName('Test Item');
            $cartItem->setSku(456);
            $cartItem->setPrice(1045.5000);
            $cartItem->setQty(10);
            $cartItem->setRowTotal(10455.0000);
            $cartItems[] = $cartItem;
        }
        return $cartItems;
    }

    /**
     * Validate data
     * @throws \ReflectionException
     */
    public function testData()
    {

        $headerData = $this->invokeProtectedMethod($this->cartDownload, 'getHeaders');

        $cartItems = $this->getCartItems(2);

        $this->checkoutSession->expects($this->once())->method('getQuote')->willReturn($this->quote);

        $this->currency->expects($this->any())->method('format')->willReturn('$1045.50');

        $this->quote->expects($this->once())->method('getItems')->willReturn($cartItems);

        $bodyData = $this->invokeProtectedMethod($this->cartDownload, 'getData');

        // Check response is array
        $this->assertEquals(true, is_array($bodyData));
        // Check that header and body sizes match
        $this->assertEquals(count($headerData), count($bodyData[0]));
        // Check ItemData
        $this->assertContains('Test Item', $bodyData[0]);
        $this->assertContains(456, $bodyData[0]);
        $this->assertContains('$1045.50', $bodyData[0]);
        $this->assertContains(10, $bodyData[0]);

    }

}