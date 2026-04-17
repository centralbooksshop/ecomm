<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.

 */
namespace Ubertheme\UbContentSlider\Model\Slide\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class IsActive
 */
class IsActive implements OptionSourceInterface
{
    /**
     * @var \Ubertheme\UbContentSlider\Model\Slide
     */
    protected $slide;

    /**
     * Constructor
     *
     * @param \Ubertheme\UbContentSlider\Model\Slide $slide
     */
    public function __construct(\Ubertheme\UbContentSlider\Model\Slide $slide)
    {
        $this->slide = $slide;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $availableOptions = $this->slide->getAvailableStatuses();
        $options = [];
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}
