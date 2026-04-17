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
namespace Webkul\DeliveryBoy\Encryption;

class Encryptor implements EncryptorInterface
{
    /**
     * Get Sha256 hash.
     *
     * @param string $data
     * @return string
     */
    public function getSha256Hash(string $data): string
    {
        return hash(self::HASH_VERSION_SHA_256, $data);
    }
}
