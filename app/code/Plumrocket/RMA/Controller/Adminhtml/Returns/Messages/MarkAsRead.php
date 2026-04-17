<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Controller\Adminhtml\Returns\Messages;

use Plumrocket\RMA\Controller\Adminhtml\Returns;

class MarkAsRead extends Returns
{
    /**
     * Mark returns as readed
     *
     * @return json
     */
    public function execute()
    {
        $data = [];
        $id = $this->getRequest()->getParam('id');
        $time = $this->getRequest()->getParam('time');
        if (is_numeric($id) && is_numeric($time)) {
            $this->_getModel()
                ->setReadMarkAt($time)
                ->save();

            $data['success'] = true;
        }
        $this->getResponse()->setBody(json_encode($data));
    }
}
