<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Controller\Returns;

use Magento\Framework\Controller\ResultFactory;
use Plumrocket\RMA\Block\Returns\Messages\Uploader;
use Plumrocket\RMA\Controller\AbstractReturns;
use Plumrocket\RMA\Model\Returns\Message;

class Save extends AbstractReturns
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        try {
            $request = $this->getRequest();
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

            if (! $request->isPost()) {
                return $resultRedirect->setUrl(
                    $this->_redirect->getRefererUrl()
                );
            }

            $model = $this->getModel();

            if ($model->isClosed()) {
                return $resultRedirect->setUrl(
                    $this->_redirect->getRefererUrl()
                );
            }

            // Validate data.
            $validator = $this->validatorFactory->create()
                ->validateMessage(
                    $request->getParam('comment'),
                    $request->getParam(Uploader::FILE_FIELD_NAME)
                );

            if (! $validator->isValid()) {
                foreach ($validator->getMessages() as $message) {
                    $this->messageManager->addErrorMessage($message);
                }
                $this->dataHelper->setFormData();
                return $resultRedirect->setUrl(
                    $this->_redirect->getRefererUrl()
                );
            }

            // Add message.
            $message = $model->addMessage(
                Message::FROM_CUSTOMER,
                $request->getParam('comment'),
                $request->getParam(Uploader::FILE_FIELD_NAME)
            );

            // Send email.
            if ($message && $message->getId()) {
                $model->setUpdatedAt($this->dateTime->gmtDate())->save();

                $this->emailFactory->create()
                    ->setReturns($model)
                    ->setMessage($message)
                    ->notifyManagerAboutUpdate();
            }

            // Clear form data.
            $this->dataHelper->setFormData(false);

            $this->messageManager->addSuccessMessage(__('Message has been sent'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage('Unknown Error');
            $this->dataHelper->setFormData();
        }

        return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
    }

    /**
     * {@inheritdoc}
     */
    public function canViewOrder()
    {
        // Client canot have separate order on this page
        return false;
    }
}
