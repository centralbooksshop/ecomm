<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Infomodus\Fedexlabel\Controller\Rma;

class FormPost extends \Infomodus\Fedexlabel\Controller\Rma
{
    /**
     * Customer address edit action
     *
     * @return \Magento\Framework\Controller\Result\Forward
     */
    public function execute()
    {
        $redirectUrl = null;
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }
        $products = $this->getRequest()->getParam('product');
        $order_id = $this->getRequest()->getParam('order_id');
        $order = $this->orderFactory->create()->load($order_id);
        $weight = 0;
        foreach ($order->getAllVisibleItems() as $product) {
            if (array_key_exists($product->getId(), $products)
                && is_numeric($products[$product->getId()])
                && $products[$product->getId()] <= $product->getQtyShipped()) {
                $weight += $product->getWeight()*$products[$product->getId()];
            }
        }
        $this->_handy->intermediate($order, 'refund');
        $this->_handy->defConfParams['package']= [$this->_handy->defPackageParams[0]];
        $this->_handy->defConfParams['package'][0]['weight'] = $weight;
        $this->_handy->getLabel(null, 'refund', null, $this->_handy->defConfParams);
        if ($this->_handy->label[0]->getLstatus()==0) {
            $this->messageManager->addSuccessMessage(__('Label(s) was created'));
            try {
                $to = $this->senderResolver->resolve($this->_scopeConfig->getValue('fedexlabel/return/refundaccess_admin_email'));
                $this->_inlineTranslation->suspend();
                $transport = $this->_transportBuilder
                    ->setTemplateIdentifier($this->_scopeConfig->getValue(
                        'fedexlabel/return/refundaccess_email_template'))
                    ->setTemplateOptions([
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                    ])
                    ->setTemplateVars(['order_id' => $order->getIncrementId(), 'tracking_number' => $this->_handy->label[0]->getTrackingnumber(), 'date' => date('Y-m-d H:i:s'), 'customer_name' => $order->getBillingAddress()->getFirstName().' '.$order->getBillingAddress()->getLastName()])
                    ->setFromByScope($this->_scopeConfig->getValue('fedexlabel/return/refundaccess_admin_email'))
                    ->addTo($to['email'], $to['name']);
                $transport = $transport->getTransport();
                $transport->sendMessage();
                $this->_inlineTranslation->resume();
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Error in sending email'));
                $this->_handy->_conf->log($e->getMessage(), $e->getTrace());
            }
        } else {
            $this->messageManager->addErrorMessage(__('Label(s) was not created'));
        }

        if(!$order->getCustomerIsGuest()) {
            return $this->resultRedirectFactory->create()->setUrl($this->_buildUrl('sales/order/view',
                ['order_id' => $order_id]));
        } else {
            return $this->resultRedirectFactory->create()->setUrl($this->_buildUrl('sales/guest/view',
                ['order_id' => $order_id]));
        }
    }
}
