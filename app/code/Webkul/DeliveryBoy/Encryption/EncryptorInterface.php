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

interface EncryptorInterface
{
    public const HASH_VERSION_SHA_256 = 'sha256';

    /**
     * Get SHa256 hash.
     *
     * @param string $data
     * @return string
     */
    public function getSha256Hash(string $data): string;
}
