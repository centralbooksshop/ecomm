<?php

namespace Shipsy\EcommerceExtension\Controller\Adminhtml\Order;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Api\OrderManagementInterface;

class Details extends \Magento\Sales\Controller\Adminhtml\Order
{
    public function execute()
    {
        $selected = $this->getRequest()->getParam('selected');
        $orderID = implode(',', $selected);
        $paramsToSend = ["id" => $orderID];
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('softdatasync/manageorders/index', $paramsToSend);
    }
}
