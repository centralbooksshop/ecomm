<?php
namespace Retailinsights\Orders\Controller\Adminhtml\Orders;

use Magento\Backend\App\Action;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\Filesystem\DirectoryList;

class DownloadFile extends Action
{
    protected $fileFactory;
    protected $directoryList;

    public function __construct(
        Action\Context $context,
        FileFactory $fileFactory,
        DirectoryList $directoryList
    ) {
        parent::__construct($context);
        $this->fileFactory = $fileFactory;
        $this->directoryList = $directoryList;
    }

    public function execute()
    {
        $fileParam = $this->getRequest()->getParam('file');
        if (!$fileParam) {
            $this->messageManager->addErrorMessage(__('No file specified.'));
            return $this->_redirect('sales/order');
        }

        $file = base64_decode($fileParam);
        $filePath = $this->directoryList->getPath(DirectoryList::VAR_DIR) . '/cbo_invoices/' . $file;

        if (!file_exists($filePath)) {
            $this->messageManager->addErrorMessage(__('File not found: %1', $file));
            return $this->_redirect('sales/order');
        }

        return $this->fileFactory->create(
            $file,
            file_get_contents($filePath),
            DirectoryList::VAR_DIR,
            'application/pdf'
        );
    }
}
