<?php
namespace Retailinsights\Orders\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File;
use Laminas\Barcode\Barcode;
use Zend_Pdf_Color_GrayScale;
use Zend_Pdf_Resource_Image_Png;

class ThermalPrinter extends AbstractHelper
{
    protected $directoryList;
    protected $orderRepository;
    protected $file;

    public function __construct(
        Context $context,
        OrderRepositoryInterface $orderRepository,
        DirectoryList $directoryList,
        File $file
    ) {
        parent::__construct($context);
        $this->directoryList = $directoryList;
        $this->orderRepository = $orderRepository;
        $this->file = $file;
    }

		/**
	 * Generate combined PDF for multiple orders (thermal printer style)
	 */
	public function generateCombinedPdf(array $orders)
	{
		$pdf = new \Zend_Pdf();
		$font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_HELVETICA);
		$fontBold = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_HELVETICA_BOLD);
		$fontSize = 9;

		$pageWidth = 230; // approx 80mm thermal
		$baseHeight = 3000;

		foreach ($orders as $order) {
			$itemCount = count($order->getAllVisibleItems());
			$pageHeight = max($baseHeight, 120 + ($itemCount * 40));
			$page = new \Zend_Pdf_Page($pageWidth . ':' . $pageHeight);
			$pdf->pages[] = $page;

			$page->setFont($font, $fontSize);
			$y = $pageHeight - 30;

			// --- Order header ---
			$page->drawText("Order ID: " . $order->getIncrementId(), 10, $y);
			$y -= 12;
			$page->drawText("Student Name: " . $order->getStudentName(), 10, $y);
			$y -= 12;

			// --- Bundle Name (bold, wrap full) ---
			$bundleName = '';
			foreach ($order->getAllVisibleItems() as $item) {
				if ($item->getProductType() === 'bundle') {
					$bundleName = $item->getName();
					break;
				}
			}

			if ($bundleName) {
				$page->setFont($fontBold, $fontSize);
				$wrappedBundle = $this->getWrappedLines("Bundle Name: " . $bundleName, 45);
				foreach ($wrappedBundle as $line) {
					$page->drawText($line, 10, $y);
					$y -= 12;
				}
				$y -= 6;
				$page->setFont($font, $fontSize);
			}

			// --- Table Header (bold) ---
			$xStart = 10;
			$tableWidth = 210;
			$qtyX = 180;

			$page->setLineWidth(0.5);
			$page->setFont($fontBold, $fontSize);
			$page->drawRectangle($xStart, $y, $xStart + $tableWidth, $y - 15, \Zend_Pdf_Page::SHAPE_DRAW_STROKE);
			$page->drawText("Item Description", $xStart + 3, $y - 11);
			$page->drawText("Qty", $qtyX + 10, $y - 11);

			// Draw vertical border between columns (Description | Qty)
			$page->drawLine($qtyX + 5, $y, $qtyX + 5, $y - 15);

			$y -= 15;
			$page->setFont($font, $fontSize);

			// --- Table Rows ---
			foreach ($order->getAllItems() as $item) {
				if ($item->getProductType() === 'bundle' && $item->getParentItemId() === null) {
					continue; // skip only bundle parents
				}

				//$page->setFont($fontBold, $fontSize);
				$given_options = $item->getGivenOptions();
				$baseName = $item->getName();

				if ($given_options == 1) {
					$name = $baseName . ' (will be given)';
				} elseif ($given_options == 2) {
					$name = $baseName . ' (school given)';
				} else {
					$name = $baseName;
				}

				$qty = (int)$item->getQtyOrdered();
				$wrappedLines = $this->getWrappedLines($name, 38);
				$rowHeight = (count($wrappedLines) * 10) + 5;

				// Draw row border
				$page->drawRectangle($xStart, $y, $xStart + $tableWidth, $y - $rowHeight, \Zend_Pdf_Page::SHAPE_DRAW_STROKE);

				// Draw vertical line between description and quantity for each row
				$page->drawLine($qtyX + 5, $y, $qtyX + 5, $y - $rowHeight);

				$textY = $y - 12;
				foreach ($wrappedLines as $line) {
					$page->drawText($line, $xStart + 3, $textY);
					$textY -= 10;
				}

				$page->drawText((string)$qty, $qtyX + 15, $y - 12);
				$y -= $rowHeight;
			}

			// --- Footer / Barcode ---
			$barcodeHeight = 40;
			$barcodeMargin = 20;
			$y -= $barcodeMargin + $barcodeHeight;

			if ($y < 20) {
				// create new page if needed
				$newPage = new \Zend_Pdf_Page($pageWidth . ':' . $pageHeight);
				$pdf->pages[] = $newPage;
				$page = $newPage;
				$y = $pageHeight - 30;
			}

			// draw barcode (order ID) at current Y
			$barcodeImgResource = Barcode::factory(
				'code128', 'image', ['text' => $order->getIncrementId()], ['imageType' => 'png']
			)->draw();

			$tempBarcodePath = tempnam(sys_get_temp_dir(), 'barcode_') . '.png';
			imagepng($barcodeImgResource, $tempBarcodePath);
			$barcodeImg = \Zend_Pdf_Image::imageWithPath($tempBarcodePath);

			$xCenter = ($pageWidth - 200) / 2;
			$page->drawImage($barcodeImg, $xCenter, $y, $xCenter + 200, $y + $barcodeHeight);
			unlink($tempBarcodePath);
		}

		// --- Save PDF ---
		$directory = $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR) . '/cbo_invoices/';
		if (!is_dir($directory)) {
			mkdir($directory, 0777, true);
		}

		$fileName = 'CBO_invoices_' . date('Ymd_His') . '.pdf';
		$filePath = $directory . $fileName;
		file_put_contents($filePath, $pdf->render());
		$this->_logger->info('Thermal 80mm PDF generated: ' . $filePath);

		return $filePath;
	}

	/**
	 * Wrap long text into multiple lines
	 */
	protected function getWrappedLines($text, $maxChars = 40)
	{
		$words = explode(' ', $text);
		$lines = [];
		$currentLine = '';

		foreach ($words as $word) {
			if (strlen($currentLine . ' ' . $word) <= $maxChars) {
				$currentLine .= ($currentLine ? ' ' : '') . $word;
			} else {
				$lines[] = $currentLine;
				$currentLine = $word;
			}
		}

		if ($currentLine) {
			$lines[] = $currentLine;
		}

		return $lines;
	}

}
