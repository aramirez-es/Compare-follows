<?php

require_once realpath( __DIR__ . '/../../lib' ) . '/TwitterAuthModel.class.php';

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

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetUserByUsernameInvalidArgument()
    {
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

        $this->twitter_model_mock->expects( $this->once() )
            ->method( 'get' )
            ->will( $this->returnValue( $this->getFakeRestResult() ) );

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

    /**
     * @expectedException InvalidArgumentException
     * @dataProvider dataProviderForCompareFriendsExpectingException
     */
    public function testCompareFriendsExpectingException( $users, $type )
    {
        $this->twitter_model_mock->compareFriends( $users, $type );
    }

    public function dataProviderForCompareFriendsExpectingException()
    {
        return array(
            'Empty users, empty type' => array(
                array(),
                null
            ),
            'One user, empty type' => array(
                array( '@fakeuser' ),
                null
            ),
            'Two user, empty type' => array(
                array( '@fakeuser', '@fakeuser_2' ),
                null
            ),
            'One user, true type' => array(
                array( '@fakeuser' ),
                'followers'
            ),
            'Two user, fake type' => array(
                array( '@fakeuser', '@fakeuser_2' ),
                'faketype'
            )
        );
    }

    /**
     * @dataProvider dataProviderForCompareFriendsCallIsSuccess
     *
     * @param string $friend_type Type of friend to search (followings/followers)
     * @param string $expected_friend_url Expected url of api to call.
     */
    public function testCompareFriendsCallIsSuccess( $friend_type, $expected_friend_url )
    {
        $users = array( '@fakeuser1', '@fakeuser2', '@fakeuser3' );

        foreach ( $users as $index => $user )
        {
            $this->twitter_model_mock->expects( $this->at( $index ) )
                ->method( 'get' )
                ->with(
                    $this->equalTo( $expected_friend_url ),
                    $this->equalTo( array( 'screen_name' => $user ) )
                )
                ->will( $this->returnValue( array() ) );
        }

        $this->twitter_model_mock->compareFriends( $users, $friend_type );
    }

    /**
     * @return array
     */
    public function dataProviderForCompareFriendsCallIsSuccess()
    {
        return array(
            'Call to followers' => array( 'followers', 'followers/ids' ),
            'Call to followings' => array( 'followings', 'friends/ids' ),
        );
    }

    public function testCompareFriendsCompareCommonsIds()
    {
        $users = array(
            '@fakeuser1' => array( 6, 1, 3, 4, 5, 2 ),
            '@fakeuser2' => array( 1, 2, 4, 6, 7 ),
            '@fakeuser3' => array( 2, 6, 4, 7, 10 )
        );

        $commons_ids = array( 2, 4, 6 );
        $last_call_to_get = 0;

        foreach ( array_keys( $users ) as $index => $user )
        {
            $last_call_to_get = $index;
            $this->twitter_model_mock->expects( $this->at( $index ) )
                ->method( 'get' )
                ->will( $this->returnValue( array( 'ids' => $users[$user] ) ) );
        }

        $this->twitter_model_mock->expects( $this->at( ++$last_call_to_get ) )
            ->method( 'get' )
            ->with(
                $this->equalTo( 'users/lookup' ),
                $this->equalTo( array( 'user_id' => implode( ',', $commons_ids ) ) )
            )
            ->will( $this->returnValue( array( 0 => $this->getFakeRestResult() ) ) );


        $result = $this->twitter_model_mock->compareFriends( array_keys( $users ), 'followers' );

        $this->assertInternalType( PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $result );
        $this->assertArrayHasKey( 0, $result );
        $this->assertInternalType( PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $result[0] );
    }

    protected function getFakeRestResult()
    {
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

        return $rest_result;
    }
}

?>
