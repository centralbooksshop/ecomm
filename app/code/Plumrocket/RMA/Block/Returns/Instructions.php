<?php
/**
 * @package     Plumrocket_RMA
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\RMA\Block\Returns;

use Plumrocket\RMA\Model\Config\Source\ReturnsStatus;

class Instructions extends \Plumrocket\RMA\Block\Returns\Template
{
    /**
     * Instructions block html
     *
     * @var string
     */
    protected $instructions = null;

    /**
     * Check if need to show instructions
     *
     * @return boolean
     */
    public function showInstructions()
    {
        return ReturnsStatus::STATUS_PROCESSED_CLOSED !== $this->returnsHelper->getStatus($this->getEntity())
            && $this->returnsHelper->hasAuthorized($this->getEntity());
    }

    /**
     * Get instructions text
     *
     * @return string
     */
    public function getInstructions()
    {
        if (null === $this->instructions) {
            $this->instructions = $this->getCmsBlockHtml(
                $this->getConfigHelper()->getReturnInstructionsBlock()
            );
        }

        return $this->instructions;
    }

    /**
     * Check if buttons exist in block html
     *
     * @return boolean
     */
    public function hasButtons()
    {
        $html = $this->getInstructions();
        return false !== mb_strpos($html, 'prrma-instructions-buttons');
    }
}
