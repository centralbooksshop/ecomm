<?php
namespace Retailinsights\Orders\Controller\Adminhtml\Orders;

use Magento\Backend\App\Action;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\Filesystem\DirectoryList;

class Download extends Action
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

        // --- Step 1: Create a temporary HTML page that triggers file download and redirect
        $downloadUrl = $this->getUrl('retailinsights_admin/orders/downloadFile', ['file' => $fileParam]);
		$this->messageManager->addSuccessMessage(__('Selected orders have been updated successfully.'));
        $redirectUrl = $this->getUrl('sales/order');

        $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="refresh" content="5;url={$redirectUrl}">
    <script>
        window.onload = function() {
            window.location.href = "{$downloadUrl}";
            setTimeout(function(){
                window.location.href = "{$redirectUrl}";
            }, 4000);
        }
    </script>
</head>
<body style="font-family: sans-serif; text-align: center; padding-top: 50px;">
    <h2>Download started...</h2>
    <p>You will be redirected back to the orders list in a few seconds.</p>
</body>
</html>
HTML;

        $this->getResponse()->setBody($html);
        return;
    }
}
