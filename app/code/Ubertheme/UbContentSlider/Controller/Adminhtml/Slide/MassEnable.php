<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 */
namespace Ubertheme\UbContentSlider\Controller\Adminhtml\Slide;

use Ubertheme\UbContentSlider\Model\Slide;

class MassEnable extends MassAction
{
    /**
     * @var string success message
     */
    protected $successMessage = 'A total of %1 sliders have been enabled';

    /**
     * @var string error message
     */
    protected $errorMessage = 'An error occurred while enabling sliders.';

    /**
     * @var bool
     */
    protected $isActive = true;

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
