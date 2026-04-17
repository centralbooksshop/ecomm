<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Controller\Adminhtml\Returns;

use Plumrocket\RMA\Block\Adminhtml\Returns\Messages\Uploader as MessageUploader;
use Plumrocket\RMA\Block\Adminhtml\Returns\ShippingLabel\Uploader;
use Plumrocket\RMA\Controller\Adminhtml\Returns;
use Plumrocket\RMA\Helper\Returns\Item as ItemHelper;
use Plumrocket\RMA\Model\Returns\Message;
use Plumrocket\RMA\Model\Returns\Track;

class Save extends Returns
{
    protected function _beforeSave($model, $request)
    {
		$reason_id = (int)$model['reason_id'];
		$condition_id = (int)$model['condition_id'];
		$resolution_id = (int)$model['resolution_id'];
		//echo '<pre>';print_r($model);die;
		$request = $this->getRequest();
        $this->_getRegistry()->register('current_model', $model);

        if ($model->isObjectNew() &&
            ! $this->returnsHelper->canReturnAdmin($model->getOrder())
        ) {
            $this->_redirect('*/*');
            return false;
        }

        // Validate data.
        $validator = $this->validatorFactory->create()
            ->setReturns($model)
            ->validateMessage(
                $request->getParam('comment'),
                $request->getParam(MessageUploader::FILE_FIELD_NAME),
                false
            );

        foreach ((array)$request->getParam('track_add') as $track) {
            $validator->validateTrack(
                isset($track['carrier_code']) ? $track['carrier_code'] : null,
                isset($track['track_number']) ? $track['track_number'] : null
            );
        }

        if (! $model->isClosed()) {
			if (empty($reason_id) || empty($condition_id) || empty($resolution_id)) {
               $validator->validateItemsAdmin($request->getParam('items'));
			}
        }

        if (! $validator->isValid()) {
            foreach ($validator->getMessages() as $message) {
                $this->messageManager->addErrorMessage($message);
            }
            $this->dataHelper->setFormData();
            return false;
        }

        $model->setValidItems($validator->getValidItems());

        // Remove shipping label.
        if ($request->getParam('shipping_label_delete')) {
            $model->setData('shipping_label', null);
        }
    }

    protected function _afterSave($model, $request)
    {
 
        if (! $model->isVirtual()) {
            // Add tracks.
            foreach ((array)$request->getParam('track_add') as $track) {
                $model->addTrack(
                    Track::FROM_MANAGER,
                    $track['carrier_code'],
                    $track['track_number']
                );
            }

            // Remove tracks.
            foreach ((array)$request->getParam('track_remove') as $trackId => $remove) {
                if ($remove) {
                    $track = $model->getTrackById($trackId);
                    if ($track && $track->getId()) {
                        $track->delete();
                    }
                }
            }

            // Take shipping label file.
            if ($filesTmp = $request->getParam(Uploader::FILE_FIELD_NAME)) {
                $shippingLabelFile = $this->fileHelper
                    ->setAdditionalPath($model->getId())
                    ->takeShippingLabel($filesTmp);

                if ($shippingLabelFile) {
                    $model
                        ->setData('shipping_label', $shippingLabelFile)
                        ->save();
                }
            }
        }

        // Save items.
        $validItems = $model->getValidItems();
		$reason_id = (int)$model['reason_id'];
		$condition_id = (int)$model['condition_id'];
		$resolution_id = (int)$model['resolution_id'];
		$entity_id = (int)$model['entity_id'];
        $modelitems = $model['items'];
		
		//echo '<pre>';print_r($model);die;
		
        if (is_array($validItems)) {
            $hasItemChanges = false;
            foreach ($validItems as $data) {
                $item = $this->itemFactory->create();
                if ('' === $data[ItemHelper::ENTITY_ID]) {
                    $orderItem = $this->orderItemFactory->create()
                        ->load($data[ItemHelper::ORDER_ITEM_ID]);

                    $item->setReturns($model)
                        ->setQtyPurchased(
                            $this->itemHelper->getQtyToReturn($orderItem, $model->getId())
                        );

                    $data[ItemHelper::QTY_AUTHORIZED] = $data[ItemHelper::QTY_REQUESTED];
                    $hasItemChanges = true;
                } else {
                    $item->load($data[ItemHelper::ENTITY_ID]);
                    if (! $item->getId()
                        || $item->getOrderItemId() != $data[ItemHelper::ORDER_ITEM_ID]
                        || $item->getParentId() != $model->getId()
                    ) {
                        continue;
                    }
                }

                // Prepare data before save.
                unset($data[ItemHelper::ENTITY_ID]);

                $cols = [
                    ItemHelper::QTY_AUTHORIZED,
                    ItemHelper::QTY_RECEIVED,
                    ItemHelper::QTY_APPROVED,
                ];

                foreach ($cols as $col) {
                    if (isset($data[$col]) && '' === $data[$col]) {
                        $data[$col] = null;
                    }
                }

                $item->addData($data)->save();
                $hasItemChanges = true;
            }

            if ($hasItemChanges) {
                // If items was created then reset items in model.
                $model->setItems(null);
            }
        } else if (is_array($modelitems)) {
		 
	       foreach ($modelitems as $data) {
			    $data_entity_id = (int)$data['entity_id'];
				//$data_entity_id = (int)($data['entity_id'] ?? 0);
				$order_item_id  = (int)($data['order_item_id'] ?? 0);
				$qty_purchased  = (int)($data['qty_purchased'] ?? 0);
				//$qty_requested  = (int)($data['qty_requested'] ?? $qty_purchased);
				//$qty_authorized = (int)($data['qty_authorized'] ?? $qty_purchased);
				$qty_requested = (!empty($data['qty_requested']) && (int)$data['qty_requested'] > 0)
				? (int)$data['qty_requested']
				: (int)$qty_purchased;

				$qty_authorized = (!empty($data['qty_authorized']) && (int)$data['qty_authorized'] > 0)
				? (int)$data['qty_authorized']
				: (int)$qty_purchased;

				
				
				if (!empty($reason_id) || !empty($condition_id) || !empty($resolution_id)) {
				    if(empty($data_entity_id))
					{ 
					   $returnItemData = $this->itemFactory->create();
					   $returnItemData->setParentId($entity_id)->save();
					   $returnItemData->setOrderItemId($order_item_id)->save();
					   $returnItemData->setReasonId($reason_id)->save();
					   $returnItemData->setConditionId($condition_id)->save();
					   $returnItemData->setResolutionId($resolution_id)->save();

						$returnItemData->setQtyPurchased($qty_purchased)->save();
						$returnItemData->setQtyRequested($qty_requested)->save();
						$returnItemData->setQtyAuthorized($qty_authorized)->save();
					} else {
						$returnItemData = $this->itemFactory->create()->load($data_entity_id);
						$returnItemData->setParentId($entity_id)->save();
						$returnItemData->setOrderItemId($order_item_id)->save();
						$returnItemData->setReasonId($reason_id)->save();
						$returnItemData->setConditionId($condition_id)->save();
						$returnItemData->setResolutionId($resolution_id)->save();

						$returnItemData->setQtyPurchased($qty_purchased)->save();
						$returnItemData->setQtyRequested($qty_requested)->save();
						$returnItemData->setQtyAuthorized($qty_authorized)->save();

					}
				}
	       }
		   
	    } 

		//echo '<pre>';print_r($model);die;

        // Calculate and save new status.
        $statusChanged = false;
        //$status = $this->returnsHelper->getStatus($model);
		if(isset($model['pty_select'])){
		   $status = $model['pty_select'];
		}
	
	    if (is_array($validItems)) {
		 
	       foreach ($validItems as $data) {
		    $returnItemData = $this->itemFactory->create()->load($data['entity_id']);
				if (!empty($reason_id) || !empty($condition_id) || !empty($resolution_id)) {
					$returnItemData->setReasonId($reason_id)->save();
					$returnItemData->setConditionId($condition_id)->save();
					$returnItemData->setResolutionId($resolution_id)->save();
				}
	       }
		   //echo '<pre>';print_r($model);die;
	    } 
	    if ($status && $status != $model->getStatus() && ! $model->isClosed()) {
            // If it is one of final statuses then close return.
            if (in_array(
                $status,
                array_keys($this->returnsStatusSource->getFinalStatuses())
            )) {
                $model->setIsClosed(true);
	    }
	   
	    $model->setStatus($status)->save();
	    if (is_array($validItems)) {
	       foreach ($validItems as $data) {
		    $returnItemData = $this->itemFactory->create()->load($data['entity_id']);
			    $qtyRequested = $returnItemData->getQtyRequested();
	    	    $qtyAuthorized = $returnItemData->getQtyAuthorized();
	    	    $qtyReceived = $returnItemData->getQtyReceived();
	    	    if($model->getStatus() == "authorized" && $qtyRequested > 0){
		    	$returnItemData->setQtyAuthorized($qtyRequested)->save();
	    	    }else if($model->getStatus() == "received" && $qtyAuthorized > 0 && $qtyRequested > 0){
		    	$returnItemData->setQtyReceived($qtyRequested)->save();
	   	    }else if($model->getStatus() == "processed_closed" && $qtyReceived > 0 && $qtyRequested > 0){
	           	 $returnItemData->setQtyApproved($qtyRequested)->save();	    
		    }else if($model->getStatus() == "processed_closed" && $qtyAuthorized > 0 && $qtyRequested > 0){
			 $returnItemData->setQtyReceived($qtyRequested)->save();
			 $returnItemData->setQtyApproved($qtyRequested)->save();
		    }
	       }
	    } 
			//$model->save();
            $statusChanged = true;
        }

        // Add message.
        $message = $model->addMessage(
            Message::FROM_MANAGER,
            $request->getParam('comment'),
            $request->getParam(MessageUploader::FILE_FIELD_NAME),
            false,
            $request->getParam('comment_is_internal')
        );

        // Send email.
        $email = $this->emailFactory->create()
            ->setReturns($model)
            ->setMessage($message);

        // New object after save.
        if ($model->isObjectNew()) {
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

            // Send emails
            if ($model->getManagerId() != $this->_auth->getUser()->getId()) {
                $email->notifyManagerAboutCreate(
                    $this->_auth->getUser()
                );
            }

            if ($request->getParam('comment_send_email')
                && ! $request->getParam('comment_is_internal')) {
                $email->notifyCustomerAboutCreate();
            }
        } else {
            // Add system message if status is changed.
            $systemMessage = null;
            if ($statusChanged) {
                $systemMessage = $model->addMessage(
                    Message::FROM_MANAGER,
                    __('Status of return request has been updated to: %1', $model->getStatusLabel()),
                    null,
                    true,
                    // If manager has added previous message as internal then status message make internal too
                    $request->getParam('comment_is_internal')
                );
            }

            // If return is updated, send emails only if message exists
            if ($message || $systemMessage) {
                // If message is empty then use system message. Othervise use message as the primary
                if (! $message) {
                    $email->setMessage($systemMessage);
                }

                if ($model->getManagerId() != $this->_auth->getUser()->getId()) {
                    $email->notifyManagerAboutUpdate(
                        $this->_auth->getUser()
                    );
                }

                if ($request->getParam('comment_send_email')
                    && ! $request->getParam('comment_is_internal')
                ) {
                    $email->notifyCustomerAboutUpdate();
                }
            }
        }
    }

    public function _saveAction()
    {
        $request = $this->getRequest();
		$post = $this->getRequest()->getPostValue();
		//echo '<pre>';print_r($post);die;
		//$entity_id = $post['entity_id'];
		if(isset($post['entity_id'])) {
		//if(!empty($entity_id)) {
		$reason_id = (int)$post['reason_id'];
		$condition_id = (int)$post['condition_id'];
		$resolution_id = (int)$post['resolution_id'];
		}
        $model = $this->_getModel();

        if (!$request->isPost()) {
            $this->getResponse()->setRedirect($this->_redirect->getRefererUrl());
        }

        try {
            $date = $this->dateTime->gmtDate();

            $model->addData($request->getParams())
                ->setUpdatedAt($date);

			if(isset($post['note'])){
				  $note = $post['note'];
				  $parent_id = $model['entity_id'];
				if(isset($parent_id)){
					if(!empty($parent_id)) {
					  $order_id = $post['order_id'];
					  $manager_id = $post['manager_id'];
					  $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
					  $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
					  $connection = $resource->getConnection();
					  $tableName = $resource->getTableName('plumrocket_rma_returns_note'); //gives table name with prefix

					 $notefetchsql = "Select 'note' FROM " . $tableName . " where parent_id = ". $parent_id ." AND note = '$note'" ;
					 $noteresult = $connection->fetchRow($notefetchsql);
					 if($noteresult){
					   $notemsg = $noteresult['note'];
					 } else {
					   $notemsg = '';
					 }
					 //if($notemsg != $note){
					 if (strcmp($notemsg, $note) !== 0) {
					 $notesql = "Insert Into " . $tableName . " (entity_id, parent_id, order_id, manager_id, note, created_at, updated_at) Values ('',$parent_id,$order_id,$manager_id,"."'$note','$date','$date')";
					 $connection->query($notesql);
					 }
				  }
                }
            }

            if (!$model->getId()) {
                $model->setCreatedAt($date)
                    ->setReadMarkAt($date);
            }

            if (false === $this->_beforeSave($model, $request)) {
                $this->_redirect($this->_redirect->getRefererUrl());
                return;
            }

            $model->save();

            $this->_afterSave($model, $request);

            // Check which controller use
            if ($model->isClosed()) {
                $controller = 'returnsarchive';
            } else {
                $controller = 'returns';
            }

            $this->messageManager->addSuccessMessage(__($this->_objectTitle.' has been saved.'));
            $this->_setFormData(false);

            if ($request->getParam('back')) {
                $this->_redirect("*/{$controller}/edit", [$this->_idKey => $model->getId()]);
            } else {
                $this->_redirect("*/{$controller}");
            }
            return;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage(nl2br($e->getMessage()));
            $this->_setFormData();
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->_setFormData();
        }

        // $this->_forward('new');
        $this->_redirect($this->_redirect->getRefererUrl());
    }
}
