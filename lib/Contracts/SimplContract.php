<?php

namespace Simpl\Contracts;

/**
 * Base PHPSimpl Class used to control Simpl at its highest level
 *
 */
interface SimplContract
{
    /**
     * Does various Actions with the Cache
     *
     * @param string $action
     * @return bool
     */
    public function ClearCache($action);
}