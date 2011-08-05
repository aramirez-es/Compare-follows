<?php

namespace Adapter\Twitter;

/**
 * Description of TwitterAuthProxy
 *
 * @author alberto
 */
class TwitterAuthProxy
{
    /**
     * Adapter of Twitter API.
     *
     * @var Twitter\TwitterAuthAdapter
     */
    public $twitter_adapter;

    /**
     * System of storage parameters.
     *
     * @var Twitter\TwitterAuthStep
     */
    public $twitter_steps;

    /**
     * Construct of class, require storage system and twitter adapter api.
     *
     * @param Twitter\TwitterAuthAdapter $twitter_adapter Instance of adapter.
     * @param Twitter\TwitterAuthStep $twitter_step Instace of storage system.
     */
    public function __construct( $twitter_adapter, $twitter_step )
    {
        if ( empty( $twitter_adapter ) || empty( $twitter_step ) )
        {
            throw new \InvalidArgumentException( 'Adapter and Storage not found.' );
        }

        $this->twitter_adapter  = $twitter_adapter;
        $this->twitter_steps    = $twitter_step;
    }

    /**
     * This method throws a request to twitter and receibe a response that save in storage.
     *
     * @param string $callback_url Url that Twitter will use to send response.
     */
    public function getTokenAndSaveIt( $callback_url )
    {
        $token = $this->twitter_adapter->getRequestToken( $callback_url );

        $this->twitter_steps->set( 'oauth_token' , $token['oauth_token'] );
        $this->twitter_steps->set( 'oauth_token_secret' , $token['oauth_token_secret'] );
    }

    /**
     * This method call to getAutorizeUrl from Twitter with previously save token.
     *
     * @return string
     */
    public function getAuthorizeURLFromTokenSaved()
    {
        return $this->twitter_adapter->getAuthorizeURL(
            $this->twitter_steps->get( 'oauth_token' )
        );
    }

    /**
     * This method get access token and save data into storage system.
     *
     * @param HttpRequest $request Request with arguments.
     */
    public function saveUserAsVerified( $request )
    {
        if ( empty( $request ) )
        {
            throw new \InvalidArgumentException( 'Request parameter not found.' );
        }

        $access_token = $this->twitter_adapter->getAccessToken(
            $request->get( 'oauth_verifier' )
        );

        $this->twitter_steps->set( 'access_token', uniqid() );
        $this->twitter_steps->set( 'access_token.oauth_token', $access_token['oauth_token'] );
        $this->twitter_steps->set(
            'access_token.oauth_token_secret',
            $access_token['oauth_token_secret']
        );
        $this->twitter_steps->set( 'oauth_token', null );
        $this->twitter_steps->set( 'oauth_token_secret', null );
    }

    /**
     * Compare one parameter of request and storage and return true if are equal, false else.
     *
     * @param HttpRequest $request Request with arguments.
     * @return boolean
     */
    public function requestTokenIsEqualToSaved( $request )
    {
        if ( empty( $request ) )
        {
            throw new \InvalidArgumentException( 'Request parameter not found.' );
        }

        $request_token = $request->get( 'oauth_token' );
        $storage_token = $this->twitter_steps->get( 'oauth_token' );

        return ( !empty( $request_token ) && ( $request_token === $storage_token ) );
    }

    /**
     * This methods call to regenerate the storage system and then set if user need sign in or not.
     *
     * @param boolean $need_signin Set if user need sing in or not.
     */
    public function regenerateStepsProcess( $need_signin = true )
    {
        if ( $need_signin )
        {
            $this->twitter_steps->regenerateStorage();
        }

        $this->twitter_steps->setNeedSignin( $need_signin );
    }

    /**
     * Re-build the adapter object with new token properties.
     *
     * @param string $rebuild_type Type of rebuild represented by a string.
     */
    public function rebuildAuthToken( $rebuild_type )
    {
        $adapter_class_name     = get_class( $this->twitter_adapter );
        $this->twitter_adapter  = new $adapter_class_name(
            $this->twitter_adapter->last_customer_key,
            $this->twitter_adapter->last_user_password,
            $this->twitter_steps->get( $rebuild_type . 'oauth_token' ),
            $this->twitter_steps->get( $rebuild_type . 'oauth_token_secret' )
        );
    }
}

?>
