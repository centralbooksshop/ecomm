<?php

namespace Magecomp\Cancelorder\Model;

use Magento\Framework\Model\AbstractModel;

class Cancelorder extends AbstractModel
{
    public function _construct()
    {
        $this->_init('Magecomp\Cancelorder\Model\ResourceModel\Cancelorder');
    }
}