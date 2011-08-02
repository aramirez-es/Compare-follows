<?php

namespace Cache;

require_once 'CacheSystem.interface.php' ;

/**
 * APC system engine to caching user data.
 *
 * @todo TTL.
 * @todo overrite on set.
 *
 * @author alberto
 */
class CacheApcEngine implements CacheSystem
{
    /**
     * TTL of saved cache, 4 hours.
     *
     * @var integer
     */
    const CACHE_TTL = 14400;

    /**
     * Save if last operation was success operation or not.
     *
     * @var boolean
     */
    public $last_operation_was_success = false;

    /**
     * Check if Apc is loaded as extension.
     */
    public function __construct()
    {
        if ( !extension_loaded( 'apc' ) )
        {
            throw new \RuntimeException( 'The APC extension is not loaded!' );
        }
    }

    /**
     * Get value of key into cache system.
     *
     * @param string $key Key to retrieve from cache.
     * @return mixed
     */
    public function get( $key )
    {
        if ( empty( $key ) )
        {
            throw new \InvalidArgumentException( 'Invalid param key' );
        }

        return apc_fetch( $key, $this->last_operation_was_success );
    }

    /**
     * If key already exists delete it. Save value on key.
     *
     * @param string $key Key to save.
     * @param string $value Value to save.
     */
    public function set( $key, $value, $ttl = self::CACHE_TTL )
    {
        if ( empty( $key ) || empty( $value ) )
        {
            throw new \InvalidArgumentException( 'Required param key or value not found.' );
        }

        if ( false !== $this->get( $key ) )
        {
            $this->delete( $key );
        }

        $this->last_operation_was_success = apc_store( $key, $value, $ttl );
    }

    /**
     *
     * @todo In APC 3.1.4 apc_exists is a function, use it!
     *
     * @param string $key Key to check.
     * @return mixed
     */
    public function exists( $key )
    {
        if ( empty( $key ) )
        {
            throw new \InvalidArgumentException( 'Invalid param key' );
        }

        apc_fetch( $key, $this->last_operation_was_success );

        return $this->last_operation_was_success;
    }

    /**
     * Delete the given key form cache.
     *
     * @param string $key Key to delete
     */
    public function delete( $key )
    {
        if ( empty( $key ) )
        {
            throw new \InvalidArgumentException( 'Invalid param key' );
        }

        $this->last_operation_was_success = apc_delete( $key );
    }
}

?>
