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
namespace Webkul\DeliveryBoy\ViewModel;

use Magento\Framework\Json\Helper\Data as JsonHelper;

class BlockHelper implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @var JsonHelper
     */
    private $jsonHelper;
    
    /**
     * @param JsonHelper $jsonHelper
     */
    public function __construct(
        JsonHelper $jsonHelper
    ) {
        $this->jsonHelper = $jsonHelper;
    }

    /**
     * Return Json Helper.
     *
     * @return JsonHelper
     */
    public function getJsonHelper(): JsonHelper
    {
        return $this->jsonHelper;
    }
}
