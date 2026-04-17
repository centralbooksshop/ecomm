<?php
namespace Retailinsights\Bundles\Controller\Product;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\DateTime;

class Approve extends Action
{
    protected $productRepository;
    protected $resultJsonFactory;
    protected $date;

    public function __construct(
        Context $context,
        ProductRepositoryInterface $productRepository,
        JsonFactory $resultJsonFactory,
        DateTime $date
    ) {
        parent::__construct($context);
        $this->productRepository = $productRepository;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->date = $date;
    }

    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();

        try {
            $productId = (int) $this->getRequest()->getParam('product_id');
            if (!$productId) {
                throw new LocalizedException(__('Invalid product ID.'));
            }

            $product = $this->productRepository->getById($productId);
            $product->setData('approve_school_preview', 1);
            $product->setData('approve_school_preview_date', $this->date->gmtDate());
            $this->productRepository->save($product);

            return $resultJson->setData([
                'success' => true,
                'message' => __('Product approved successfully.')
            ]);
        } catch (\Exception $e) {
            return $resultJson->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
