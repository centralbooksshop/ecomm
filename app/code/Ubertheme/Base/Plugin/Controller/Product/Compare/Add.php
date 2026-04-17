<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 */

namespace Ubertheme\Base\Plugin\Controller\Product\Compare;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;

/**
 * Class Add
 * @package Ubertheme\Base\Plugin\Controller\Product\Compare
 */
class Add extends \Magento\Catalog\Controller\Product\Compare\Add implements HttpPostActionInterface
{
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_jsonHelper;

    /**
     * Add constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Catalog\Model\Product\Compare\ItemFactory $compareItemFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\Compare\Item\CollectionFactory $itemCollectionFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Model\Visitor $customerVisitor
     * @param \Magento\Catalog\Model\Product\Compare\ListCompare $catalogProductCompareList
     * @param \Magento\Catalog\Model\Session $catalogSession
     * @param StoreManagerInterface $storeManager
     * @param Validator $formKeyValidator
     * @param PageFactory $resultPageFactory
     * @param ProductRepositoryInterface $productRepository
     * @param Data $jsonHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Catalog\Model\Product\Compare\ItemFactory $compareItemFactory,
        \Magento\Catalog\Model\ResourceModel\Product\Compare\Item\CollectionFactory $itemCollectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\Visitor $customerVisitor,
        \Magento\Catalog\Model\Product\Compare\ListCompare $catalogProductCompareList,
        \Magento\Catalog\Model\Session $catalogSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Validator $formKeyValidator,
        PageFactory $resultPageFactory,
        ProductRepositoryInterface $productRepository,
        Data $jsonHelper
    )
    {
        parent::__construct(
            $context,
            $compareItemFactory,
            $itemCollectionFactory,
            $customerSession,
            $customerVisitor,
            $catalogProductCompareList,
            $catalogSession,
            $storeManager,
            $formKeyValidator,
            $resultPageFactory,
            $productRepository
        );

        $this->_jsonHelper = $jsonHelper;
    }

    /**
     * @param \Magento\Catalog\Controller\Product\Compare\Add $subject
     * @param \Closure $proceed
     */
    public function aroundExecute(\Magento\Catalog\Controller\Product\Compare\Add $subject, \Closure $proceed)
    {
        $result = [];
        $params = $subject->getRequest()->getParams();
        $productId = (int)$subject->getRequest()->getParam('product');

        if (isset($params['isAjaxCompare']) && $params['isAjaxCompare']) {
            if ($productId && ($this->_customerVisitor->getId() || $this->_customerSession->isLoggedIn())) {
                try {
                    $product = $this->productRepository->getById(
                        $productId,
                        false,
                        $this->_storeManager->getStore()->getId()
                    );
                } catch (NoSuchEntityException $e) {
                    $product = null;
                }
                if ($product) {
                    $this->_catalogProductCompareList->addProduct($product);
                    $this->_eventManager->dispatch('catalog_product_compare_add_product', ['product' => $product]);

                    $productName = $this->_objectManager->get(
                        \Magento\Framework\Escaper::class
                    )->escapeHtml($product->getName());
                    $result['success'] = true;
                    $result['message'] = __('You added product %1 to the comparison list.', [$productName]);
                    $this->messageManager->addSuccessMessage($result['message']);

                    $subject->getResponse()->representJson($this->_jsonHelper->jsonEncode($result));
                }
                $this->_objectManager->get(\Magento\Catalog\Helper\Product\Compare::class)->calculate();
            }

        } else {
            $result['success'] = false;
            $result['message'] = __('Failure');
            $subject->getResponse()->representJson($this->_jsonHelper->jsonEncode($result));
        }
    }

}