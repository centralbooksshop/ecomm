<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Cron;

use Plumrocket\RMA\Model\Config\Source\ReturnsStatus;
use Plumrocket\RMA\Model\Returns\Message;

class AutoClose
{
    /**
     * @var \Plumrocket\RMA\Helper\Data
     */
    protected $dataHelper;

    /**
     * @var \Plumrocket\RMA\Helper\Config
     */
    protected $configHelper;

    /**
     * @var \Plumrocket\RMA\Helper\Returns
     */
    protected $returnsHelper;

    /**
     * @var \Plumrocket\RMA\Model\ResourceModel\Returns\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * Statuses for close
     *
     * @var array
     */
    protected $statuses = [
        ReturnsStatus::STATUS_NEW,
        ReturnsStatus::STATUS_REJECTED_PART,
        ReturnsStatus::STATUS_AUTHORIZED_PART,
        ReturnsStatus::STATUS_AUTHORIZED,
    ];

    /**
     * @param \Plumrocket\RMA\Helper\Data                                   $dataHelper
     * @param \Plumrocket\RMA\Helper\Config                                 $configHelper
     * @param \Plumrocket\RMA\Helper\Returns                                $returnsHelper
     * @param \Plumrocket\RMA\Model\ResourceModel\Returns\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Plumrocket\RMA\Helper\Data $dataHelper,
        \Plumrocket\RMA\Helper\Config $configHelper,
        \Plumrocket\RMA\Helper\Returns $returnsHelper,
        \Plumrocket\RMA\Model\ResourceModel\Returns\CollectionFactory $collectionFactory
    ) {
        $this->dataHelper = $dataHelper;
        $this->configHelper = $configHelper;
        $this->returnsHelper = $returnsHelper;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Automatically close the inactive returns
     *
     * @return void
     */
    public function execute()
    {
        if ($this->dataHelper->moduleEnabled()) {

            $formatter = new \IntlDateFormatter(
                null,
                \IntlDateFormatter::FULL,
                \IntlDateFormatter::FULL,
                null,
                null,
                'YYYY-MM-dd hh:mm:ss'
            );

            // Count of days
            $offset = (int)$this->configHelper->getAutoClose() * 86400;
            if ($offset > 0) {
                $collection = $this->collectionFactory->create()
                    ->addNotArchiveFilter()
                    ->addFieldToFilter('updated_at', [
                        'lteq' => $formatter->format(time() - $offset)
                    ]);
                    //->addFieldToFilter('status', ['in' => $this->statuses]);

                foreach ($collection as $returns) {
                    $this->cancel($returns);
                }
            }
        }
    }

    /**
     * Cancel return
     *
     * @param  \Plumrocket\RMA\Model\Returns $returns
     * @return void
     */
    private function cancel($returns)
    {
        try {
            $returns
                ->setIsClosed(true);
             $status = $this->returnsHelper->getStatus($returns);
             $returns
                ->setStatus($status)
                ->save();

            // Add system message.
            $systemMessage = $returns->addMessage(
                Message::FROM_SYSTEM,
                __('Return request has been automatically closed'),
                null,
                true
            );
        } catch (\Exception $e) {
            // Do nothing.
        }
    }
}
