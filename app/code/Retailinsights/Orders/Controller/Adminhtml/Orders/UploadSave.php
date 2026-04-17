<?php
namespace Retailinsights\Orders\Controller\Adminhtml\Orders;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Sales\Model\Order\ItemFactory;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Stdlib\DateTime\DateTime;

class UploadSave extends Action
{
    protected $fileUploaderFactory;
    protected $filesystem;
    protected $orderItemFactory;
    protected $date;

    public function __construct(
        Action\Context $context,
        UploaderFactory $fileUploaderFactory,
        Filesystem $filesystem,
        ItemFactory $orderItemFactory,
        DateTime $date
    ) {
        $this->fileUploaderFactory = $fileUploaderFactory;
        $this->filesystem = $filesystem;
        $this->orderItemFactory = $orderItemFactory;
        $this->date = $date;
        parent::__construct($context);
    }

     public function execute()
	{
		$itemIdsParam = (string) $this->getRequest()->getParam('item_id', '');
		$itemIds = $itemIdsParam !== '' ? array_filter(explode(',', $itemIdsParam)) : [];

		$resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
		$resultRedirect->setPath('retailinsights_admin/willbegiven/index');

		if (empty($itemIds)) {
			$this->messageManager->addErrorMessage(__('No items selected for upload.'));
			return $resultRedirect;
		}

		if (isset($_FILES['acknowledgement_file']['name']) && $_FILES['acknowledgement_file']['name'] != '') {
			try {
				$uploader = $this->fileUploaderFactory->create(['fileId' => 'acknowledgement_file']);
				$uploader->setAllowedExtensions(['pdf', 'jpg', 'jpeg', 'png']);
				$uploader->setAllowRenameFiles(true);
				$uploader->setFilesDispersion(false);

				$mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
				$result = $uploader->save($mediaDirectory->getAbsolutePath('acknowledgements'));

				if ($result && isset($result['file'])) {
					$fileName = $result['file'];

					foreach ($itemIds as $itemId) {
						$orderItem = $this->orderItemFactory->create()->load($itemId);
						if ($orderItem->getId()) {
							$orderItem->setData('acknowledgement_upload', $fileName);
							$orderItem->setData('dispatch_date', $this->date->gmtDate());
							$orderItem->setData('dispatch_status', 'confirmed');
							$orderItem->save();
						}
					}

					$this->messageManager->addSuccessMessage(__(
						'File uploaded successfully for %1 item(s).', count($itemIds)
					));
				} else {
					$this->messageManager->addErrorMessage(__('File upload failed.'));
				}
			} catch (\Exception $e) {
				$this->messageManager->addErrorMessage(__('Error uploading file: %1', $e->getMessage()));
			}
		} else {
			$this->messageManager->addErrorMessage(__('No file selected.'));
		}

		return $resultRedirect;
	}

}
