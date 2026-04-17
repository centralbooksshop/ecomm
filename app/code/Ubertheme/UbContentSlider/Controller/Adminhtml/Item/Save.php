<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 */
namespace Ubertheme\UbContentSlider\Controller\Adminhtml\Item;

use Magento\Backend\App\Action;
use Ubertheme\UbContentSlider\Model\Item\Image as ImageModel;
use Ubertheme\Base\Model\Upload;

class Save extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Ubertheme_UbContentSlider::item_save';

    /**
     * @var PostData Processor
     */
    protected $dataProcessor;

    /**
     * @param Action\Context $context
     * @param PostDataProcessor $dataProcessor
     * @param ImageModel $imageModel
     * @param Upload $uploadModel
     */
    public function __construct(
        Action\Context $context,
        PostDataProcessor $dataProcessor,
        ImageModel $imageModel,
        Upload $uploadModel
    )
    {
        $this->dataProcessor = $dataProcessor;
        $this->imageModel = $imageModel;
        $this->uploadModel = $uploadModel;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(self::ADMIN_RESOURCE);
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data) {
            //filter posted data
            $data = $this->dataProcessor->filter($data);

            //create model object
            /** @var \Ubertheme\UbContentSlider\Model\Item $model */
            $model = $this->_objectManager->create('Ubertheme\UbContentSlider\Model\Item');

            //init model
            $id = $this->getRequest()->getParam('item_id');
            if ($id) {
                $model->load($id);
            }

            //set new data
            $model->setData($data);

            $allowedImageFileTypes = ['jpg', 'jpeg', 'gif', 'png'];
            //process upload the main image
            $imageName = $this->uploadModel->processUpload(
                'image',
                $this->imageModel->getBaseDir(),
                $data,
                $allowedImageFileTypes
            );
            $model->setImage($imageName);

            //process upload the mobile image if has
            $mobileImageName = $this->uploadModel->processUpload(
                'mobile_image',
                $this->imageModel->getBaseDir(),
                $data,
                $allowedImageFileTypes
            );
            $model->setMobileImage($mobileImageName);

            //process upload the cover image of video if has
            if ($model->getVideoId()) {
                $coverImageName = $this->uploadModel->processUpload(
                    'video_cover',
                    $this->imageModel->getBaseDir(),
                    $data,
                    $allowedImageFileTypes
                );
                $model->setVideoCover($coverImageName);
            }

            if (!$this->dataProcessor->validate($data)) {
                return $resultRedirect->setPath('*/*/edit', ['item_id' => $model->getId(), '_current' => true]);
            }

            try {
                $model->save();
                $this->messageManager->addSuccess(__('You saved this slide item.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['item_id' => $model->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/', ['slide_id' => $model->getSlideId()]);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the slide item information.'));
            }
            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['item_id' => $this->getRequest()->getParam('item_id')]);
        }

        return $resultRedirect->setPath('*/*/', ['slide_id' => $data['slide_id']]);
    }
}
