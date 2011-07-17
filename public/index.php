<?php

/**
 * Main file of project.
 *
 * @author Alberto Ramírez.
 */

ini_set( 'display_errors', true );
error_reporting( E_ALL );

require_once ( __DIR__ . '/../silex.phar' );
require_once ( __DIR__ . '/../vendor/twig/lib/lib/Twig/Autoloader.php' );
require_once ( __DIR__ . '/../vendor/adapter/twitter/lib/TwitterAuthAdapter.class.php' );
require_once ( __DIR__ . '/../vendor/adapter/twitter/lib/TwitterAuthStep.class.php' );

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
$app->register( new Silex\Extension\SessionExtension() );
$app->register( new Silex\Extension\TwigExtension(), array(
	'twig.path'			=> ( __DIR__ . '/../views' ),
	'twig.class_path'	=> ( __DIR__ . '/../vendor/twig/lib' )
));
$app['twitter'] = $app->share( function() use ( $app )
{
    return new Twitter\TwitterAuthStep( $app['session'] );
});
$app['twitter.customer_key']    = 'dhtbnJJRdbQz3u55u9dig';
$app['twitter.user_password']   = 'ZbABdtNjXZF10DsJOcjEnnlq4qXoW00BQaZRy2YMY';
$app['twitter.callback_url']    = 'http://local.dev:8888/receive-response-twitter';
$app['twitter.adapter']         = $app->share( function () use ( $app )
{
    return new Twitter\TwitterAuthAdapter(
        $app['twitter.customer_key'],
        $app['twitter.user_password']
    );
} );

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

    if ( $app['twitter']->isFirstCall() )
    {
        $app['twitter']->regenerateStorage();
        $app['twitter']->setNeedSignin( true );

        $template_2_render = 'twitter/signin.twig';
    }
    else
    {
        var_dump ( 'no es first call' );
    }

	return $app['twig']->render( $template_2_render );
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
    if ( $app['twitter']->getNeedSignin() )
    {
        $request_token = $app['twitter.adapter']->getRequestToken( $app['twitter.callback_url'] );

        $app['twitter']->set( 'oauth_token', $request_token['oauth_token'] );
        $app['twitter']->set( 'oauth_token_secret', $request_token['oauth_token_secret'] );

        unset( $request_token );

        if( $app['twitter.adapter']->isResponseSuccess() )
        {
            /** @todo Redirec by silex system. */
            header('Location: ' . $app['twitter.adapter']->getAuthorizeURL(
                $app['twitter']->get( 'oauth_token' )
            ) );
            exit;
        }

        $app['twitter']->regenerateStorage();
        /** @todo Show a friendly message to client. */
        die( 'Inténtelo más tarde.' );
    }

    /** @todo Redirec by silex system. */
    header('Location: ' . $app['request']->getBasePath() . '/' );
    exit;
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
    $request_auth_token = $app['request']->get( 'oauth_token' );
    if ( !$app['twitter']->get( 'oauth_token' ) || empty( $request_auth_token )
        || $app['twitter']->get( 'oauth_token' ) !== $app['request']->get( 'oauth_token' ) )
    {
        $app['twitter']->regenerateStorage();
        $app['twitter']->setNeedSignin( true );

        /** @todo Redirec by silex system. */
        header('Location: ' . $app['request']->getBasePath() . '/' );
        exit;
    }

    $app['twitter.adapter'] = new Twitter\TwitterAuthAdapter(
        $app['twitter.customer_key'],
        $app['twitter.user_password'],
        $app['twitter']->get( 'oauth_token' ),
        $app['twitter']->get( 'oauth_token_secret' )
    );

    $app['twitter']->set( 'access_token', $app['twitter.adapter']->getAccessToken(
        $app['request']->get( 'oauth_verifier' )
    ));

    $app['twitter']->set( 'oauth_token', null );
    $app['twitter']->set( 'oauth_token_secret', null );

    if ( $app['twitter.adapter']->isResponseSuccess() )
    {
        $app['twitter']->set( 'verified', uniqid() );
        $app['twitter']->setNeedSignin( false );
        /** @todo Redirec by silex system. */
        header('Location: ' . $app['request']->getBasePath() . '/' );
        exit;
    }
    else
    {
        $app['twitter']->regenerateStorage();
        $app['twitter']->setNeedSignin( true );
        /** @todo Redirec by silex system. */
        header('Location: ' . $app['request']->getBasePath() . '/' );
    }
});

/**
 * Routing "/send" by POST method.
 *
 * Handle form sent and process action submited.
 *
 * @return String
 */
$app->post( '/send', function() use ( $app )
{
    $code   = 200;
    $action = $app->escape( $app['request']->get( 'action' ) );

    switch( $action )
    {
        case ACTION_SEARCH:
        {
            $response = 'Busca!';
            break;
        }
        case ACTION_COMPARE:
        {
            $response = 'Compara!';
            break;
        }
        default:
        {
            $code = 500;
            $response = 'Action not valid.';
        }
    }

	return new Response( json_encode( $response ), $code );
});

/**
 * Run the application.
 */
$app->run();