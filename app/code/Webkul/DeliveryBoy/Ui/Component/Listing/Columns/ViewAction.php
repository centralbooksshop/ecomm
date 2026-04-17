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
namespace Webkul\DeliveryBoy\Ui\Component\Listing\Columns;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Webkul\DeliveryBoy\Model\ResourceModel\Order\CollectionFactory as DeliveryboyOrderCollF;

class ViewAction extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param DeliveryboyOrderCollF $deliveryboyOrderCollF
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        DeliveryboyOrderCollF $deliveryboyOrderCollF,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->deliveryboyOrderCollF = $deliveryboyOrderCollF;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Initialize data source.
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource["data"]["items"])) {
            foreach ($dataSource["data"]["items"] as &$item) {
                $name = $this->getData("name");
                $deliveryboyOrderColl = $this->deliveryboyOrderCollF->create();
                $deliveryboyOrderColl->leftJoinOrderTransactionTable($deliveryboyOrderColl->getSelect());
                $deliveryboyOrderColl->addFieldToFilter('id', $item['id']);
                $deliveryboyOrder = $deliveryboyOrderColl->getFirstItem();
                $orderId = $deliveryboyOrder->getOrderId();
                if (!empty($orderId)) {
                    $item[$name]["edit"] = [
                        "href"   => $this->urlBuilder->getUrl(
                            "sales/order/view",
                            [
                                "order_id" => $orderId
                            ]
                        ),
                        "label"  => __("View Order"),
                        "hidden" => false
                    ];
                } else {
                    $item[$name]["edit"] = [
                        "href"  => "#",
                        "label" => "<span>" . __("Offline Order") . "</span>",
                        "hidden" => true
                    ];
                }
                $transactionEntityId = $deliveryboyOrder->getTransactionEntityId();
                if (!empty($transactionEntityId)) {
                    $item[$name]["viewTransaction"] = [
                        "href"   => $this->urlBuilder->getUrl(
                            "expressdelivery/ordertransaction/view",
                            [
                                "id" => $transactionEntityId
                            ]
                        ),
                        "label"  => __("View Transaction"),
                        "hidden" => false
                    ];
                }
            }
        }
        return $dataSource;
    }
}
