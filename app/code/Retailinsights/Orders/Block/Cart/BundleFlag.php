<?php
namespace Retailinsights\Orders\Block\Cart;

use Magento\Framework\View\Element\Template;
use Magento\Checkout\Model\Session;

class BundleFlag extends Template
{
    private $checkoutSession;

    public function __construct(
        Template\Context $context,
        Session $checkoutSession,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->checkoutSession = $checkoutSession;
    }

    public function isBundleInvalid(): bool
    {
        $quote = $this->checkoutSession->getQuote();
        return $quote && (bool)$quote->getData('bundle_invalid');
    }
}
