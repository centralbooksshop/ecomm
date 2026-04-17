<?php
/**
 * @author CynoInfotech Team
 * @package Cynoinfotech_StorePickup
 */
namespace Cynoinfotech\StorePickup\Plugin;

/**
 * Insert Delivery Date information Block to PDF
 */
abstract class AbstractPdf
{
    /**
     * @var \Magento\Sales\Model\Order\Shipment[] | \Magento\Sales\Model\Order\Invoice[]
     */
    protected $objects = [];

    protected $storepickuporder;

    public function __construct(
        \Cynoinfotech\StorePickup\Model\StorePickupOrderFactory $storepickuporder
    ) {
        $this->storepickuporder = $storepickuporder;
    }

    /**
     * @param \Magento\Sales\Model\Order\Pdf\AbstractPdf $subject
     * @param \Magento\Sales\Model\Order\Shipment[] | \Magento\Sales\Model\Order\Invoice[] $objects
     *
     * @return array
     */
    public function beforeGetPdf($subject, $objects = [])
    {
        $this->objects = $objects;
        return [$objects];
    }

    /**
     * @param \Magento\Sales\Model\Order\Pdf\AbstractPdf $subject
     * @param \Zend_Pdf_Page $page
     * @param string $text
     *
     * @return array
     */
    public function beforeInsertDocumentNumber($subject, $page, $text)
    {
        $order = $this->getCurrentOrder($text);
		//echo'<pre>';print_r($page->getWidth());die;
        $fieldToShow = $this->getWhatShow();
        if (!$order || empty($fieldToShow)) {
            return [$page, $text];
        }
        $storepickup = $this->getCurrentStorePickup($order);
        if (!$storepickup) {
            return [$page, $text];
        }

        $pagewidth = $page->getWidth();
		if($pagewidth == '358') {
		     if ($storepickup[0]['pickup_address']) {
				$store_picup_address = $storepickup[0]['pickup_address'];
				$store_picup_address_array = array_map(
					function ($value) {
						return implode(',', $value);
					},
					array_chunk(explode(',', $store_picup_address), 2)
				);
				$i = 1;
				foreach ($store_picup_address_array as $k => $v) {
					if ($i == 1) {
						$page->drawText(__('Store Pickup Address') . ': ' , 160, $subject->y+146, 'UTF-8');
						$subject->y -= 15;
						$page->drawText($v, 160, $subject->y+147, 'UTF-8');
						$subject->y -= 15;
					} else {
						$page->drawText($v, 160, $subject->y+147, 'UTF-8');
						$subject->y -= 15;
					}
					$i++;
				}
			}
			if ($storepickup[0]['calendar_inputField']) {
				$page->drawText(__('Store Pickup Date') . ': ' .
					$storepickup[0]['calendar_inputField'], 160, $subject->y+149, 'UTF-8');
				$subject->y -= 15;

			}

		} else {

				   
				if ($storepickup[0]['calendar_inputField']) {
					$page->drawText(__('Store Pickup Date') . ': ' .
					$storepickup[0]['calendar_inputField'], 285, $subject->y+50, 'UTF-8');
					$subject->y -= 15;
				}

				if ($storepickup[0]['pickup_address']) {
					$store_picup_address = $storepickup[0]['pickup_address'];
					$store_picup_address_array = array_map(
						function ($value) {
							return implode(',', $value);
						},
						array_chunk(explode(',', $store_picup_address), 2)
					);
					$i = 1;
					foreach ($store_picup_address_array as $k => $v) {
						if ($i == 1) {
							//$page->drawText(__('Store Pickup Address') . ': ' . $v, 285, $subject->y+36, 'UTF-8');
					    $page->drawText(__('Store Pickup Address') . ': ' , 285, $subject->y+36, 'UTF-8');
						$subject->y -= 15;
						$page->drawText($v, 285, $subject->y+36, 'UTF-8');
							$subject->y -= 15;
						} elseif ($i == 2) {
							$page->drawText($v, 285, $subject->y+36, 'UTF-8');
							$subject->y -= 15;
						} elseif ($i == 3) {
							$page->drawText($v, 285, $subject->y+36, 'UTF-8');
							$subject->y -= 15; 
						} elseif ($i == 4) {
							$page->drawText($v, 285, $subject->y+36, 'UTF-8');
							$subject->y -= 15; 
						} else {
							$page->drawText($v, 285, $subject->y+36, 'UTF-8');
							$subject->y -= 80;
							//$subject->y -= 15;
						}
						$i++;
					}
				}
		}
        
        $subject->y += 105;
        
        return [$page, $text];
    }

    /**
     * Get array of Delivery Date fields name which can be drawn in PDF
     *
     * @return string[]
     */
    abstract protected function getWhatShow();

    /**
     * Get order for current PDF page.
     * GetPdf method contains array of Shipment (or Invoice), in this method we search current Shipment (or Invoice)
     *
     * @param string $text
     *
     * @return \Magento\Sales\Model\Order|false
     */
    protected function getCurrentOrder($text)
    {
        if (!count($this->objects)) {
            return false;
        }
        // if we cant find which shipment (or Invoice) element on current page, then just take first.
        $currentObject = current($this->objects);
        foreach ($this->objects as $object) {
            if ($this->getPhrasePrefix() . $object->getIncrementId() == $text) {
                $currentObject = $object;
                break;
            }
        }

        return $currentObject->getOrder();
    }

    /**
     * Get Phrase prefix of page title. For find current shipment (or Invoice)
     *
     * @return \Magento\Framework\Phrase|string
     */
    abstract protected function getPhrasePrefix();

    /**
     * Get Delivery Date entity for current Order
     *
     * @param \Magento\Sales\Model\Order $order
     *
     * @return \Amasty\Deliverydate\Model\Deliverydate|false
     */
    protected function getCurrentStorePickup($order)
    {
        $storepickup_data = $this->storepickuporder
            ->create()->getCollection()
            ->addFieldToSelect(['pickup_address','calendar_inputField'])
            ->addFieldToFilter('order_id', $order->getId());
        
        return $storepickup_data->getData();
    }
}
