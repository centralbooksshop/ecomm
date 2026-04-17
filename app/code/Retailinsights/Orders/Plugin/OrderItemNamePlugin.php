<?php
namespace Retailinsights\Orders\Plugin;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Escaper;

class OrderItemNamePlugin
{
    /**
     * @var State
     */
    private $appState;

    /**
     * @var Escaper
     */
    private $escaper;

    public function __construct(
        State $appState,
        Escaper $escaper
    ) {
        $this->appState = $appState;
        $this->escaper = $escaper;
    }

    /**
     * Append given options suffix to item name for frontend/email contexts only.
     *
     * @param \Magento\Sales\Model\Order\Item $subject
     * @param string $result Original item name
     * @return string
     */
    public function afterGetName(\Magento\Sales\Model\Order\Item $subject, $result)
    {
        try {
            $area = $this->appState->getAreaCode();
        } catch (\Exception $e) {
            $area = null;
        }

        // Apply only for frontend (emails and storefront)
        if ($area !== Area::AREA_FRONTEND) {
            return $result;
        }

        $given = (int)$subject->getData('given_options');
        // treat given_options_msg as string and escape for HTML safety
        $givenOptionsMsg = (string)$subject->getData('given_options_msg');
        $givenOptionsMsg = trim($givenOptionsMsg);

        if ($given === 1) {
            if ($givenOptionsMsg !== '') {
                return $result . ' (' . $this->escaper->escapeHtml($givenOptionsMsg) . ')';
            }
            return $result . ' ' . __('(will be given)');
        }

        if ($given === 2) {
            return $result . ' ' . __('(This item will be issued by school)');
        }

        return $result;
    }
}
