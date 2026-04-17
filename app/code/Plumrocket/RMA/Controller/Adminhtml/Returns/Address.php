<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Controller\Adminhtml\Returns;

use Plumrocket\RMA\Controller\Adminhtml\Returns;
use Plumrocket\RMA\Helper\Data;

class Address extends Returns
{
    /**
     * Form session key
     *
     * @var string
     */
    protected $_formSessionKey  = 'rma_returns_address_form_data';

    /**
     * Id param name
     *
     * @var string
     */
    protected $_idKey = 'parent_id';

    /**
     * Edit returns address form
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $request = $this->getRequest();
        $address = $this->addressFactory->create();

        if ($parentId = $request->getParam('parent_id')) {
            // From return edit page
            $address->load($parentId, 'parent_id');

            $model = $this->_getModel();
            if (! $address->getId() && $model && $model->getId() === $parentId) {
                $address = $model->getOrder()->getShippingAddress();
            }
        } elseif ($orderId = $request->getParam('order_id')) {
            // From return create page
            if ($unassignedAddress = $address->getUnassigned($orderId)) {
                $address = $unassignedAddress;
            } else {
                $order = $this->orderFactory->create()->load($orderId);
                if ($order && $order->getId()) {
                    $address = $order->getShippingAddress();
                }
            }
        }

        $this->coreRegistry->register('returns_address', $address);

        // Load form data in local storage and clear form data from session.
        $this->dataHelper->getFormData();
        $this->dataHelper->addFormData('returns_address', false);

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu($this->_activeMenu);
        $resultPage->getConfig()->getTitle()->prepend(__('Edit Return Address'));

        // Do not display VAT validation button on edit returns address form
        $addressFormContainer = $resultPage->getLayout()->getBlock(
            Data::SECTION_ID . '_returns_address.form.container'
        );
        if ($addressFormContainer) {
            $addressFormContainer->getChildBlock('form')->setDisplayVatValidationButton(false);
        }

        return $resultPage;
    }
}
