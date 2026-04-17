<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */

namespace Infomodus\Fedexlabel\Controller\Adminhtml\Items;

class Editone extends \Infomodus\Fedexlabel\Controller\Adminhtml\Items
{
    public function execute()
    {
        $this->_initAction();
        $this->_view->getLayout()->getBlock('items_items_editone');
        $this->_view->renderLayout();
    }
}
