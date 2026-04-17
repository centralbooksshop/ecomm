<?php
/*
 * Author Rudyuk Vitalij Anatolievich
 * Email rvansp@gmail.com
 * Blog www.cervic.info
 */

namespace Infomodus\Fedexlabel\Helper;

class Config extends \Magento\Framework\App\Helper\AbstractHelper
{
    public $error = true;
    protected $filesystem;
    protected $storeManager;
    protected $shippingConfig;
    private $ch;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Shipping\Model\Config $shippingConfig
    )
    {
        parent::__construct($context);
        $this->filesystem = $filesystem;
        $this->storeManager = $storeManager;
        $this->shippingConfig = $shippingConfig;
    }

    public function getBaseDir($alias)
    {
        $reader = $this->filesystem->getDirectoryRead($alias);
        return $reader->getAbsolutePath();
    }

    public function getBaseUrl($alias)
    {
        return $this->storeManager->getStore()->getBaseUrl($alias);
    }

    public function getStoreConfig($config_path, $scopeCode = null)
    {
        return $this->scopeConfig->getValue(
            $config_path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $scopeCode
        );
    }

    public function createMediaFolders()
    {
        $baseMediaDir = $this->getBaseDir('media');

        $path_upsdir = $baseMediaDir . '/fedexlabel';
        if (!is_dir($path_upsdir)) {
            mkdir($path_upsdir, 0777);
        }
        $path_upsdir = $baseMediaDir . '/fedexlabel/label';
        if (!is_dir($path_upsdir)) {
            mkdir($path_upsdir, 0777);
        }
        $path_upsdir = $baseMediaDir . '/fedexlabel/test_xml';
        if (!is_dir($path_upsdir)) {
            mkdir($path_upsdir, 0777);
        }

        if (is_dir($path_upsdir)) {
            if (!file_exists($path_upsdir . "/.htaccess")) {
                file_put_contents($path_upsdir . "/.htaccess", "deny from all");
            }
        }
    }

    public function getRequest()
    {
        return $this->_getRequest();
    }

    public function getUrl($route, $params = [])
    {
        return parent::_getUrl($route, $params);
    }

    public function escapeXML($string)
    {
        //$string = preg_replace('/&/is', '&amp;', $string);
        //$string = preg_replace('/</is', '&lt;', $string);
        //$string = preg_replace('/>/is', '&gt;', $string);
       // $string = preg_replace('/\'/is', '&apos;', $string);
        //$string = preg_replace('/"/is', '&quot;', $string);
        $string = str_replace(
            ['ą', 'ć', 'ę', 'ł', 'ń', 'ó', 'ś', 'ź', 'ż', 'Ą', 'Ć', 'Ę', 'Ł', 'Ń', 'Ó', 'Ś', 'Ź', 'Ż', 'ü', 'ò', 'è', 'à', 'ì', 'é', 'ô', 'Ä', 'ä', 'Ü', 'ü', 'Ö', 'ö', 'ß',
                'À', 'Á', 'Â', 'Ã', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ô', 'Õ', 'Ù', 'Ú', 'Û', 'Ý', 'Þ', 'á', 'â', 'ã', 'å', 'æ', 'ç', 'ê', 'ë', 'í', 'î', 'ï', 'ð', 'ñ', 'õ', 'ù', 'ú', 'û', 'ý', 'þ', 'ÿ', 'Œ', 'œ', 'Š', 'š', 'Ÿ', 'Ø', 'ø'],
            ['a', 'c', 'e', 'l', 'n', 'o', 's', 'z', 'z', 'A', 'C', 'E', 'L', 'N', 'O', 'S', 'Z', 'Z', 'u', 'o', 'e', 'a', 'i', 'e', 'o', 'A', 'a', 'U', 'u', 'O', 'o', 'ss',
                'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'U', 'U', 'U', 'Y', 'Th', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'i', 'i', 'i', 'o', 'n', 'o', 'u', 'u', 'u', 'y', 'th', 'y', 'Oe', 'oe', 'S', 's', 'Y', 'O', 'o'],
            $string
        );
        return $string;
    }

    public function escapePhone($phone)
    {
        return str_replace(array(" ", "+", "-"), array("", "", ""), $phone);
    }

    public function curlSend($url, $data = null)
    {
        $this->error = true;
        $result = $this->curlSetOption($url, $data);
        $ch = $this->ch;
        if ($result) {
            $result1 = $result;
            $result = strstr($result, '<?xml');
            if ($result === false) {
                $result = $result1;
            }
            curl_close($ch);
            $this->error = false;
            return $result;
        } else {
            curl_close($ch);
            $this->error = true;
            return ['errordesc' => 'Server Error (cUrl)', 'error' => curl_errno($ch) . ' - ' . curl_error($ch)];
        }
    }

    public function curlSetOption($url, $data = null)
    {
        $sslV = curl_version();
        $ch = curl_init($url);
        if ($data != null) {
            curl_setopt($ch, CURLOPT_HEADER, 1);
        } else {
            curl_setopt($ch, CURLOPT_HEADER, 0);
        }
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        if (strpos($sslV['ssl_version'], 'NSS/') === false) {
            curl_setopt($ch, CURLOPT_SSL_CIPHER_LIST, 'TLSv1.2');
        }
        if ($data !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        $this->ch = $ch;
        return curl_exec($ch);
    }

    public function sendPrint($data, $storeId = null)
    {
        try {
            $ip = trim($this->getStoreConfig('fedexlabel/printing/automatic_printing_ip', $storeId));
            $port = trim($this->getStoreConfig('fedexlabel/printing/automatic_printing_port', $storeId));
            $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            if ($ip != '' && $port != '') {
                if ($socket === false) {
                    $this->_logger->error("socket_create() failed: reason: " . socket_strerror(socket_last_error()));
                } else {
                    $result = socket_connect($socket, $ip, $port);
                    if ($result === false) {
                        $this->_logger->error("socket_connect() failed.\nReason: (" . $ip . ":" . $port . ") "
                            . socket_strerror(socket_last_error($socket)));
                    } else {
                        socket_write($socket, $data, strlen($data));
                    }
                    socket_close($socket);
                }
            } else {
                $this->_logger->error("ip and port are not specified");
            }
        } catch (\Exception $e) {
            $this->_logger->error("Print error " . $e->getCode() . ": " . $e->getMessage());
        }
    }

    public function getStoreByCode($storeCode)
    {
        $manager = $this->storeManager;
        $stores = array_keys($manager->getStores());
        foreach ($stores as $id) {
            $store = $manager->getStore($id);
            if ($store->getCode() == $storeCode) {
                return $store;
            }
        }
        return null; // if not found
    }

    public function getShippingMethods($storeId = null)
    {
        $option = [];
        $_methods = $this->shippingConfig->getActiveCarriers($storeId);
        foreach ($_methods as $_carrierCode => $_carrier) {
            if ($_carrierCode !== "ups" && $_carrierCode !== "dhl" && $_carrierCode !== "fedex"
                && $_carrierCode !== "usps"
                && $_method = $_carrier->getAllowedMethods()) {
                if (!$_title = $this->getStoreConfig('carriers/' . $_carrierCode . '/title', $storeId)) {
                    $_title = $_carrierCode;
                }
                foreach ($_method as $_mcode => $_m) {
                    $_code = $_carrierCode . '_' . $_mcode;
                    $option[] = ['label' => "(" . $_title . ")  " . $_m, 'value' => $_code];
                }
            }
        }
        return $option;
    }

    public function getShippingMethodsSimple($storeId = null)
    {
        $option = [];
        $_methods = $this->shippingConfig->getActiveCarriers($storeId);
        foreach ($_methods as $_carrierCode => $_carrier) {
            if ($_carrierCode !== "ups" && $_carrierCode !== "dhl" && $_carrierCode !== "fedex"
                && $_carrierCode !== "usps" && $_method = $_carrier->getAllowedMethods()) {
                if (!$_title = $this->getStoreConfig('carriers/' . $_carrierCode . '/title', $storeId)) {
                    $_title = $_carrierCode;
                }
                foreach ($_method as $_mcode => $_m) {
                    $_code = $_carrierCode . '_' . $_mcode;
                    $option[$_code] = "(" . $_title . ")  " . $_m;
                }
            }
        }
        return $option;
    }

    public function getCurrencyByCountry($countryCode)
    {
        $c = array("AF" => "AFN", "AL" => "ALL", "DZ" => "DZD", "AS" => "USD", "CM" => "USD", "EC" => "USD", "SV" => "USD", "GU" => "USD", "HT" => "USD", "MH" => "USD", "FM" => "USD", "MP" => "USD", "PW" => "USD", "PA" => "USD", "PR" => "USD", "TL" => "USD", "TC" => "USD", "US" => "USD", "VG" => "USD", "VI" => "USD", "YA" => "USD", "AD" => "EUR", "AT" => "EUR", "BE" => "EUR", "CY" => "EUR", "EE" => "EUR", "FI" => "EUR", "FR" => "EUR", "GF" => "EUR", "TF" => "EUR", "DE" => "EUR", "GR" => "EUR", "GP" => "EUR", "IE" => "EUR", "IT" => "EUR", "LV" => "EUR", "LT" => "EUR", "LU" => "EUR", "MT" => "EUR", "MQ" => "EUR", "YT" => "EUR", "MC" => "EUR", "ME" => "EUR", "NL" => "EUR", "PT" => "EUR", "RE" => "EUR", "SM" => "EUR", "SK" => "EUR", "SI" => "EUR", "ES" => "EUR", "AO" => "AOA", "AI" => "XCD", "AG" => "XCD", "DM" => "XCD", "GD" => "XCD", "MS" => "XCD", "KN" => "XCD", "LC" => "XCD", "VC" => "XCD", "AR" => "ARS", "AM" => "AMD", "AW" => "AWG", "AU" => "AUD", "CX" => "AUD", "CC" => "AUD", "HM" => "AUD", "KI" => "AUD", "NR" => "AUD", "NF" => "AUD", "TV" => "AUD", "AZ" => "AZN", "BS" => "BSD", "BH" => "BHD", "BD" => "BDT", "BB" => "BBD", "BY" => "BYR", "BZ" => "BZD", "BJ" => "XOF", "BF" => "XOF", "CI" => "XOF", "GW" => "XOF", "ML" => "XOF", "NE" => "XOF", "SN" => "XOF", "TG" => "XOF", "BM" => "BMD", "BT" => "INR", "IN" => "INR", "BO" => "BOB", "BA" => "BAM", "BW" => "BWP", "NO" => "NOK", "BR" => "BRL", "BN" => "BND", "BG" => "BGN", "BI" => "BIF", "KH" => "KHR", "CA" => "CAD", "CV" => "CVE", "KY" => "KYD", "CF" => "XAF", "TD" => "XAF", "CG" => "XAF", "GQ" => "XAF", "GA" => "XAF", "CL" => "CLP", "CN" => "RMB", "CO" => "COP", "KM" => "KMF", "CD" => "CDF", "CK" => "NZD", "NZ" => "NZD", "PN" => "NZD", "CR" => "CRC", "HR" => "HRK", "CU" => "CUP", "CZ" => "CZK", "DK" => "DKK", "FO" => "DKK", "GL" => "DKK", "DJ" => "DJF", "DO" => "DOP", "EG" => "EGP", "ER" => "ERN", "ET" => "ETB", "FK" => "FKP", "FJ" => "FJD", "PF" => "XPF", "NC" => "XPF", "WF" => "XPF", "GM" => "GMD", "GE" => "GEL", "GH" => "GHS", "GI" => "GIP", "GT" => "GTQ", "GG" => "GBP", "JE" => "GBP", "GB" => "GBP", "WL" => "GBP", "GN" => "GNF", "GY" => "GYD", "HN" => "HNL", "HK" => "HKD", "HU" => "HUF", "IS" => "ISK", "ID" => "IDR", "IR" => "IRR", "IQ" => "IQD", "IL" => "ILS", "JM" => "JMD", "JP" => "JPY", "JO" => "JOD", "KZ" => "KZT", "KE" => "KES", "KP" => "KPW", "KR" => "KRW", "KW" => "KWD", "KG" => "KGS", "LA" => "LAK", "LB" => "LBP", "LS" => "ZAR", "ZA" => "ZAR", "LR" => "LRD", "LY" => "LYD", "LI" => "CHF", "CH" => "CHF", "MO" => "MOP", "MK" => "MKD", "MG" => "MGA", "MW" => "MWK", "MY" => "MYR", "MV" => "MVR", "MR" => "MRO", "MU" => "MUR", "MX" => "MXN", "MD" => "MDL", "MN" => "MNT", "MA" => "MAD", "MZ" => "MZN", "MM" => "MMK", "NP" => "NPR", "NI" => "NIO", "NG" => "NGN", "OM" => "OMR", "PK" => "PKR", "PG" => "PGK", "PY" => "PYG", "PE" => "PEN", "PH" => "PHP", "PL" => "PLN", "QA" => "QAR", "RO" => "RON", "RU" => "RUB", "RW" => "RWF", "WS" => "WST", "ST" => "STD", "SA" => "SAR", "RS" => "RSD", "SC" => "SCR", "SL" => "SLL", "SG" => "SGD", "SB" => "SBD", "LK" => "LKR", "SR" => "SRD", "SZ" => "SZL", "SE" => "SEK", "TW" => "TWD", "TJ" => "TJS", "TZ" => "TZS", "TH" => "THB", "TO" => "TOP", "TT" => "TTD", "TN" => "TND", "TR" => "TRY", "TM" => "TMM", "UG" => "UGX", "UA" => "UAH", "AE" => "AED", "UY" => "UYU", "UZ" => "UZS", "VU" => "VUV", "VE" => "VEB", "VN" => "VND", "YE" => "YER", "ZM" => "ZMK", "ZW" => "ZWD");

        $response = isset($c[$countryCode]) ? $c[$countryCode] : 'USD';
        return $response;
    }

    public function getWeightUnitByCountry($countryCode)
    {
        $c = array("AD" => "KG", "AE" => "KG", "AF" => "KG", "AG" => "LB", "AI" => "LB", "AL" => "KG", "AM" => "KG", "AN" => "KG", "AO" => "KG", "AR" => "KG", "AS" => "LB", "AT" => "KG", "AU" => "KG", "AW" => "LB", "AZ" => "KG", "BA" => "KG", "BB" => "LB", "BD" => "KG", "BE" => "KG", "BF" => "KG", "BG" => "KG", "BH" => "KG", "BI" => "KG", "BJ" => "KG", "BM" => "LB", "BN" => "KG", "BO" => "KG", "BR" => "KG", "BS" => "LB", "BT" => "KG", "BW" => "KG", "BY" => "KG", "BZ" => "KG", "CA" => "LB", "CD" => "KG", "CF" => "KG", "CG" => "KG", "CH" => "KG", "CI" => "KG", "CK" => "KG", "CL" => "KG", "CM" => "KG", "CN" => "KG", "CO" => "KG", "CR" => "KG", "CU" => "KG", "CV" => "KG", "CY" => "KG", "CZ" => "KG", "DE" => "KG", "DJ" => "KG", "DK" => "KG", "DM" => "LB", "DO" => "LB", "DZ" => "KG", "EC" => "KG", "EE" => "KG", "EG" => "KG", "ER" => "KG", "ES" => "KG", "ET" => "KG", "FI" => "KG", "FJ" => "KG", "FK" => "KG", "FM" => "LB", "FO" => "KG", "FR" => "KG", "GA" => "KG", "GB" => "KG", "GD" => "LB", "GE" => "KG", "GF" => "KG", "GG" => "KG", "GH" => "KG", "GI" => "KG", "GL" => "KG", "GM" => "KG", "GN" => "KG", "GP" => "KG", "GQ" => "KG", "GR" => "KG", "GT" => "KG", "GU" => "LB", "GW" => "KG", "GY" => "LB", "HK" => "KG", "HN" => "KG", "HR" => "KG", "HT" => "LB", "HU" => "KG", "IC" => "KG", "ID" => "KG", "IE" => "KG", "IL" => "KG", "IN" => "KG", "IQ" => "KG", "IR" => "KG", "IS" => "KG", "IT" => "KG", "JE" => "KG", "JM" => "KG", "JO" => "KG", "JP" => "KG", "KE" => "KG", "KG" => "KG", "KH" => "KG", "KI" => "KG", "KM" => "KG", "KN" => "LB", "KP" => "KG", "KR" => "KG", "KV" => "KG", "KW" => "KG", "KY" => "LB", "KZ" => "KG", "LA" => "KG", "LB" => "KG", "LC" => "LB", "LI" => "KG", "LK" => "KG", "LR" => "KG", "LS" => "KG", "LT" => "KG", "LU" => "KG", "LV" => "KG", "LY" => "KG", "MA" => "KG", "MC" => "KG", "MD" => "KG", "ME" => "KG", "MG" => "KG", "MH" => "LB", "MK" => "KG", "ML" => "KG", "MM" => "KG", "MN" => "KG", "MO" => "KG", "MP" => "LB", "MQ" => "KG", "MR" => "KG", "MS" => "LB", "MT" => "KG", "MU" => "KG", "MV" => "KG", "MW" => "KG", "MX" => "KG", "MY" => "KG", "MZ" => "KG", "NA" => "KG", "NC" => "KG", "NE" => "KG", "NG" => "KG", "NI" => "KG", "NL" => "KG", "NO" => "KG", "NP" => "KG", "NR" => "KG", "NU" => "KG", "NZ" => "KG", "OM" => "KG", "PA" => "KG", "PE" => "KG", "PF" => "KG", "PG" => "KG", "PH" => "KG", "PK" => "KG", "PL" => "KG", "PR" => "LB", "PT" => "KG", "PW" => "KG", "PY" => "KG", "QA" => "KG", "RE" => "KG", "RO" => "KG", "RS" => "KG", "RU" => "KG", "RW" => "KG", "SA" => "KG", "SB" => "KG", "SC" => "KG", "SD" => "KG", "SE" => "KG", "SG" => "KG", "SH" => "KG", "SI" => "KG", "SK" => "KG", "SL" => "KG", "SM" => "KG", "SN" => "KG", "SO" => "KG", "SR" => "KG", "SS" => "KG", "ST" => "KG", "SV" => "KG", "SY" => "KG", "SZ" => "KG", "TC" => "LB", "TD" => "KG", "TG" => "KG", "TH" => "KG", "TJ" => "KG", "TL" => "KG", "TN" => "KG", "TO" => "KG", "TR" => "KG", "TT" => "LB", "TV" => "KG", "TW" => "KG", "TZ" => "KG", "UA" => "KG", "UG" => "KG", "US" => "LB", "UY" => "KG", "UZ" => "KG", "VC" => "LB", "VE" => "KG", "VG" => "LB", "VI" => "LB", "VN" => "KG", "VU" => "KG", "WS" => "KG", "XB" => "LB", "XC" => "LB", "XE" => "LB", "XM" => "LB", "XN" => "LB", "XS" => "KG", "XY" => "LB", "YE" => "KG", "YT" => "KG", "ZA" => "KG", "ZM" => "KG", "ZW" => "KG");
        $response = isset($c[$countryCode]) ? $c[$countryCode] : 'KG';
        return $response;
    }

    public function getDimensionUnitByCountry($countryCode)
    {
        $c = array("AD" => "CM", "AE" => "CM", "AF" => "CM", "AG" => "IN", "AI" => "IN", "AL" => "CM", "AM" => "CM", "AN" => "CM", "AO" => "CM", "AR" => "CM", "AS" => "IN", "AT" => "CM", "AU" => "CM", "AW" => "IN", "AZ" => "CM", "BA" => "CM", "BB" => "IN", "BD" => "CM", "BE" => "CM", "BF" => "CM", "BG" => "CM", "BH" => "CM", "BI" => "CM", "BJ" => "CM", "BM" => "IN", "BN" => "CM", "BO" => "CM", "BR" => "CM", "BS" => "IN", "BT" => "CM", "BW" => "CM", "BY" => "CM", "BZ" => "CM", "CA" => "IN", "CD" => "CM", "CF" => "CM", "CG" => "CM", "CH" => "CM", "CI" => "CM", "CK" => "CM", "CL" => "CM", "CM" => "CM", "CN" => "CM", "CO" => "CM", "CR" => "CM", "CU" => "CM", "CV" => "CM", "CY" => "CM", "CZ" => "CM", "DE" => "CM", "DJ" => "CM", "DK" => "CM", "DM" => "IN", "DO" => "IN", "DZ" => "CM", "EC" => "CM", "EE" => "CM", "EG" => "CM", "ER" => "CM", "ES" => "CM", "ET" => "CM", "FI" => "CM", "FJ" => "CM", "FK" => "CM", "FM" => "IN", "FO" => "CM", "FR" => "CM", "GA" => "CM", "GB" => "CM", "GD" => "IN", "GE" => "CM", "GF" => "CM", "GG" => "CM", "GH" => "CM", "GI" => "CM", "GL" => "CM", "GM" => "CM", "GN" => "CM", "GP" => "CM", "GQ" => "CM", "GR" => "CM", "GT" => "CM", "GU" => "IN", "GW" => "CM", "GY" => "IN", "HK" => "CM", "HN" => "CM", "HR" => "CM", "HT" => "IN", "HU" => "CM", "IC" => "CM", "ID" => "CM", "IE" => "CM", "IL" => "CM", "IN" => "CM", "IQ" => "CM", "IR" => "CM", "IS" => "CM", "IT" => "CM", "JE" => "CM", "JM" => "CM", "JO" => "CM", "JP" => "CM", "KE" => "CM", "KG" => "CM", "KH" => "CM", "KI" => "CM", "KM" => "CM", "KN" => "IN", "KP" => "CM", "KR" => "CM", "KV" => "CM", "KW" => "CM", "KY" => "IN", "KZ" => "CM", "LA" => "CM", "LB" => "CM", "LC" => "IN", "LI" => "CM", "LK" => "CM", "LR" => "CM", "LS" => "CM", "LT" => "CM", "LU" => "CM", "LV" => "CM", "LY" => "CM", "MA" => "CM", "MC" => "CM", "MD" => "CM", "ME" => "CM", "MG" => "CM", "MH" => "IN", "MK" => "CM", "ML" => "CM", "MM" => "CM", "MN" => "CM", "MO" => "CM", "MP" => "IN", "MQ" => "CM", "MR" => "CM", "MS" => "IN", "MT" => "CM", "MU" => "CM", "MV" => "CM", "MW" => "CM", "MX" => "CM", "MY" => "CM", "MZ" => "CM", "NA" => "CM", "NC" => "CM", "NE" => "CM", "NG" => "CM", "NI" => "CM", "NL" => "CM", "NO" => "CM", "NP" => "CM", "NR" => "CM", "NU" => "CM", "NZ" => "CM", "OM" => "CM", "PA" => "CM", "PE" => "CM", "PF" => "CM", "PG" => "CM", "PH" => "CM", "PK" => "CM", "PL" => "CM", "PR" => "IN", "PT" => "CM", "PW" => "CM", "PY" => "CM", "QA" => "CM", "RE" => "CM", "RO" => "CM", "RS" => "CM", "RU" => "CM", "RW" => "CM", "SA" => "CM", "SB" => "CM", "SC" => "CM", "SD" => "CM", "SE" => "CM", "SG" => "CM", "SH" => "CM", "SI" => "CM", "SK" => "CM", "SL" => "CM", "SM" => "CM", "SN" => "CM", "SO" => "CM", "SR" => "CM", "SS" => "CM", "ST" => "CM", "SV" => "CM", "SY" => "CM", "SZ" => "CM", "TC" => "IN", "TD" => "CM", "TG" => "CM", "TH" => "CM", "TJ" => "CM", "TL" => "CM", "TN" => "CM", "TO" => "CM", "TR" => "CM", "TT" => "IN", "TV" => "CM", "TW" => "CM", "TZ" => "CM", "UA" => "CM", "UG" => "CM", "US" => "IN", "UY" => "CM", "UZ" => "CM", "VC" => "IN", "VE" => "CM", "VG" => "IN", "VI" => "IN", "VN" => "CM", "VU" => "CM", "WS" => "CM", "XB" => "IN", "XC" => "IN", "XE" => "IN", "XM" => "IN", "XN" => "IN", "XS" => "CM", "XY" => "IN", "YE" => "CM", "YT" => "CM", "ZA" => "CM", "ZM" => "CM", "ZW" => "CM");
        $response = isset($c[$countryCode]) ? $c[$countryCode] : 'CM';
        return $response;
    }

    public function arraySum($arr1, $arr2)
    {
        $result = [];

        foreach ($arr1 as $val) {
            $result[] = $val;
        }

        foreach ($arr2 as $val) {
            $result[] = $val;
        }

        return $result;
    }
}
