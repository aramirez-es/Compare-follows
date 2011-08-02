<?php

namespace Cache;

/**
 * Interace to define public API of cache engines.
 *
 * @author alberto
 */
interface CacheSystem
{
    /**
     * Retrieve an element of cache.
     */
    public function get( $key );

    /**
     * Set an element to cache.
     */
    public function set( $key, $value, $ttl );

    /**
     * Check if an element exists on cache.
     */
    public function exists( $key );

    /**
     * Delete an element of cache system.
     */
    public function delete( $key );
}

?>
