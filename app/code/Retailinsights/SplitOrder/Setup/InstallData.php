<?php

namespace Retailinsights\SplitOrder\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\StatusFactory;
use Magento\Sales\Model\ResourceModel\Order\StatusFactory as StatusResourceFactory;


class InstallData implements InstallDataInterface
{
    /**
     * Order StatusFactory
     *
     * @var \Magento\Sales\Model\Order\StatusFactory
     */
    protected $statusFactory;

    /**
     * Order StatusResourceFactory
     *
     * @var \Magento\Sales\Model\ResourceModel\Order\StatusFactory
     */
    protected $statusResourceFactory;

    /**
     * Split Order-Status code
     */
    const ORDER_STATUS_SPLITORDER_CODE = 'order_split';

    /**
     * Split Order-Status label
     */
    const ORDER_STATUS_SPLITORDER_LABEL = 'Order Split';

    /**
     * UpgradeData Constructor
     *
     * @param StatusFactory         $statusFactory         statusFactory
     * @param StatusResourceFactory $statusResourceFactory statusResourceFactory
     */
    public function __construct(
        StatusFactory $statusFactory,
        StatusResourceFactory $statusResourceFactory
    ) {
        $this->statusFactory = $statusFactory;
        $this->statusResourceFactory = $statusResourceFactory;
    }

    /**
     * Install data
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Validate_Exception
     */
    public function install(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $statusResource = $this->statusResourceFactory->create();
        $status = $this->statusFactory->create();
        $status->setData([
            'status' => self::ORDER_STATUS_SPLITORDER_CODE,
            'label' => self::ORDER_STATUS_SPLITORDER_LABEL,
        ]);

        $statusResource->save($status);

        $status->assignState(Order::STATE_PROCESSING, false, true);
    }
}
