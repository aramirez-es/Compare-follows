<?php

ini_set( 'display_errors', true );

require_once realpath( __DIR__ . '/../lib' ) . '/CacheFactory.class.php';

use Cache;

/**
 * Description of CacheFactoryTest
 *
 * @author alberto
 */
class CacheFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateWithEmptyParamShouldReturnsNullObject()
    {
        $this->assertInstanceOf( 'Cache\CacheNullEngine', Cache\CacheFactory::createInstance() );
    }

    public function testCreateWithTypeNullShouldReturnsNullObject()
    {
        $this->assertInstanceOf(
            'Cache\CacheNullEngine',
            Cache\CacheFactory::createInstance( \Cache\CacheFactory::TYPE_NULL )
        );
    }

    public function testCreateWithApcTypeShouldInstanciateApcObject()
    {
        $this->assertInstanceOf(
            'Cache\CacheApcEngine',
            Cache\CacheFactory::createInstance( \Cache\CacheFactory::TYPE_APC )
        );
    }
}

?>
