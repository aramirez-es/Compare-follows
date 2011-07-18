<?php

require_once __DIR__ . '/../../lib/TwitterAuthProxy.class.php';
require_once __DIR__ . '/../../lib/TwitterAuthAdapter.class.php';
require_once __DIR__ . '/../../lib/TwitterAuthStep.class.php';

use Adapter\Twitter;

/**
 * Description of TwitterAuthProxyTest
 *
 * @author alberto
 */
class TwitterAuthProxyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Twitter\TwitterAuthProxy
     */
    protected $twitter_proxy = null;

    public function setUp()
    {
        $mock_session       = $this->getMock( '\stdClass', array( 'start' ) );
        $twitter_adapter    = new Twitter\TwitterAuthAdapter( 'customerkey', 'pass' );
        $twitter_steps      = new Twitter\TwitterAuthStep( $mock_session );

        $this->twitter_proxy = new Twitter\TwitterAuthProxy( $twitter_adapter, $twitter_steps );
    }

    public function testConstructRequireInjection()
    {
        $this->setExpectedException( '\RuntimeException' );
        $this->twitter_proxy = new Twitter\TwitterAuthProxy( null, null );
    }

    public function testConstructWithClassInjected()
    {
        $mock_session       = $this->getMock( '\stdClass', array( 'start' ) );
        $twitter_adapter    = new Twitter\TwitterAuthAdapter( 'customerkey', 'pass' );
        $twitter_steps      = new Twitter\TwitterAuthStep( $mock_session );

        $this->twitter_proxy = new Twitter\TwitterAuthProxy( $twitter_adapter, $twitter_steps );

        $this->assertAttributeInstanceOf(
            get_class( $twitter_adapter ),
            'twitter_adapter',
            $this->twitter_proxy
        );
        $this->assertAttributeInstanceOf(
            get_class( $twitter_steps ),
            'twitter_steps',
            $this->twitter_proxy
        );
    }

    public function testGetTokenAndSaveIt()
    {
        $adapter_mock = $this->getMock( 'TwitterAuthAdapter', array( 'getRequestToken' ) );
        $adapter_mock->expects( $this->once() )
            ->method( 'getRequestToken' )
            ->with( $this->isType( PHPUnit_Framework_Constraint_IsType::TYPE_STRING ) )
            ->will( $this->returnValue( array(
                'oauth_token'           => 'sample_token',
                'oauth_token_secret'    => 'sample_secret'
            ) ) );

        $storage_mock = $this->getMock( '\stdClass', array( 'start', 'set' ) );
        $storage_mock->expects( $this->exactly( 2 ) )
            ->method( 'set' )
            ->with(
                $this->logicalOr(
                    $this->equalTo( 'oauth_token' ),
                    $this->equalTo( 'oauth_token_secret' )
                ),
                $this->logicalOr(
                    $this->equalTo( 'sample_token' ),
                    $this->equalTo( 'sample_secret' )
                )
            )
            ->will( $this->returnValue( true ) );

        $this->twitter_proxy = new Twitter\TwitterAuthProxy( $adapter_mock, $storage_mock );
        $this->twitter_proxy->getTokenAndSaveIt( 'http://callback.url' );
    }

    public function testGetAuthorizeURLFromTokenSaved()
    {
        $storage_mock = $this->getMock( '\stdClass', array( 'start', 'get' ) );
        $storage_mock->expects( $this->once() )
            ->method( 'get' )
            ->with( $this->equalTo( 'oauth_token' ) )
            ->will( $this->returnValue( 'fakevalue' ) );

        $adapter_mock = $this->getMock( 'TwitterAuthAdapter', array( 'getAuthorizeURL' ) );
        $adapter_mock->expects( $this->once() )
            ->method( 'getAuthorizeURL' )
            ->with( $this->equalTo( 'fakevalue' ) )
            ->will( $this->returnValue( 'http://autoreize.url' ) );

        $this->twitter_proxy = new Twitter\TwitterAuthProxy( $adapter_mock, $storage_mock );
        $this->twitter_proxy->getAuthorizeURLFromTokenSaved();
    }

    public function testSaveUserAsVerifiedEmptyRequest()
    {
        $this->setExpectedException( '\RuntimeException' );
        $this->twitter_proxy->saveUserAsVerified( null );
    }

    public function testSaveUserAsVerified()
    {
        $request_mock = $this->getMock( '\stdClass', array( 'get' ) );
        $request_mock->expects( $this->once() )
            ->method( 'get' )
            ->with( $this->equalTo( 'oauth_verifier' ) )
            ->will( $this->returnValue( 'fake_verifier' ) );

        $adapter_mock = $this->getMock( 'TwitterAuthAdapter', array( 'getAccessToken' ) );
        $adapter_mock->expects( $this->once() )
            ->method( 'getAccessToken' )
            ->with( $this->equalTo( 'fake_verifier' ) )
            ->will( $this->returnValue( array(
                'oauth_token'           => 'fake_token',
                'oauth_token_secret'    => 'fake_secret'
            ) ) );

        $mock_session = $this->getMock( '\stdClass', array( 'start', 'set' ) );
        $mock_session->expects( $this->at( 0 ) )
            ->method( 'set' )
            ->with(
                $this->equalTo( 'access_token' ),
                $this->isType( PHPUnit_Framework_Constraint_IsType::TYPE_STRING )
            );
        $mock_session->expects( $this->at( 1 ) )
            ->method( 'set' )
            ->with(
                $this->equalTo( 'access_token.oauth_token' ),
                $this->equalTo( 'fake_token' )
            );
        $mock_session->expects( $this->at( 2 ) )
            ->method( 'set' )
            ->with(
                $this->equalTo( 'access_token.oauth_token_secret' ),
                $this->equalTo( 'fake_secret' )
            );
        $mock_session->expects( $this->at( 3 ) )
            ->method( 'set' )
            ->with(
                $this->equalTo( 'oauth_token' ),
                $this->equalTo( null )
            );
        $mock_session->expects( $this->at( 4 ) )
            ->method( 'set' )
            ->with(
                $this->equalTo( 'oauth_token_secret' ),
                $this->equalTo( null )
            );

        $this->twitter_proxy = new Twitter\TwitterAuthProxy( $adapter_mock, $mock_session );
        $this->twitter_proxy->saveUserAsVerified( $request_mock );
    }

    public function testRequestTokenIsSameAsSavedWithEmptyRequest()
    {
        $this->setExpectedException( '\RuntimeException' );
        $this->twitter_proxy->requestTokenIsEqualToSaved( null );
    }

    public function testRequestTokenIsSameAsSaved()
    {
        $request_mock = $this->getMock( '\stdClass', array( 'get' ) );
        $request_mock->expects( $this->exactly( 3 ) )
            ->method( 'get' )
            ->with( $this->equalTo( 'oauth_token' ) )
            ->will( $this->onConsecutiveCalls(
                null,
                'fake_token',
                'fake_token'
            ) );

        $storage_mock = $this->getMock( '\stdClass', array( 'start', 'get' ) );
        $storage_mock->expects( $this->exactly( 3 ) )
            ->method( 'get' )
            ->with( $this->equalTo( 'oauth_token' ) )
            ->will( $this->onConsecutiveCalls(
                '',
                'distinct_token',
                'fake_token'
            ) );

        $adapter_mock = $this->getMock( 'TwitterAuthAdapter' );

        $this->twitter_proxy = new Twitter\TwitterAuthProxy( $adapter_mock, $storage_mock );

        $this->AssertFalse( $this->twitter_proxy->requestTokenIsEqualToSaved( $request_mock ) );
        $this->AssertFalse( $this->twitter_proxy->requestTokenIsEqualToSaved( $request_mock ) );
        $this->AssertTrue( $this->twitter_proxy->requestTokenIsEqualToSaved( $request_mock ) );
    }
}

?>
