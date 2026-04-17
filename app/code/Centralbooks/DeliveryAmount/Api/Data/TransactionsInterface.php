<?php
declare(strict_types=1);

namespace Centralbooks\DeliveryAmount\Api\Data;

interface TransactionsInterface
{

    const ID = 'id';

    /**
     * Get id
     * @return string|null
     */
    public function getId();

    /**
     * Set id
     * @param string $id
     * @return \Centralbooks\DeliveryAmount\Amount\Api\Data\TransactionsInterface
     */
    public function setId($id);
}



