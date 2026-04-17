<?php

namespace Retailinsights\Orders\Observer;
 
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Registry;

 
 
class CatalogProductImportBunchSaveAfter implements ObserverInterface
{
    /**
     * payment_method_is_active event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * 
     * 
     */

    // protected $_cart;
    protected $_checkoutSession;
    protected $registry;
    private $logger;
    private $_request;
    private $productRepository;
    private $_productloader;
    private $productRep;

    public function __construct(
        \Magento\Catalog\Model\ProductFactory $_productloader,
        \Magento\Catalog\Model\ProductRepository $productRep,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Quote\Model\QuoteRepository $quoteRepository,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Registry $registry,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->productRep = $productRep;
        $this->_productloader = $_productloader;
        $this->productRepository = $productRepository;
        $this->quoteRepository = $quoteRepository;
        $this->_request = $request;
        $this->registry = $registry;
        $this->_checkoutSession = $checkoutSession;
        $this->_storeManager = $storeManager;
        $this->logger = $logger;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $this->reindexAll();
        } catch (\Execption $e) {
            echo $e->getMessage(); 
        }
        
    }

    public function reindexAll() {
       
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $indexerFactory = $objectManager->get('Magento\Indexer\Model\IndexerFactory');
        $indexerIds = array(
            'catalog_category_product',
            'catalog_product_category',
            'catalog_product_price',
            'catalog_product_attribute',
            'cataloginventory_stock',
            'catalogrule_product',
            'catalogsearch_fulltext',
        );
        foreach ($indexerIds as $indexerId) {
            $indexer = $indexerFactory->create();
            $indexer->load($indexerId);
            $indexer->reindexAll();
        }
    }

    
}