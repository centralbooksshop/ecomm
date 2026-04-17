<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 */

namespace Ubertheme\Base\Plugin\Controller\Wishlist\Index;

use Magento\Framework\App\Action;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Add
 * @package Ubertheme\Base\Plugin\Controller\Wishlist\Index
 */
class Add extends \Magento\Wishlist\Controller\Index\Add
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonEncode;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $resultRedirect;

    /**
     * Add constructor.
     * @param Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Wishlist\Controller\WishlistProviderInterface $wishlistProvider
     * @param ProductRepositoryInterface $productRepository
     * @param Validator $formKeyValidator
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Json\Helper\Data $jsonEncode
     * @param \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory
     */
    public function __construct(
        Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Wishlist\Controller\WishlistProviderInterface $wishlistProvider,
        ProductRepositoryInterface $productRepository,
        Validator $formKeyValidator,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Json\Helper\Data $jsonEncode,
        \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory
    )
    {
        parent::__construct($context, $customerSession, $wishlistProvider, $productRepository, $formKeyValidator);

        $this->storeManager = $storeManager;
        $this->jsonEncode = $jsonEncode;
        $this->resultRedirect = $redirectFactory;
    }

    /**
     * @param $subject
     * @param \Closure $proceed
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws NoSuchEntityException
     */
    public function aroundExecute($subject, \Closure $proceed)
    {
        $result = [];
        $params = $subject->getRequest()->getParams();
        $product = $this->_initProduct($subject);

        if (isset($params['isAjaxWishlist']) && $params['isAjaxWishlist']) {
            $proceed();
            $result['success'] = true;
            $result['message'] = __("%1 has been added to your Wish List", $product->getName());
            $subject->getResponse()->representJson($this->jsonEncode->jsonEncode($result));
        } else {
            $proceed();
            return $this->resultRedirect->create()->setPath('*');
        }
    }

    /**
     * @param $subject
     * @return bool|\Magento\Catalog\Api\Data\ProductInterface
     * @throws NoSuchEntityException
     */
    protected function _initProduct($subject)
    {
        $productId = (int)$subject->getRequest()->getParam('product');
        if ($productId) {
            $storeId = $this->storeManager->getStore()->getId();
            try {
                $product = $this->productRepository->getById($productId, false, $storeId);
                return $product;
            } catch (NoSuchEntityException $e) {
                return false;
            }
        }
        return false;
    }
}
