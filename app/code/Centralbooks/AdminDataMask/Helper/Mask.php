<?php
namespace Centralbooks\AdminDataMask\Helper;

/**
 * Helper class for masking customer data fields.
 */
class Mask
{
       /**
        * Mask an email address.
        *
        * @param string $email
        * @return string
        */
    public function maskEmail($email)
    {
        if (!$email) {
            return $email;
        }
        return preg_replace('/(.{2}).+(@.+)/', '$1****$2', $email);
    }
   /**
    * Mask a phone number.
    *
    * @param string $phone
    * @return string
    */
    public function maskPhone($phone)
    {
        if (!$phone) {
            return $phone;
        }
        return substr($phone, 0, 2) . '******' . substr($phone, -2);
    }
 /**
  * Mask a name
  *
  * @param string $name
  * @return string
  */
    public function maskName($name)
    {
        if (!$name) {
            return $name;
        }
        return substr($name, 0, 1) . '****';
    }
      /**
       * Mask an address.
       *
       * @param string $address
       * @return string
       */
    public function maskAddress($address)
    {
        if (!$address) {
            return $address;
        }
        $lines = explode(',', $address);

        if (count($lines) <= 1) {
            return '******';
        }

        $lastLine = array_pop($lines);

        return '****** , ' . $lastLine;
    }
}
