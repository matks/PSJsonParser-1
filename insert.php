<?php

require_once('class/Execution.php');
require_once('class/Suite.php');
require_once('class/Test.php');
require_once('class/Parser.php');

require_once('class/database.php');
//db properties
define('DB_TYPE','mysql');
define('DB_HOST','localhost');
define('DB_USER','simon');
define('DB_PASS','phpmyadmin');
define('DB_NAME','prestashop_results');

// make a connection to mysql here
try{
    $db = Database::get();
} catch (Exception $e) {
    exit("Error when connecting to database : ".$e->getMessage()."\n");
}
$parser = new Parser($db);

die();

$directory = 'files/';
$list = array_diff(scandir($directory), array('..', '.'));
foreach($list as $file) {
    echo "-- Inserting $file...<br />";
    $parser->init('1.7.6.x', $directory.$file);
    echo "-- $file inserted !<br /><br />";
}

echo "Done !";