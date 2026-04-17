<?php
namespace Retailinsights\Orders\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

class Acknowledgementview extends Column
{
    protected $urlBuilder;

    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $file = $item[$this->getData('name')] ?? null;
                if ($file) {
                    // Construct URL to media folder
                    $url = $this->urlBuilder->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA]). 'acknowledgements/' . $file;

                    // Check file extension
                    $ext = pathinfo($file, PATHINFO_EXTENSION);

                    if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif'])) {
                        // Show image preview
                        $item[$this->getData('name')] = '<img src="' . $url . '" style="max-width:80px; max-height:80px;" />';
                    } else {
                        // Show link for PDF or other files
                        $item[$this->getData('name')] = '<a href="' . $url . '" target="_blank">View File</a>';
                    }
                } else {
                    $item[$this->getData('name')] = 'No file';
                }
            }
        }
        return $dataSource;
    }
}
