<?php
/**
 *
 * Copyright © 2016 Ubertheme.com All rights reserved.
 *
 */
namespace Ubertheme\UbContentSlider\Controller\Adminhtml\Item;

class AjaxValidateSku extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Ubertheme_UbContentSlider::item_save';

    /** @var \Magento\Framework\Controller\Result\Raw */
    protected $_rawResult;

    /** @var \Magento\Framework\Json\Encoder  */
    protected $_jsonEncoder;

    /** @var \Magento\Catalog\Model\ProductRepository $productRepository */
    protected $_productRepository;

    /**
     * AjaxValidateSku constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Json\Encoder $jsonEncoder
     * @param \Magento\Framework\Controller\Result\Raw $rawResult
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Json\Encoder $jsonEncoder,
        \Magento\Framework\Controller\Result\Raw $rawResult,
        \Magento\Catalog\Model\ProductRepository $productRepository
    )
    {
        $this->_jsonEncoder = $jsonEncoder;
        $this->_rawResult = $rawResult;
        $this->_productRepository = $productRepository;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $productSKU = $this->getRequest()->getParam('product_sku');
        $result = [
            'found' => false,
            'message' => ''
        ];
        if (!empty($productSKU)) {
            try {
                $product = $this->_productRepository->get($productSKU);
                if ($product->getId()) {
                    $result['found'] = true;
                } else {
                    $result['message'] = __('This SKU does not exist.');
                }
            } catch (\Exception $e) {
                //$result['message'] = $e->getMessage();
                $result['message'] = __('This SKU does not exist.');
            }
        } else {
            // display error message
            $result['message'] = __('Entering SKU is required.');
        }

        $this->_rawResult->setHeader('Content-type', 'application/json');
        return $this->_rawResult->setContents($this->_jsonEncoder->encode($result));
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(self::ADMIN_RESOURCE);
    }
}
