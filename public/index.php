<?php

/**
 * Main file of project.
 *
 * @author Alberto Ramírez.
 */

define( 'BASE_DIR', realpath( __DIR__ . '/..' ) );

/**
 * Get the application.
 */
$app = require_once ( BASE_DIR . '/src/app.php' );

/**
 * Run the application.
 */
$app->run();