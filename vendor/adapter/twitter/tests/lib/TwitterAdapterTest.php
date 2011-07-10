<?php

ini_set( 'display_errors', true );

require_once __DIR__ . '/../../lib/TwitterAdapter.class.php';

use Adapter\Twitter\TwitterAdapter;

/**
 * Description of TwitterAdapterTest
 *
 * @author alberto
 */
class TwitterAdapterTest extends PHPUnit_Framework_TestCase
{
    public function testConstructNotConsumerKeyAndNotUserPassword()
    {
        $this->setExpectedException( '\RuntimeException' );
        $adapter = new TwitterAdapter();
    }

    public function testConstructNotUserPassword()
    {
        $this->setExpectedException( '\RuntimeException' );
        $adapter = new TwitterAdapter();
    }
}

?>
