<?php
/**
 * Copyright © 2016 Ubertheme. All rights reserved.
 */
namespace Ubertheme\UbThemeHelper\App\Config\Scope;

interface ReaderPoolInterface
{
    /**
     * Retrieve reader by scope
     *
     * @param string $scopeType
     * @return ReaderInterface|null
     */
    public function getReader($scopeType);
}
