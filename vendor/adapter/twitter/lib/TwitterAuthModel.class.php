<?php

namespace Adapter\Twitter;

require_once ( __DIR__ . '/TwitterAuthAdapter.class.php' );

/**
 * Description of TwitterAuthModel
 *
 * @author alberto
 */
class TwitterAuthModel extends TwitterAuthAdapter
{
    /**
     * Number of minim users to be compared.
     *
     * @var integer
     */
    const MIN_USERS_TO_COMPARE = 2;

    /**
     * Allowed types of friends to compare.
     *
     * @var array
     */
    public $allowed_friend_types = array( 'followers', 'followings' );

    /**
     * Check if current user is verify or not.
     *
     * @return stdClass
     */
    public function verifyCredentials()
    {
        return $this->get( 'account/verify_credentials' );
    }

    /**
     * This method find a username by username in twitter (screen_name) and return needed data.
     *
     * @param string $username Username to search in twitter.
     *
     * @return array if user is  found, null else
     */
    public function getUserByUsername( $username )
    {
        if ( empty( $username ) )
        {
            throw new \InvalidArgumentException( 'username must not be empty.' );
        }

        $rest_response = $this->get( 'users/show', array( 'screen_name' => $username ) );

        if ( property_exists( $rest_response, 'error' ) )
        {
            return null;
        }

        return $this->hydrateRecord( $rest_response );
    }

    /**
     * Encapsulate the out of the expected record.
     *
     * @param \stdClass $rest_response Value received from Twitter.
     * @return array
     */
    protected function hydrateRecord( \stdClass $rest_response )
    {
        return array(
            'id'            => $rest_response->id,
            'username'      => $rest_response->screen_name,
            'name'          => $rest_response->name,
            'description'   => $rest_response->description,
            'picture'       => $rest_response->profile_image_url,
            'url'           => $rest_response->url,
            'tweets'        => $rest_response->statuses_count,
            'followers'     => $rest_response->followers_count,
            'followings'    => $rest_response->friends_count,
        );
    }

    /**
     * Compare friends (followers/followings) between multiples users given.
     *
     * @param array $users
     * @param string $friend_type
     *
     * @return array
     */
    public function compareFriends( Array $users, $friend_type )
    {
        if ( self::MIN_USERS_TO_COMPARE > count( $users )
            || !in_array( $friend_type, $this->allowed_friend_types ) )
        {
            throw new \InvalidArgumentException( "Required params not found." );
        }

        $commons_friends = $this->getCommonFriends( $users, $friend_type );
        $method_to_apply = array( $this, 'hydrateRecord' );

        return ( !empty( $commons_friends ) )
            ? array_map( $method_to_apply, $commons_friends )
            : array();
    }

    /**
     *
     * @param array $users
     * @param string $friend_type Friend
     *
     * @return array
     */
    protected function getCommonFriends( Array $users, $friend_type )
    {
        $current_index      = 0;
        $num_users          = count( $users );
        $commons_friend_ids = $this->getFriendsByUsername( $users[$current_index++], $friend_type );

        do
        {
            $commons_friend_ids = array_intersect(
                $commons_friend_ids,
                $this->getFriendsByUsername( $users[$current_index++], $friend_type )
            );
        }
        while ( $current_index < $num_users );

        return $this->getUsersByMultiplesIds( $commons_friend_ids );
    }

    /**
     * Given an array with multiples user ids, search this user info.
     *
     * @param array $user_ids Array with all users to lookup.
     *
     * @return array
     */
    protected function getUsersByMultiplesIds( Array $user_ids )
    {
        $user_ids   = $this->sortCommonUsersArray( $user_ids );
        $request    = $this->get( 'users/lookup', array( 'user_id' => implode( ',', $user_ids ) ) );

        if ( !is_array( $request ) )
        {
            $request = array();
        }

        return $request;
    }

    /**
     * Order user commons ids array.
     *
     * @param array $user_ids User common ids.
     * @return array
     */
    protected function sortCommonUsersArray( Array $user_ids )
    {
        sort( $user_ids, SORT_STRING );
        return $user_ids;
    }

    /**
     * Get all friends of type given from an username given.
     *
     * @param string $username Username to search
     * @param string $friend_type Type of friends to search
     *
     * @return array
     */
    protected function getFriendsByUsername( $username, $friend_type )
    {
        return (array) $this->get(
            $this->getUrlByFriendType( $friend_type ),
            array( 'screen_name' => $username )
        );
    }

    /**
     * Given a friend type, returns a url of friends ids.
     *
     * @param string $friend_type Type of friends to search.
     * @return string
     */
    protected function getUrlByFriendType( $friend_type )
    {
        $friend_urls = array(
            'followers' => 'followers/ids',
            'followings' => 'friends/ids'
        );

        return $friend_urls[$friend_type];
    }
}

?>
