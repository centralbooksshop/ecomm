<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */

namespace Infomodus\Fedexlabel\Controller\Adminhtml\Items;

class NewAction extends \Infomodus\Fedexlabel\Controller\Adminhtml\Items
{

    public function execute()
    {
        $this->_forward('editone');
    }
}
