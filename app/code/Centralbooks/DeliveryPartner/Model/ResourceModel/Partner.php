<?php
declare(strict_types=1);

namespace Centralbooks\DeliveryPartner\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Partner extends AbstractDb
{

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init('centralbooks_deliverypartner_partner', 'partner_id');
    }
}

