<?php

namespace Cache;

require_once 'CacheSystem.interface.php' ;

/**
 * Null system engine of caching.
 *
 * @author alberto
 */
class CacheNullEngine implements CacheSystem
{
    /**
     * Null cache returns false.
     *
     * @param string $key
     * @return boolean
     */
    public function get( $key )
    {
        return false;
    }

    /**
     * Null cache doesn't set any value and always returns false.
     *
     * @param string $key Key to save
     * @param string $value Value to save
     * @return boolean
     */
    public function set( $key, $value, $ttl = 0 )
    {
        return false;
    }

    /**
     * Null cache doesn't check if key exists really, returns false.
     *
     * @param string $key Key to check.
     * @return boolean
     */
    public function exists( $key )
    {
        return false;
    }

    /**
     * Null cache doesn't delete the given key.
     *
     * @param string $key Key to check.
     * @return boolean
     */
    public function delete( $key )
    {
        return false;
    }
}

?>
