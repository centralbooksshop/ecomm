<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Controller\Adminhtml\Returns\Messages;

class LoadTemplate extends \Plumrocket\RMA\Controller\Adminhtml\Returns
{
    /**
     * Retrieve response template by id
     *
     * @return json
     */
    public function execute()
    {
        $data = [];
        if ($id = $this->getRequest()->getParam('id')) {
            if ($template = $this->responseTemplate->load($id)) {
                $data = $template->getData();
            }
        }
        $this->getResponse()->setBody(json_encode($data));
    }
}
