<?php

ini_set( 'display_errors', true );

require_once __DIR__ . '/../../lib/TwitterAuthAdapter.class.php';
require_once __DIR__ . '/../../twitteroauth/twitteroauth/twitteroauth.php';

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
        $this->twitter_adapter = new TwitterAuthAdapter( 'key', 'secret' );
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
     * @todo Refactoring three next methods.
     */
    public function testGetWithEmptyParameters()
    {
        $this->setExpectedException( '\RuntimeException' );
        $this->twitter_adapter->get( null );
    }

    public function testGet()
    {
        $request_method = 'followers/ids';

        $twitter_oauth_mock = $this->getMock(
            '\TwitterOAuth',
            array( 'get' ),
            array(),
            '',
            false
        );
        $twitter_oauth_mock->expects( $this->once() )
            ->method( 'get' )
            ->with( $this->equalTo( $request_method ) )
            ->will( $this->returnValue( true ) );

        $this->twitter_adapter->setAuthObject( $twitter_oauth_mock );
        $expecte_return = $this->twitter_adapter->get( $request_method );
        $this->assertTrue( $expecte_return );
    }

    public function testGetWithArguments()
    {
        $request_method = 'followers/ids';
        $request_params = array( 'screen_name' => 'somescreenname' );

        $twitter_oauth_mock = $this->getMock(
            '\TwitterOAuth',
            array( 'get' ),
            array(),
            '',
            false
        );
        $twitter_oauth_mock->expects( $this->once() )
            ->method( 'get' )
            ->with
            (
                $this->equalTo( $request_method ),
                $this->equalTo( $request_params )
            )
            ->will( $this->returnValue( true ) );

        $this->twitter_adapter->setAuthObject( $twitter_oauth_mock );
        $expecte_return = $this->twitter_adapter->get( $request_method, $request_params );
        $this->assertTrue( $expecte_return );
    }

}

?>
