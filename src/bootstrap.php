<?php

ini_set( 'display_errors', true );

use Adapter\Twitter;

if ( is_file( BASE_DIR . '/twitterdata.php' ) )
{
    require_once ( BASE_DIR . '/twitterdata.php' );
}
require_once ( BASE_DIR . '/silex.phar' );
require_once ( BASE_DIR . '/vendor/twig/lib/lib/Twig/Autoloader.php' );
require_once ( BASE_DIR . '/vendor/adapter/twitter/lib/TwitterAuthModel.class.php' );
require_once ( BASE_DIR . '/vendor/adapter/twitter/lib/TwitterAuthStep.class.php' );
require_once ( BASE_DIR . '/vendor/adapter/twitter/lib/TwitterAuthProxy.class.php' );

Twig_Autoloader::register();

$app = new Silex\Application();

$app->register( new Silex\Extension\SessionExtension() );
$app->register( new Silex\Extension\TwigExtension(), array(
	'twig.path'			=> ( BASE_DIR . '/views' ),
	'twig.class_path'	=> ( BASE_DIR . '/vendor/twig/lib' )
));

$app['twitter'] = $app->share( function() use ( $app )
{
    if ( !defined( 'TWITTER_CUSTOMER_KEY' ) || !defined( 'TWITTER_PASSWORD' ) )
    {
        throw new RuntimeException( 'Twitter customer key and Twitter user password required' );
    }

    $app['twitter.callback_url'] = $app['request']->getUriForPath('/receive-response-twitter');

    $twitter_step       = new Twitter\TwitterAuthStep( $app['session'] );
    $twitter_adapter    = new Twitter\TwitterAuthModel( TWITTER_CUSTOMER_KEY, TWITTER_PASSWORD );

    return new Twitter\TwitterAuthProxy( $twitter_adapter, $twitter_step );
});

?>
