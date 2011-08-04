<?php

namespace Cache;

require_once 'CacheSystem.interface.php' ;
require_once 'CacheApcEngine.class.php' ;
require_once 'CacheNullEngine.class.php' ;

/**
 * Easy build of correct cache system.
 *
 * @author alberto
 */
abstract class CacheFactory
{
    /**
     * Contains the class name of cache system type Null.
     *
     * @var string
     */
    const TYPE_NULL = 'Cache\CacheNullEngine';

    /**
     * Contains the class name of cache system type Apc.
     *
     * @var string
     */
    const TYPE_APC  = 'Cache\CacheApcEngine';

    /**
     * Create and returns one instancie of given class type.
     *
     * @param string $type Name class to instantiate.
     *
     * @return CacheSystem
     */
    public static function createInstance( $type = self::TYPE_NULL )
    {
        return new $type;
    }
}

?>
