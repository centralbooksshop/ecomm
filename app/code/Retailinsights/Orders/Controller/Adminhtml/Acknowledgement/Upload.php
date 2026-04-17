<?php
namespace Retailinsights\Orders\Controller\Adminhtml\Acknowledgement;

use Magento\Backend\App\Action;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Controller\Result\JsonFactory;

class Upload extends Action
{
    protected $uploaderFactory;
    protected $resultJsonFactory;

    public function __construct(
        Action\Context $context,
        UploaderFactory $uploaderFactory,
        JsonFactory $resultJsonFactory
    ) {
        $this->uploaderFactory = $uploaderFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();

        try {
            $uploader = $this->uploaderFactory->create(['fileId' => 'file']);
            $uploader->setAllowedExtensions(['pdf']);
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(false);
            $path = $this->_objectManager->get('Magento\Framework\Filesystem')
                ->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)
                ->getAbsolutePath('acknowledgements/');
            $resultFile = $uploader->save($path);

            // Save file name to sales_order_item table
            $itemId = $this->getRequest()->getParam('item_id');
            $item = $this->_objectManager->create('Magento\Sales\Model\Order\Item')->load($itemId);
            $item->setData('acknowledgement_upload', $resultFile['file']);
            $item->save();

            $result->setData(['success' => true]);
        } catch (\Exception $e) {
            $result->setData(['success' => false, 'error' => $e->getMessage()]);
        }

        return $result;
    }
}
