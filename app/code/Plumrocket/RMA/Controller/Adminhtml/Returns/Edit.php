<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Controller\Adminhtml\Returns;

class Edit extends \Plumrocket\RMA\Controller\Adminhtml\Returns
{
    protected function _beforeAction()
    {
        // Load form data in local storage and clear form data from session.
        $this->dataHelper->getFormData();
        $this->dataHelper->setFormData(false);
    }
}
