<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 */
namespace Ubertheme\UbContentSlider\Controller\Adminhtml\Item;

use Ubertheme\UbContentSlider\Model\Item;

class MassDelete extends MassAction
{
    /**
     * @var string success message
     */
    protected $successMessage = 'A total of %1 record(s) have been deleted';

    /**
     * @var string error message
     */
    protected $errorMessage = 'An error occurred while deleting record(s).';

    /**
     * @param $item
     * @return $this
     */
    protected function runAction(Item $item)
    {
        $item->delete();
        return $this;
    }
}
