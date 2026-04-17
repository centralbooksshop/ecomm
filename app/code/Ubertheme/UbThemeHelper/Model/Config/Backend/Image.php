<?php
/**
 * Copyright © 2016 Ubertheme. All rights reserved.
 */

/**
 * System config image field backend model
 */
namespace Ubertheme\UbThemeHelper\Model\Config\Backend;

class Image extends File
{
    /**
     * Getter for allowed extensions of uploaded files
     *
     * @return string[]
     */
    protected function _getAllowedExtensions()
    {
        return ['jpg', 'jpeg', 'gif', 'png'];
    }
}
