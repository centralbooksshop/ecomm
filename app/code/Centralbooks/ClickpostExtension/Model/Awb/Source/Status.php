<?php

namespace Centralbooks\ClickpostExtension\Model\Awb\Source;

class Status implements \Magento\Framework\Option\ArrayInterface
{
    const ASSIGNED = "AwbRegistered";
	const Delivered = "Delivered";
    const Cancelled = 'Cancelled';
    const NotServiceable = 'NotServiceable';
    const FailedDelivery = 'FailedDelivery';
    const OutForDelivery = 'OutForDelivery';
    const ContactCustomerCare = 'ContactCustomerCare';
    const ShipmentHeld = 'ShipmentHeld';
    const ShipmentDelayed = 'ShipmentDelayed';
    const InTransit = 'InTransit';
    const PickedUp = 'PickedUp';
    const PickupFailed = 'PickupFailed';
    const LastMilePrePickup = 'LastMilePrePickup';
    const PickupPending = "PickupPending";
    const OutForPickup = "OutForPickup";
    const NoStatusExist = "NoStatusExist";
    const OrderPlaced = "OrderPlaced";

	const ExchangeInTransit = "Exchange-In-Transit";
    const ExchangePickup = 'Exchange-Pickup';
    const ReadyToShip = 'Ready-To-Ship';
    const Aged = 'Aged';
    const Expired = 'Expired';
    const CourierPartnerTrackingDataMissing = 'CourierPartnerTrackingDataMissing';
    const RTODelivered = 'RTO-Delivered';
    const RTOShipmentDelay = 'RTO-ShipmentDelay';
    const RTOContactCustomerCare = 'RTO-ContactCustomerCare';
    const RTOInTransit = 'RTO-InTransit';
    const RTOFailed = 'RTO-Failed';
    const RTOOutForDelivery = 'RTO-OutForDelivery';
    const RTOMarked = "RTO-Marked";
    const RTORequested = "RTO-Requested";
    const Damaged = "Damaged";
    const Lost = "Lost";
    const OriginCityOut = "OriginCityOut";
	const ExchangeDelivered = "Exchange-Delivered";
    const RTOHandover = "RTO-Handover";
    const DestinationHubIn = "DestinationHubIn";
    const OriginCityIn = "OriginCityIn";

	

    /**
     * to option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'value' => self::ASSIGNED,
                'label' => __('Awb Registered')
            ],
            [
                'value' => self::OrderPlaced,
                'label' => __('OrderPlaced')
            ],
            [
                'value' => self::NoStatusExist,
                'label' => __('NoStatusExist')
            ],
            [
                'value' => self::OutForPickup,
                'label' => __('OutForPickup')
            ],
            [
                'value' => self::PickupPending,
                'label' => __('PickupPending')
            ],
            [
                'value' => self::LastMilePrePickup,
                'label' => __('LastMilePrePickup')
            ],
            [
                'value' => self::PickupFailed,
                'label' => __('PickupFailed')
            ],
            [
                'value' => self::PickedUp,
                'label' => __('PickedUp')
            ],
            [
                'value' => self::InTransit,
                'label' => __('InTransit')
            ],
            [
                'value' => self::ShipmentDelayed,
                'label' => __('ShipmentDelayed')
            ],
            [
                'value' => self::ShipmentHeld,
                'label' => __('ShipmentHeld')
            ],
            [
                'value' => self::ContactCustomerCare,
                'label' => __('ContactCustomerCare')
            ],
            [
                'value' => self::OutForDelivery,
                'label' => __('OutForDelivery')
            ],
            [
                'value' => self::FailedDelivery,
                'label' => __('FailedDelivery')
            ],
            [
                'value' => self::NotServiceable,
                'label' => __('NotServiceable')
            ],
            [
                'value' => self::Delivered,
                'label' => __('Delivered')
            ],
			[
                'value' => self::Cancelled,
                'label' => __('Cancelled')
            ],
            [
                'value' => self::Lost,
                'label' => __('Lost')
            ],
            [
                'value' => self::Damaged,
                'label' => __('Damaged')
            ],
            [
                'value' => self::RTORequested,
                'label' => __('RTO-Requested')
            ],
            [
                'value' => self::RTOMarked,
                'label' => __('RTO-Marked')
            ],
            [
                'value' => self::RTOOutForDelivery,
                'label' => __('RTO-OutForDelivery')
            ],
            [
                'value' => self::RTOFailed,
                'label' => __('RTO-Failed')
            ],
            [
                'value' => self::RTOInTransit,
                'label' => __('RTO-InTransit')
            ],
            [
                'value' => self::RTOContactCustomerCare,
                'label' => __('RTO-ContactCustomerCare')
            ],
            [
                'value' => self::RTOShipmentDelay,
                'label' => __('RTO-ShipmentDelay')
            ],
            [
                'value' => self::RTODelivered,
                'label' => __('RTO-Delivered')
            ],
            [
                'value' => self::CourierPartnerTrackingDataMissing,
                'label' => __('CourierPartnerTrackingDataMissing')
            ],
            [
                'value' => self::Expired,
                'label' => __('Expired')
            ],
            [
                'value' => self::Aged,
                'label' => __('Aged')
            ],
            [
                'value' => self::ReadyToShip,
                'label' => __('Ready To Ship')
            ],

			[
                'value' => self::ExchangePickup,
                'label' => __('Exchange Pickup')
            ],
            [
                'value' => self::ExchangeInTransit,
                'label' => __('Exchange In Transit')
            ],
            [
                'value' => self::ExchangeDelivered,
                'label' => __('Exchange Delivered')
            ],
            [
                'value' => self::RTOHandover,
                'label' => __('RTO Handover')
            ],
            [
                'value' => self::DestinationHubIn,
                'label' => __('DestinationHubIn')
            ],
            [
                'value' => self::OriginCityIn,
                'label' => __('OriginCityIn')
            ],
            [
                'value' => self::OriginCityOut,
                'label' => __('OriginCityOut')
            ],
        ];
        return $options;

    }
}
