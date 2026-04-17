<?php
namespace Retailinsights\Orders\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\UrlInterface;

class UploadAction extends Column
{
    /** @var UrlInterface */
    protected $urlBuilder;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */

    public function prepareDataSource(array $dataSource)
	{
		if (isset($dataSource['data']['items'])) {
			foreach ($dataSource['data']['items'] as &$item) {
				if (isset($item['item_id'])) {
					$isUploaded = !empty($item['acknowledgement_upload']);
					$isConfirmed = !empty($item['delivery_date']);

					// Default label
					$label = $isUploaded ? __('Uploaded') : __('Upload');

					// Build action array
					$action = [
						'label' => $label,
						'hidden' => false,
					];

					// Only add href if not uploaded and confirmed
					if (!$isUploaded && $isConfirmed) {
						$action['href'] = $this->urlBuilder->getUrl(
							$this->getData('config/viewUrlPath'),
							['item_id' => $item['all_item_ids'] ?? $item['item_id']]
						);
					}

					if ($isConfirmed) {
						$item[$this->getData('name')] = ['upload' => $action];
					}
				}
			}
		}
		return $dataSource;
	}



	/*public function prepareDataSource123(array $dataSource)
	{
		if (isset($dataSource['data']['items'])) {
			foreach ($dataSource['data']['items'] as & $item) {
				if (isset($item['item_id'])) {
					$item[$this->getData('name')] = [
						'upload' => [
							'href' => $this->urlBuilder->getUrl(
								$this->getData('config/viewUrlPath'),
								['item_id' => $item['all_item_ids'] ?? $item['item_id']]
							),
							'label' => __('Upload'),
							'hidden' => false,
						],
					];
				}
			}
		}
		return $dataSource;
	}*/

}
