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
declare(strict_types=1);

namespace Webkul\DeliveryBoy\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;
use Magento\InventoryCatalogApi\Model\IsSingleSourceModeInterface;
use Magento\InventorySalesApi\Api\Data\ItemToSellInterfaceFactory;
use Magento\InventorySalesApi\Api\PlaceReservationsForSalesEventInterface;
use Magento\InventorySalesApi\Model\GetSkuFromOrderItemInterface;
use Magento\InventoryShipping\Model\GetItemsToDeductFromShipment;
use Magento\InventoryShipping\Model\SourceDeductionRequestFromShipmentFactory;
use Magento\InventoryShippingAdminUi\Ui\DataProvider\GetSourcesByOrderIdSkuAndQty;
use Magento\InventorySourceDeductionApi\Model\SourceDeductionRequestInterface;
use Magento\InventorySourceDeductionApi\Model\SourceDeductionServiceInterface;
use Magento\Sales\Model\Order\Item;

class SourceDeductionProcessor extends \Magento\InventoryShipping\Observer\SourceDeductionProcessor
{
    /**
     * @var IsSingleSourceModeInterface
     */
    private $isSingleSourceMode;

    /**
     * @var DefaultSourceProviderInterface
     */
    private $defaultSourceProvider;

    /**
     * @var GetItemsToDeductFromShipment
     */
    private $getItemsToDeductFromShipment;

    /**
     * @var SourceDeductionRequestFromShipmentFactory
     */
    private $sourceDeductionRequestFromShipmentFactory;

    /**
     * @var SourceDeductionServiceInterface
     */
    private $sourceDeductionService;

    /**
     * @var ItemToSellInterfaceFactory
     */
    private $itemsToSellFactory;

    /**
     * @var PlaceReservationsForSalesEventInterface
     */
    private $placeReservationsForSalesEvent;

    /**
     * @var GetSourcesByOrderIdSkuAndQty
     */
    private $getSourcesByOrderIdSkuAndQty;

    /**
     * @var GetSkuFromOrderItemInterface
     */
    private $getSkuFromOrderItem;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @param IsSingleSourceModeInterface $isSingleSourceMode
     * @param DefaultSourceProviderInterface $defaultSourceProvider
     * @param GetItemsToDeductFromShipment $getItemsToDeductFromShipment
     * @param SourceDeductionRequestFromShipmentFactory $sourceDeductionRequestFromShipmentFactory
     * @param SourceDeductionServiceInterface $sourceDeductionService
     * @param ItemToSellInterfaceFactory $itemsToSellFactory
     * @param \Magento\Framework\App\RequestInterface $request
     * @param GetSkuFromOrderItemInterface $getSkuFromOrderItem
     * @param PlaceReservationsForSalesEventInterface $placeReservationsForSalesEvent
     * @param GetSourcesByOrderIdSkuAndQty $getSourcesByOrderIdSkuAndQty
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        IsSingleSourceModeInterface $isSingleSourceMode,
        DefaultSourceProviderInterface $defaultSourceProvider,
        GetItemsToDeductFromShipment $getItemsToDeductFromShipment,
        SourceDeductionRequestFromShipmentFactory $sourceDeductionRequestFromShipmentFactory,
        SourceDeductionServiceInterface $sourceDeductionService,
        ItemToSellInterfaceFactory $itemsToSellFactory,
        \Magento\Framework\App\RequestInterface $request,
        GetSkuFromOrderItemInterface $getSkuFromOrderItem,
        PlaceReservationsForSalesEventInterface $placeReservationsForSalesEvent,
        GetSourcesByOrderIdSkuAndQty $getSourcesByOrderIdSkuAndQty,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->isSingleSourceMode = $isSingleSourceMode;
        $this->getSkuFromOrderItem = $getSkuFromOrderItem;
        $this->defaultSourceProvider = $defaultSourceProvider;
        $this->getItemsToDeductFromShipment = $getItemsToDeductFromShipment;
        $this->sourceDeductionRequestFromShipmentFactory = $sourceDeductionRequestFromShipmentFactory;
        $this->sourceDeductionService = $sourceDeductionService;
        $this->itemsToSellFactory = $itemsToSellFactory;
        $this->placeReservationsForSalesEvent = $placeReservationsForSalesEvent;
        $this->request = $request;
        $this->getSourcesByOrderIdSkuAndQty = $getSourcesByOrderIdSkuAndQty;
        $this->logger = $logger;
        parent::__construct(
            $isSingleSourceMode,
            $defaultSourceProvider,
            $getItemsToDeductFromShipment,
            $sourceDeductionRequestFromShipmentFactory,
            $sourceDeductionService,
            $itemsToSellFactory,
            $placeReservationsForSalesEvent
        );
    }

    /**
     * Reduce Product Stock on order place.
     *
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        try {

            /** @var \Magento\Sales\Model\Order\Shipment */
            $shipment = $observer->getEvent()->getShipment();
            if ($shipment->getOrigData('entity_id')) {
                return;
            }
    
            if (!empty($shipment->getExtensionAttributes())
                && !empty($shipment->getExtensionAttributes()->getSourceCode())
            ) {
                $sourceCode = $shipment->getExtensionAttributes()->getSourceCode();
            } elseif ($this->isSingleSourceMode->execute()) {
                $sourceCode = $this->defaultSourceProvider->getCode();
            }
            if ($this->request->getParam("deliveryboyId")) {
                $order = $shipment->getOrder();
                $orderId = (int)$order->getId();
                foreach ($order->getAllItems() as $orderItem) {
                    if ($orderItem->getIsVirtual()
                        || $orderItem->getLockedDoShip()
                        || $orderItem->getHasChildren()
                    ) {
                        continue;
                    }
                    $item = $orderItem->isDummy(true) ? $orderItem->getParentItem() : $orderItem;
                    $qty = $item->getSimpleQtyToShip();
                    $qty = $this->castQty($item, $qty);
                    $sku = $this->getSkuFromOrderItem->execute($item);
                    $sourceCode = $this->getSources($orderId, $sku, $qty);
                    if ($sourceCode) {
                        break;
                    }
                }
            }
    
            $shipmentItems = $this->getItemsToDeductFromShipment->execute($shipment);
    
            if (!empty($shipmentItems)) {
                $sourceDeductionRequest = $this->sourceDeductionRequestFromShipmentFactory->execute(
                    $shipment,
                    $sourceCode,
                    $shipmentItems
                );
                $this->sourceDeductionService->execute($sourceDeductionRequest);
                $this->placeCompensatingReservation($sourceDeductionRequest);
            }
        } catch (\Throwable $t) {
            $this->logger->critical($t);
        }
    }

    /**
     * Convert Qty.
     *
     * @param Item $item
     * @param string|int|float $qty
     * @return float|int
     */
    private function castQty(Item $item, $qty)
    {
        if ($item->getIsQtyDecimal()) {
            $qty = (double)$qty;
        } else {
            $qty = (int)$qty;
        }

        return $qty > 0 ? $qty : 0;
    }

    /**
     * Get available sources.
     *
     * @param int $orderId
     * @param string $sku
     * @param float $qty
     * @return string
     */
    private function getSources(int $orderId, string $sku, float $qty): string
    {
        $sources = $this->getSourcesByOrderIdSkuAndQty->execute($orderId, $sku, $qty);
        $source = "";
        foreach ($sources as $source) {
            $source = $source['sourceCode'];
            break;
        }
        return $source;
    }

    /**
     * Place compensating reservation after source deduction
     *
     * @param SourceDeductionRequestInterface $sourceDeductionRequest
     * @return void
     */
    private function placeCompensatingReservation(SourceDeductionRequestInterface $sourceDeductionRequest): void
    {
        $items = [];
        foreach ($sourceDeductionRequest->getItems() as $item) {
            $items[] = $this->itemsToSellFactory->create(
                [
                'sku' => $item->getSku(),
                'qty' => $item->getQty()
                ]
            );
        }
        $this->placeReservationsForSalesEvent->execute(
            $items,
            $sourceDeductionRequest->getSalesChannel(),
            $sourceDeductionRequest->getSalesEvent()
        );
    }
}
