<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 */
namespace Ubertheme\UbContentSlider\Controller\Adminhtml\Slide;

use Ubertheme\UbContentSlider\Model\Slide;

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
     * @param $slide
     * @return $this
     */
    protected function runAction(Slide $slide)
    {
        $slide->delete();
        return $this;
    }
}
