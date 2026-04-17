<?php
/**
 * Webkul Software.
 *
 *
 * @category  Webkul
 * @package   Webkul_DeliveryBoy
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */
namespace Webkul\DeliveryBoy\Plugin;

use Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\CollectionFactory as DeliveryboyCollectionFactory;
use Magento\Framework\Controller\ResultFactory;

class VerifyDeliveryBoy
{
    /**
     * @var array
     */
    private $singleParamSupportedApis;

    /**
     * @var array
     */
    private $multipleParamsSupportedApis;

    /**
     * @var DeliveryboyCollectionFactory
     */
    private $deliveryboyCollectionFactory;

    /**
     * @param array $singleParamSupportedApis
     * @param array $multipleParamsSupportedApis
     * @param DeliveryboyCollectionFactory $deliveryboyCollectionFactory
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param ResultFactory $resultFactory
     */
    public function __construct(
        $singleParamSupportedApis,
        $multipleParamsSupportedApis,
        DeliveryboyCollectionFactory $deliveryboyCollectionFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        ResultFactory $resultFactory
    ) {
        $this->singleParamSupportedApis = $singleParamSupportedApis;
        $this->multipleParamsSupportedApis = $multipleParamsSupportedApis;
        $this->deliveryboyCollectionFactory = $deliveryboyCollectionFactory;
        $this->request = $request;
        $this->jsonHelper = $jsonHelper;
        $this->resultFactory = $resultFactory;
    }

   /**
    * Verify Deliveryboy existance.
    *
    * @param \Webkul\DeliveryBoy\Controller\Api\ApiController $subject
    * @param \Closure $proceed
    * @return mixed
    */
    public function aroundExecute(
        \Webkul\DeliveryBoy\Controller\Api\ApiController $subject,
        \Closure $proceed
    ) {
        if ($this->shouldIntercept()) {
            $firstAttemptId =  $this->getDeliveryboyIdFromSingleParam();
            if ($firstAttemptId !== false && $firstAttemptId == 0) {
                return $this->getFailureResponse();
            }
            $secondAttemptId = $this->getDeliveryboyIdFromMultipleParam();
            if ($secondAttemptId !== false && $secondAttemptId == 0) {
                return $this->getFailureResponse();
            }
        }
        return $proceed($subject);
    }

    /**
     * Get failure response.
     *
     * @return array
     */
    public function getFailureResponse()
    {
        $response = [
            'success' => false,
            'otherError' => __('User no longer exist')
        ];
        return $this->getJsonResponse($response);
    }

    /**
     * Should verify.
     *
     * @return bool
     */
    public function shouldIntercept()
    {
        $firstAttempt =  $this->getDeliveryboyIdFromSingleParam();
        $secondAttempt = $this->getDeliveryboyIdFromMultipleParam();
        if ($firstAttempt !== false || $secondAttempt !== false) {
            return true;
        }
        return false;
    }

    /**
     * Return full action name.
     *
     * @return string
     */
    public function getFullActionName()
    {
        $fullActionName = $this->request->getFullActionName('_');
        return strtolower($fullActionName);
    }

    /**
     * Get Param from the request.
     *
     * @param string $name
     * @return mixed
     */
    public function getParam($name)
    {
        return $this->request->getParam($name, $this->request->getPostValue($name, ""));
    }

    /**
     * Get Deliveryboy id from multiple params.
     *
     * @return int|bool
     */
    public function getDeliveryboyIdFromMultipleParam()
    {
        $fullActionName = $this->getFullActionName();
        if (isset($this->multipleParamsSupportedApis[$fullActionName])) {
            $params = $this->multipleParamsSupportedApis[$fullActionName];
            $checkParamName = $params['check'];
            $idParamName = $params['id'];
            
            $checkParamValue = $this->getParam($checkParamName);
            $idParamValue = $this->getParam($idParamName);
            if ($checkParamValue <= 0 || $idParamValue <= 0) {
                return false;
            }

            $firstItem = $this->deliveryboyCollectionFactory->create()
                ->addFieldToFilter(
                    'id',
                    $idParamValue
                )->getFirstItem();
            return $firstItem->getId();
        }
        return false;
    }
    
    /**
     * Get deliveryboy id from single param.
     *
     * @return int|bool
     */
    public function getDeliveryboyIdFromSingleParam()
    {
        $fullActionName = $this->getFullActionName();
        if (isset($this->singleParamSupportedApis[$fullActionName])) {
            $paramName = $this->singleParamSupportedApis[$fullActionName];
            $paramValue = $this->getParam($paramName);
            if(is_string($paramValue)) {
                return true;
            }
            if ($paramValue) {
                $firstItem = $this->deliveryboyCollectionFactory->create()
                    ->addFieldToFilter(
                        'id',
                        $paramValue
                    )->getFirstItem();
                return $firstItem->getId();
            }
            return false;
        }
        return false;
    }

    /**
     * Get Json Response.
     *
     * @param array $responseContent
     * @param int $responseCode
     * @param string $token
     * @return \Magento\Framework\Controller\ResultInterface
     */
    protected function getJsonResponse(
        array $responseContent = [],
        int $responseCode = \Magento\Framework\Webapi\Response::HTTP_OK,
        string $token = null
    ): \Magento\Framework\Controller\ResultInterface {
        $resultJson = $this->resultFactory
            ->create(ResultFactory::TYPE_JSON)
            ->setHttpResponseCode($responseCode)
            ->setData($responseContent);
        if ($token) {
            $resultJson->setHeader("token", $token, true);
        }
        return $resultJson;
    }
}
