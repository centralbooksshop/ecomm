<?php
namespace SchoolZone\Search\Model;

use SchoolZone\Search\Model\ResourceModel\NotifyReport\Grid\CollectionFactory;



class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var array
     */
    protected $loadedData;
    protected $collection;
    // @codingStandardsIgnoreStart
    public function __construct(
        \Magento\Framework\Data\CollectionFactory $collection,
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $blogCollectionFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $extensionUser = $objectManager->get('Magento\Backend\Model\Auth\Session')->getUser()->getRole()->getData();

        if($extensionUser['role_name'] == 'Author'){
            $this->collection = $blogCollectionFactory->create()
            ->addFieldToSelect('*')
            ->addFieldToFilter('is_deleted', array('in' => array('false')));
        }else{
            $this->collection = $blogCollectionFactory->create()
            ->addFieldToSelect('*')
            ->addFieldToFilter('is_deleted', array('in' => array('false','true')));
        }
        
    }

    
}