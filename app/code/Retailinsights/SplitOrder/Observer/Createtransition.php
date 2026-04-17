<?php
 
namespace Retailinsights\SplitOrder\Observer;
 
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
 
class Createtransition implements ObserverInterface
{
    protected $transactions;
    protected $logger;
    protected $productRepository;
    protected $helperData;
 
    public function __construct(
        \Magento\Sales\Api\Data\TransactionSearchResultInterfaceFactory $transactions,
        \Magento\Sales\Model\Order $order,
        LoggerInterface $logger,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Retailinsights\SplitOrder\Helper\Data $helperData
    )
    {
        $this->transactions = $transactions;
        $this->order = $order;
        $this->logger = $logger;
        $this->helperData = $helperData;
        $this->productRepository = $productRepository;
    }
 
    public function execute(\Magento\Framework\Event\Observer $observer, $paymentData = array())
    {
        $orderId = $observer->getEvent()->getOrderIds();
        $order = $this->order->load($orderId);
        $id = $order->getId();

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName = $resource->getTableName('sales_order_payment');

        $sql = $connection->select()->from($tableName)->where('parent_id = ?', $id);

        // $sql = "SELECT * FROM " . $tableName. "WHERE 'parent_id'=".$id;
        $result = $connection->fetchAll($sql); 

        $entity_id =$parent_id=$additional_information='';
        foreach ($result as $key => $value) {
            $entity_id = $value['entity_id'];
            $parent_id = $value['parent_id'];
            $additional_information = $value['additional_information'];
        }

       try {
            $transactions = $this->transactions->create()->addOrderIdFilter($orderId);
            $transactions->getItems();
            $flag=false;
            
            foreach ($transactions as $key => $value) {
            //$this->logger->info($value->getData()); 
            //$this->logger->info($id); 
                
                if($value['order_id'] == $id){
                    $flag='false';
                }else{
                    $flag='true';
                }
            }
            //$this->logger->info('flad'.$flag); 

            if($flag=='true'){
                $n =20;
                $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                $txn_id = '';
              
                for ($i = 0; $i < $n; $i++) {
                    $index = rand(0, strlen($characters) - 1);
                    $txn_id .= $characters[$index];
                }
                $tableNameTransation = $resource->getTableName('sales_payment_transaction');

                $sqlInsert = "INSERT INTO " . $tableNameTransation . " (order_id, payment_id, txn_id, txn_type, is_closed, additional_information) VALUES ('".$parent_id."','".$entity_id."','".$txn_id."','order',1,'".$additional_information."')";
                $connection->query($sqlInsert);
            }
        } catch (\Exception $e) {
             // $this->logger->info($e->getMessage());
        }
    }
}