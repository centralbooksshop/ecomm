<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Infomodus\Fedexlabel\Controller;

use Magento\Framework\App\RequestInterface;

/**
 * Customer address controller
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class Pdf extends \Magento\Framework\App\Action\Action
{
    protected $_handy;
    protected $_pdf;
    protected $fileFactory;
    protected $_customerSession;
    protected $_conf;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Infomodus\Fedexlabel\Helper\Handy $handy,
        \Infomodus\Fedexlabel\Helper\Pdf $pdf,
        \Infomodus\Fedexlabel\Helper\Config $config,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory
    )
    {
        $this->_handy = $handy;
        $this->_pdf = $pdf;
        $this->_conf = $config;
        $this->fileFactory = $fileFactory;
        $this->_customerSession = $customerSession;
        parent::__construct($context);
    }

    /**
     * Retrieve customer session object
     *
     * @return \Magento\Customer\Model\Session
     */
    protected function _getSession()
    {
        return $this->_customerSession;
    }

    /**
     * Check customer authentication
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    /*public function dispatch(RequestInterface $request)
    {
        if (!$this->_getSession()->authenticate()) {
            $this->_actionFlag->set('', 'no-dispatch', true);
        }
        return parent::dispatch($request);
    }*/

    /**
     * @param string $route
     * @param array $params
     * @return string
     */
    protected function _buildUrl($route = '', $params = [])
    {
        return $this->_url->getUrl($route, $params);
    }
}
