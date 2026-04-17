<?php

namespace Centralbooks\Freshchat\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;


class Data extends AbstractHelper {

    public function getUsername() {
        return $this->scopeConfig->getValue('cb_navision/general/username', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getPassword() {
        return $this->scopeConfig->getValue('cb_navision/general/password', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getApiurl() {
        return $this->scopeConfig->getValue('cb_navision/general/url', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    
    public function getNavisionlogging($message){
    }
    
    /**
     * @return mixed|string
     */
    public function GenerateToken()
    {

        try {
            //$this->getNavisionlogging("generate api call started");
            $postfields = array("username" => $this->getUsername(), //"MAGENTO",
                "password" => $this->getPassword(), //"1234P@ssw0rd",
                "grant_type" => "password");

            $fields_string = http_build_query($postfields);
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => $this->getApiurl() . 'CBSUATAPI/api/GetToken',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_POSTFIELDS => $fields_string, //'username=MAGENTO&password=1234P%40ssw0rd&grant_type=password',
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/x-www-form-urlencoded'
                ),
            ));

            $response = curl_exec($curl);
            if ($e = curl_error($curl)) {
                //$this->getNavisionlogging($e);
                return "error";
            } else {
                $rep = json_decode($response, TRUE);

                if (isset($rep["access_token"])) {
                    //$this->getNavisionlogging("NAvision token generation successfull");
                    return $rep["access_token"];
                } else {
                    //$this->getNavisionlogging($rep["Message"]);
                    return "error";
                }
            }
            curl_close($curl);
        } catch (Exception $exc) {
            //$this->getNavisionlogging($exc->getMessage());
            return "error";
        }
    }
}
