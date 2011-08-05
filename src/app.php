<?php

require_once ( BASE_DIR . '/src/bootstrap.php' );

/**
 * Mount over / all aplication located in controllers.
 */
$app->mount( '/', require_once ( BASE_DIR . '/src/controllers.php' ) );

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

return $app;

?>
