<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Number
 */

/**
 * Copyright © 2015 Amasty. All rights reserved.
 */

namespace Amasty\Number\Plugin;

class SequenceManagerPlugin
{
    /**
     * @var string
     */
    protected $type = 'order';

    /**
     * @var \Amasty\Number\Model\SequenceConfiguration
     */
    private $sequenceConfiguration;

    public function __construct(\Amasty\Number\Model\SequenceConfiguration $sequenceConfiguration)
    {

        $this->sequenceConfiguration = $sequenceConfiguration;
    }

    /**
     * @param \Magento\SalesSequence\Model\Manager $subject
     * @param string                               $entityType
     * @param int                                  $storeId
     */
    public function beforeGetSequence(\Magento\SalesSequence\Model\Manager $subject, $entityType, $storeId)
    {
        $this->sequenceConfiguration->setEntityType($entityType)
            ->setStoreId($storeId);
    }
}
