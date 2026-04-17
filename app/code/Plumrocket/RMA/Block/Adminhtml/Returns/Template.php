<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Block\Adminhtml\Returns;

use Magento\Backend\Block\Template as BackendTemplate;
use Plumrocket\RMA\Block\Adminhtml\Returns\TemplateTrait;
use Plumrocket\RMA\Helper\Data;

class Template extends BackendTemplate
{
    use TemplateTrait;

    /**
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl(Data::SECTION_ID . '/returns/save');
    }

    /**
     * @return string
     */
    public function getRememberFormUrl()
    {
        return $this->getUrl(Data::SECTION_ID . '/returns/rememberForm');
    }
}
