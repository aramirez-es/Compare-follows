<?php

namespace Adapter\Twitter;

require_once __DIR__ . '/../twitteroauth/twitteroauth/twitteroauth.php';

/**
 * Description of TwitterAuthAdapter.
 *
 * @author alberto
 */
class TwitterAuthAdapter
{
    /**
     * Code expected to identify a success response.
     *
     * @var integer
     */
    const CODE_SUCCESS = 200;

    /**
     * Twitter API.
     *
     * @var \TwitterOAuth
     */
    protected $twitter_api = null;

    /**
     * Construct of class, check required params and init the API.
     *
     * @param string $customer_key Customer key to connect with Twitter.
     * @param string $user_password Password to connect with Twitter.
     * @param string $oauth_token Temporal token to connect by means of OAuth with Twitter.
     * @param string $oauth_token_secret Secret key for OAuth.
     */
    public function __construct( $customer_key, $user_password, $oauth_token = null,
        $oauth_token_secret = null )
    {
        if ( empty( $customer_key ) || empty( $user_password ) )
        {
            throw new \RuntimeException( 'Required parameters not founds.' );
        }

        $this->twitter_api = new \TwitterOAuth( $customer_key, $user_password );
    }

    /**
     * Returns a temporal token to can access.
     *
     * @param string $callback_url URL used by Twitter as callback.
     */
    public function getRequestToken( $callback_url )
    {
        return $this->getNextStep2SignIn( 'getRequestToken', $callback_url );
    }

    /**
     * Given an auth token for Twitter, return a URL.
     *
     * @param string $auth_token Token for sign in in Twitter.
     *
     * @return string
     */
    public function getAuthorizeURL( $auth_token )
    {
        return $this->getNextStep2SignIn( 'getAuthorizeURL', $auth_token );
    }

    /**
     * Given an auth token for Twitter, return a URL.
     *
     * @param string $auth_token Token for sign in in Twitter.
     *
     * @return string
     */
    public function getAccessToken( $access_token )
    {
        return $this->getNextStep2SignIn( 'getAccessToken', $access_token );
    }

    /**
     * Unify in only one protected method the calls needed to sign in with Twitter.
     *
     * @param string $method Method to execute.
     * @param string $parameter Execute $method with this parameter.
     * @return string
     */
    protected function getNextStep2SignIn( $method, $parameter )
    {
        if ( empty( $method ) || empty( $parameter ) )
        {
            throw new \RuntimeException( 'Required parameters not found.' );
        }

        return $this->twitter_api->$method( $parameter );
    }

    /**
     * Check if response code if success and returns true, else returns false.
     *
     * @return boolean
     */
    public function isResponseSuccess()
    {
        return ( $this->twitter_api->http_code == self::CODE_SUCCESS );
    }

    /**
     * Call to original get method of API of Twitter.
     *
     * @param string $method Method that Twitter API will call as URL.
     * @param array $parameters Parameters that will be sent to URL.
     *
     * @return mixed
     */
    public function get( $method, $parameters = array() )
    {
        if ( empty( $method ) || !is_string( $method ) )
        {
            throw new \RuntimeException( 'Required parameters not found.' );
        }

        return $this->twitter_api->get( $method, $parameters );
    }

    /**
     * Set this object as new twitter API. Basicaly used for inject mocks from UT.
     *
     * @param \TwitterOAuth $twitter_auth_api The new twitter api object
     */
    public function setAuthObject( \TwitterOAuth $twitter_auth_api )
    {
        $this->twitter_api = $twitter_auth_api;
    }
}

?>
