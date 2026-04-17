<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Controller\Returns;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Plumrocket\RMA\Controller\AbstractReturns;
use Plumrocket\RMA\Helper\Data;

class AddressSave extends AbstractReturns
{
    /**
     * Save returns address
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $request = $this->getRequest();
        $address = $this->addressFactory->create();
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        if ($parentId = $request->getParam('parent_id')) {
            // From return edit page
            /*$address->load($parentId, 'parent_id');
            $model = $this->_getModel();

            if (! $address->getId() && $model->getId()) {
                $address->setParentId($model->getId())
                    ->setOrderId($model->getOrder()->getId());
            }

            $backToReturn['id'] = $parentId;
            $backToAddress['parent_id'] = $parentId;*/

            // Cannot edit address on frontend
            return $resultRedirect
                ->setPath(Data::SECTION_ID . '/*/view', ['id' => $parentId]);
        } elseif ($orderId = $request->getParam('order_id')) {
            // From return create page
            if ($unassignedAddress = $address->getUnassigned($orderId)) {
                $address = $unassignedAddress;
            } else {
                $order = $this->orderFactory->create()->load($orderId);
                if ($order && $order->getId()) {
                    $address->setOrderId($order->getId());
                }
            }

            $backToReturn['order_id'] = $orderId;
            $backToAddress['order_id'] = $orderId;
        }

        $data = $request->getPostValue();
        if ($data && $address->getOrderId()) {
            $address->addData($data);
            try {
                $address->save();
                $this->dataHelper->addFormData('returns_address', false);
                $this->messageManager->addSuccessMessage(__('You updated the return address.'));
                return $resultRedirect->setPath(Data::SECTION_ID . '/*/create', $backToReturn);
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('We can\'t update the return address right now.'));
            }

            $this->dataHelper->addFormData('returns_address', $data);
        }

        return $resultRedirect->setPath(Data::SECTION_ID . '/*/address', $backToAddress);
    }

    /**
     * @inheritdoc
     */
    public function canViewReturn()
    {
        // Client cannot have return on this page
        return false;
    }
}
