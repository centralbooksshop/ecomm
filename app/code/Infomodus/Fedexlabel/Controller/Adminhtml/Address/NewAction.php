<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */

namespace Infomodus\Fedexlabel\Controller\Adminhtml\Address;

class NewAction extends \Infomodus\Fedexlabel\Controller\Adminhtml\Address
{

    public function execute()
    {
        $this->_forward('edit');
    }
}
