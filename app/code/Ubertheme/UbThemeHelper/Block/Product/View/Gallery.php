<?php

namespace Ubertheme\UbThemeHelper\Block\Product\View;

class Gallery extends \Magento\Catalog\Block\Product\View\Gallery
{
    /**
     * @var \Ubertheme\UbThemeHelper\App\Config
     */
    protected $_themeConfig;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\Stdlib\ArrayUtils $arrayUtils
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param array $data
     * @param \Ubertheme\UbThemeHelper\App\Config $themeConfig
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Stdlib\ArrayUtils $arrayUtils,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Ubertheme\UbThemeHelper\App\Config $themeConfig,
		array $data = []
    ) {
        parent::__construct($context, $arrayUtils, $jsonEncoder, $data);

        $this->_themeConfig = $themeConfig;
    }

    public function getThemeConfig($fullPath)
    {
        return $this->_themeConfig->getValue($fullPath);
    }
}
