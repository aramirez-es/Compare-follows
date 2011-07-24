<?php

require_once __DIR__ . '/../../lib/TwitterAuthModel.class.php';

ini_set( 'display_errors', true );

use Adapter\Twitter;

/**
 * Description of TwitterAuthModelTest
 *
 * @author alberto
 */
class TwitterAuthModelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Adapter\Twitter\TwitterAuthModel
     */
    protected $twitter_model_mock = null;

    public function setUp()
    {
        $this->twitter_model_mock = $this->getMock(
            'Adapter\Twitter\TwitterAuthModel',
            array( 'get' ),
            array(),
            '',
            false
        );
    }

    public function testInstanceExtendsOf()
    {
        $this->assertInstanceOf( 'Adapter\Twitter\TwitterAuthAdapter', $this->twitter_model_mock );
    }

    public function testVerifyCredentials()
    {
        $this->twitter_model_mock->expects( $this->once() )
            ->method( 'get' )
            ->with( $this->equalTo( 'account/verify_credentials' ) );

        $this->twitter_model_mock->verifyCredentials();
    }

    public function testGetUserByUsernameInvalidArgument()
    {
        $this->setExpectedException( 'InvalidArgumentException' );
        $this->twitter_model_mock->getUserByUsername( null );
    }

    public function testGetUserByUsernameNotFound()
    {
        $find_user = '@fake_username';
        $rest_result = new \stdClass();
        $rest_result->error = 'Not found';

        $this->twitter_model_mock->expects( $this->once() )
            ->method( 'get' )
            ->with(
                $this->equalTo( 'users/show' ),
                $this->logicalAnd(
                    $this->isType( PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY ),
                    $this->arrayHasKey( 'screen_name' )
                )
            )
            ->will( $this->returnValue( $rest_result ) );

        $this->assertNull( $this->twitter_model_mock->getUserByUsername( $find_user ) );
    }

    public function testGetUserByUsernameFound()
    {
        $find_user = '@fake_username';

        $rest_result = new \stdClass();
        $rest_result->url = 'http://url.com';
        $rest_result->name = 'fakename';
        $rest_result->description = 'fake description';
        $rest_result->profile_image_url = 'http://image.com';
        $rest_result->followers_count = 200;
        $rest_result->friends_count = 100;
        $rest_result->screen_name = 'fake_username';
        $rest_result->id = 123456789;
        $rest_result->statuses_count = 550;

        $this->twitter_model_mock->expects( $this->once() )
            ->method( 'get' )
            ->will( $this->returnValue( $rest_result ) );

        $user_given     = $this->twitter_model_mock->getUserByUsername( $find_user );
        $user_expected  = array(
            'id'            => 123456789,
            'username'      => 'fake_username',
            'name'          => 'fakename',
            'description'   => 'fake description',
            'picture'       => 'http://image.com',
            'url'           => 'http://url.com',
            'tweets'        => 550,
            'followers'     => 200,
            'followings'    => 100,
        );

        $this->assertEquals( $user_expected, $user_given );
    }
}

?>
