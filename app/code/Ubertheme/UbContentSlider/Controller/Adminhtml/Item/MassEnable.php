<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 */
namespace Ubertheme\UbContentSlider\Controller\Adminhtml\Item;

use Ubertheme\UbContentSlider\Model\Item;

class MassEnable extends MassAction
{
    /**
     * @var string success message
     */
    protected $successMessage = 'A total of %1 slide items have been enabled';

    /**
     * @var string error message
     */
    protected $errorMessage = 'An error occurred while enabling slide items.';

    /**
     * @var bool
     */
    protected $isActive = true;

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
