<?php
/**
 * CedCommerce
  *
  * NOTICE OF LICENSE
  *
  * This source file is subject to the End User License Agreement (EULA)
  * that is bundled with this package in the file LICENSE.txt.
  * It is also available through the world-wide-web at this URL:
  * http://cedcommerce.com/license-agreement.txt
  *
  * @category    Ced
  * @package     Ced_CsImportAwb
  * @author      CedCommerce Core Team <connect@cedcommerce.com >
  * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
  * @license      http://cedcommerce.com/license-agreement.txt
  */
namespace Ecom\Ecomexpress\Controller\Adminhtml\Ecomexpress\MassLabel;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Backend\Model\View\Result\ForwardFactory;

class Shipping extends \Magento\Backend\App\Action
{
	/**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */

    /**
     * @var FileFactory
     */
    protected $_fileFactory;

    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

	/*public function __construct(
        Context $context,
        FileFactory $fileFactory,
        ForwardFactory $resultForwardFactory
    ) {
        $this->_fileFactory = $fileFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        parent::__construct($context,$fileFactory);
    }*/
   
	public function execute()
	{	
		$objectManager = $this->_objectManager;
		if($shipmentIds = $this->getRequest()->getPostValue('selected'))
		{		
			//print_r($shipmentIds);die;
			$pdf = $this->_objectManager->get('Ecom\Ecomexpress\Model\Shippinglabel')->getPdf($shipmentIds);
			$date = $this->_objectManager->get('Magento\Framework\Stdlib\DateTime\DateTime')->date('Y-m-d_H-i-s');
	    	return $this->_objectManager->get('Magento\Framework\App\Response\Http\FileFactory')->create(
	    			'shippinglabel' . $date . '.pdf',
	    			$pdf->render(),
	    			DirectoryList::VAR_DIR,
	    			'application/pdf'
	    	);
		}
	}
}