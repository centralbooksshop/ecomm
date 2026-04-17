<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Controller\Returns\Track;

use Magento\Framework\Controller\ResultFactory;
use Plumrocket\RMA\Controller\AbstractReturns;
use Plumrocket\RMA\Model\Returns\Track;

class Remove extends AbstractReturns
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $response = ['messages' => []];
        $request = $this->getRequest();

        if (! $this->configHelper->enabledTrackingNumber()) {
            $response['messages'][] = __('Tracking is disabled for the customers');
            return $resultJson->setData($response);
        }

        try {
            $model = $this->getModel();
            if ($model->isClosed()) {
                throw new \Exception(__('Return is closed'));
            }

            if ($model->isVirtual()) {
                throw new \Exception(__('Return is virtual'));
            }

            $track = $model->getTrackById($request->getParam('track_id'));
            if ($track
                && $track->getId()
                && $track->getType() === Track::FROM_CUSTOMER
            ) {
                $track->delete();
                $model->setUpdatedAt($this->dateTime->gmtDate())->save();
                $response['success'] = true;
                $response['messages'][] = __('Tracking number has been removed');
            } else {
                $response['messages'][] = __('This tracking number cannot be removed');
            }
        } catch (\Exception $e) {
            $response['messages'][] = __('Unknown Error');
        }

        return $resultJson->setData($response);
    }

    /**
     * {@inheritdoc}
     */
    public function canViewOrder()
    {
        // Client cannot have separate order on this page
        return false;
    }
}
