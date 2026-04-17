<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Plugin;

use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\RequestInterface;
use Plumrocket\RMA\Helper\Data;
use Plumrocket\RMA\Helper\Returnrule;
use Plumrocket\RMA\Model\Config\Source\Position;

class QuotePlugin
{
    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var Returnrule
     */
    protected $returnruleHelper;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @param Data             $dataHelper
     * @param Returnrule       $returnruleHelper
     * @param RequestInterface $httpRequest
     */
    public function __construct(
        Data $dataHelper,
        Returnrule $returnruleHelper,
        RequestInterface $httpRequest
    ) {
        $this->dataHelper = $dataHelper;
        $this->returnruleHelper = $returnruleHelper;
        $this->request = $httpRequest;
    }

    /**
     * Method calls after all checkout items were retrieved
     *
     * @param $subject
     * @param \Magento\Quote\Model\Quote\Item[] $items
     * @return \Magento\Quote\Model\Quote\Item[]
     */
    public function afterGetAllItems($subject, $items)
    {
        if ($this->dataHelper->moduleEnabled()) {
            switch (true) {
                case $this->returnruleHelper->showPosition(Position::CHECKOUT)
                    && false !== strpos((string) $this->request->getModuleName(), 'checkout')
                    && $this->request->getControllerName()    == 'index'
                    && $this->request->getActionName()        != 'success':
                case $this->returnruleHelper->showPosition(Position::CHECKOUT)
                    && $this->request->getModuleName() == 'onestepcheckout':

                    if (is_array($items)) {
                        $this->returnruleHelper->setAdditionalOption($items);
                    }
                    break;
            }
        }

        return $items;
    }
}
