<?php

namespace Webkul\DeliveryBoy\Controller\Adminhtml\Export;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Webkul\DeliveryBoy\Model\Export\ConvertToCsv;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Ui\Component\MassAction\Filter;
use Psr\Log\LoggerInterface;

class CustomGridToCsv extends Action
{
    protected $converter;
    protected $fileFactory;
    private $filter;
    private $logger;

    public function __construct(
        Context $context,
        ConvertToCsv $converter,
        FileFactory $fileFactory,
        Filter $filter = null,
        LoggerInterface $logger = null
    ) {
        parent::__construct($context);
        $this->converter = $converter;
        $this->fileFactory = $fileFactory;
        $this->filter = $filter ?: ObjectManager::getInstance()->get(Filter::class);
        $this->logger = $logger ?: ObjectManager::getInstance()->get(LoggerInterface::class);
    }

    public function execute()
    {
        return $this->fileFactory->create('runsheet.csv', $this->converter->getCsvFile(), 'var');
    }

     /**
     * Checking if the user has access to requested component.
     *
     * @inheritDoc
     */
    protected function _isAllowed()
    {
        if ($this->_request->getParam('namespace')) {
            try {
                $component = $this->filter->getComponent();
                $dataProviderConfig = $component->getContext()
                    ->getDataProvider()
                    ->getConfigData();
                if (isset($dataProviderConfig['aclResource'])) {
                    return $this->_authorization->isAllowed(
                        $dataProviderConfig['aclResource']
                    );
                }
            } catch (\Throwable $exception) {
                $this->logger->critical($exception);

                return false;
            }
        }

        return true;
    }

}

