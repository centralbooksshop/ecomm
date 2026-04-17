<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Block\Adminhtml\Returns\ShippingLabel;

use Plumrocket\RMA\Block\Adminhtml\Returns\TemplateTrait;
use Plumrocket\RMA\Helper\Data;

class Uploader extends \Plumrocket\RMA\Block\File\Uploader
{
    use TemplateTrait;

    /**
     * Name of form element
     */
    const FILE_FIELD_NAME = 'shipping_label_file';

    /**
     * {@inheritdoc}
     */
    public function getSubmitUrl()
    {
        return $this->_urlBuilder->getUrl(
            Data::SECTION_ID . '/returns/shippinglabel_upload'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getMaxFilesCount()
    {
        return $this->getConfigHelper()->getShippingLabelCount();
    }
}
