<?php

/**
 * Main file of project.
 *
 * @author Alberto Ramírez.
 */

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
        $app['twitter.adapter'] = new Twitter\TwitterAuthAdapter(
            $app['twitter.customer_key'],
            $app['twitter.user_password'],
            $app['twitter']->get( 'access_token.oauth_token' ),
            $app['twitter']->get( 'access_token.oauth_token_secret' )
        );
    }

    $signed_user = $app['twitter.adapter']->get( 'account/verify_credentials' );
	return $app['twig']->render( $template_2_render, array( 'signed_user' => $signed_user ) );
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
            $authorize_url = $app['twitter.adapter']->getAuthorizeURL(
                $app['twitter']->get( 'oauth_token' )
            );
            return $app->redirect( $authorize_url );
        }

        $app['twitter']->regenerateStorage();
        $app['twitter']->setNeedSignin( true );

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
    $request_auth_token = $app['request']->get( 'oauth_token' );
    $url_2_redirect     = '/twitter-signin';
    $need_signin        = true;

    if ( !$app['twitter']->get( 'oauth_token' ) || empty( $request_auth_token )
        || $app['twitter']->get( 'oauth_token' ) !== $app['request']->get( 'oauth_token' ) )
    {
        $app['twitter']->regenerateStorage();
        $app['twitter']->setNeedSignin( $need_signin );
        return $app->redirect( $app['request']->getBasePath() . $url_2_redirect );
    }

    $app['twitter.adapter'] = new Twitter\TwitterAuthAdapter(
        $app['twitter.customer_key'],
        $app['twitter.user_password'],
        $app['twitter']->get( 'oauth_token' ),
        $app['twitter']->get( 'oauth_token_secret' )
    );

    $access_token = $app['twitter.adapter']->getAccessToken(
        $app['request']->get( 'oauth_verifier' )
    );

    $app['twitter']->set( 'access_token', microtime() );
    $app['twitter']->set( 'access_token.oauth_token', $access_token['oauth_token'] );
    $app['twitter']->set( 'access_token.oauth_token_secret', $access_token['oauth_token_secret'] );
    $app['twitter']->set( 'oauth_token', null );
    $app['twitter']->set( 'oauth_token_secret', null );

    if ( $app['twitter.adapter']->isResponseSuccess() )
    {
        $app['twitter']->set( 'verified', uniqid() );
        $need_signin = false;
        $url_2_redirect = '/';
    }
    else
    {
        $app['twitter']->regenerateStorage();
    }

    $app['twitter']->setNeedSignin( $need_signin );
    return $app->redirect( $app['request']->getBasePath() . $url_2_redirect );
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