<?php

namespace Centralbooks\Freshchat\Model;

use Magento\Framework\Model\Context;
use Psr\Log\LoggerInterface as PsrLogger;

class Itemmaster {

    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    protected $_curl;

    public function __construct(\Magento\Framework\HTTP\Client\Curl $curl, PsrLogger $logger) {
        $this->_curl = $curl;
        $this->_logger = $logger;
//        ?parent::__construct($context);
    }

    public function helloFromModel() {
        try {
            $postfields = array("username" => "MAGENTO",
                "password" => "1234P@ssw0rd",
                "grant_type" => "password");
            
            $fields_string = http_build_query($postfields);
            $curl = curl_init();

            curl_setopt_array($curl, array(
              CURLOPT_URL => 'https://cbsnav.centralbooks.in/CBSUATAPI/api/GetToken',
              CURLOPT_CUSTOMREQUEST => 'GET',
              CURLOPT_POSTFIELDS => $fields_string,  //'username=MAGENTO&password=1234P%40ssw0rd&grant_type=password',
              CURLOPT_HTTPHEADER => array(
                'Content-Type: application/x-www-form-urlencoded'
              ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            echo $response;




//            $postfields = array("username" => "MAGENTO",
//                "password" => "1234P@ssw0rd",
//                "grant_type" => "password");
//            $curl = curl_init();
//
//            curl_setopt_array($curl, array(
//                CURLOPT_URL => 'https://cbsnav.centralbooks.in/CBSUATAPI/api/GetToken',
//                CURLOPT_CUSTOMREQUEST => 'GET',
//                CURLOPT_POSTFIELDS => 'username=MAGENTO&password=1234P%40ssw0rd&grant_type=password',
//                CURLOPT_HTTPHEADER => array(
//                    'Content-Type: application/x-www-form-urlencoded'
//                ),
//            ));
//
//            $response = curl_exec($curl);
//
//            curl_close($curl);
//            echo $response;


//            $postfields = array("username" => "MAGENTO",
//                "password" => "1234P@ssw0rd",
//                "grant_type" => "password");
//            
//            $fields_string = http_build_query($postfields);
//            
//            print_r($fields_string);
//            echo "<br />";
//            $url = "https://cbsnav.centralbooks.in/CBSUATAPI/api/GetToken";
//            //if the method is get
////            $this->_curl->get($url);
//
//            $headers = [
//                "Content-Type" => "application/x-www-form-urlencoded",
//            ];
////            $options = [CURLOPT_POSTFIELDS = $fields_string];
//            $this->_curl->setOption(CURLOPT_POSTFIELDS,$fields_string);
//            $this->_curl->setHeaders($headers);
//            $this->_curl->get($url);
//            $response = $this->_curl->getBody();
//
//            print_r($response);
//            $options = [CURLOPT_RETURNTRANSFER => true, CURLOPT_PORT => 8080];
//            
//            $this->_curl->setOption(CURLOPT_POSTFIELDS,)
//            $url="https://ess.b2bsoftech.com/CBSWEBAPI/api/MasterItemDetails";
//            $headers = [
//                    "Content-Type" => "application/json",
//                    "Content-Length" => "200",
//                    "Authorization"=>"bearer xTE3KTejgIftqSPOjfFNJmmldkUk1UcMOZLGoWkGHZBKKhZohJ4hCwgHxneVOVR018_EKocNUm7vzruPr9sFgDs9j5j1rZf-2hcaRVhdRs2zWJrNm03D5ZRa49VHOHgulKXz8UXa_WR7pw9gq27rdoAwUdD1EPTmlE4IDN9jr6k6sONtsWavUDKcH_1bBVPc5B_yX2RhIf6Yr-yjwRkNCvEKp3XXfWvzQPMNveB55NNOs2u2kL0pTGvlCCdxl8lZL2eb13hVeQ2BqoUPSk76n2YAN_4XGZN6pBAqn06Tdre-sIjM73qUsIJZJO2Fzrcrgt9iPpgDD0PoghfIoHeb-EOK2ze5iuAGR9HyPsf-WSE",
//                    "username"=>"MAGENTO",
//                    "password"=>"1234P@ssw0rd",
//                ];
//            $this->_curl->setHeaders($headers);
//            $this->curl->get($url);
//            //read response
//            $response = $this->curl->getBody();
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }

        return 'Hello';
    }

}
