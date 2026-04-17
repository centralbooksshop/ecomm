<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */

namespace Infomodus\Fedexlabel\Model;

use Magento\Framework\Model\AbstractModel;

class Boxes extends AbstractModel
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Infomodus\Fedexlabel\Model\ResourceModel\Boxes');
    }
}
