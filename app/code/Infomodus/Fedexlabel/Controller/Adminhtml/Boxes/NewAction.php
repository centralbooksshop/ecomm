<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */

namespace Infomodus\Fedexlabel\Controller\Adminhtml\Boxes;

class NewAction extends \Infomodus\Fedexlabel\Controller\Adminhtml\Boxes
{

    public function execute()
    {
        $this->_forward('edit');
    }
}
