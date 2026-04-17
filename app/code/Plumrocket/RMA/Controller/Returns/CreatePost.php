<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket RMA v2.x.x
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\RMA\Controller\Returns;

use Magento\Framework\Controller\ResultFactory;
use Plumrocket\RMA\Block\Returns\Messages\Uploader;
use Plumrocket\RMA\Controller\AbstractReturns;
use Plumrocket\RMA\Helper\Data;
use Plumrocket\RMA\Helper\Returns\Item as ItemHelper;
use Plumrocket\RMA\Model\Config\Source\ReturnsStatus;
use Plumrocket\RMA\Model\Returns;
use Plumrocket\RMA\Model\Returns\Message;


class CreatePost extends AbstractReturns
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        try {
            $request = $this->getRequest();
			//echo '<pre>';print_r($request->getParams());die;
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

            if (! $request->isPost()) {
                return $resultRedirect->setUrl(
                    $this->_redirect->getRefererUrl()
                );
            }



            $model = $this->getModel();
            if (! $model->hasOrderId()) {
                $orderId = $request->getParam('order_id');
                $model->setOrderId($orderId);
            }
            $this->registry->register('current_model', $model);

            $order = $model->getOrder();
            if (! $order || ! $order->getId()) {
                throw new \Exception(__('Order id is missing'));
            }

            // Validate data.
            $validator = $this->validatorFactory->create()
                ->setReturns($model)
                ->validateItemsCustomer($request->getParam('items'))
                ->validateMessage(
                    $request->getParam('comment'),
                    $request->getParam(Uploader::FILE_FIELD_NAME),
                    false
                );

            if ($this->configHelper->enabledReturnPolicy()) {
                $validator->validateAgree(
                    $request->getParam('agree_return_policy')
                );
            }

            if (! $validator->isValid()) {
                foreach ($validator->getMessages() as $message) {
                    $this->messageManager->addError($message);
                }
                $this->dataHelper->setFormData();
                return $resultRedirect->setUrl(
                    $this->_redirect->getRefererUrl()
                );
            }

            // Save return.
            $date = $this->dateTime->gmtDate();

            $status = ReturnsStatus::STATUS_NEW;
            if ($this->configHelper->canAutoAuthorize()) {
                $status = ReturnsStatus::STATUS_AUTHORIZED;
            }

            $model
                ->setStatus($status)
                ->setOrderId($order->getId())
                ->setManagerId($this->configHelper->getDefaultManagerId())
                ->setItems($request->getParam('items'))
                ->setCreatedAt($date)
                ->setUpdatedAt($date)
                ->save();

            // Assign address.
            $address = $model->getAddress();
            if (! $address || ! $address->getId()) {
                $unassignedAddress = $this->addressFactory->create()
                    ->getUnassigned($model->getOrder()->getId());

                if ($unassignedAddress) {
                    $unassignedAddress->setParentId($model->getId())
                        ->save();
                }
            }

            // Save items.
            $validItems = $validator->getValidItems();
            if (is_array($validItems)) {
                foreach ($validItems as $data) {
                    $orderItem = $this->orderItemFactory->create()
                        ->load($data[ItemHelper::ORDER_ITEM_ID]);

                    if ($this->configHelper->canAutoAuthorize()) {
                        $data[ItemHelper::QTY_AUTHORIZED] = $data[ItemHelper::QTY_REQUESTED];
                    }

                    $item = $this->itemFactory->create()
                        ->setReturns($model)
                        ->setQtyPurchased(
                            $this->itemHelper->getQtyToReturn($orderItem, $model->getId())
                        )
                        ->addData($data)
                        ->save();
                    $this->messageManager->addSuccess(__('Return has been saved.'));
                }
            }

            $mobile = $order->getShippingAddress()->getTelephone();
            $custmerName = $order->getShippingAddress()->getFirstname();
            $incrementId = $model->getId();
            $email = $order->getCustomerEmail();
			$orderid = $order->getIncrementId();
			$rmastatus = $model->getStatus();
            
            // Clear form data.
            $this->dataHelper->setFormData(false);
            
           
			
			$msg = "Dear ".$custmerName.", your order ".$incrementId." - Return request is processing, We'll update you the status once approved and completed the request. for more details mail us at help@centralbooksonline.com Thanks, CentralBooksOnline";

            $sms = $this->itemHelper->SendSms($msg,"Y",$mobile,$incrementId,$email,$custmerName,$orderid,$rmastatus);
            
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
            $baseurl = $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB); 
            //echo $baseurl;
            //return $resultRedirect->setPath($baseurl."default/prrma/returns/success/id/".$model->getId());

            // return $resultRedirect->setPath(Data::SECTION_ID . '/*/success', [
            //     'id' => $model->getId()
            // ]);

            // Add message.
            $message = $model->addMessage(
                Message::FROM_CUSTOMER,
                $request->getParam('comment'),
                $request->getParam(Uploader::FILE_FIELD_NAME)
            );

            // Send email.
            $this->emailFactory->create()
                ->setReturns($model)
                ->notifyCustomerAboutCreate() // notify customer without him message
                ->setMessage($message)
                ->notifyManagerAboutCreate();

        } catch (\Exception $e) {
            // $this->messageManager->addError(__('Error on Creating Return'));
            $this->dataHelper->setFormData();
        }

        return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
    }

    /**
     * {@inheritdoc}
     */
    public function canViewReturn()
    {
        // Client cannot have return on create page
        return false;
    }
}
