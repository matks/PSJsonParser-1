<?php

require_once('Execution.php');
require_once('Suite.php');
require_once('Test.php');
require_once('Parser.php');

require_once('database.php');
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

$parser->init('test.json');
$parser->save();


echo "Done !";