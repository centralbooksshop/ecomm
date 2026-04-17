<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Block\Returns;

use Plumrocket\RMA\Helper\Data;

class History extends \Plumrocket\RMA\Block\Returns\Template
{
    /**
     * Image size
     */
    const IMAGE_WIDTH = 40;
    const IMAGE_HEIGHT = 40;

    /**
     * @var string
     */
    protected $_template = 'returns/history.phtml';

    /**
     * @var \Plumrocket\RMA\Model\ResourceModel\Returns\CollectionFactory
     */
    protected $returnsCollectionFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Plumrocket\RMA\Model\ResourceModel\Returns\Collection
     */
    protected $returnsCollection;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Plumrocket\RMA\Model\ResourceModel\Returns\CollectionFactory $returnsCollectionFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Plumrocket\RMA\Model\ResourceModel\Returns\CollectionFactory $returnsCollectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    ) {
        $this->returnsCollectionFactory = $returnsCollectionFactory;
        $this->customerSession = $customerSession;
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('My Returns'));
    }

    /**
     * @return bool|\Plumrocket\RMA\Model\ResourceModel\Returns\Collection
     */
    public function getReturnsCollection()
    {
        $customerId = $this->customerSession->getCustomerId();
        $orderId = $this->getOrderId();

        // This may be insecure. Additional control need to add to controller
        if (! $customerId && ! $orderId) {
            return false;
        }

        if (! $this->returnsCollection) {
            $this->returnsCollection = $this->returnsCollectionFactory->create()
            ->addFieldToSelect('*')
            ->addOrderData()
            ->setOrder(
                'updated_at',
                'desc'
            );

            if ($customerId) {
                $this->returnsCollection
                    ->addFieldToFilter('o.customer_id', $customerId);
            }

            if ($orderId) {
                $this->returnsCollection
                    ->addFieldToFilter('o.entity_id', $orderId);
            }
        }
        return $this->returnsCollection;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getReturnsCollection()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'sales.order.history.pager'
            )->setCollection(
                $this->getReturnsCollection()
            );
            $this->setChild('pager', $pager);
            $this->getReturnsCollection()->load();
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * Get current order id
     *
     * @return int|null
     */
    protected function getOrderId()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        if (! is_numeric($orderId) || $orderId <= 0) {
            return null;
        }

        return $orderId;
    }

    /**
     * @param object $returns
     * @return string
     */
    public function getViewUrl($returns)
    {
        return $this->returnsHelper->getViewUrl($returns);
    }

    /**
     * Get image of one item
     *
     * @param \Plumrocket\RMA\Model\Returns $returns
     * @return string
     */
    public function getImageUrl($returns)
    {
        $url = '';
        $items = $returns->getItems();

        $item = reset($items);
        $url = $this->itemHelper->getImageUrl(
            $item->getOrderItem(),
            self::IMAGE_WIDTH,
            self::IMAGE_HEIGHT
        );

        return $url;
    }

    /**
     * Get counter of items
     *
     * @param  object $returns
     * @return string
     */
    public function getItemsCounter($returns)
    {
        $items = $returns->getItems();
        return count($items) - 1;
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('customer/account/');
    }
}
