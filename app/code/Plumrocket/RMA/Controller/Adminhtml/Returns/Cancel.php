<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Controller\Adminhtml\Returns;

use Plumrocket\RMA\Model\Config\Source\ReturnsStatus;
use Plumrocket\RMA\Model\Returns\Message;

class Cancel extends \Plumrocket\RMA\Controller\Adminhtml\Returns
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $model = $this->_getModel();
        if ($model->isClosed()) {
            $this->_redirect('*/*');
            return;
        }

        try {
            $model
                ->setIsClosed(true)
                ->setStatus(ReturnsStatus::STATUS_CANCELLED)
                ->save();

            // Add system message.
            $systemMessage = $model->addMessage(
                Message::FROM_MANAGER,
                __('Return request has been canceled by store manager'),
                null,
                true
            );

            // Send email.
            $email = $this->emailFactory->create()
                ->setReturns($model)
                ->setMessage($systemMessage)
                ->notifyCustomerAboutUpdate();

            if ($model->getManagerId() != $this->_auth->getUser()->getId()) {
                $email->notifyManagerAboutUpdate(
                    $this->_auth->getUser()
                );
            }

            $this->_redirect('*/*');
        } catch (\Exception $e) {
            $this->_redirect('*/*/edit', ['id' => $model->getId()]);
        }
    }
}
