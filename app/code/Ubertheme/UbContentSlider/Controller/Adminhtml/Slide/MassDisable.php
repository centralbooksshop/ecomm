<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 */
namespace Ubertheme\UbContentSlider\Controller\Adminhtml\Slide;

use Ubertheme\UbContentSlider\Model\Slide;

class MassDisable extends MassEnable
{
    /**
     * @var string success message
     */
    protected $successMessage = 'A total of %1 sliders have been disabled';

    /**
     * @var string error message
     */
    protected $errorMessage = 'An error occurred while disabling sliders.';

    /**
     * @var bool
     */
    protected $isActive = false;

    /**
     * @param Slide $slide
     * @return $this
     */
    protected function runAction(Slide $slide)
    {
        $slide->setIsActive($this->isActive);
        $slide->save();
        return $this;
    }
}
