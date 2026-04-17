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
namespace Webkul\DeliveryBoy\Model\Carrier;

use Magento\Shipping\Model\Rate\Result;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;

class Shipping extends AbstractCarrier implements CarrierInterface
{
    /**
     * @var string
     */
    protected $_code = "expressdeliveryboy";

    public const CODE = "expressdeliveryboy";

    /**
     * @var \Magento\Shipping\Model\Rate\ResultFactory
     */
    protected $_rateResultFactory;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    protected $_rateMethodFactory;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param array $data
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        array $data = []
    ) {
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * Collect Shipping rates.
     *
     * @param RateRequest $request
     * @return Result|bool
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag("active")) {
            return false;
        }
        /** @var Result $result */
        $result = $this->_rateResultFactory->create();
        $shippingPrice = $this->getShippingPrice($request);
        if ($shippingPrice !== false) {
            $method = $this->createResultMethod($shippingPrice);
            $result->append($method);
        }
        return $result;
    }

    /**
     * Get allowed methods for this carrier.
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        return ["expressdeliveryboy" => $this->getConfigData("name")];
    }

    /**
     * Get Shipping Price.
     *
     * @param RateRequest $request
     * @return bool|float
     */
    private function getShippingPrice(RateRequest $request)
    {
        $shippingPrice = false;
        $configPrice = $this->getConfigData("price");
        if ($this->getConfigData("type") === "O") {
            // per order ////////////////////////////////////////////////////////////
            $shippingPrice = $this->getShippingPricePerOrder($configPrice);
        } elseif ($this->getConfigData("type") === "I") {
            // per item /////////////////////////////////////////////////////////////
            $shippingPrice = $this->getShippingPricePerItem($request, $configPrice);
        }
        $shippingPrice = $this->getFinalPriceWithHandlingFee($shippingPrice);
        return $shippingPrice;
    }

    /**
     * Create Success Result.
     *
     * @param int|float $shippingPrice
     * @return \Magento\Quote\Model\Quote\Address\RateResult\Method
     */
    private function createResultMethod($shippingPrice)
    {
        /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
        $method = $this->_rateMethodFactory->create();
        $method->setCarrier($this->_code);
        $method->setCarrierTitle($this->getConfigData("title"));
        $method->setMethod($this->_code);
        $method->setMethodTitle($this->getConfigData("name"));
        $method->setPrice($shippingPrice);
        $method->setCost($shippingPrice);
        return $method;
    }

    /**
     * Get Base price per item.
     *
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     * @param float $basePrice
     * @return float
     */
    public function getShippingPricePerItem(\Magento\Quote\Model\Quote\Address\RateRequest $request, $basePrice)
    {
        return $request->getPackageQty() * $basePrice;
    }

    /**
     * Get Base price per order.
     *
     * @param float $basePrice
     * @return float
     */
    public function getShippingPricePerOrder($basePrice)
    {
        return $basePrice;
    }
}
