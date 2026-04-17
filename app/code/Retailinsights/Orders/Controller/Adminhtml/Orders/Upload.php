<?php
namespace Retailinsights\Orders\Controller\Adminhtml\Orders;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\MediaStorage\Model\File\UploaderFactory;

class Upload extends Action
{
    protected $uploaderFactory;
    protected $filesystem;

    public function __construct(
        Action\Context $context,
        UploaderFactory $uploaderFactory,
        \Magento\Framework\Filesystem $filesystem
    ){
        parent::__construct($context);
        $this->uploaderFactory = $uploaderFactory;
        $this->filesystem = $filesystem;
    }

    public function execute()
    {
        try {
            $itemId = $this->getRequest()->getParam('item_id');
            $uploader = $this->uploaderFactory->create(['fileId' => 'file']);
            $uploader->setAllowCreateFolders(true);
            $uploader->setAllowedExtensions(['pdf','jpg','jpeg','png']);
            
            $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
            $result = $uploader->save($mediaDirectory . '/acknowledgements/');

            if ($result['file']) {
                // Save the file path to database
                $filePath = 'acknowledgements/' . $result['file'];
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $orderItem = $objectManager->create('Magento\Sales\Model\Order\Item')->load($itemId);
                $orderItem->setData('acknowledgement_upload', $filePath);
                $orderItem->save();
            }

            $this->getResponse()->representJson(json_encode(['success' => true, 'file' => $result['file']]));
        } catch (\Exception $e) {
            $this->getResponse()->representJson(json_encode(['error' => $e->getMessage()]));
        }
    }
}
