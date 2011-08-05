<?php

namespace   Adapter\Twitter;
use         Cache;

require_once realpath( __DIR__ . '/..' ) . '/twitteroauth/twitteroauth/twitteroauth.php';
require_once realpath( __DIR__ . '/../../../cache/lib' ) . '/CacheFactory.class.php';

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
     * Last customer key that object used.
     *
     * @var string
     */
    public $last_customer_key = null;

    /**
     * Last user password that object used.
     *
     * @var string
     */
    public $last_user_password = null;

    /**
     * Not cacheable actions.
     *
     * @var array
     */
    protected $not_cacheable_actions = array( 'account/verify_credentials' );

    /**
     * System cache to use for save twitter responses on memory.
     *
     * @var \Cache\CacheSystem
     */
    protected $cache_system = null;

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
            throw new \InvalidArgumentException( 'Required parameters not founds.' );
        }

        $this->last_customer_key    = $customer_key;
        $this->last_user_password   = $user_password;
        $this->setTwitterApi( $oauth_token, $oauth_token_secret );
        $this->makeNullCacheSystem();
    }

    /**
     * Refactoring of contruct to extract method that instance twitter api oauth.
     *
     * @param string $oauth_token Temporal token to connect by means of OAuth with Twitter.
     * @param string $oauth_token_secret Secret key for OAuth.
     */
    protected function setTwitterApi( $oauth_token, $oauth_token_secret )
    {
        $this->twitter_api = new \TwitterOAuth(
            $this->last_customer_key,
            $this->last_user_password,
            $oauth_token,
            $oauth_token_secret
        );
    }

    /**
     * Returns current object of system cache.
     *
     * @return \Cache\CacheSystem
     */
    public function getCacheSystem()
    {
        return $this->cache_system;
    }

    /**
     * Set the current instance of system cache to Null type.
     */
    public function makeNullCacheSystem()
    {
        $this->cache_system = \Cache\CacheFactory::createInstance();
    }

    /**
     * Set the current instance of system cache to Apc type.
     */
    public function makeApcCacheSystem()
    {
        $this->cache_system = \Cache\CacheFactory::createInstance( \Cache\CacheFactory::TYPE_APC );
    }

    /**
     * Set system of cache from injected parameter.
     *
     * @param \Cache\CacheSystem $cache_system Injected system of cache.
     */
    public function setCacheSystem( \Cache\CacheSystem $cache_system )
    {
        $this->cache_system = $cache_system;
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
            throw new \InvalidArgumentException( 'Required parameters not found.' );
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
     * @todo cache at this point.
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
            throw new \InvalidArgumentException( 'Required parameters not found.' );
        }

        if ( !in_array( $method, $this->not_cacheable_actions ) )
        {
            $this->makeApcCacheSystem();
        }

        return $this->retrieveFromApiOrCache( $method, $parameters );
    }

    /**
     * Return the response of api from cache system first or twitter rest api else.
     *
     * @param string $method Api url to call.
     * @param array $parameters Parameters to search as screen_name or id.
     *
     * @return mixed
     */
    protected function retrieveFromApiOrCache( $method, $parameters )
    {
        $key            = base64_encode( $method . '?' . http_build_query( $parameters ) );
        $api_response   = $this->cache_system->get( $key );

        if ( false === $api_response )
        {
            $api_response = $this->twitter_api->get( $method, $parameters );
            $this->cache_system->set( $key, $api_response );
        }

        return $api_response;
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
