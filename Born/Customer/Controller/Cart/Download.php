<?php

namespace Born\Customer\Controller\Cart;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class Download
 * @package Born\Customer\Controller\Cart
 */
class Download extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;
    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $directory;
    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $filefactory;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
    /**
     * @var \Magento\Directory\Model\Currency
     */
    protected $currency;
    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $formKeyValidator;

    /**
     * Download constructor.
     * @param Context $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Directory\Model\Currency $currency
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Directory\Model\Currency $currency,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
    )
    {
        parent::__construct($context);
        $this->filefactory = $fileFactory;
        $this->filesystem = $filesystem;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->checkoutSession = $checkoutSession;
        $this->logger = $logger;
        $this->currency = $currency;
        $this->formKeyValidator = $formKeyValidator;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {

        if ($this->formKeyValidator->validate($this->getRequest())) {
            try {
                return $this->createFile();

            } catch (\Exception $e) {

                $this->logger->error('Error exporting customer CSV file' . $e->getMessage());

                $this->messageManager->addErrorMessage(
                    'There was an issue downloading your file. We are working hard to fix this experience. Please try again soon.'
                );

            }
        } else {
            $this->messageManager->addErrorMessage('Invalid Form Key');
        }
        $response = $this->resultRedirectFactory->create();
        $response->setUrl($this->_redirect->getRefererUrl());
        return $response;

    }

    /**
     * @return \Magento\Framework\App\ResponseInterface
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function createFile()
    {
        $name = md5(microtime());
        $file = 'customer-export/my-cart-' . $name . '.csv';
        $this->directory->create('customer-export');
        $stream = $this->directory->openFile($file, 'w+');
        $stream->lock();
        $stream->writeCsv($this->getHeaders());
        $data = $this->getData();
        foreach ($data as $data_output) {
            $stream->writeCsv($data_output);
        }

        $content['type'] = 'filename';
        $content['value'] = $file;
        $content['rm'] = '1';

        return $this->filefactory->create('My-Cart.csv', $content, DirectoryList::VAR_DIR);
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getData()
    {
        $returnData = [];
        $cart_items = $this->checkoutSession->getQuote()->getItems();
        /**
         * @var \Magento\Quote\Model\Quote\Item $cart_item ;
         */
        foreach ($cart_items as $k => $cart_item) {
            $returnData[$k][] = $cart_item->getName();
            $returnData[$k][] = $cart_item->getSku();
            $returnData[$k][] = $this->currency->format($cart_item->getPrice(), [], false);
            $returnData[$k][] = $cart_item->getQty();
            $returnData[$k][] = $this->currency->format($cart_item->getRowTotal(), [], false);
        }
        return $returnData;
    }


    /**
     * @return array
     */
    protected function getHeaders()
    {
        $headers = ['Product name', 'SKU', 'Price', 'Qty', 'Subtotal'];
        return $headers;

    }
}