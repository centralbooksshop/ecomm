<?php

/**
 * Copyright © 2017 Ubertheme.com All rights reserved.
 */

namespace Ubertheme\Base\Block\Ajax;

use \Magento\Framework\View\Element\Template;
use \Magento\Framework\View\Element\Template\Context;
use \Ubertheme\Base\Helper\Data as DataHelper;

/**
 * Class Wishlist
 * @package Ubertheme\Base\Block\Ajax
 */
class Wishlist extends Template
{

    /** @var DataHelper $helper */
    protected $helper;

    /**
     * Compare constructor.
     * @param Context $context
     * @param array $data
     * @param DataHelper $helper
     */
    public function __construct(
        Context $context,
        DataHelper $helper,
		array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->helper = $helper;
    }

    public function getAjaxWishlistOptions()
    {
        return $this->helper->getAjaxWishlistOptions();
    }
}
