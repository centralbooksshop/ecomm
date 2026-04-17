<?php
namespace Plumrocket\RMA\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class ReturnsReason implements ArrayInterface
{
    public function toOptionArray()
    {
        $result = [];
		//echo '<pre>';print_r($this->getOptions());die;
        foreach ($this->getOptions() as $value => $label) {
            $result[] = [
                 'value' => $value,
                 'label' => $label,
             ];
        }

        return $result;
    }

    public function getOptions()
    {
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
		$connection = $resource->getConnection();
		$tableReason = $connection->getTableName('plumrocket_rma_reason');
        $queryReason = "SELECT `title` FROM `" . $tableReason . "` WHERE status = 1";
			$result = [];
			$optresult = $connection->fetchAll($queryReason);
				foreach ($optresult as $key => $valueName) {
				   $result[ $valueName['title'] ] = $valueName['title'];
				}

		return $result;
    }
}
