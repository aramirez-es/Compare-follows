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
            'username'      => '@' . $rest_response->screen_name,
            'name'          => $rest_response->name,
            'description'   => $rest_response->description,
            'picture'       => $rest_response->profile_image_url,
            'url'           => $rest_response->url,
            'tweets'        => $rest_response->statuses_count,
            'followers'     => $rest_response->followers_count,
            'followings'    => $rest_response->friends_count,
        );
    }

    public function compareFriends( Array $users, $friend_type )
    {
        if ( self::MIN_USERS_TO_COMPARE > count( $users )
            || !in_array( $friend_type, $this->allowed_friend_types ) )
        {
            throw new \InvalidArgumentException( "Required params not found." );
        }

        $current_index      = 0;
        $num_users          = count( $users );
        $commons_friend_ids = $this->get(
            'followers/ids',
            array( 'screen_name' => $users[$current_index++] )
        );

        do
        {
            $commons_friend_ids = array_intersect(
                $commons_friend_ids,
                (array) $this->get( 'followers/ids', array( 'screen_name' => $users[$current_index++] ) )
            );
        }
        while ( $current_index < $num_users );

        $return = array();
        foreach ( $commons_friend_ids as $friend_id )
        {
            $return[] = $this->hydrateRecord($this->get( 'users/show', array( 'user_id' => $friend_id ) ));
        }

        return $return;
    }
}

?>
