<?php

require_once realpath( __DIR__ . '/../lib' ) . '/CacheNullEngine.class.php' ;

use Cache;

/**
 * Description of CacheNullEngineTest
 *
 * @author alberto
 */
class CacheNullEngineTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Cache\CacheNullEngine
     */
    public $cache_system = null;

    public function setUp()
    {
        $this->cache_system = new Cache\CacheNullEngine();
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf( '\Cache\CacheSystem' , $this->cache_system );
    }

    public function testGetAlwaysReturnFalse()
    {
        $this->assertFalse( $this->cache_system->get( 'somekey' ) );
    }

    public function testSetAlwaysReturnFalse()
    {
        $this->assertFalse( $this->cache_system->set( 'somekey', 'somevalue' ) );
    }

    public function testExistsAlwaysReturnFalse()
    {
        $this->assertFalse( $this->cache_system->exists( 'somekey' ) );
    }

    public function testDeleteAlwaysReturnFalse()
    {
        $this->assertFalse( $this->cache_system->delete( 'somekey' ) );
    }
}

?>
