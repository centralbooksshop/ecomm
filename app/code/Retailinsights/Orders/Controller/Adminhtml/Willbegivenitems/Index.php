<?php
namespace Retailinsights\Orders\Controller\Adminhtml\Willbegivenitems;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    protected $resultPageFactory;

    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
       /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();

		// Get name from URL
		$encodedName = $this->getRequest()->getParam('name', '');
		$name = $encodedName ? urldecode($encodedName) : 'Will be Given Details';

		// Get product_purchased from URL
		$encodedProductPurchased = $this->getRequest()->getParam('product_purchased', '');
		$productPurchased = $encodedProductPurchased ? urldecode($encodedProductPurchased) : '';

		// Combine name and product_purchased
		$pageTitle = $productPurchased;
		if ($name) {
			$pageTitle .= ' - (' . $name . ')';
		}

		// Set the page title
		$resultPage->getConfig()->getTitle()->prepend($pageTitle);

		return $resultPage;

    }
}
