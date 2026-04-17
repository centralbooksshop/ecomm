<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Retailinsights\Orders\Model\Magento\Bundle\Order\Pdf\Items;

use Magento\Framework\App\ObjectManager;
//use Magento\Framework\Serialize\Serializer\Json;

/**
 * Order invoice pdf default items renderer
 */
class Invoice extends \Magento\Bundle\Model\Sales\Order\Pdf\Items\AbstractItems
{
    /**
     * @var \Magento\Framework\Stdlib\StringUtils
     */
    protected $string;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     * @param \Magento\Framework\Stdlib\StringUtils $coreString
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Tax\Helper\Data $taxData,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Magento\Framework\Stdlib\StringUtils $coreString,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        \Magento\Framework\Serialize\Serializer\Json $serializer = null
    ) {
        $this->string = $coreString;
		$this->serializer = $serializer; 
        parent::__construct(
            $context,
            $registry,
            $taxData,
            $filesystem,
            $filterManager,
            $resource,
            $resourceCollection,
            $data
            //$serializer
        );
    }

    /**
     * Draw item line
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function draw()
    {
        $order = $this->getOrder();
        $item = $this->getItem();
        $pdf = $this->getPdf();
        $page = $this->getPage();

        $this->_setFontRegular();
        $items = $this->getChildren($item);

        $prevOptionId = '';
        $drawItems = [];
        foreach ($items as $childItem) {
            $line = [];

            $attributes = $this->getSelectionAttributes($childItem);
            if (is_array($attributes)) {
                $optionId = $attributes['option_id'];
            } else {
                $optionId = 0;
            }

            if (!isset($drawItems[$optionId])) {
                $drawItems[$optionId] = ['lines' => [], 'height' => 15];
            }

            $optional = 0;
            $optional_array =array('language','optional');

            if ($childItem->getOrderItem()->getParentItem()) {
                if ($prevOptionId != $attributes['option_id']) {
                   if($this->findString($optional_array,strtolower($attributes['option_label']))) {
                     $line[0] = [
                        'font' => 'italic',
                        'text' => $this->string->split($attributes['option_label'], 45, true, true),
                        'feed' => 35,
                    ];

                    $drawItems[$optionId] = ['lines' => [$line], 'height' => 15];

                    $line = [];
                    $prevOptionId = $attributes['option_id'];
                    $optional = 1;
                   } 
                }
            }

            /* in case Product name is longer than 80 chars - it is written in a few lines */
            if ($childItem->getOrderItem()->getParentItem()) {
                if($optional) {
                     $feed = 40;
                $name = $this->getValueHtml($childItem).'1';
                } else {
                    $name = '';
                } 
            } else {
                $feed = 35;
                $name = $childItem->getName().'2';
                // $qty = $childItem->getQty();
            }
             
            $line[] = ['text' => $this->string->split($name, 35, true, true), 'feed' => $feed];
        

           // draw SKUs
            // if (!$childItem->getOrderItem()->getParentItem()) {
            //     $text = [];
            //     foreach ($this->string->split($item->getSku(), 17) as $part) {
            //         $text[] = $part;
            //     }
            //     $line[] = ['text' => $text, 'feed' => 255];
            // }
            
            // draw prices
            // if ($this->canShowPriceInfo($childItem)) {
                if (!$childItem->getOrderItem()->getParentItem()) {
                    $orderItems = $order->getAllItems();
                    $total_qty = 0;
                    foreach ($orderItems as $itemA)
                    {
                        if ($itemA->getParentItem()) {
                            $total_qty = $total_qty + $itemA->getQtyOrdered();
                        }
                    }
                    $price = $order->formatPriceTxt($order->getSubtotal());
                    $line[] = ['text' => $price, 'feed' => 395, 'font' => 'bold', 'align' => 'right'];

                    $line[] = ['text' => $childItem->getQty()*1, 'feed' => 495, 'font' => 'bold'];
                    $tax = $order->formatPriceTxt($order->getTaxAmount());
                    $line[] = ['text' => $tax, 'feed' => 435, 'font' => 'bold', 'align' => 'right'];
    
                    $row_total = $order->formatPriceTxt($order->getSubtotal());
                    $line[] = ['text' => $row_total, 'feed' => 565, 'font' => 'bold', 'align' => 'right'];
                }

            // }

            $drawItems[$optionId]['lines'][] = $line;
        }
        // custom options
        $options = $item->getOrderItem()->getProductOptions();
        if ($options) {
            if (isset($options['options'])) {
                foreach ($options['options'] as $option) {
                    $lines = [];
                    $logger->info($this->string->split(
                            $this->filterManager->stripTags($option['label'])));
                    $lines[][] = [
                        'text' => $this->string->split(
                            $this->filterManager->stripTags($option['label']),
                            40,
                            true,
                            true
                        ),
                        'font' => 'italic',
                        'feed' => 35,
                    ];

                    if ($option['value']) {
                        $text = [];
                        $printValue = isset(
                            $option['print_value']
                        ) ? $option['print_value'] : $this->filterManager->stripTags(
                            $option['value']
                        );
                        $values = explode(', ', $printValue);
                        foreach ($values as $value) {
                            foreach ($this->string->split($value, 30, true, true) as $subValue) {
                                  $logger->info($subValue);
                                $text[] = $subValue;
                            }
                        }

                        $lines[][] = ['text' => $text, 'feed' => 40];
                    }

                    $drawItems[] = ['lines' => $lines, 'height' => 15];
                }
            }
        }

        $page = $pdf->drawLineBlocks($page, $drawItems, ['table_header' => true]);

        $this->setPage($page);
    }

  protected function findString($array, $string) {
    foreach ( $array as $a ) {
        if ( strpos ( $string , $a ) !== FALSE )
             return true;
    }
    return false;
} 
}
