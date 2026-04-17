<?php
namespace SchoolZone\Review\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Controller\ResultFactory;
use SchoolZone\Review\Helper\DataBuilder;
use SchoolZone\Review\Model\SchooldataFactory;
use SchoolZone\Review\Model\ResourceModel\Schooldata as SchooldataResource;
use SchoolZone\Review\Block\Display;
use Psr\Log\LoggerInterface;

class Build extends Action
{
	const XML_PATH_SCHOOLS_LIST = 'schoolzone_review/general/school_list';
	const XML_PATH_SCHOOLS_START_DATE = 'schoolzone_review/general/bundle_start_date';
	const XML_PATH_SCHOOLS_END_DATE = 'schoolzone_review/general/bundle_end_date';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var DataBuilder
     */
    protected $dataBuilder;

    /**
     * @var SchooldataFactory
     */
    protected $schooldataFactory;

    /**
     * @var SchooldataResource
     */
    protected $schooldataResource;

    /**
     * @var LoggerInterface
     */
    protected $logger;
    protected $displayBlock;

    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        DataBuilder $dataBuilder,
        SchooldataFactory $schooldataFactory,
	SchooldataResource $schooldataResource,
	Display $displayBlock,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->scopeConfig = $scopeConfig;
        $this->dataBuilder = $dataBuilder;
        $this->schooldataFactory = $schooldataFactory;
	$this->schooldataResource = $schooldataResource;
	$this->displayBlock = $displayBlock;
        $this->logger = $logger;
    }

    public function execute()
    {
        $resultRaw = $this->resultFactory->create(ResultFactory::TYPE_RAW);

        try {
            $schoolsConfig = $this->scopeConfig->getValue(
                self::XML_PATH_SCHOOLS_LIST,
                ScopeInterface::SCOPE_STORE
	    );
	    $schoolStartDate = $this->scopeConfig->getValue(
                self::XML_PATH_SCHOOLS_START_DATE,
                ScopeInterface::SCOPE_STORE
	    );
	    $schoolsEndDate = $this->scopeConfig->getValue(
                self::XML_PATH_SCHOOLS_END_DATE,
                ScopeInterface::SCOPE_STORE
            );

            if (!$schoolsConfig) {
                $resultRaw->setContents("No schools configured in Stores > Configuration > SchoolZone > School Data.");
                return $resultRaw;
            }

            $schools = array_filter(array_map('trim', preg_split("/\r\n|\n|\r/", $schoolsConfig)));
            $processed = 0;
            $errors = [];

            foreach ($schools as $schoolName) {
 	      try {
		$classes = $this->displayBlock->getClass($schoolName);
		$schoolData = [];
                foreach ($classes as $class) {
			$bundles = $this->displayBlock->getProductsByClass($schoolName, $class);
			 $seenBundles = [];
			foreach ($bundles as $bundle) {
			    foreach ($bundle['data'] as $bundleItem) {
				    $bundleName = $bundleItem['options'] ?? '';
				     if (in_array($bundleName, $seenBundles)) {
             				   continue;  // Skip duplicates
			            }
				    $seenBundles[] = $bundleName;
			    $bundleId = $bundleItem['entity_id'];
			    $bundleSaleCount = $this->displayBlock->getBundleOrderCount($bundleId, $schoolStartDate, $schoolsEndDate);
			    $books = $this->displayBlock->getBooksByBundleId($bundleItem['entity_id']);
			    $schoolData[] = [
                                'class'  => $class['label'] ?? '',
                                'bundle' => [
					'name'  => $bundleName,
					'org_name' => $bundleItem['org_name'],
					'entity_id' => $bundleItem['entity_id'],
				/*	'sku' => $bundleItem['sku'],
				'isbn' => $bundleItem['isbn'], */
					'bundle_sale_count' => $bundleSaleCount,
                                        'books' => $books
                                ]
                            ];
			}
		    }
		}
		
		$groupedByClass = [];
                foreach ($schoolData as $entry) {
                    $className = $entry['class'] ?? 'Unknown Class';
                    if (!isset($groupedByClass[$className])) {
                        $groupedByClass[$className] = [
                            'class' => $className,
                            'bundles' => []
                        ];
                    }
                    $groupedByClass[$className]['bundles'][] = $entry['bundle'];
                }

/*		echo "<pre>";
		print_r($groupedByClass);
		echo "</pre>"; die; */ 

		$model = $this->schooldataFactory->create();
		$this->schooldataResource->load($model, $schoolName, 'school_name');
		if (!$model->getId()) {
    			$model->setSchoolName($schoolName);
		}
		$model->setDataJson(json_encode($groupedByClass));
		$this->schooldataResource->save($model); 

		    $processed++;

                } catch (\Throwable $e) {
                    $this->logger->error("Schooldata build failed for '{$schoolName}': " . $e->getMessage());
                    $errors[] = $schoolName . ' => ' . $e->getMessage();
                }
            }

            $message = "School data build completed. Processed: {$processed}.";
            if (!empty($errors)) {
                $message .= " Errors for " . count($errors) . " school(s):\n" . implode("\n", $errors);
            }

            $resultRaw->setContents($message);
            return $resultRaw;

        } catch (\Throwable $e) {
            $this->logger->critical('Schooldata build fatal: ' . $e->getMessage());
            $resultRaw->setContents('Fatal error: ' . $e->getMessage());
            return $resultRaw;
        }
    }
}

