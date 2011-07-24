<?php

ini_set('display_errors', true);

/**
 * Main file of project.
 *
 * @author Alberto Ramírez.
 */

require_once ( __DIR__ . '/../silex.phar' );
require_once ( __DIR__ . '/../vendor/twig/lib/lib/Twig/Autoloader.php' );
require_once ( __DIR__ . '/../vendor/adapter/twitter/lib/TwitterAuthModel.class.php' );
require_once ( __DIR__ . '/../vendor/adapter/twitter/lib/TwitterAuthStep.class.php' );
require_once ( __DIR__ . '/../vendor/adapter/twitter/lib/TwitterAuthProxy.class.php' );

Twig_Autoloader::register();

define( 'ACTION_SEARCH', 'search' );
define( 'ACTION_COMPARE', 'compare' );

/**
 * Use namespaces.
 */
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception;
use Adapter\Twitter;

/**
 * Declarate of application Silex microframework.
 *
 * @var Silex\Application
 */
$app = new Silex\Application();

/**
 * Services and ertensions declaration.
 */
// Session.
$app->register( new Silex\Extension\SessionExtension() );
// Twig.
$app->register( new Silex\Extension\TwigExtension(), array(
	'twig.path'			=> ( __DIR__ . '/../views' ),
	'twig.class_path'	=> ( __DIR__ . '/../vendor/twig/lib' )
));
// Twitter.
$app['twitter.customer_key']    = 'dhtbnJJRdbQz3u55u9dig';
$app['twitter.user_password']   = 'ZbABdtNjXZF10DsJOcjEnnlq4qXoW00BQaZRy2YMY';
$app['twitter.callback_url']    = 'http://local.dev:8888/receive-response-twitter';
$app['twitter'] = $app->share( function() use ( $app )
{
    $twitter_step       = new Twitter\TwitterAuthStep( $app['session'] );
    $twitter_adapter    = new Twitter\TwitterAuthModel(
        $app['twitter.customer_key'],
        $app['twitter.user_password']
    );

    return new Twitter\TwitterAuthProxy( $twitter_adapter, $twitter_step );
});

/**
 * Error method to handle errors of type 404 or 500.
 *
 * @return Response
 */
$app->error( function( \Exception $error )
{
	if ( $error instanceof NotFoundHttpException )
	{
		return new Response( 'un 404 del copón!', 404 );
	}

	$code = ( $error instanceof HttpException ) ? $error->getStatusCode() : 500;
	return new Response(
		sprintf(
			'Un error 500 de la ostia! <pre>%s</pre>',
			$error->getMessage()
		),
		$code
	);
});

/**
 * Routing "/" by GET method.
 *
 * It's the homepage of web application.
 *
 * @return String
 */
$app->get( '/', function() use ( $app )
{
    $template_2_render = 'homepage.twig';

    if ( $app['twitter']->twitter_steps->isFirstCall() )
    {
        $app['twitter']->regenerateStepsProcess();
        $template_2_render = 'twitter/signin.twig';
    }
    else
    {
        $app['twitter']->rebuildAuthToken( Twitter\TwitterAuthStep::CONFIRMED_TOKEN );
    }

    $signed_user = $app['twitter']->twitter_adapter->verifyCredentials();
	return $app['twig']->render( $template_2_render, array(
        'signed_user' => $signed_user
    ) );
});

/**
 * Routing "/twitter-signin" by GET method.
 *
 * It's the page for sign in with twitter.
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
 * It's the page for twitter callback.
 *
 * @return String
 */
$app->get( 'receive-response-twitter', function() use ( $app )
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
 * Routing "/send" by POST method.
 *
 * Handle form sent and process action submited.
 *
 * @return String
 */
$app->post( '/search-user', function() use ( $app )
{
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
 * Run the application.
 */
$app->run();