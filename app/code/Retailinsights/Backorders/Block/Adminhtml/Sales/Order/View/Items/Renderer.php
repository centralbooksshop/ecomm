<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Retailinsights\Backorders\Block\Adminhtml\Sales\Order\View\Items;

use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Framework\Serialize\Serializer\Json;

class Renderer extends \Magento\Bundle\Block\Adminhtml\Sales\Order\View\Items\Renderer
{
   
    private $serializer;
    protected $retailHelper;
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Magento\Framework\Registry $registry,
        \Magento\GiftMessage\Helper\Message $messageHelper,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Retailinsights\Backorders\Helper\Data $retailHelper,
        array $data = [],
        Json $serializer = null
    ) {
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(Json::class);

         $this->retailHelper = $retailHelper;   

        parent::__construct(
            $context,
            $stockRegistry,
            $stockConfiguration,
            $registry,
            $messageHelper,
            $checkoutHelper,
            $data
        );
    }

    public function isBackorder($item) {
      return $this->retailHelper->isBackordred($item);
    }

    

}
