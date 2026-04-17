<?php
/**
 * @author CynoInfotech Team
 * @package Cynoinfotech_StorePickup
 */
namespace Cynoinfotech\StorePickup\Controller\Adminhtml\Import;

class Save extends \Cynoinfotech\StorePickup\Controller\Adminhtml\Import
{
    protected $fileUploaderFactory;
    
    protected $fileSystem;
    
    protected $csvProcessor;
    
    /**
     * construct
     *
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploader
     * @param \Magento\Framework\Filesystem $fileSystem
     * @param \Cynoinfotech\StorePickup\Model\StorePickupFactory $storepickupFactory,
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    
    public function __construct(
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploader,
        \Magento\Framework\Filesystem $fileSystem,
        \Magento\Framework\File\Csv $csvProcessor,
        \Cynoinfotech\StorePickup\Model\StorePickupFactory $storepickupFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->fileUploaderFactory = $fileUploader;
        $this->fileSystem = $fileSystem;
        $this->csvProcessor = $csvProcessor;
        parent::__construct($storepickupFactory, $registry, $resultRedirectFactory, $context);
    }
  
    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Cynoinfotech_StorePickup::import_save');
    }
    
    /**
     * run the action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
     
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $resultRedirect = $this->resultRedirectFactory->create();
        $csv_file = $this->getRequest()->getFiles('csvfile');
        $csv_filename = $csv_file['name'];
        $csv_file_name = $csv_file['name'];
        
        $csv_file_name_array = str_split($csv_filename);
        $path = $this->fileSystem->getDirectoryRead(
            \Magento\Framework\App\Filesystem\DirectoryList::MEDIA
        )->getAbsolutePath('storeimport');
         
        if ($csv_filename) {
             $uploader = $this->fileUploaderFactory->create(['fileId' => 'csvfile']);
               $uploader->setAllowedExtensions(['csv']);
               $uploader->setAllowRenameFiles(true);
               $uploader->setFilesDispersion(true);
               $result = $uploader->save($path);
               
              /**
               * Old methods
               * $csv_file_name = $path.'/'.$csv_file_name_array[0].'/'.$csv_file_name_array[1].'/'.$csv_file_name;
               **/
               
              $csv_file_name =  $path.'/'.$result['file'];
              $importRawData = $this->csvProcessor->getData($csv_file_name);
              
            try {
                $i =0;
                foreach ($importRawData as $dataRow) {
                    $store = $this->storepickupFactory->create();
                    $store->getCollection();
                         
                    if ($i !=0) {
                        if (isset($dataRow[1])) {
                            $store->setStoreImage($dataRow[1]);
                        }
                        if (isset($dataRow[2])) {
                            $store->setName($dataRow[2]);
                        }
                        if (isset($dataRow[3])) {
                            $store->setStoreAddress($dataRow[3]);
                        }
                        if (isset($dataRow[4])) {
                            $store->setStoreCity($dataRow[4]);
                        }
                        if (isset($dataRow[5])) {
                            $store->setStoreState($dataRow[5]);
                        }
                        if (isset($dataRow[6])) {
                            $store->setStoreCountry($dataRow[6]);
                        }
                        if (isset($dataRow[7])) {
                            $store->setStorePincode($dataRow[7]);
                        }
                        if (isset($dataRow[8])) {
                            $store->setStoreLatitude($dataRow[8]);
                        }
                        if (isset($dataRow[9])) {
                            $store->setStoreLongitude($dataRow[9]);
                        }
                        if (isset($dataRow[10])) {
                            $store->setStoreEmail($dataRow[10]);
                        }
                        if (isset($dataRow[11])) {
                            $store->setStorePhone($dataRow[11]);
                        }
                        if (isset($dataRow[12])) {
                            $store->setStoreStatus($dataRow[12]);
                        }
                        
                        $store->save();
                    }
                     $i++;
                }
                
                    
                $this->messageManager->addSuccess(__('The Data has been saved.'));
                $resultRedirect->setPath('storepickup/*/');
                return $resultRedirect;
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the Data.'));
                $resultRedirect->setPath('storepickup/*/');
                return $resultRedirect;
            }
            
            $resultRedirect->setPath('storepickup/*/');
            return $resultRedirect;
        }
    }
}
