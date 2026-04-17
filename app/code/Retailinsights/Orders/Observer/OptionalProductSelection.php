<?php

namespace Retailinsights\Orders\Observer;
 
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Registry;

 
class OptionalProductSelection implements ObserverInterface
{
    /**
     * payment_method_is_active event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * 
     * 
     */

    private $quoteItemFactory;
    private $itemResourceModel;
    protected $_checkoutSession;
    protected $registry;
    private $logger;
    private $_request;

    public function __construct(
         \Magento\Quote\Model\Quote\ItemFactory $quoteItemFactory,
        \Magento\Quote\Model\ResourceModel\Quote\Item $itemResourceModel,
        \Magento\Quote\Model\QuoteRepository $quoteRepository,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Registry $registry,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->quoteItemFactory = $quoteItemFactory;
        $this->itemResourceModel = $itemResourceModel;
        $this->quoteRepository = $quoteRepository;
        $this->_request = $request;
        $this->registry = $registry;
        $this->_checkoutSession = $checkoutSession;
        $this->_storeManager = $storeManager;
        $this->logger = $logger;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {   
        $optional_items='';
        if(isset($_SESSION["optional_items"])){
            $optional_items = $_SESSION["optional_items"];
        }

		$order= $observer->getEvent()->getOrder();
        $quote= $observer->getEvent()->getQuote();
        $quoteId = $quote->getId();

        if($quoteId!=''){
            $quote = $this->quoteRepository->get($quoteId);
            foreach($quote->getAllVisibleItems() as $itemq){
                $itemq->setOptionalItems($optional_items);
                $itemq->save();
            }
        }

    }
    
}