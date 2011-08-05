<?php

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception;
use Adapter\Twitter;

/**
 * Routing "/" by GET method.
 *
 * The homepage of web application.
 *
 * @return String
 */
$app->get( '/', function() use ( $app )
{
    $signed_user        = null;
    $template_2_render  = 'homepage.twig';

    if ( $app['twitter']->twitter_steps->isFirstCall() )
    {
        $app['twitter']->regenerateStepsProcess();
        $template_2_render = 'twitter/signin.twig';
    }
    else
    {
        $app['twitter']->rebuildAuthToken( Twitter\TwitterAuthStep::CONFIRMED_TOKEN );
        $signed_user = $app['twitter']->twitter_adapter->verifyCredentials();

        if ( !$app['twitter']->twitter_adapter->isResponseSuccess() )
        {
            throw new RuntimeException( 'Credentials not verified!!' );
        }
    }

	return $app['twig']->render( $template_2_render, array( 'signed_user' => $signed_user ) );
});

/**
 * Routing "/twitter-signin" by GET method.
 *
 * Page for signing in with twitter.
 *
 * @return String
 */
$app->get( '/twitter-signin', function() use ( $app )
{
    if ( $app['twitter']->twitter_steps->getNeedSignin() )
    {
        $app['twitter']->getTokenAndSaveIt( $app['twitter.callback_url'] );

        if ( $app['twitter']->twitter_adapter->isResponseSuccess() )
        {
            return $app->redirect( $app['twitter']->getAuthorizeURLFromTokenSaved() );
        }

        $app['twitter']->regenerateStepsProcess();
        return $app['twig']->render( 'twitter/error_response.twig' );
    }

    return $app->redirect( $app['request']->getBasePath() . '/' );
});

/**
 * Routing "/receive-response-twitter" by GET method.
 *
 * Page for the twitter callback.
 *
 * @return String
 */
$app->get( '/receive-response-twitter', function() use ( $app )
{
    if ( !$app['twitter']->requestTokenIsEqualToSaved( $app['request'] ) )
    {
        $app['twitter']->regenerateStepsProcess();
        return $app->redirect( $app['request']->getBasePath() . '/twitter-signin' );
    }

    $app['twitter']->rebuildAuthToken( Twitter\TwitterAuthStep::REQUESTED_TOKEN );
    $app['twitter']->saveUserAsVerified( $app['request'] );

    if ( $app['twitter']->twitter_adapter->isResponseSuccess() )
    {
        $app['twitter']->regenerateStepsProcess( false );
        return $app->redirect( $app['request']->getBasePath() . '/' );
    }

    $app['twitter']->regenerateStepsProcess();
    return $app->redirect( $app['request']->getBasePath() . '/twitter-signin' );

});

/**
 * Routing "/search-user" by POST method.
 *
 * Handle form sent and process action submited.
 *
 * @return String
 */
$app->post( '/search-user', function() use ( $app )
{
    $app['twitter']->rebuildAuthToken( Twitter\TwitterAuthStep::CONFIRMED_TOKEN );

    $search_form    = $app['request']->get( 'search' );
    $response       = $app['twitter']->twitter_adapter->getUserByUsername(
        $app->escape( $search_form['name'] )
    );

	return new Response(
        json_encode( $response ),
        ( null != $response ) ? 200 : 404,
        array( 'Content-Type' => 'application/json' )
    );
});

/**
 * Routing "/compare-users" by POST method.
 *
 * Handle form sent and process action submited.
 *
 * @return String
 */
$app->post( '/compare-users', function() use ( $app )
{
    $app['twitter']->rebuildAuthToken( Twitter\TwitterAuthStep::CONFIRMED_TOKEN );

    $compare_form   = $app['request']->get( 'compare' );
    $search_type    = $app->escape( $compare_form['type'][0] );
    $search_users   = array_filter(
        array_unique( $compare_form['users'] ),
        array( $app, 'escape' )
    );

    $response = $app['twitter']->twitter_adapter->compareFriends(
        $search_users,
        $search_type
    );

	return new Response(
        json_encode( $response ),
        200,
        array( 'Content-Type' => 'application/json' )
    );
});

return $app;

?>
