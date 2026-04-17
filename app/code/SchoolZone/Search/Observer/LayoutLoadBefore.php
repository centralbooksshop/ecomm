<?php

namespace SchoolZone\Search\Observer;

class LayoutLoadBefore implements \Magento\Framework\Event\ObserverInterface {

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
       if ($this->storeManager->getWebsite()->getCode() == 'schools') {
           $layout = $observer->getEvent()->getLayout();

           $handlecode ='website_'.$this->storeManager->getStore()->getWebsiteId();
           $layout->getUpdate()->addHandle($handlecode);
        }

        return $this;
    }

}