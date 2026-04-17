<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 */
namespace Ubertheme\UbContentSlider\Controller\Adminhtml\Item;

use Ubertheme\UbContentSlider\Model\Item;

class MassDisable extends MassEnable
{
    /**
     * @var string success message
     */
    protected $successMessage = 'A total of %1 items have been disabled';

    /**
     * @var string error message
     */
    protected $errorMessage = 'An error occurred while disabling slide items.';

    /**
     * @var bool
     */
    protected $isActive = false;

    /**
     * @param Item $item
     * @return $this
     */
    protected function runAction(Item $item)
    {
        $item->setIsActive($this->isActive);
        $item->save();
        return $this;
    }
}
