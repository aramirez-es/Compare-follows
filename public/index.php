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

Twig_Autoloader::register();

define( 'ACTION_SEARCH', 'search' );
define( 'ACTION_COMPARE', 'compare' );

/**
 * Use namespaces.
 */
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Declarate of application Silex microframework.
 *
 * @var Silex\Application
 */
$app = new Silex\Application();

/**
 * Services declaration.
 */
$app->register( new Silex\Extension\TwigExtension(), array(
	'twig.path'			=> ( __DIR__ . '/../views' ),
	'twig.class_path'	=> ( __DIR__ . '/../vendor/twig/lib' )
));

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
	return $app['twig']->render( 'homepage.twig' );
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