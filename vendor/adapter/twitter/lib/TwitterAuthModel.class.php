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
}

?>
