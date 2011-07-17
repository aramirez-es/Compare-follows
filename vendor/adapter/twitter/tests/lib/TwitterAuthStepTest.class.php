<?php

require_once __DIR__ . '/../../lib/TwitterAuthStep.class.php';
require_once ( __DIR__ . '/../../../../../silex.phar' );

ini_set( 'display_errors' , true );

use Adapter\Twitter\TwitterAuthStep;

/**
 * Fake session class to emulate session container.
 */
class FakeSessionStorageClass
{
    protected $started = false;
    protected $attributes = array();
    public function start()
    {
        $this->started = true;
    }
    public function has( $key )
    {
        return ( isset( $this->attributes[$key] ) );
    }
    public function set( $key, $value )
    {
        $this->attributes[$key] = $value;
    }
    public function get( $key )
    {
        return $this->has( $key ) ? $this->attributes[$key] : false;
    }
    public function invalidate()
    {
        $this->attributes = array();
    }
}

/**
 * Description of TwitterAuthStep
 *
 * @author alberto
 */
class TwitterAuthStepTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var TwitterAdapter
     */
    public $twitter_auth_step = null;

    public function setUp()
    {
        $session = new FakeSessionStorageClass();
        $this->twitter_auth_step = new TwitterAuthStep( $session );
    }

    public function tearDown()
    {
        $this->twitter_auth_step->close();
    }
    /**
     * @dataProvider dataProviderForIsFirstCall
     *
     * @param type $storage
     * @param type $expected_result
     */
    public function testIsFirstCall( $storage, $expected_result )
    {
        $session = new FakeSessionStorageClass();
        foreach ( $storage as $key => $value )
        {
            $session->set( $key, $value );
        }
        $this->twitter_auth_step = new TwitterAuthStep( $session );

        $this->assertEquals( $expected_result, $this->twitter_auth_step->isFirstCall() );
    }

    /**
     * @return array
     */
    public function dataProviderForIsFirstCall()
    {
        return array(
            'With empty storage'            => array( array(), true ),
            'With required storage twitter' => array(
                array(
                    'twitter.access_token'                      => microtime(),
                    'twitter.access_token.oauth_token'          => uniqid(),
                    'twitter.access_token.oauth_token_secret'   => uniqid()
                ),
                false
            ),
        );
    }

    /**
     * @dataProvider dataProviderForSetNeedSignin
     *
     * @param type $need_signin
     * @param type $expected
     */
    public function testSetNeedSignin( $need_signin, $expected )
    {
        $this->twitter_auth_step->setNeedSignin( $need_signin );
        $this->assertEquals( $expected, $this->twitter_auth_step->getNeedSignin() );
    }

    /**
     * @return array
     */
    public function dataProviderForSetNeedSignin()
    {
        return array(
            'Need signin'       => array( true, true ),
            'Need not signin'   => array( false, false )
        );
    }

    /**
     * @todo Refactoring the next two methods.
     */
    public function testClose()
    {
        $session_mock = $this->getMock( 'FakeSessionStorageClass', array( 'invalidate' ) );
        $session_mock->expects( $this->once() )
            ->method( 'invalidate' )
            ->with()
            ->will( $this->returnValue( true ) );

        $this->twitter_auth_step = new TwitterAuthStep( $session_mock );
        $this->twitter_auth_step->close();
    }

    public function testRegenerateStorage()
    {
        $methods_2_mock = array( 'invalidate', 'start' );
        $session_mock = $this->getMock( 'FakeSessionStorageClass', $methods_2_mock );
        $session_mock->expects( $this->once() )
            ->method( 'invalidate' )
            ->with()
            ->will( $this->returnValue( true ) );
        $session_mock->expects( $this->exactly( 2 ) )
            ->method( 'start' )
            ->with()
            ->will( $this->returnValue( true ) );

        $this->twitter_auth_step = new TwitterAuthStep( $session_mock );
        $this->twitter_auth_step->regenerateStorage();
    }

    /**
     * @todo Refactoring the next four methods.
     */
    public function testSetWithEmptyParams()
    {
        $this->setExpectedException( '\RuntimeException' );
        $this->twitter_auth_step->set( null, 'hasthisvalue' );
    }

    public function testGetWithEmptyParams()
    {
        $this->setExpectedException( '\RuntimeException' );
        $this->twitter_auth_step->get( null );
    }

    public function testSetAndGet()
    {
        $this->twitter_auth_step->set( 'someparameter', 'hasthisvalue' );
        $this->assertEquals( 'hasthisvalue', $this->twitter_auth_step->get( 'someparameter' ) );
    }

    public function testSetAndGetDefaultValue()
    {
        $this->assertEquals( 'defaultvalue',
            $this->twitter_auth_step->get( 'someparameter', 'defaultvalue' )
        );
    }
}

?>
