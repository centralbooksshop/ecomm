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
namespace Webkul\DeliveryBoy\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Helper\Data as PaymentHelper;

class OfflinePaymentMethods implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var PaymentHelper
     */
    protected $paymentHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param PaymentHelper $paymentHelper
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(PaymentHelper $paymentHelper, ScopeConfigInterface $scopeConfig)
    {
        $this->paymentHelper = $paymentHelper;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Function generates array with all active carrier method available
     *
     * @return array
     */
    public function toOptionArray()
    {
        $methods = [];
        $allMethods = $this->paymentHelper->getPaymentMethods();
        $offlineMethods = array_filter($allMethods, function ($value, $key) {
            return  (
                (isset($value['group']) && $value['group'] === 'offline')
                || (!empty($value['is_offline'])))
                && !empty($value['active']);
        }, ARRAY_FILTER_USE_BOTH);
        foreach ($offlineMethods as $code => $offlineMethod) {
            $methods[] = [
                'value' => $code,
                'label' => $offlineMethod['title'],
            ];
        }
        return $methods;
    }
}
