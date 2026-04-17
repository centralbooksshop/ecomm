<?php
/**
 * Copyright © 2016 Ubertheme. All rights reserved.
 */

namespace Ubertheme\UbThemeHelper\Model\Config\Source;

class PopupEffects implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __("- None -")],
            ['value' => 'ub-effect-1', 'label' => __('Fade in & Scale')],
            ['value' => 'ub-effect-2', 'label' => __('Slide in (right)')],
            ['value' => 'ub-effect-3', 'label' => __('Slide in (bottom)')],
            ['value' => 'ub-effect-4', 'label' => __('Newspaper')],
            ['value' => 'ub-effect-5', 'label' => __('Fall')],
            ['value' => 'ub-effect-6', 'label' => __('Side Fall')],
            ['value' => 'ub-effect-7', 'label' => __('Sticky Up')],
            ['value' => 'ub-effect-8', 'label' => __('3D Flip (horizontal)')],
            ['value' => 'ub-effect-9', 'label' => __('3D Flip (vertical)')],
            ['value' => 'ub-effect-10', 'label' => __('3D Sign')],
            ['value' => 'ub-effect-11', 'label' => __('Super Scaled')],
            ['value' => 'ub-effect-12', 'label' => __('Just Me')],
            ['value' => 'ub-effect-13', 'label' => __('3D Slit')],
            ['value' => 'ub-effect-14', 'label' => __('3D Rotate Bottom')],
            ['value' => 'ub-effect-15', 'label' => __('3D Rotate In Left')],
            ['value' => 'ub-effect-16', 'label' => __('Blur')],
            ['value' => 'ub-effect-17', 'label' => __('Let me in')],
            ['value' => 'ub-effect-18', 'label' => __('Make way!')],
            ['value' => 'ub-effect-19', 'label' => __('Slip from top')]
        ];
    }
}
