<?php
/**
 * Webkul Software.
 *
 *
 * @category  Webkul
 * @package   Webkul_DeliveryBoy
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */
namespace Webkul\DeliveryBoy\Block\Adminhtml\System\Config;

class WarehouseCoordinates extends \Magento\Backend\Block\Template
{
    public const SECTION_NAME = 'deliveryboy';

    /**
     * @var \Webkul\DeliveryBoy\Helper\Data $helperData
     */
    protected $helperData;

    /**
     * @param \Webkul\DeliveryBoy\Helper\Data $helperData
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\App\RequestInterface $request
     * @param array $data
     */
    public function __construct(
        \Webkul\DeliveryBoy\Helper\Data $helperData,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\App\RequestInterface $request,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->request = $request;
        $this->helperData = $helperData;
    }

    /**
     * Return Google Map Key
     *
     * @return string
     */
    public function getGoogleMapKey()
    {
        return $this->helperData->getGoogleMapKey();
    }

    /**
     * Is DeliveryBoy Configuration secion
     *
     * @return bool
     */
    public function isDeliveyrboySection()
    {
        return self::SECTION_NAME === $this->getCurrentSectionName();
    }

    /**
     * Get Current secion name.
     *
     * @return string
     */
    public function getCurrentSectionName()
    {
        return $this->request->getParam('section');
    }
}
