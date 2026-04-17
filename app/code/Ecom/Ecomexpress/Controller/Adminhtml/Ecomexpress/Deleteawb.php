<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Ecomexpress
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */
namespace Ecom\Ecomexpress\Controller\Adminhtml\Ecomexpress;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
class Deleteawb extends \Magento\Backend\App\Action
{
    /**
     * Index action
     *
     * @return $this
     */
      protected $resultPageFactory;
    public function __construct(
      \Magento\Backend\App\Action\Context $context,
      \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ){
        $this->resultPageFactory = $resultPageFactory; 
        parent::__construct($context);
    }
    public function execute()
    {  
       $model = $this->_objectManager->create('Ecom\Ecomexpress\Model\Awb');
       
       if(!count($model->getCollection()->getData())){
        $this->messageManager->addErrorMessage(__('There is no AWB to delete.'));
        $this->_redirect('ecomexpress/ecomexpress/awb');
        return;
       }
       
       if(count($model->getCollection()->getData())){
           $connection = $model->getCollection()->getConnection();
            $tableName = $model->getCollection()->getMainTable();
            $connection->truncateTable($tableName);
       }
       $this->messageManager->addSuccessMessage(__('AWB has been flushed successfully.'));
       $this->_redirect('ecomexpress/ecomexpress/awb');
      
          
    }
}