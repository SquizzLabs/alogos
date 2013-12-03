<?php
$t = microtime(true);
//include_once("/var/killboard/xhprof/inc/prepend.php");

// Fire up the session!
session_cache_limiter(false);
session_start();

// Autoload Slim + Twig
require( "vendor/autoload.php" );

// Load modules + database stuff (and the config)
require( "init.php" );

// initiate the timer!
$timer = new Timer();

// Start slim and load the config from the config file
$app = new \Slim\Slim($config);

// Error handling
$app->error(function (\Exception $e) use ($app){
    include ( "view/error.php" );
});

// Check if the user has autologin turned on
if(!User::isLoggedIn()) User::autoLogin();

// Load the routes - always keep at the bottom of the require list ;)
include( "routes.php" );

// Load twig stuff
include( "twig.php" );

// Run the thing!
$app->run();
