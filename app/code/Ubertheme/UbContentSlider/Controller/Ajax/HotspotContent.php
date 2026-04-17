<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 */

namespace Ubertheme\UbContentSlider\Controller\Ajax;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;

class HotspotContent extends Action
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var JsonFactory
     */
    protected $_resultJsonFactory;

    /**
     * HotspotContent constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_resultJsonFactory = $resultJsonFactory;

        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->_resultJsonFactory->create();
        $resultPage = $this->_resultPageFactory->create();

        $productId = $this->getRequest()->getParam('product_id');
        $productSKU = $this->getRequest()->getParam('product_sku');

        /** @var \Ubertheme\UbContentSlider\Block\Ajax\HotspotContent $block */
        $block = $resultPage->getLayout()
            ->createBlock('Ubertheme\UbContentSlider\Block\Ajax\HotspotContent')
            ->setTemplate('Ubertheme_UbContentSlider::ajax/hotspot_content.phtml')
            ->setData('product_id', $productId)
            ->setData('product_sku', $productSKU);
        $html = $block->toHtml();

        $result->setData([
            'html' => $html
        ]);

        return $result;
    }
}
