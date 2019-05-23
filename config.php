<?php

require_once('class/models/Model.php');
require_once('class/models/Execution.php');
require_once('class/models/Suite.php');
require_once('class/models/Test.php');
require_once('class/models/Parser.php');

require_once('class/Tools.php');

require_once('class/database.php');

//db properties
define('DB_TYPE','mysql');
define('DB_HOST','localhost');
define('DB_USER','simon');
define('DB_PASS','phpmyadmin');
define('DB_NAME','prestashop_results');

define('BASEURL', '/json/');

try{
    $db = Database::get();
} catch (Exception $e) {
    exit("Error when connecting to database : ".$e->getMessage()."\n");
}