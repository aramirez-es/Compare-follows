<?php

require_once realpath( __DIR__ . '/../lib' ) . '/CacheApcEngine.class.php' ;

use Cache;

/**
 * Description of CacheApcEngineTest
 *
 * @important The system directive apc.enable_cli must be on!
 *
 * @author alberto
 */
class CacheApcEngineTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Cache\CacheNullEngine
     */
    public $cache_system = null;

    public function setUp()
    {
        $this->mockValues();
        $this->cache_system = new Cache\CacheApcEngine();
    }

    protected function mockValues()
    {
        apc_store( 'keythatexists', 'fakevalue' );
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf( '\Cache\CacheSystem' , $this->cache_system );
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetWithEmptyKey()
    {
        $this->cache_system->get( '' );
    }

    public function testGetUnexistsKey()
    {
        $this->assertFalse( $this->cache_system->get( 'keythatdoesntexists' ) );
    }

    public function testGetWhenKeyExists()
    {
        $this->assertEquals( 'fakevalue', $this->cache_system->get( 'keythatexists' ) );
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSetWithEmptyParams()
    {
        $this->cache_system->set( '', '' );
    }

    public function testSetWithValidParams()
    {
        $key = 'anotherexistskey';

        $this->cache_system = $this->getMock(
            get_class( $this->cache_system ),
            array( 'delete' )
        );
        $this->cache_system->expects( $this->never() )
            ->method( 'delete' );

        $this->cache_system->set( $key, 'fakevalue2' );
        $this->assertEquals( 'fakevalue2', apc_fetch( $key ) );
    }

    public function testSetWithKeyCollision()
    {
        $key = 'keythatexists';

        $this->cache_system = $this->getMock( 'Cache\CacheApcEngine', array( 'get', 'delete' ) );
        $this->cache_system->expects( $this->once() )
            ->method( 'get' )
            ->with( $this->equalTo( $key ) )
            ->will( $this->returnValue( true ) );
        $this->cache_system->expects( $this->once() )
            ->method( 'delete' )
            ->with( $this->equalTo( $key ) )
            ->will( $this->returnValue( true ) );

        $this->cache_system->set( $key, 'fakevalue2' );
        $this->assertEquals( 'fakevalue', apc_fetch( $key ) );
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testDeleteWithEmptyKey()
    {
        $this->cache_system->delete( '' );
    }

    public function testDeleteExistsKey()
    {
        $this->cache_system->delete( 'keythatexists' );
        $this->assertFalse( apc_fetch( 'keythatexists' ) );
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testExistsInvalidArgument()
    {
        $this->cache_system->exists( '' );
    }

    public function testExistsWithUnexistsKey()
    {
        $this->assertFalse( $this->cache_system->exists( 'unexistskey' ) );
    }

    public function testExistsWithAnExistsKey()
    {
        $this->assertTrue( $this->cache_system->exists( 'keythatexists' ) );
    }

    public function tearDown()
    {
        apc_clear_cache( 'user' );
    }
}

?>
