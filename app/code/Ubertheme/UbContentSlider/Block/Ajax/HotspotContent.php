<?php
/**
 * Copyright © 2015 Ubertheme. All rights reserved.
 */
namespace Ubertheme\UbContentSlider\Block\Ajax;

class HotspotContent extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Catalog\Model\ProductRepository $productRepository
     */
    protected $_productRepository;

    /**
     * @var \Magento\Catalog\Block\Product\ImageBuilder
     */
    protected $_imageBuilder;

    /**
     * HotspotContent constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder,
        array $data = []
    )
    {
        $this->_productRepository = $productRepository;
        $this->_imageBuilder = $imageBuilder;
        parent::__construct($context, $data);
    }

    protected function _toHtml()
    {
        $sku = $this->getData('product_sku');
        $product = null;
        if ($sku) {
            $product = $this->getProductBySku($sku);
        }
        $this->assign('product', $product);

        return $this->fetchView($this->getTemplateFile());
    }

    public function getProductBySku($sku)
    {
        return $this->_productRepository->get($sku);
    }

    public function getProductById($id)
    {
        return $this->_productRepository->getById($id);
    }

    /**
     * Return HTML block with tier price
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $priceType
     * @param string $renderZone
     * @param array $arguments
     * @return string
     */
    public function getProductPriceHtml(
        \Magento\Catalog\Model\Product $product,
        $priceType,
        $renderZone = \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST,
        array $arguments = []
    ) {
        if (!isset($arguments['zone'])) {
            $arguments['zone'] = $renderZone;
        }

        /** @var \Magento\Framework\Pricing\Render $priceRender */
        $priceRender = $this->getLayout()->getBlock('product.price.render.default');
        $price = '';

        if ($priceRender) {
            $price = $priceRender->render($priceType, $product, $arguments);
        }
        return $price;
    }

    /**
     * Retrieve product image
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $imageId
     * @param array $attributes
     * @return \Magento\Catalog\Block\Product\Image
     */
    public function getImage($product, $imageId, $attributes = [])
    {
        return $this->_imageBuilder->create($product, $imageId, $attributes);
    }

}
