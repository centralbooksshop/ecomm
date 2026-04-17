<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */

namespace Infomodus\Fedexlabel\Controller\Adminhtml\Conformity;

class NewAction extends \Infomodus\Fedexlabel\Controller\Adminhtml\Conformity
{

    public function execute()
    {
        $this->_forward('edit');
    }
}
