<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Block\Returns;

use Plumrocket\RMA\Block\Returns\TemplateTrait;
use Plumrocket\RMA\Helper\Data;

class Template extends \Magento\Framework\View\Element\Template
{
    use TemplateTrait;

    /**
     * @return string
     */
    public function getActionUrl()
    {
        if ($this->isNewEntity()) {
            return $this->getUrl('*/*/createPost');
        } else {
            return $this->getUrl('*/*/save');
        }
    }

    /**
     * @return string
     */
    public function getRememberFormUrl()
    {
        return $this->getUrl(Data::SECTION_ID . '/returns/rememberForm');
    }

    /**
     * Get cms static block html
     *
     * @param  int $id
     * @return string
     */
    public function getCmsBlockHtml($id)
    {
        return $this->getLayout()
            ->createBlock('Magento\Cms\Block\Block')
            ->setBlockId($id)
            ->toHtml();
    }
}
