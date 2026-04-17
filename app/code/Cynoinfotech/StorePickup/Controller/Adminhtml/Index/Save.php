<?php
/**
 * @author CynoInfotech Team
 * @package Cynoinfotech_StorePickup
 */
namespace Cynoinfotech\StorePickup\Controller\Adminhtml\Index;

class Save extends \Cynoinfotech\StorePickup\Controller\Adminhtml\StorePickup
{
    protected $fileUploaderFactory;
    
    protected $fileSystem;
    
    /*
    *
    * Backend Session
    *
    * @var \Magento\Backend\Model\Session
    */
    
    protected $backendSession;
    
    /**
     * Construct
     * @param \Magento\Backend\Model\Session $backendSession
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\MediaStorage\Model\File\UploadFactory $fileuploader
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Registry $registry
     * @param \Cynoinfotech\StorePickup\Model\StorePickupFactory $storepickupFactory
     * @param \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
     */
    
    public function __construct(
        \Magento\MediaStorage\Model\File\UploaderFactory $fileuploader,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Backend\Model\Session $backendSession,
        \Cynoinfotech\StorePickup\Model\StorePickupFactory $storepickupFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->fileUploaderFactory = $fileuploader;
        $this->fileSystem = $filesystem;
        $this->backendSession = $backendSession;
        parent::__construct($storepickupFactory, $registry, $resultRedirectFactory, $context);
    }
    
    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Cynoinfotech_StorePickup::save');
    }
    
    
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        
        $resultRedirect = $this->resultRedirectFactory->create();
        $storepickup = $this->_initStorePickup();
        
        if (isset($data)) {
            $storepickup->setData($data);
            
            $path = $this->fileSystem->getDirectoryRead(
                \Magento\Framework\App\Filesystem\DirectoryList::MEDIA
            )->getAbsolutePath('storepickup');
            
            $file = [];
            $file = $this->getRequest()->getFiles('store_image');
			
            if (isset($file)) {
                $file_name = $file['name'];
                if ($file_name) {
                    $file_name_array = str_split($file_name);
                    $uploader = $this->fileUploaderFactory->create(['fileId' => 'store_image']);
                    $uploader->save($path);
                    $storepickup->setStoreImage($file_name);
                } else {
                    if (isset($data['store_image']['delete'])) {
                        $storepickup->setStoreImage($data['store_image']['value']);
                        if ($data['store_image']['delete'] ==1) {
                            $storepickup->setStoreImage('');
                        }
                        if ($data['store_image']['value'] == "") {
                            $storepickup->setStoreImage('');
                        }
                    }else{
						
						if (isset($data['store_image']['value'])) {									
							$value = $data['store_image']['value'];
                            $storepickup->setStoreImage($value);
                        }
					
					
					}
                }
            }
            
            try {
                $storepickup->save();
                $this->messageManager->addSuccess(__('The Store has been saved.'));
                $this->backendSession->setStorepickupData(false);
               
                if ($this->getRequest()->getParam('back')) {
                    $resultRedirect->setPath(
                        'storepickup/*/edit',
                        [
                            'entity_id' => $storepickup->getId(),
                            '_current' => true
                        ]
                    );
                    return $resultRedirect;
                }
                $resultRedirect->setPath('storepickup/*/');
                return $resultRedirect;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError($e, __('Something went wrong while saving store'));
            }
            $this->_getSession()->setEventsData($data);
            $resultRedirect->setPath(
                'storepickup/*/edit',
                [
                    'entity_id' => $storepickup->getId(),
                    '_current' => true
                ]
            );
            return $resultRedirect;
        }
        $resultRedirect->setPath('storepickup/*/');
        return $resultRedirect;
    }
}
