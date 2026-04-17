<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Block\Returns\Messages;

use Plumrocket\RMA\Block\Returns\TemplateTrait;
use Plumrocket\RMA\Helper\Data;

class Uploader extends \Plumrocket\RMA\Block\File\Uploader
{
    use TemplateTrait;

    /**
     * Name of form element
     */
    const FILE_FIELD_NAME = 'comment_file';

    /**
     * {@inheritdoc}
     */
    public function getSubmitUrl()
    {
        $params = [];
        if ($this->isNewEntity()) {
            $params['order_id'] = $this->getOrder()->getId();
        } else {
            $params['id'] = $this->getEntity()->getId();
        }

        return $this->_urlBuilder->getUrl(
            Data::SECTION_ID . '/returns/messages_upload',
            $params
        );
    }
}
