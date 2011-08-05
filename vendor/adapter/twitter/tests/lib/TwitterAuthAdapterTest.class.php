<?php

ini_set( 'display_errors', true );

require_once realpath( __DIR__ . '/../../lib' ) . '/TwitterAuthAdapter.class.php';
require_once realpath( __DIR__ . '/../..' ) . '/twitteroauth/twitteroauth/twitteroauth.php';

use Adapter\Twitter\TwitterAuthAdapter;

/**
 * Description of TwitterAuthAdapter
 *
 * @author alberto
 */
class TwitterAuthAdapterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TwitterAuthAdapter
     */
    protected $twitter_adapter = null;

    public function setUp()
    {
        $this->twitter_adapter = new Adapter\Twitter\TwitterAuthAdapter( 'key', 'secret' );
    }

    /**
     * @dataProvider dataProviderForConstruct
     *
     * @param type $expected_exception
     * @param type $param1
     * @param type $param2
     */
    public function testConstruct( $expected_exception, $param1, $param2 )
    {
        if ( !empty( $expected_exception ) )
        {
            $this->setExpectedException( $expected_exception );
        }

        $twitter_adapter = new TwitterAuthAdapter( $param1, $param2 );

        if ( empty( $expected_exception ) )
        {
            $this->assertAttributeInstanceOf( 'TwitterOAuth', 'twitter_api', $twitter_adapter );
        }
    }

    /**
     * @return array
     */
    public function dataProviderForConstruct()
    {
        return array(
            'Without parameters' => array( '\RuntimeException', null, null ),
            'With one parameter' => array( '\RuntimeException', 'somevalue', null ),
            'With one parameter' => array( false, 'somevalue', 'othervalue' )
        );
    }

    public function testObjectShouldHasNotCacheableActionsAndCacheSystem()
    {
        $this->assertObjectHasAttribute( 'not_cacheable_actions', $this->twitter_adapter );
        $this->assertObjectHasAttribute( 'cache_system', $this->twitter_adapter );
        $this->assertInstanceOf( 'Cache\CacheNullEngine', $this->twitter_adapter->getCacheSystem() );
    }

    /**
     * @dataProvider dataProviderForMakeNullCacheSystem
     */
    public function testMakeCacheSystem( $make_method, $expected_system )
    {
        $this->twitter_adapter->$make_method();
        $this->assertInstanceOf( $expected_system, $this->twitter_adapter->getCacheSystem() );
    }

    /**
     * @return array
     */
    public function dataProviderForMakeNullCacheSystem()
    {
        return array(
            'MakeNullCacheSystem'   => array( 'makeNullCacheSystem', 'Cache\CacheNullEngine' ),
            'makeApcCacheSystem'    => array( 'makeApcCacheSystem', 'Cache\CacheApcEngine' )
        );
    }

    /**
     * @dataProvider dataProviderForWrapperSomeMethods
     *
     * @param string $method The method to test as wrapper.
     */
    public function testWrapperSomeMethods( $method, $parameter )
    {
        if ( empty( $parameter ) )
        {
            $this->setExpectedException( '\RuntimeException' );
        }
        else
        {
            $twitter_oauth_mock = $this->getMock(
                '\TwitterOAuth',
                array( $method ),
                array(),
                '',
                false
            );
            $twitter_oauth_mock->expects( $this->once() )
                ->method( $method )
                ->with( $this->equalTo( $parameter ) )
                ->will( $this->returnValue( true ) );
            $this->twitter_adapter->setAuthObject( $twitter_oauth_mock );
        }

        $this->twitter_adapter->$method( $parameter );
    }

    /**
     * @return array
     */
    public function dataProviderForWrapperSomeMethods()
    {
        return array(
            'Test method getAuthorizeURL required param not found' => array(
                'getAuthorizeURL',
                null
            ),
            'Test method getRequestToken required param not found' => array(
                'getRequestToken',
                null
            ),
            'Test method getAccessToken required param not found' => array(
                'getAccessToken',
                null
            ),
            'Test method getAuthorizeURL correct param' => array(
                'getAuthorizeURL',
                uniqid()
            ),
            'Test method getRequestToken correct param' => array(
                'getRequestToken',
                'http://my.domain.com/callback.php'
            ),
            'Test method getAccessToken correct param' => array(
                'getAccessToken',
                uniqid()
            )
        );
    }

    /**
     * @dataProvider dataProviderForIsResponseSuccess
     *
     * @param boolean $expected_response Expected response is success or not.
     */
    public function testIsResponseSuccessIsSuccess( $expected_response )
    {
        if ( $expected_response )
        {
            $twitter_auth = new \TwitterOAuth( 'cosumer_key', 'consumer_secret' );
            $twitter_auth->http_code = TwitterAuthAdapter::CODE_SUCCESS;
            $this->twitter_adapter->setAuthObject( $twitter_auth );
        }

        $this->assertEquals( $expected_response, $this->twitter_adapter->isResponseSuccess() );
    }

    /**
     * @return type
     */
    public function dataProviderForIsResponseSuccess()
    {
        return array(
            'Response is success' => array( true ),
            'Response is not success' => array( false )
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetWithEmptyParameters()
    {
        $this->twitter_adapter->get( null );
    }

    /**
     * @todo solve UT error.
     * @dataProvider dataProviderForGetWhenNotCached
     */
    public function testGet( $cached, $request_method, $call_to_api, $call_to_make_cache )
    {
        $this->configureMockTwitterAdapter();

        $request_parameters = array( 'screen_name' => 'somescreenname' );

        $null_cache         = $this->getMock( 'Cache\CacheNullEngine', array( 'get' ) );
        $null_cache_caller  = $null_cache->expects( $this->once() );
        $null_cache_caller  ->method( 'get' )->will( $this->returnValue( $cached ) );

        $twitter_oauth_mock = $this->getTwitterOauthMock();
        $expected_caller    = $twitter_oauth_mock->expects( $call_to_api );
        $expected_caller    ->method( 'get' )->will( $this->returnValue( true ) );

        if ( !$cached )
        {
            $expected_caller->with(
                $this->equalTo( $request_method ),
                $this->equalTo( $request_parameters )
            );
        }

        $this->twitter_adapter->expects( $call_to_make_cache )->method( 'makeApcCacheSystem' );
        $this->twitter_adapter->setCacheSystem( $null_cache );
        $this->twitter_adapter->setAuthObject( $twitter_oauth_mock );

        $this->assertTrue( $this->twitter_adapter->get( $request_method, $request_parameters ) );
    }

    /**
     * @return array
     */
    public function dataProviderForGetWhenNotCached()
    {
        return array(
            'when is not cached should call to api' => array(
                false,
                'account/verify_credentials',
                $this->once(),
                $this->never()
            ),
            'when is cached should not call to api' => array(
                true,
                'account/verify_credentials',
                $this->never(),
                $this->never()
            ),
            'when method is cacheable should be makeApcCacheSystem called' => array(
                true,
                'followers/ids',
                $this->never(),
                $this->once()
            )
        );
    }

    protected function getTwitterOauthMock()
    {
        return $this->getMockBuilder( '\TwitterOAuth' )
            ->disableOriginalConstructor()
            ->setMethods( array( 'get' ) )
            ->getMock();
    }

    protected function configureMockTwitterAdapter()
    {
        $this->twitter_adapter = $this->getMock(
            'Adapter\Twitter\TwitterAuthAdapter',
            array( 'makeApcCacheSystem' ),
            array( 'key', 'pass' ),
            '',
            true
        );
    }
}

?>
