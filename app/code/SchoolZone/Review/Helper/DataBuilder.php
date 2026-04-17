<?php
namespace SchoolZone\Review\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Bundle\Model\Product\Type as BundleType;

class DataBuilder extends AbstractHelper
{
    /**
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var BundleType
     */
    protected $bundleType;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        ProductCollectionFactory $productCollectionFactory,
        BundleType $bundleType
    ) {
        parent::__construct($context);
        $this->productCollectionFactory = $productCollectionFactory;
        $this->bundleType = $bundleType;
    }

    /**
     * Build the complete school data as JSON
     *
     * @param string $schoolName
     * @return string JSON
     */
    public function buildData($schoolName)
    {
        $data = [
            'school_name' => $schoolName,
            'classes' => []
        ];

        $classes = $this->getClasses($schoolName);

        foreach ($classes as $classId => $classLabel) {
		$bundles = $this->getBundlesByClass($schoolName, $classLabel);

		 $uniqueBundles = [];
        foreach ($bundles as $bundle) {
            $bundleKey = $bundle['name']; // Or use $bundle['bundle_id'] for unique ID
            if (!isset($uniqueBundles[$bundleKey])) {
                $uniqueBundles[$bundleKey] = $bundle;
            }
            // If you want to merge book lists for duplicates, add logic here
        }

        // Use re-indexed array
        $bundles = array_values($uniqueBundles);

            $data['classes'][] = [
                'class_id'   => $classId,
                'class_label'=> $classLabel,
                'bundles'    => $bundles
            ];
        }

        return json_encode($data);
    }

    /**
     * Fetch unique class options for the given school
     *
     * @param string $schoolName
     * @return array
     */
    protected function getClasses($schoolName)
    {
        $collection = $this->productCollectionFactory->create()
            ->addAttributeToSelect('class')
            ->addAttributeToFilter('type_id', 'bundle')
            ->addAttributeToFilter('status', 1)
            ->addAttributeToFilter('school', ['eq' => $schoolName])
            ->distinct(true);

        $classes = [];
        foreach ($collection as $product) {
            $classLabel = $product->getAttributeText('class');
            if ($classLabel) {
                $classes[$product->getClass()] = $classLabel;
            }
        }
        return $classes;
    }

    /**
     * Fetch bundles for the given school & class
     *
     * @param string $schoolName
     * @param string $classLabel
     * @return array
     */
    protected function getBundlesByClass($schoolName, $classLabel)
    {
        $bundlesData = [];
        $bundleCollection = $this->productCollectionFactory->create()
            ->addAttributeToSelect(['name', 'sku'])
            ->addAttributeToFilter('type_id', 'bundle')
            ->addAttributeToFilter('status', 1)
            ->addAttributeToFilter('school', ['eq' => $schoolName])
            ->addAttributeToFilter('class', ['eq' => $classLabel]);

        foreach ($bundleCollection as $bundle) {
            $bundlesData[] = [
                'bundle_id' => $bundle->getId(),
                'name'      => $bundle->getName(),
                'sku'       => $bundle->getSku(),
                'books'     => $this->getBooksByBundle($bundle)
            ];
        }

        return $bundlesData;
    }

    /**
     * Get all books for a bundle
     *
     * @param \Magento\Catalog\Model\Product $bundle
     * @return array
     */
    protected function getBooksByBundle($bundle)
    {
        $bookData = [];
        $optionIds = $this->bundleType->getOptionsIds($bundle);
        $optionsCollection = $this->bundleType->getOptionsCollection($bundle);
        $selectionsCollection = $this->bundleType->getSelectionsCollection($optionIds, $bundle);

        foreach ($selectionsCollection as $selection) {
            $bookData[] = [
                'id'   => $selection->getId(),
                'name' => $selection->getName(),
                'sku'  => $selection->getSku()
            ];
        }

        return $bookData;
    }
}

